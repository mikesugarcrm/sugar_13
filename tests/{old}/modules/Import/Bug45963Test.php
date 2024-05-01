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

class Bug45963Test extends TestCase
{
    protected function setUp(): void
    {
        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }

    /**
     * @group bug45963
     */
    public function testGetImportableModules()
    {
        $modules = Importer::getImportableModules();

        $this->assertNotEmpty($modules['Contacts']);
        $this->assertNotEmpty($modules['Accounts']);
    }
}
