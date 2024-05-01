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

namespace Sugarcrm\SugarcrmTestsUnit\Console\Command\Password;

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;
use User;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Password\PasswordResetCommand
 */
class PasswordResetCommandTest extends AbstractPasswordCommandTestCase
{
    /**
     * @covers ::configure
     * @covers ::execute
     * @covers ::getUser
     * @covers ::getPassword
     * @covers ::isNotInteractive
     * @dataProvider providerTestExecute
     */
    public function testExecute(array $input, $output, User $user = null, $compliant = true, HelperSet $hs = null)
    {
        $cmd = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Console\Command\Password\PasswordResetCommand::class)
            ->setMethods(['loadUserBean', 'isPasswordCompliant'])
            ->getMock();

        $cmd->expects($this->any())
            ->method('loadUserBean')
            ->will($this->returnValue($user));

        $cmd->expects($this->any())
            ->method('isPasswordCompliant')
            ->will($this->returnValue($compliant));

        if (null !== $hs) {
            $cmd->setHelperSet($hs);
        }

        $tester = new CommandTester($cmd);
        $tester->execute($input);

        $output = self::$fixturePath . $output;
        $this->assertStringEqualsFile($output, $tester->getDisplay(true));
    }

    public function providerTestExecute()
    {
        return [
            // valid user, matching passwords, invalid rules, but skip
            [
                ['userid' => '123456', '--skip-rules' => true],
                'PasswordResetCommand_0.txt',
                $this->getUserBean([
                    'id' => '123456',
                    'user_name' => 'userx',
                    'first_name' => 'First test',
                    'last_name' => 'Last test',
                    'employee_status' => 'Active',
                ]),
                false,
                $this->getQuestionHelperMock('newpass', 'newpass'),
            ],

            // valid user, matching passwords, valid rules
            [
                ['userid' => '123456'],
                'PasswordResetCommand_0.txt',
                $this->getUserBean([
                    'id' => '123456',
                    'user_name' => 'userx',
                    'first_name' => 'First test',
                    'last_name' => 'Last test',
                    'employee_status' => 'Active',
                ]),
                true,
                $this->getQuestionHelperMock('newpass', 'newpass'),
            ],
        ];
    }

    /**
     * @covers ::configure
     * @covers ::execute
     * @covers ::getUser
     * @covers ::getPassword
     * @covers ::isNotInteractive
     * @dataProvider providerTestExecuteFailure
     */
    public function testExecuteFailure(array $input, $exception, User $user = null, $compliant = true, HelperSet $hs = null)
    {
        $this->expectExceptionMessage($exception);

        $cmd = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Console\Command\Password\PasswordResetCommand::class)
            ->setMethods(['loadUserBean', 'isPasswordCompliant'])
            ->getMock();

        $cmd->expects($this->any())
            ->method('loadUserBean')
            ->will($this->returnValue($user));

        $cmd->expects($this->any())
            ->method('isPasswordCompliant')
            ->will($this->returnValue($compliant));

        if (null !== $hs) {
            $cmd->setHelperSet($hs);
        }

        $tester = new CommandTester($cmd);
        $tester->execute($input);
    }

    public function providerTestExecuteFailure()
    {
        return [
            // invalid user id
            [
                ['userid' => '123456'],
                'User not found',
            ],

            // valid user id, but external auth only
            [
                ['userid' => '123456'],
                'Cannot set password for external authenticated users',
                $this->getUserBean([
                    'id' => '123456',
                    'external_auth_only' => 1,
                ]),
            ],

            // valid user id, but is_group
            [
                ['userid' => '123456'],
                'Cannot set password for group users',
                $this->getUserBean([
                    'id' => '123456',
                    'is_group' => 1,
                ]),
            ],

            // valid user, non matching passwords
            [
                ['userid' => '123456'],
                'Passwords do not match',
                $this->getUserBean([
                    'id' => '123456',
                    'user_name' => 'userx',
                    'first_name' => 'First test',
                    'last_name' => 'Last test',
                    'employee_status' => 'Active',
                ]),
                true,
                $this->getQuestionHelperMock('newpass', 'doesnotmatch'),
            ],

            // valid user, empty password
            [
                ['userid' => '123456'],
                'Password cannot be empty',
                $this->getUserBean([
                    'id' => '123456',
                    'user_name' => 'userx',
                    'first_name' => 'First test',
                    'last_name' => 'Last test',
                    'employee_status' => 'Active',
                ]),
                true,
                $this->getQuestionHelperMock('', 'doesnotmatch'),
            ],

            // valid user, matching passwords, invalid rules
            [
                ['userid' => '123456'],
                "Password doesn't meet complexity criteria",
                $this->getUserBean([
                    'id' => '123456',
                    'user_name' => 'userx',
                    'first_name' => 'First test',
                    'last_name' => 'Last test',
                    'employee_status' => 'Active',
                ]),
                false,
                $this->getQuestionHelperMock('newpass', 'newpass'),
            ],
        ];
    }

    /**
     * Get mocked user bean
     * @param array $data
     * @return User
     */
    protected function getUserBean(array $data = [])
    {
        $user = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['setNewPassword'])
            ->getMock();

        foreach ($data as $prop => $value) {
            $user->$prop = $value;
        }

        return $user;
    }

    /**
     * Get mocked helperset for interactive dialogs
     * @param string $pwd1
     * @param string $pwd2
     * @return HelperSet
     */
    protected function getQuestionHelperMock($pwd1, $pwd2)
    {
        $qh = $this->getMockBuilder(\Symfony\Component\Console\Helper\QuestionHelper::class)
            ->setMethods(['ask'])
            ->getMock();

        $qh->expects($this->exactly(2))
            ->method('ask')
            ->willReturnOnConsecutiveCalls($pwd1, $pwd2);

        $helperSet = new HelperSet();
        $helperSet->set($qh);

        return $helperSet;
    }
}
