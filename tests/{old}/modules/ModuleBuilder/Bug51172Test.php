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
 * Bug #51172
 * Employees |  Employees custom fields not working
 *
 * @author imatsiushyna@sugarcrm.com
 * @ticket 51172
 */
class Bug51172Test extends TestCase
{
    /**
     * @var string name custom fields
     */
    protected $field_name = 'test_bug51172';

    /**
     * @var string modules name
     */
    protected $module = 'Employees';
    protected $add_module = 'Users';

    protected function tearDown(): void
    {
        $_REQUEST = [];
        sugar_cache_clear('mod_strings.en_us');

        // Make sure to remove the test files from the filemap cache when removing
        // them since leaving them in the filemap cache breaks downstream tests
        // in the suite.
        $eLang = 'custom/modules/' . $this->module . '/language/en_us.lang.php';
        $uLang = 'custom/modules/' . $this->add_module . '/language/en_us.lang.php';

        if (file_exists($eLang)) {
            unlink($eLang);
        }

        if (file_exists($uLang)) {
            unlink($uLang);
        }

        SugarTestHelper::tearDown();
    }

    /**
     * @return array
     */
    public function getRequestData()
    {
        return [
            'name' => $this->field_name,
            'view_module' => $this->module,
            'label' => 'LBL_' . strtoupper($this->field_name),
            'labelValue' => $this->field_name,
        ];
    }

    /**
     * @group 51172
     * Check that the label custom fields of Employees module was saved also for Users module
     *
     * @return void
     */
    public function testSaveLabelForCustomFields()
    {
        $_REQUEST = $this->getRequestData();

        $mb = new ModuleBuilderController();
        $mb->action_SaveLabel();

        $mod_strings = return_module_language($GLOBALS['current_language'], $this->add_module);

        //assert that array $mod_strings Users module contains current label
        $this->assertArrayHasKey($_REQUEST['label'], $mod_strings);
    }
}
