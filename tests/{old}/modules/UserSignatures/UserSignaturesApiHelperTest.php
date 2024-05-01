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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

class UserSignaturesApiHelperTest extends TestCase
{
    protected $bean;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->bean = BeanFactory::newBean('UserSignatures');
        $this->bean->id = Uuid::uuid1();
    }

    protected function tearDown(): void
    {
        unset($this->bean);
        SugarTestHelper::tearDown();
    }

    public function testFormatForApi_NoDefault_IsDefaultFalse()
    {
        $helper = new UserSignaturesApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $data = $helper->formatForApi($this->bean);
        $this->assertFalse($data['is_default']);
    }

    public function testFormatForApi_DefaultDoesNotMatchId_IsDefaultTrue()
    {
        global $current_user;
        $current_user->setPreference('signature_default', 'not_my_id');
        $helper = new UserSignaturesApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $data = $helper->formatForApi($this->bean);
        $this->assertFalse($data['is_default']);
    }

    public function testFormatForApi_DefaultMatchesId_IsDefaultTrue()
    {
        global $current_user;
        $current_user->setPreference('signature_default', $this->bean->id);
        $helper = new UserSignaturesApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $data = $helper->formatForApi($this->bean);
        $this->assertTrue($data['is_default']);
    }
}
