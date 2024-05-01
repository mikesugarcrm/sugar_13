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
 * @group ApiTests
 */
class PasswordApiTest extends TestCase
{
    public $accounts;
    public $roles;
    public $unifiedSearchApi;
    public $moduleApi;
    public $serviceMock;
    public $args = [
        'email' => 'test@test.com',
        'username' => 'test',
    ];

    /**
     * @var PasswordApi
     */
    private $passwordApi;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        // Stored in SugarTestHelper:initVar and restoring in global scope each tearDown.
        $GLOBALS['sugar_config']['passwordsetting']['SystemGeneratedPasswordON'] = true;

        $this->passwordApi = new PasswordApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();

        $this->passwordApi->usr = $this->createMock('User');

        $this->passwordApi->usr->expects($this->any())->method('retrieve_user_id')->will($this->returnValue('test_id'));
        $this->passwordApi->usr->expects($this->any())->method('retrieve')->will($this->returnValue(true));

        $this->passwordApi->usr->db = $this->createMock(get_class($GLOBALS['db']));
        $this->passwordApi->usr->db->expects($this->any())->method('query')->will($this->returnValue(true));
        $this->passwordApi->usr->emailAddress = $this->createMock('EmailAddress');
        $this->passwordApi->usr->emailAddress->expects($this->any())->method('getPrimaryAddress')->will($this->returnValue($this->args['email']));

        $this->passwordApi->usr->portal_only = false;
        $this->passwordApi->usr->is_group = false;
        $this->passwordApi->usr->email1 = $this->args['email'];

        $this->passwordApi->usr->username = $this->args['username'];
    }

    protected function tearDown(): void
    {
        unset($this->passwordApi);
        SugarTestHelper::tearDown();
    }

    // test that when read only is set for every field you can still retrieve
    public function testRequestPasswordCorrect()
    {
        $this->passwordApi->usr->expects($this->any())->method('sendEmailForPassword')->will(
            $this->returnValue(
                [
                    'status' => true,
                ]
            )
        );
        $this->passwordApi->usr->expects($this->any())->method('isPrimaryEmail')->will(
            $this->returnValue(
                true
            )
        );

        $this->args['email'] = 'test@test.com';
        $result = $this->passwordApi->requestPassword($this->serviceMock, $this->args);
        $this->assertEquals($result, 1);
    }

    /**
     * Test change password link.
     */
    public function testRequestPasswordAsSystemGeneratedLink()
    {
        $this->passwordApi->usr->expects($this->any())->method('isPrimaryEmail')->will(
            $this->returnValue(true)
        );
        $this->passwordApi->usr->expects($this->once())->method('sendEmailForPassword')
            ->with(
                $GLOBALS['sugar_config']['passwordsetting']['lostpasswordtmpl'],
                $this->logicalAnd(
                    $this->arrayHasKey('url'),
                    $this->containsEqual(true), // Link.
                    $this->containsEqual('') // Password.
                )
            )
            ->will($this->returnValue(['status' => true]));

        $GLOBALS['sugar_config']['passwordsetting']['SystemGeneratedPasswordON'] = false;

        $this->passwordApi->requestPassword($this->serviceMock, $this->args);
    }

    public function testRequestPasswordAsSystemGeneratedLinkByDefault()
    {
        $this->passwordApi->usr->expects($this->any())->method('isPrimaryEmail')->will(
            $this->returnValue(true)
        );
        $this->passwordApi->usr->expects($this->once())->method('sendEmailForPassword')
            ->with(
                $GLOBALS['sugar_config']['passwordsetting']['lostpasswordtmpl'],
                $this->logicalAnd(
                    $this->arrayHasKey('url'),
                    $this->containsEqual(true), // Link.
                    $this->containsEqual('') // Password.
                )
            )
            ->will($this->returnValue(['status' => true]));

        unset($GLOBALS['sugar_config']['passwordsetting']['SystemGeneratedPasswordON']);

        $this->passwordApi->requestPassword($this->serviceMock, $this->args);
    }

    public function testMissingParamException()
    {
        unset($this->args['email']);

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->passwordApi->requestPassword($this->serviceMock, $this->args);
    }

    public function testEmptyParam()
    {
        $this->args['email'] = '';

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->passwordApi->requestPassword($this->serviceMock, $this->args);
    }

    public function testForgotPasswordException()
    {
        $this->expectException(SugarApiExceptionRequestMethodFailure::class);
        $this->passwordApi->requestPassword($this->serviceMock, $this->args);
    }

    /**
     * @dataProvider providerEmailData
     */
    public function testRequestException($data)
    {
        $this->passwordApi->usr->expects($this->any())->method('sendEmailForPassword')->will(
            $this->returnValue(
                [
                    'status' => $data['status'],
                    'message' => $data['message'],
                ]
            )
        );
        $this->passwordApi->usr->expects($this->any())->method('isPrimaryEmail')->will(
            $this->returnValue($data['primary'])
        );
        $this->passwordApi->usr->emailAddress = $this->createMock('EmailAddress');
        $this->passwordApi->usr->emailAddress->expects($this->any())->method('getPrimaryAddress')->will(
            $this->returnValue($data['email'])
        );
        $this->passwordApi->usr->portal_only = $data['portalOnly'];

        $this->expectException(SugarApiExceptionRequestMethodFailure::class);
        $this->passwordApi->requestPassword($this->serviceMock, $this->args);
    }

    public function providerEmailData()
    {
        return [
            [
                [
                    // Not primary email.
                    'primary' => false,
                    'status' => true,
                    'message' => 'fail',
                    'email' => $this->args['email'],
                    'portalOnly' => false,
                ],
            ],
            [
                [
                    // Status is false. Message exists.
                    'primary' => true,
                    'status' => false,
                    'message' => 'fail',
                    'email' => $this->args['email'],
                    'portalOnly' => false,
                ],
            ],
            [
                [
                    // Status is false. Message empty.
                    'primary' => true,
                    'status' => false,
                    'message' => '',
                    'email' => $this->args['email'],
                    'portalOnly' => false,
                ],
            ],
            [
                [
                    // Portal only user.
                    'primary' => true,
                    'status' => true,
                    'message' => 'fail',
                    'email' => $this->args['email'],
                    'portalOnly' => true,
                ],
            ],
            [
                [
                    // Wrong Email.
                    'primary' => true,
                    'status' => true,
                    'message' => 'fail',
                    'email' => 'bad',
                    'portalOnly' => false,
                ],
            ],
        ];
    }
}
