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
 * RS-36: Prepare OAuthTokens Module
 */
class RS36Test extends TestCase
{
    /** @var Contact */
    protected $contact = null;

    /** @var array */
    protected $beans = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->contact = SugarTestContactUtilities::createContact();
    }

    protected function tearDown(): void
    {
        /** @var $bean SugarBean */
        foreach ($this->beans as $bean) {
            $bean->mark_deleted($bean->id);
        }
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testCreateAuthorized()
    {
        $consumer = new OAuthKey();
        $consumer->name = create_guid();
        $consumer->c_key = create_guid();
        $consumer->save();
        $actual = OAuthToken::createAuthorized($consumer, $GLOBALS['current_user']);
        $this->assertInstanceOf('OAuthToken', $actual);
    }

    public function testCleanup()
    {
        $actual = OAuthToken::cleanup();
        $this->assertEmpty($actual);
    }

    public function testCleanupOldUserTokensLimit1()
    {
        $bean = new OAuthToken();
        $bean->save();
        $actual = $bean->cleanupOldUserTokens();
        $this->assertEmpty($actual);
    }

    public function testCleanupOldUserTokensLimit2()
    {
        $bean = new OAuthToken();
        $bean->save();
        $actual = $bean->cleanupOldUserTokens(2);
        $this->assertEmpty($actual);
    }

    public function testCheckNonce()
    {
        $actual = OAuthToken::checkNonce(create_guid(), create_guid(), create_guid());
        $this->assertEquals(Zend_Oauth_Provider::OK, $actual);
    }

    public function testMarkDeleted()
    {
        $bean = new OAuthToken();
        $bean->save();
        $bean->mark_deleted($bean->id);
        $actual = $bean->retrieve($bean->id);
        $this->assertEmpty($actual);
    }

    public function testDeleteByConsumer()
    {
        $actual = OAuthToken::deleteByConsumer(create_guid());
        $this->assertEmpty($actual);
    }

    public function testDeleteByUser()
    {
        $actual = OAuthToken::deleteByUser(create_guid());
        $this->assertEmpty($actual);
    }
}
