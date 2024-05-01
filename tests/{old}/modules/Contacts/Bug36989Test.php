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

class Bug36989Test extends TestCase
{
    private $module = 'Contacts';
    private $searchFieldsBackup;
    private $customSearchFields;
    private $customSearchdefs;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('files');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');

        SugarTestHelper::saveFile('custom/modules/Contacts/metadata/SearchFields.php');
        if (file_exists('custom/modules/Contacts/metadata/SearchFields.php')) {
            unlink('custom/modules/Contacts/metadata/SearchFields.php');
        }

        SugarTestHelper::saveFile('modules/Contacts/metadata/SearchFields.php');
        file_put_contents('modules/Contacts/metadata/SearchFields.php', '<?php $searchFields[\'Contacts\'] = array(\'test\' => array());');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testOverrideSearchFields()
    {
        $list = new ViewList();
        $list->module = 'Contacts';
        $list->seed = new Contact();
        $list->prepareSearchForm();
        $this->assertTrue(isset($list->searchForm->searchFields['test']));
    }
}
