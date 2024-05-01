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
 * @coversDefaultClass \commentlog
 */
class CommentLogTest extends TestCase
{
    /**
     * @var SugarBean The CommentLog SugarBean
     */
    private $bean;

    /**
     * @var array Stores the id of the created commentlog
     */
    private static $created_commentlogs;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function setUp(): void
    {
        $this->bean = BeanFactory::newBean('CommentLog');
        self::$created_commentlogs = [];
    }

    protected function tearDown(): void
    {
        $this->removeCommentLog();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
    }

    /**
     * Test that comment log is not importable
     */
    public function testNotImportable()
    {
        $this->assertFalse($this->bean->importable);
    }

    /**
     * Removes the commentlog in $created_commentlogs
     */
    public function removeCommentLog()
    {
        $db = DBManagerFactory::getInstance();
        $ids = "'" . implode("','", self::$created_commentlogs) . "'";

        $db->query("DELETE FROM commentlog WHERE id IN ($ids)");
        $db->query("DELETE FROM commentlog_rel WHERE commentlog_id IN ($ids)");
    }

    /**
     * Checks whether the fields are there in the bean->field_defs
     * @dataProvider CheckCommentLogBeanProvider
     * @param SugarBean $bean The $bean to test against
     * @param string $property The property we would like to check
     */
    public function testCheckCommentLogBean(SugarBean $bean, string $property)
    {
        $this->assertArrayHasKey($property, $bean->field_defs);
    }

    public function CheckCommentLogBeanProvider()
    {
        $bean = BeanFactory::getBean('CommentLog');

        return [
            [$bean, 'id'],
            [$bean, 'date_entered'],
            [$bean, 'entry'],
        ];
    }

    /**
     * Check if the database table setup for commentlog module is setup
     * correctly
     */
    public function testCheckCommentLogModuleDBTableSetup()
    {
        $this->assertTrue(DBManagerFactory::getInstance()->tableExists('commentlog')); // verify that the table exists
    }

    /**
     * Checks whether the $required field is in $reality
     * @param string $required The required field name
     * @param array $reality The actual list of field names in commentlog DB
     * @dataProvider CheckCommentLogDBFieldSetupProvider
     */
    public function testCheckCommentLogDBFieldSetup(string $required, array $reality)
    {
        $this->assertArrayHasKey($required, $reality);
    }

    public function CheckCommentLogDBFieldSetupProvider()
    {
        $db = DBManagerFactory::getInstance();
        $columns = $db->get_columns('commentlog');

        return [
            ['id', $columns],
            ['date_entered', $columns],
            ['entry', $columns],
        ];
    }

    /**
     * Tests whether setModule works correctly
     * @param string $module The module to set in commentlog
     * @param bool $setted Whether $module should be set in the bean or not
     * @dataProvider SetModuleProvider
     */
    public function testSetModule(string $module, bool $setted)
    {
        $result = $this->bean->setModule($module);

        $this->assertEquals($result, $setted);

        if ($setted) {
            $this->assertEquals($this->bean->module, $module);
        }
    }

    public function SetModuleProvider()
    {
        return [
            ['Accounts', true,],
            ['Bugs', true,],
            ["I would be suprised If I'm a module", false,],
        ];
    }

    /**
     * Cases when only the bean has the information about parent module
     */
    public function BeanHasModuleProvider()
    {
        return [
            [
                'Meetings',
                [
                    'commentlog' => 'The is a well grown bean',
                ],
            ],
        ];
    }

    /**
     * Cases when both bean and $params has info anout parent module
     */
    public function ParamsHasModuleProvider()
    {
        return [
            [
                'Bugs',
                [
                    'module' => 'Bugs',
                    'commentlog' => 'This bean, has a weakness',
                ],
            ],
        ];
    }

    public function testgetParentRecord()
    {
        $record = SugarTestMeetingUtilities::createMeeting();
        $commentlog_field = new SugarFieldCommentLog('commentlog');

        $commentlog_field->apiSave($record, ['commentlog' => 'watashigakita!!'], 'commentlog', []);

        $record->load_relationship('commentlog_link');
        $commentlog_beans = $record->commentlog_link->getBeans();

        self::$created_commentlogs[] = array_keys($commentlog_beans)[0];

        $bean = BeanFactory::retrieveBean('CommentLog', array_keys($commentlog_beans)[0]);

        $actual = $bean->getParentRecord();

        $this->assertEquals($record->id, $actual['record']);
        $this->assertEquals($record->getModuleName(), $actual['module']);
    }
}
