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


/**
 * Bug #43339
 * get_entries_count doesn't work with custom fields
 *
 * @ticket 43339
 */
class GetEntriesCustomTest extends SOAPTestCase
{
    private $moduleName = 'Contacts';
    private $customFieldName = 'test_custom_c';
    private $field;
    private $df;

    protected $session = [];

    protected function setUp(): void
    {
        parent::setUp();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';

        require_once 'modules/DynamicFields/FieldCases.php';
        $this->field = get_widget('varchar');
        $this->field->id = $this->moduleName . $this->customFieldName;
        $this->field->name = $this->customFieldName;
        $this->field->comments = null;
        $this->field->help = null;
        $this->field->custom_module = $this->moduleName;
        $this->field->type = 'varchar';
        $this->field->label = 'LBL_' . strtoupper($this->customFieldName);
        $this->field->len = 255;
        $this->field->required = 0;
        $this->field->default_value = '';
        $this->field->date_modified = '2012-03-14 02:23:23';
        $this->field->deleted = 0;
        $this->field->audited = 0;
        $this->field->massupdate = 0;
        $this->field->duplicate_merge = 0;
        $this->field->reportable = 1;
        $this->field->importable = 'true';
        $this->field->ext1 = null;
        $this->field->ext2 = null;
        $this->field->ext3 = null;
        $this->field->ext4 = null;

        global $beanList, $beanFiles;

        $className = $beanList[$this->moduleName];
        require_once $beanFiles[$className];
        $module = new $className();

        $this->df = new DynamicField($this->moduleName);

        $this->df->setup($module);
        $this->df->addFieldObject($this->field);
    }

    /**
     * Test for soap/SoapSugarUsers.php::get_entries_count()
     *
     * @group 43339
     */
    public function testGetEntriesCountFromBasicSoap()
    {
        $this->login();
        $actualObj = $this->soapClient->get_entries_count(
            $this->sessionId,
            $this->moduleName,
            $this->customFieldName . ' LIKE \'\'',
            0
        );
        $actual = get_object_vars($actualObj);

        $this->assertNotSame(null, $actual['result_count'], 'Null value returned by get_entries_count.');
    }

    protected function tearDown(): void
    {
        $this->df->deleteField($this->field);

        SugarTestHelper::tearDown();
        unset($_SERVER['REMOTE_ADDR']);

        $this->tearDownTestUser();
    }
}
