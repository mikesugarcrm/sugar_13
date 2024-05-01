<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\DbArchiver;

use BeanFactory;
use Elastica\Exception\NotFoundException;
use EmbeddedFile;
use Document;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sugarcrm\Sugarcrm\Elasticsearch\Container;
use SugarQuery;
use SugarTestAccountUtilities;
use SugarTestCaseUtilities;
use SugarTestEmailUtilities;
use SugarTestHelper;
use SugarTestNoteUtilities;

/**
 * Class DbArchiverTest
 * @coversDefaultClass Sugarcrm\Sugarcrm\DbArchiver
 * @package Sugarcrm\Sugarcrm\DbArchiver
 */
class DbArchiverTest extends TestCase
{
    private static $db;

    protected static $dropTables = [
        'accounts_archive',
        'accounts_cstm_archive',
        'cases_archive',
        'opportunities_archive',
        'pmse_inbox_archive',
        'pmse_bpm_flow_archive',
        'pmse_bpm_thread_archive',
    ];

    public static function setUpBeforeClass(): void
    {
        static::$db = \DBManagerFactory::getInstance();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
        SugarTestAccountUtilities::createAccount('id_archive', [
            'name' => 'Test DB Archiver Account',
        ]);
        SugarTestAccountUtilities::createAccount('id_hard_delete', [
            'name' => 'Test DB Archiver Account',
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        $conn = \DBManagerFactory::getConnection();
        $sm = $conn->getSchemaManager();
        foreach (static::$dropTables as $tableToDrop) {
            if (static::$db->tableExists($tableToDrop)) {
                $sm->dropTable($tableToDrop);
            }
        }
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestHelper::tearDown();
        SugarTestHelper::tearDownCustomFields();
        parent::tearDownAfterClass();
    }

    public function tearDown(): void
    {
        if ($this->getName() === 'testArchive' || $this->getName() === 'testArchiveBean') {
            $this->tearDownAfterArchive();
        }
    }

    /**
     * Custom tear down function to be used after individual archiving tests take place
     */
    protected function tearDownAfterArchive()
    {
        // Reset archive table after this test
        $conn = \DBManagerFactory::getConnection();
        $sm = $conn->getSchemaManager();
        $sm->dropTable('accounts_archive');
    }

    /**
     * @covers ::getModule()
     */
    public function testGetModule()
    {
        $archiver = new DbArchiver('Accounts');
        $this->assertEquals('Accounts', $archiver->getModule());
    }

    public function setCreateArchiveTableProvider()
    {
        return [
            ['Accounts', 'accounts_archive', false],
            ['Accounts', 'accounts_archive', true],
            ['Opportunities', 'opportunities_archive', false],
            ['Opportunities', 'opportunities_archive', true],
        ];
    }

    /**
     * Tests a valid call to createArchiveTable
     * @covers ::createArchiveTable()
     * @dataProvider setCreateArchiveTableProvider
     */
    public function testCreateArchiveTableSuccess(string $module, string $expectedTableName, bool $exists)
    {
        // Check that archive table either does or does not exist before we try to create it
        $this->assertSame($exists, static::$db->tableExists($expectedTableName));

        // Check that calling createArchiveTable works properly
        $archiver = new DbArchiver($module);
        $archiver->createArchiveTable();
        $this->assertTrue(static::$db->tableExists($expectedTableName));
    }

    /**
     * Tests an invalid call to createArchiveTable
     * @covers ::createArchiveTable()
     */
    public function testCreateArchiveTableException()
    {
        // Check than an invalid module name will throw an exception
        $archiver = new DbArchiver('Apples');

        // Expect an exception to be thrown
        $this->expectException(RuntimeException::class);
        $archiver->createArchiveTable();
        $this->assertFalse(static::$db->tableExists('apples_archive'));
    }


    /**
     * Tests the archiving process
     * @covers ::archive($where, $type)
     * @throws RuntimeException
     * @throws \SugarQueryException
     **/
    public function testArchive()
    {
        SugarTestHelper::setUpCustomField('Accounts', [
            'name' => 'test_c',
            'type' => 'varchar',
            'len' => 1,
        ]);

        // Create fake testing accounts
        $ac1 = SugarTestAccountUtilities::createAccount('', ['name' => 'Test Account', 'test_c' => 'Test value']);
        $ac2 = SugarTestAccountUtilities::createAccount('', ['name' => 'Test Account', 'test_c' => 'Test value']);
        $ac3 = SugarTestAccountUtilities::createAccount('', ['name' => 'Test Account', 'test_c' => 'Test value']);
        $ac4 = SugarTestAccountUtilities::createAccount('', ['name' => 'Test Account', 'test_c' => 'Test value']);

        // Create the archiver
        $archiver = new DbArchiver('Accounts');
        $archiver->createArchiveTable();

        // Check that the archive table is empty
        $result = $archiver->getBean()->db->query('SELECT * FROM accounts_archive');
        $row = $archiver->getBean()->db->fetchByAssoc($result, true);
        $this->assertFalse($row);

        $result = $archiver->getBean()->db->query('SELECT * FROM accounts_cstm_archive');
        $row = $archiver->getBean()->db->fetchByAssoc($result, true);
        $this->assertFalse($row);

        $result = $archiver->getBean()->db->get_columns('accounts_cstm_archive');
        $this->assertEquals(2, count($result));

        SugarTestHelper::setUpCustomField('Accounts', [
            'name' => 'test2_c',
            'type' => 'varchar',
            'len' => 1,
        ]);

        $archiver = new DbArchiver('Accounts');
        $archiver->createArchiveTable();

        $result = $archiver->getBean()->db->get_columns('accounts_cstm_archive');
        $this->assertEquals(3, count($result));

        // Apply criteria to the archiver
        $q = new \SugarQuery();
        $w = $q->where()->equals('name', 'Test Account');
        $archiver->performProcess($w);

        // Check that the result of the query on the archive table produces the desired results
        $result = static::$db->query('SELECT id FROM accounts_archive');
        $rows = [];
        while ($row = static::$db->fetchByAssoc($result)) {
            $rows[$row['id']] = $row['id'];
        }

        $this->assertCount(4, $rows);
        $this->assertArrayHasKey($ac1->id, $rows);
        $this->assertArrayHasKey($ac2->id, $rows);
        $this->assertArrayHasKey($ac3->id, $rows);
        $this->assertArrayHasKey($ac4->id, $rows);
    }

    /**
     * Tests the archiving process when we use a bean with auto_increment field (special case)
     * @covers ::archive($where, $type)
     * @throws RuntimeException
     * @throws \SugarQueryException
     **/
    public function testArchiveWithAutoIncrementField()
    {
        // Create fake testing accounts
        $case1 = SugarTestCaseUtilities::createCase('', ['name' => 'Test Case']);

        // Create the archiver
        $archiver = new DbArchiver('Cases');
        $archiver->createArchiveTable();

        // Check that the archive table is empty
        $result = $archiver->getBean()->db->query('SELECT * FROM cases_archive');
        $row = $archiver->getBean()->db->fetchByAssoc($result, true);
        $this->assertFalse($row);

        // Apply criteria to the archiver
        $q = new \SugarQuery();
        $w = $q->where()->equals('name', 'Test Case');
        $archiver->performProcess($w);

        // Check that the result of the query on the archive table produces the desired results
        $result = static::$db->query('SELECT id FROM cases_archive');
        $rows = [];
        while ($row = static::$db->fetchByAssoc($result)) {
            $rows[$row['id']] = $row['id'];
        }

        $this->assertCount(1, $rows);
        $this->assertArrayHasKey($case1->id, $rows);
    }

    /**
     * Tests the archiving process for a single bean
     * @covers ::archiveBean($id)
     * @throws RuntimeException
     */
    public function testArchiveBean()
    {
        // Create special account for testing
        $ac1 = SugarTestAccountUtilities::createAccount('', ['name' => 'Bean Test Account']);
        $ac2 = SugarTestAccountUtilities::createAccount('', ['name' => 'Bean Test Account']);

        // create the archiver
        $archiver = new DbArchiver('Accounts');
        $archiver->createArchiveTable();

        // check the archive table is empty
        $result = $archiver->getBean()->db->query('SELECT * FROM accounts_archive');
        $row = $archiver->getBean()->db->fetchByAssoc($result, true);
        $this->assertFalse($row);

        // Retrieve the bean associated with the created account and archive it
        $bean = \BeanFactory::retrieveBean('Accounts', $ac1->id);
        $archiver->archiveBean($bean->id);
        $bean = \BeanFactory::retrieveBean('Accounts', $ac2->id);
        $archiver->archiveBean($bean->id);


        // Check that the result of the query on the archive table produces the desired results
        $result = static::$db->query('SELECT id FROM accounts_archive');

        $rows = [];
        while ($row = static::$db->fetchByAssoc($result)) {
            $rows[$row['id']] = $row['id'];
        }

        $this->assertCount(2, $rows);
        $this->assertArrayHasKey($ac1->id, $rows);
        $this->assertArrayHasKey($ac2->id, $rows);
    }


    /**
     * Tests the cascade process that is triggered when using the archiver with pmse_Inbox
     * @covers ::cascadeBpmProcess($casIds, $type)
     */
    public function testCascadeBpmProcess()
    {
        // create the beans necessary for cascading process
        $process = BeanFactory::newBean('pmse_Inbox');
        $process->cas_status = 'COMPLETED';
        $process->save();

        $cas_id = $process->cas_id;

        $flow = BeanFactory::newBean('pmse_BpmFlow');
        $flow->cas_id = $cas_id;
        $flow->save();

        $thread = BeanFactory::newBean('pmse_BpmThread');
        $thread->cas_id = $cas_id;
        $thread->save();

        // Check that the archive tables dont alrady exist
        $this->assertFalse(static::$db->tableExists('pmse_inbox_archive'));
        $this->assertFalse(static::$db->tableExists('pmse_bpm_flow_archive'));
        $this->assertFalse(static::$db->tableExists('pmse_bpm_thread_archive'));

        // Archive on the pmse_Inbox and check all cascaded tables for archiving success
        $archiver = new DbArchiver('pmse_Inbox');
        $q = new \SugarQuery();
        $w = $q->where()->in('cas_status', ['COMPLETED']);
        $archiver->performProcess($w);

        $result = static::$db->query('SELECT id FROM pmse_inbox_archive');
        $rows = [];
        while ($row = static::$db->fetchByAssoc($result)) {
            $rows[$row['id']] = $row['id'];
        }

        $this->assertCount(1, $rows);

        $result2 = static::$db->query('SELECT id FROM pmse_bpm_flow_archive');
        $rows2 = [];
        while ($row = static::$db->fetchByAssoc($result2)) {
            $rows2[$row['id']] = $row['id'];
        }

        $this->assertCount(1, $rows2);

        $result3 = static::$db->query('SELECT id FROM pmse_bpm_thread_archive');
        $rows3 = [];
        while ($row = static::$db->fetchByAssoc($result3)) {
            $rows3[$row['id']] = $row['id'];
        }

        $this->assertCount(1, $rows3);
    }

    /**
     * Tests the archiving process for Emails
     **/
    public function testEmailArchive()
    {
        $embeddedFile = new EmbeddedFile();
        $embeddedFile->name = 'test';
        $embeddedFile->file_name = 'test';
        $embeddedFile->save();
        $embeddedFileId = $embeddedFile->id;
        $file = "upload://{$embeddedFileId}";
        file_put_contents($file, 'test');

        $emailBody = <<<TEXT
'<p>test<img src="rest/v11_19/EmbeddedFiles/$embeddedFileId/file/description_html_file?force_download=0&amp;1671800634951=1&amp;platform=base" alt="" width="1200" height="879" /><br /></p>'
TEXT;

        $email = SugarTestEmailUtilities::createEmail('', [
            'description_html' => $emailBody,
        ]);
        $note = SugarTestNoteUtilities::createNote('', [
            'email_type' => 'Emails',
            'email_id' => $email->id,
        ]);
        $noteFileId = $note->id;
        $noteFile = "upload://{$noteFileId}";
        file_put_contents($noteFile, 'test');
        $email = BeanFactory::getBean('Emails', $email->id);

        $this->assertStringContainsString(
            "EmbeddedFiles/$embeddedFileId/file",
            $email->description_html
        );

        // Create the archiver
        $archiver = new DbArchiver('Emails');

        // Apply criteria to the archiver
        $q = (new \SugarQuery())->where()->equals('id', $email->id);
        $q->query->from($email);
        $this->assertNotEmpty($q->query->execute());

        $archiver->performProcess($q, \DataArchiver::PROCESS_TYPE_DELETE);

        // Check that the result of the query on the archive table produces the desired results
        $this->assertEmpty($q->query->execute());

        $q = (new \SugarQuery())->where()->equals('id', $note->id);
        $q->query->from($note);
        $this->assertEmpty($q->query->execute());

        $q = (new \SugarQuery())->where()->in('email_id', [$email->id]);
        $q->query->from(BeanFactory::getBean('EmailText'));
        $this->assertEmpty($q->query->execute());

        $q = (new \SugarQuery())->where()->in('id', [$embeddedFile->id]);
        $q->query->from(BeanFactory::getBean('EmbeddedFiles'));
        $this->assertEmpty($q->query->execute());

        $this->assertFalse(file_exists($file));
        $this->assertFalse(file_exists($noteFile));
    }


    public function testDeleteDocuments()
    {
        /** @var Document $document */
        $document = BeanFactory::newBean('Documents');
        $document->name = 'Test delete documents';
        $document->revision = 1;
        $document->save();

        $revision = $document->createRevisionBean();
        $revision->save();
        $fileName = "upload://{$revision->id}";
        file_put_contents($fileName, 'test delete document revision files');

        $documentQuery = (new SugarQuery())->where()->equals('id', $document->id);
        $documentQuery->query->from($document);
        $this->assertNotEmpty($documentQuery->query->execute(), 'document not created');

        $revisionQuery = (new SugarQuery())->where()->equals($revision->getTableName() . '.id', $revision->id);
        $revisionQuery->query->from($revision);
        $this->assertNotEmpty($revisionQuery->query->execute(), 'revision not created');

        $archiver = new DbArchiver('Documents');
        $archiver->performProcess($documentQuery, \DataArchiver::PROCESS_TYPE_DELETE);

        $this->assertEmpty($documentQuery->query->execute(), 'document not deleted');
        $this->assertEmpty($revisionQuery->query->execute(), 'revision not deleted');

        $this->assertFileDoesNotExist($fileName);
    }

    public function testRemovingESDocumentsAfterArchiving()
    {
        $esContainer = Container::getInstance();
        $this->assertTrue($esContainer->metaDataHelper->isModuleEnabled('Accounts'));

        $indexer = $esContainer->indexer;

        $accountId = 'id_archive';

        $account = BeanFactory::getBean('Accounts', $accountId);
        $this->assertTrue($indexer->indexBean($account));

        $query = (new SugarQuery())->where()->equals('id', $accountId);
        $archiver = new DbArchiver('Accounts');
        $archiver->performProcess($query);
        BeanFactory::clearCache();
        $account = BeanFactory::retrieveBean('Accounts', $accountId);

        $this->assertNull($account);

        // wait ES to be refreshed
        sleep(1);
        $index = $esContainer->indexPool->getWriteIndex('Accounts');
        $this->expectException(NotFoundException::class);
        $index->getDocument($accountId);
    }

    public function testRemovingESDocumentsAfterHardDelete()
    {
        $esContainer = Container::getInstance();
        $this->assertTrue($esContainer->metaDataHelper->isModuleEnabled('Accounts'));

        $indexer = $esContainer->indexer;

        $accountId = 'id_hard_delete';

        $account = BeanFactory::getBean('Accounts', $accountId);
        $this->assertTrue($indexer->indexBean($account));

        $query = (new SugarQuery())->where()->equals('id', $accountId);
        $archiver = new DbArchiver('Accounts');
        $archiver->performProcess($query, \DataArchiver::PROCESS_TYPE_DELETE);
        BeanFactory::clearCache();
        $account = BeanFactory::retrieveBean('Accounts', $accountId);

        $this->assertNull($account);

        // wait ES to be refreshed
        sleep(1);
        $index = $esContainer->indexPool->getWriteIndex('Accounts');
        $this->expectException(NotFoundException::class);
        $index->getDocument($accountId);
    }
}
