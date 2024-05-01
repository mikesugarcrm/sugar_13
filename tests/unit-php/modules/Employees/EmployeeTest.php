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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Employees;

use PHPUnit\Framework\TestCase;
use Employee;

/**
 * @coversDefaultClass Employee
 */
class EmployeeTest extends TestCase
{
    /**
     * @var Employee | MockObject
     */
    protected $employee;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = $this->getMockBuilder(Employee::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    public static function canBeAuthenticatedDataProvider(): array
    {
        return [
            'regular user' => [
                'userName' => 'user1',
                'externalAuthOnly' => false,
                'expected' => true,
            ],
            'SAML user' => [
                'userName' => 'saml@example.com',
                'externalAuthOnly' => true,
                'expected' => true,
            ],
            'legacy SAML user' => [
                'userName' => '',
                'externalAuthOnly' => true,
                'expected' => true,
            ],
            'employee only' => [
                'userName' => '',
                'externalAuthOnly' => false,
                'expected' => false,
            ],
        ];
    }

    /**
     * @param string $userName
     * @param bool $externalAuthOnly
     * @param bool $expected
     *
     * @dataProvider canBeAuthenticatedDataProvider
     * @covers ::canBeAuthenticated
     */
    public function testCanBeAuthenticated($userName, $externalAuthOnly, $expected)
    {
        $this->employee->user_name = $userName;
        $this->employee->external_auth_only = $externalAuthOnly;
        $this->assertEquals($expected, $this->employee->canBeAuthenticated());
    }
}
