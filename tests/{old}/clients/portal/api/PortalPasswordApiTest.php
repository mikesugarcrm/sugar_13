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
 * @coversDefaultClass PortalPasswordApi
 */
class PortalPasswordApiTest extends TestCase
{
    protected $oldConfig;
    protected $bean_id;

    protected function setUp(): void
    {
        $this->oldConfig = $GLOBALS['sugar_config'] ?? null;

        // Mock the password requirements
        $GLOBALS['sugar_config']['passwordsetting'] = [
            'minpwdlength' => 6,
            'maxpwdlength' => 20,
            'onelower' => true,
            'oneupper' => true,
            'onenumber' => true,
            'onespecial' => true,
        ];

        // Set up a mock bean_id to use for sample database entries
        $this->bean_id = '330c595a-ca8e-11e9-9c7f-6003089fe26e';

        // Add a sample valid token and a sample invalid (expired) token into
        // the users_password_link table
        $now = TimeDate::getInstance()->nowDb();
        $now = $GLOBALS['db']->convert("'$now'", 'datetime');
        $query = 'INSERT INTO users_password_link VALUES ' .
            "('good', '$this->bean_id', 'Contacts', 'fakeUser', $now, 0 , 'portal')";
        $GLOBALS['db']->query($query);
        $date = $GLOBALS['db']->convert("'1980-01-01 23:02:21'", 'datetime');
        $query = 'INSERT INTO users_password_link VALUES ' .
            "('bad', '$this->bean_id', 'Contacts', 'fakeUser', $date, 0 , 'portal')";
        $GLOBALS['db']->query($query);
    }

    protected function tearDown(): void
    {
        $GLOBALS['sugar_config'] = $this->oldConfig;

        // Clean up
        $query = "DELETE FROM users_password_link WHERE bean_id='$this->bean_id'";
        $GLOBALS['db']->query($query);
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    /**
     * @covers ::resetPassword
     * @dataProvider resetPasswordProvider
     * @param array $args the mock args to the reset password endpoint
     * @param bool $contactFound flag whether or not the test should find a matching contact
     * @param bool $shouldThrowException flag whether or not the test should throw an exception
     */
    public function testResetPassword($args, $contactFound, $shouldThrowException)
    {
        $apiMock = $this->getMockBuilder(\PortalPasswordApi::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendConfirmationEmail', 'updatePortalPassword'])
            ->getMock();
        $sbMock = $this->getMockBuilder(\ServiceBase::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contactMock = $this->getMockBuilder(\Contact::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($contactFound) {
            $apiMock->method('updatePortalPassword')
                ->willReturn($contactMock);
        }

        if ($shouldThrowException) {
            $this->expectException(SugarApiExceptionRequestMethodFailure::class);
        } else {
            $apiMock->expects($this->once())
                ->method('sendConfirmationEmail');
            $apiMock->expects($this->once())
                ->method('updatePortalPassword');
        }

        $apiMock->resetPassword($sbMock, $args);

        // Trick for checking that no exception was thrown
        if (!$shouldThrowException) {
            $this->assertTrue(true);
        }
    }

    public function resetPasswordProvider()
    {
        return [
            [
                // Null required parameters
                [
                    'resetID' => null,
                    'newPassword' => null,
                ],
                true,
                true,
            ],
            [
                // Bad password with non-expired token
                [
                    'resetID' => 'good',
                    'newPassword' => 'abc123',
                ],
                true,
                true,
            ],
            [
                // Good password with expired token
                [
                    'resetID' => 'bad',
                    'newPassword' => 'abC123!',
                ],
                true,
                true,
            ],
            [
                // Good password with non-expired token
                // Matching contact found in the system
                [
                    'resetID' => 'good',
                    'newPassword' => 'abC123!',
                ],
                true,
                false,
            ],
            [
                // Good password with non-expired token
                // Matching contact not found in the system
                [
                    'resetID' => 'good',
                    'newPassword' => 'abC123!',
                ],
                false,
                true,
            ],
        ];
    }

    /**
     * @covers ::validatePassword
     * @dataProvider validatePasswordProvider
     * @param string $input the test password input
     * @param bool $expected the expected result of the password validation
     */
    public function testValidatePassword($input, $expected)
    {
        $apiMock = $this->getMockBuilder(\PortalPasswordApi::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $actual = SugarTestReflection::callProtectedMethod(
            $apiMock,
            'validatePassword',
            [$input]
        );

        $this->assertEquals($expected, $actual);
    }

    public function validatePasswordProvider()
    {
        return [
            [null, false],
            ['abc', false],
            ['abc123', false],
            ['abC123', false],
            ['abC123!', true],
        ];
    }

    /**
     * @covers ::validateResetToken
     * @dataProvider validateResetTokenProvider
     * @param string $input the token ID to validate
     * @param string|null $expected the expected result of validating the token
     */
    public function testValidateResetToken($input, $expected)
    {
        $apiMock = $this->getMockBuilder(\PortalPasswordApi::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $actual = SugarTestReflection::callProtectedMethod(
            $apiMock,
            'validateResetToken',
            [$input]
        );

        $this->assertEquals($expected, $actual);
    }

    public function validateResetTokenProvider()
    {
        return [
            ['notAnExistingToken', null],
            ['bad', null],
            ['good', '330c595a-ca8e-11e9-9c7f-6003089fe26e'],
        ];
    }

    /**
     * @covers ::updatePortalPassword
     */
    public function testUpdatePortalPassword()
    {
        $apiMock = $this->getMockBuilder(\PortalPasswordApi::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        // Verify that updating the Portal password for a non-existing contact ID fails
        $actual = SugarTestReflection::callProtectedMethod(
            $apiMock,
            'updatePortalPassword',
            [$this->bean_id, 'newPassword']
        );

        $this->assertNull($actual);

        // Verify that updating the Portal password for an existing contact ID succeeds
        $contactMock = SugarTestContactUtilities::createContact($this->bean_id, [
            'portal_password' => 'oldPassword',
        ]);
        $oldPassword = $contactMock->portal_password;

        $actual = SugarTestReflection::callProtectedMethod(
            $apiMock,
            'updatePortalPassword',
            [$this->bean_id, 'newPassword']
        );
        $this->assertNotNull($actual);
        $this->assertInstanceOf(Contact::class, $actual);
        $this->assertNotEquals($oldPassword, $actual->portal_password);
    }
}
