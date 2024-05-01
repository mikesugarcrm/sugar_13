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
 * RS41: Prepare Help Api.
 */
class RS41Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, false]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testGetHelp()
    {
        $api = new HelpApi();
        $rest = new HelpRest();
        $rest->loadServiceDictionary('ServiceDictionaryRest');
        $result = $api->getHelp(
            $rest,
            []
        );
        $this->assertStringContainsString('API Help', $result);
    }
}

/**
 * Class for overriding protected method.
 */
class HelpRest extends SugarTestRestServiceMock
{
    public function loadServiceDictionary($dictionaryName)
    {
        $this->dict = parent::loadServiceDictionary($dictionaryName);
    }
}
