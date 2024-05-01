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

require_once 'include/utils.php';

class SugarAutomateUtilsTest extends TestCase
{
    /**
     * @var string|false
     */
    public static function setUpBeforeClass(): void
    {
        $GLOBALS['log'] = LoggerManager::getLogger();
        $GLOBALS['current_language'] = 'en_us';
        SugarTestHelper::init();

        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @covers ::listEnabledModulesEnumOptions
     */
    public function testListSmartGuideTemplatesByModule(): void
    {
        $response = listSmartGuideTemplatesByModule('Accounts');
        $values = array_values($response);

        if (count($values) >= 2) {
            $secondValue = $values[1];
            $this->assertEquals($secondValue, 'Yearly Account Planning A customer');
        }
    }

    /**
     * @covers ::listAutomateEnabledModulesEnumOptions
     */
    public function testListAutomateEnabledModulesEnumOptions(): void
    {
        $response = listAutomateEnabledModulesEnumOptions();
        $this->assertEquals($response['Contacts'], 'Contacts');
    }

    /**
     * @covers ::listTemplateAvailableModulesEnumOptions
     */
    public function testListTemplateAvailableModulesEnumOptions(): void
    {
        $response = listTemplateAvailableModulesEnumOptions();
        $this->assertEquals($response['Contacts'], 'Contacts');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }
}
