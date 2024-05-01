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
declare(strict_types=1);

use Regression\SugarCRMScenario;

final class ApiPlatformsConfigureRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return <<<'TEXT'
[BR-10419] Code Injection on ModuleInstaller through "$platformoptions" 
TEXT;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $customApiPlatforms = <<<'PAYLOAD'
["2']=0;file_put_contents('custom/shell2.php', '<?php system($_GET[\"t\"]);');$f['"]
PAYLOAD;
        $platformOptions = <<<'PAYLOAD'
{"base":{"enable_notifications":true},"mobile":{"enable_notifications":true},"portal":{"enable_notifications":true},"opi":{"enable_notifications":true},"lpi":{"enable_notifications":true},"collabspot":{"enable_notifications":true},"collabspotbackend":{"enable_notifications":false},"discover":{"enable_notifications":true},"dms":{"enable_notifications":true},"kiosk":{"enable_notifications":true},"sugarlambda":{"enable_notifications":true},"connections":{"enable_notifications":true},"2']=0;file_put_contents('custom/shell2.php', '<?php system($_GET[\"t\"]);');$f['":{"enable_notifications":true}}
PAYLOAD;

        $action = 'index.php?module=Administration&action=apiplatforms';

        $this->login('admin', 'asdf')
            ->bwcLogin();
        $this->submitForm(
            $action,
            [
                'module' => 'Administration',
                'action' => 'saveApiPlatforms',
                'custom_api_platforms' => $customApiPlatforms,
                'platformoptions' => $platformOptions,
                'to_pdf' => 1,
            ],
            $action,
            'POST',
            ['X-Requested-With' => 'XMLHttpRequest',],
        );
        $this->get($action);
        $this->get('custom/shell2.php')->expectStatusCode(404);
    }
}
