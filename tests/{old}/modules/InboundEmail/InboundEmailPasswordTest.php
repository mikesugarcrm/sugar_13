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
 * @coversDefaultClass InboundEmail
 */
class InboundEmailPasswordTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        OutboundEmailConfigurationTestHelper::backupExistingConfigurations();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        OutboundEmailConfigurationTestHelper::restoreExistingConfigurations();
    }

    public function inboundEmailPasswordProvider()
    {
        return [
            [
                'My&amp;Password',
                'My&Password',
            ],
            [
                'My&quot;Password',
                'My"Password',
            ],
            [
                'My&#039;Password',
                'My\'Password',
            ],
            [
                'My&lt;Password',
                'My<Password',
            ],
            [
                'My&gt;Password',
                'My>Password',
            ],
        ];
    }

    /**
     * Proves that encoded HTML characters are decoded when saving a password to the database.
     *
     * @covers ::save
     * @covers ::retrieve
     * @dataProvider inboundEmailPasswordProvider
     */
    public function testSaveInboundEmailWithPassword($encodedPassword, $decodedPassword)
    {
        $ie = OutboundEmailConfigurationTestHelper::createInboundEmail();
        $ie->email_password = $encodedPassword;
        $ie->save();

        $record = BeanFactory::retrieveBean('InboundEmail', $ie->id, ['use_cache' => false]);
        $this->assertSame($decodedPassword, $record->email_password);
    }
}
