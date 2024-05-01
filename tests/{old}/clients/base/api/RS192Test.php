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

use PHPUnit\Framework\TestCase;

/**
 * RS192: Prepare Export Api.
 */
class RS192Test extends TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var SugarApi
     */
    protected $recordList;

    /**
     * @var bool
     */
    protected static $encode;

    /**
     * @var string
     */
    protected $listId;

    /**
     * @var array
     */
    protected $records;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$encode = DBManagerFactory::getInstance()->getEncode();
        DBManagerFactory::getInstance()->setEncode(false);
        SugarTestHelper::setUp('current_user', [true, false]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
        DBManagerFactory::getInstance()->setEncode(self::$encode);
    }

    protected function setUp(): void
    {
        $this->api = new ExportApi();
        $this->recordList = new RecordListApi();
        $this->records = [];
        $account = SugarTestAccountUtilities::createAccount();
        array_push($this->records, $account->id);
        $account = SugarTestAccountUtilities::createAccount();
        array_push($this->records, $account->id);
        SugarTestAccountUtilities::createAccount();
    }

    protected function tearDown(): void
    {
        $this->recordList->recordListDelete(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => 'Accounts', 'record_list_id' => $this->listId]
        );
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public function testExactExport()
    {
        $result = $this->recordList->recordListCreate(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => 'Accounts', 'records' => $this->records]
        );
        $this->listId = $result['id'];
        $strCount = $this->getExportStringCount($this->listId);
        $this->assertEquals(3, $strCount);
    }

    public function testAllExport()
    {
        $result = $this->recordList->recordListCreate(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => 'Accounts', 'records' => []]
        );
        $this->listId = $result['id'];
        $strCount = $this->getExportStringCount($this->listId);
        $this->assertGreaterThan(3, $strCount);
    }

    protected function getExportStringCount($listId)
    {
        $result = $this->api->export(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => 'Accounts', 'record_list_id' => $listId]
        );
        $cnt = 0;
        foreach (explode("\r\n", $result) as $str) {
            if (!empty($str)) {
                $cnt++;
            }
        }
        return $cnt;
    }
}
