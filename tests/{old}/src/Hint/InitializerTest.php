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

class InitializerTest extends TestCase
{
    /** @var Initializer */
    private $initializer;

    protected function setUp(): void
    {
        $this->initializer = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Hint\Initializer::class)
            ->getMock();
    }

    public function testIsValidTable()
    {
        $res = SugarTestReflection::callProtectedMethod($this->initializer, 'isValidTable', [
            'users', 'Users',
        ]);

        $this->assertEquals(true, $res);
    }

    public function testGetCorrectSiteURL()
    {
        $res = SugarTestReflection::callProtectedMethod($this->initializer, 'getCorrectSiteURL', [json_encode([
            'email' => 'email@test.com',
            'timezone' => 'London',
            'siteUrl' => 'wrong site url',
        ])]);

        $correctSiteUrl = [
            'email' => 'email@test.com',
            'timezone' => 'London',
            'siteUrl' => SugarConfig::getInstance()->get('site_url'),
        ];
        $this->assertEquals($correctSiteUrl, $res);
    }
}
