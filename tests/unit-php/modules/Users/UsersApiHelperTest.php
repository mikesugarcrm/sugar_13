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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Users;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config as IdpConfig;

/**
 * @coversDefaultClass \UsersApiHelper
 */
class UsersApiHelperTest extends TestCase
{
    /**
     * @var \UsersApiHelper|MockObject
     */
    protected $userApiHelper;

    /**
     * @var \User|MockObject
     */
    protected $user;

    /**
     * @var \EmailAddress|MockObject
     */
    protected $emailAddress;

    /**
     * @var \Link2|MockObject
     */
    protected $primaryEmailLink;

    /**
     * @var IdpConfig|MockObject
     */
    protected $IdpConfig;

    protected function setUp(): void
    {
        $this->IdpConfig = $this->createMock(IdpConfig::class);
        $this->IdpConfig
            ->method('isIDMModeEnabled')
            ->willReturn(true);
        $this->IdpConfig
            ->method('getIDMModeDisabledFields')
            ->willReturn([
                'user_name' => [
                    'name' => 'user_name',
                    'idm_mode_disabled' => true,
                ],
                'first_name' => [
                    'name' => 'first_name',
                    'idm_mode_disabled' => true,
                ],
            ]);

        $this->userApiHelper = $this->getMockBuilder(\UsersApiHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdpConfig', 'sanitizeSubmittedData'])
            ->getMock();
        $this->userApiHelper->method('getIdpConfig')->willReturn($this->IdpConfig);

        $this->user = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'load_relationship'])
            ->getMock();
        $this->emailAddress = $this->createMock(\EmailAddress::class);
        $this->primaryEmailLink = $this->getMockBuilder(\Link2::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBeans', 'query'])
            ->getMock();

        $this->user->emailAddress = $this->emailAddress;
        $this->user->email_addresses_primary = $this->primaryEmailLink;
    }

    /**
     * @covers ::populateFromApi
     */
    public function testPopulateFromApiPrimaryEmailDoesNotExist()
    {
        $data = [
            'id' => 'exited_record',
            'user_name' => 'test1',
            'first_name' => 'test1',
            'description' => 'test1',
            'email' => [
                [
                    'invalid_email' => false,
                    'opt_out' => false,
                    'primary_address' => true,
                    'reply_to_address' => false,
                ],
                [
                    'email_address' => 'test1@example.com',
                    'invalid_email' => false,
                    'opt_out' => false,
                    'primary_address' => true,
                    'reply_to_address' => false,
                ],
                [
                    'email_address' => 'test2@example.com',
                    'invalid_email' => false,
                    'opt_out' => false,
                    'primary_address' => false,
                    'reply_to_address' => false,
                ],
            ],
        ];

        $this->emailAddress->expects($this->once())
            ->method('getPrimaryAddress')
            ->with($this->isInstanceOf(\SugarBean::class))
            ->willReturn('test3@example.com');
        $this->emailAddress->id = 'not_exists';
        $this->emailAddress->email_address = 'test3@example.com';
        $this->emailAddress->invalid_email = false;
        $this->emailAddress->opt_out = false;

        $this->user->expects($this->once())
            ->method('load_relationship')
            ->with('email_addresses_primary');

        $this->primaryEmailLink->expects($this->once())
            ->method('getBeans')
            ->willReturn([$this->emailAddress]);

        $this->primaryEmailLink->expects($this->once())
            ->method('query')
            ->willReturn(['rows' => [['reply_to_address' => true]]]);

        $this->user->id = 'exited_record';
        $this->user->new_with_id = false;
        $this->user->field_defs = [];

        $this->userApiHelper->expects($this->once())
            ->method('sanitizeSubmittedData')
            ->with($this->callback(function ($submittedData) {
                $this->assertArrayNotHasKey('user_name', $submittedData);
                $this->assertArrayNotHasKey('first_name', $submittedData);
                $this->assertArrayHasKey('description', $submittedData);
                $this->assertArrayHasKey('email', $submittedData);
                $isDbPrimaryExist = false;
                $isSubmittedPrimaryExist = false;
                foreach ($submittedData['email'] as $email) {
                    if ($email['email_address'] == 'test3@example.com') {
                        $isDbPrimaryExist = true;
                    }
                    if ($email['email_address'] == 'test1@example.com') {
                        $isSubmittedPrimaryExist = true;
                    }
                }
                $this->assertTrue($isDbPrimaryExist);
                $this->assertFalse($isSubmittedPrimaryExist);
                $this->assertCount(2, $submittedData['email']);
                return true;
            }))
            ->willReturn($data);

        $this->userApiHelper->populateFromApi($this->user, $data);
    }

    /**
     * @covers ::populateFromApi
     */
    public function testPopulateFromApiPrimaryEmailExistsWithoutPrimaryFlag()
    {
        $primaryAddress = 'test1@example.com';
        $data = [
            'id' => 'exited_record',
            'user_name' => 'test1',
            'first_name' => 'test1',
            'description' => 'test1',
            'email' => [
                [
                    'email_address_id' => 'exist_email',
                    'email_address' => $primaryAddress,
                    'invalid_email' => false,
                    'opt_out' => false,
                    'primary_address' => false,
                    'reply_to_address' => false,
                ],
            ],
        ];

        $this->emailAddress->expects($this->once())
            ->method('getPrimaryAddress')
            ->with($this->isInstanceOf(\SugarBean::class))
            ->willReturn($primaryAddress);

        $this->user->id = 'exited_record';
        $this->user->new_with_id = false;
        $this->user->field_defs = [];

        $this->userApiHelper->expects($this->once())
            ->method('sanitizeSubmittedData')
            ->with($this->callback(function ($submittedData) {
                $this->assertArrayNotHasKey('user_name', $submittedData);
                $this->assertArrayNotHasKey('first_name', $submittedData);
                $this->assertArrayHasKey('description', $submittedData);
                $this->assertArrayHasKey('email', $submittedData);
                $primary = array_pop($submittedData['email']);
                $this->assertTrue($primary['primary_address']);
                return true;
            }))
            ->willReturn($data);

        $this->userApiHelper->populateFromApi($this->user, $data);
    }
}
