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

require_once 'modules/Reports/config.php';

class Bug38864Test extends TestCase
{
    protected $modListHeader = null;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->modListHeader = query_module_access_list($GLOBALS['current_user']);

        sugar_mkdir('custom/modules/Reports/metadata/', null, true);

        file_put_contents(
            'custom/modules/Reports/metadata/reportmodulesdefs.php',
            "<?php
\$additionalModules[] = 'ProspectLists';
\$exemptModules[] = 'Accounts';"
        );
    }

    protected function tearDown(): void
    {
        unlink('custom/modules/Reports/metadata/reportmodulesdefs.php');
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testCustomReportmoduledefsExemptModulesIsParsed()
    {
        $modules = getAllowedReportModules($this->modListHeader, true);

        $this->assertArrayNotHasKey('Accounts', $modules);

        return $modules;
    }

    /**
     * @depends testCustomReportmoduledefsExemptModulesIsParsed
     */
    public function testCustomReportmoduledefsAdditionalModulesIsParsed($modules)
    {
        $this->assertArrayHasKey('ProspectLists', $modules);
    }
}
