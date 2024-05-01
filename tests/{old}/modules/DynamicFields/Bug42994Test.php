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

class Bug42994Test extends TestCase
{
    private $smarty;
    private $langManager;

    protected function setUp(): void
    {
        $this->smarty = new Sugar_Smarty();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->langManager = new SugarTestLangPackCreator();
        $GLOBALS['current_language'] = 'en_us';
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($this->langManager);
    }

    public function testSetLanguageStringDependant()
    {
        $this->langManager->setModString('LBL_DEPENDENT', 'XXDependentXX', 'DynamicFields');
        $this->langManager->save();
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'DynamicFields');
        $output = $this->smarty->fetch('modules/DynamicFields/templates/Fields/Forms/coreDependent.tpl');

        $this->assertStringContainsString('XXDependentXX', $output);
    }

    public function testSetLanguageStringVisible()
    {
        $this->langManager->setModString('LBL_VISIBLE_IF', 'XXVisible ifXX', 'DynamicFields');
        $this->langManager->save();
        $output = $this->smarty->fetch('modules/DynamicFields/templates/Fields/Forms/coreDependent.tpl');

        $this->assertStringContainsString('XXVisible ifXX', $output);
    }
}
