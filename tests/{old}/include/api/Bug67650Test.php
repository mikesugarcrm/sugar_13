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

class Bug67650Test extends TestCase
{
    protected $customIncludeDir = 'custom/data';
    protected $customIncludeFile = 'SugarBeanApiHelper.php';

    protected function setUp(): void
    {
        // create a custom include file
        $customIncludeFileContent = <<<EOQ
<?php
class CustomSugarBeanApiHelper
{
}
EOQ;
        if (!file_exists($this->customIncludeDir)) {
            sugar_mkdir($this->customIncludeDir, 0777, true);
        }

        file_put_contents($this->customIncludeDir . '/' . $this->customIncludeFile, $customIncludeFileContent);
        // clean cache
        unset(ApiHelper::$moduleHelpers['Campaigns']);
    }

    protected function tearDown(): void
    {
        // clean cache
        unset(ApiHelper::$moduleHelpers['Campaigns']);

        // remove the custom file
        if (file_exists($this->customIncludeDir . '/' . $this->customIncludeFile)) {
            unlink($this->customIncludeDir . '/' . $this->customIncludeFile);
        }
    }

    public function testFindCustomHelper()
    {
        $api = new RestService();
        $bean = BeanFactory::newBean('Campaigns');
        $helper = ApiHelper::getHelper($api, $bean);
        $this->assertEquals('CustomSugarBeanApiHelper', get_class($helper));
    }
}
