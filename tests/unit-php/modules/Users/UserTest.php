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

use PHPUnit\Framework\TestCase;
use SugarApiExceptionInvalidParameter;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use User;

/**
 * @coversDefaultClass \User
 */
class UserTest extends TestCase
{
    /**
     * @covers ::getLicenseTypes
     * @covers ::getAllLicenseTypes
     * @covers ::processLicenseTypes
     *
     * @dataProvider getLicenseTypesProvider
     */
    public function testGetLicenseTypes($systemLicenseTypes, $licenseType, $userName, $expected)
    {
        $userMock = $this->getMockBuilder('\User')
            ->disableOriginalConstructor()
            ->setMethods(['getAllSystemSubscriptionKeys'])
            ->getMock();

        $userMock->expects($this->any())
            ->method('getAllSystemSubscriptionKeys')
            ->willReturn($systemLicenseTypes);

        $userMock->user_name = $userName;
        $userMock->license_type = $licenseType;
        $this->assertSame($expected, $userMock->getAllLicenseTypes());
    }

    public function getLicenseTypesProvider()
    {
        return [
            'License type is empty' => [
                ['SUGAR_SELL'],
                '',
                'any_name',
                [],
            ],
            'License type is null' => [
                ['SUGAR_SELL'],
                null,
                'any_name',
                [],
            ],
            'License type is in json encoded empty arry' => [
                ['SUGAR_SELL'],
                json_encode([]),
                'any_name',
                [],
            ],
            'License type is valid' => [
                ['SUGAR_SELL', 'SUGAR_SERVE'],
                ['SUGAR_SELL', 'SUGAR_SERVE'],
                'any_name',
                ['SUGAR_SELL', 'SUGAR_SERVE'],
            ],
            'License type is valid and has empty entry' => [
                ['SUGAR_SELL', 'SUGAR_SERVE'],
                ['', 'SUGAR_SERVE'],
                'any_name',
                ['SUGAR_SERVE'],
            ],
            'License type is in json encoded format' => [
                ['SUGAR_SELL', 'SUGAR_SERVE'],
                json_encode(['SUGAR_SELL', 'SUGAR_SERVE']),
                'any_name',
                ['SUGAR_SELL', 'SUGAR_SERVE'],
            ],
            'License type is in json encoded format in single value' => [
                ['SUGAR_SELL', 'SUGAR_SERVE'],
                json_encode(['SUGAR_SELL']),
                'any_name',
                ['SUGAR_SELL'],
            ],
            'Suppport user get all flavors' => [
                ['SUGAR_SELL', 'SUGAR_SERVE'],
                json_encode(['SUGAR_SELL']),
                'SugarCRMSupport',
                ['SUGAR_SELL', 'SUGAR_SERVE'],
            ],
            'License type has bundled' => [
                [
                    'SUGAR_SELL',
                    'SUGAR_SERVE',
                    'SUGAR_SELL_PRIMIER_BUNDLE',
                    'CONNECT',
                    'HINT',
                ],
                ['SUGAR_SELL_PRIMIER_BUNDLE', 'CONNECT'],
                'any_name',
                ['SUGAR_SELL_PRIMIER_BUNDLE', 'CONNECT'],
            ],
        ];
    }

    /**
     * @covers ::processLicenseTypes
     *
     * @dataProvider processLicenseTypesExceptionProvider
     */
    public function testProcessLicenseTypesException($value)
    {
        $userMock = $this->getMockBuilder('\User')
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $userMock->processLicenseTypes($value);
    }

    public function processLicenseTypesExceptionProvider()
    {
        return [
            'input is not string or array' => [true],
            'input is string but not a valid json encoded' => ['string format'],
        ];
    }

    /**
     * @covers ::validateLicenseTypes
     *
     * @dataProvider getValidateTypesProvider
     */
    public function testValidateLicenseTypes($source, $systemLicenseTypes, $allowEmpty, $expected)
    {
        $userMock = $this->getMockBuilder('\User')
            ->disableOriginalConstructor()
            ->setMethods(['getTopLevelSystemSubscriptionKeys'])
            ->getMock();

        $userMock->expects($this->any())
            ->method('getTopLevelSystemSubscriptionKeys')
            ->willReturn($systemLicenseTypes);

        $licenseTypes = $userMock->processLicenseTypes($source);
        $this->assertSame($expected, $userMock->validateLicenseTypes($licenseTypes, $allowEmpty));
    }

    public function getValidateTypesProvider()
    {
        return [
            'License type is invalid' => [
                ['invalid_license_type'],
                ['SUGAR_SELL'],
                true,
                false,
            ],
            'License type is empty and empty license type is allowed' => [
                '',
                ['SUGAR_SELL'],
                true,
                true,
            ],
            'License type is null and empty license type is allowed' => [
                null,
                ['SUGAR_SELL'],
                false,
                false,
            ],
            'Empty license type and empty license type is not allowed' => [
                '',
                ['SUGAR_SELL'],
                false,
                false,
            ],
            'License type is not in current instance\'s subscriptions' => [
                ['SUGAR_SERVE'],
                ['SUGAR_SELL'],
                false,
                false,
            ],
            'License type is valid' => [
                ['SUGAR_SELL'],
                ['SUGAR_SELL'],
                false,
                true,
            ],
            'License type is one of system subscriptions' => [
                ['SUGAR_SELL'],
                ['SUGAR_SELL', 'SUGAR_SERVE'],
                true,
                true,
            ],
            'License type is in json encoded format' => [
                json_encode(['SUGAR_SERVE', 'SUGAR_SELL']),
                ['SUGAR_SELL', 'SUGAR_SERVE'],
                true,
                true,
            ],
            'License type is in bundle format' => [
                json_encode(['SUGAR_SELL_PREMIER_BUNDLE', 'SUGAR_SERVE']),
                ['SUGAR_SERVE', 'SUGAR_SELL_PREMIER_BUNDLE', 'HINT', 'PREDICT_PREMIER'],
                true,
                true,
            ],
        ];
    }

    /**
     * @covers ::allowNonAdminToContinue
     *
     * @dataProvider allowNonAdminToContinueProvider
     */
    public function testAllowNonAdminToContinue($systemStatus, $isAdmin, $invalidLicenseTypes, $expected, $unexpectedMasg)
    {
        $userMock = $this->getMockBuilder('\User')
            ->disableOriginalConstructor()
            ->setMethods(['isAdmin', 'getUserExceededAndInvalidLicenseTypes'])
            ->getMock();

        $userMock->expects($this->any())
            ->method('isAdmin')
            ->willReturn($isAdmin);

        $userMock->expects($this->any())
            ->method('getUserExceededAndInvalidLicenseTypes')
            ->willReturn($invalidLicenseTypes);

        $this->assertSame($expected, $userMock->allowNonAdminToContinue($systemStatus), $unexpectedMasg);
    }

    public function allowNonAdminToContinueProvider()
    {
        return [
            'system in good state and is admin' => [
                true,
                true,
                [],
                true,
                'system in good state and is admin',
            ],
            'system in good state and is non-admin' => [
                true,
                false,
                [],
                true,
                'system in good state and is non-admin',
            ],
            'system not in good state and is admin' => [
                ['level' => 'admin_only', 'message' => 'ERROR_LICENSE_SEATS_MAXED'],
                true,
                [],
                false,
                'system not in good state and is admin',
            ],
            'system not in good state and is non-admin' => [
                ['level' => 'admin_only', 'message' => 'ERROR_LICENSE_SEATS_MAXED'],
                false,
                [],
                true,
                'system not in good state and is non-admin',
            ],
            'system not in good state and is non-admin and level is not admin_only' => [
                ['level' => 'warning_only', 'message' => 'ERROR_LICENSE_SEATS_MAXED'],
                false,
                [],
                false,
                'system not in good state and is non-admin and level is not admin_only',
            ],
            'system not in good state and is non-admin and message is not ERROR_LICENSE_SEATS_MAXED' => [
                ['level' => 'admin_only', 'message' => 'Random message'],
                false,
                [],
                false,
                'system not in good state and is non-admin and message is not ERROR_LICENSE_SEATS_MAXED',
            ],
        ];
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
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $user->user_name = $userName;
        $user->external_auth_only = $externalAuthOnly;
        $this->assertEquals($expected, $user->canBeAuthenticated());
    }

    public function hasMangoLicenseTypesProvider()
    {
        return [
            'empty' => [null, true],
            'HINT only' => [['HINT'], false],
            'Mango License Type' => [['CURRENT'], true],
            'Mango License Type in string' => [json_encode(['CURRENT']), true],
            'Mango License Type + HINT' => [['HINT', 'CURRENT'], true],
        ];
    }

    /**
     * @covers ::hasMangoLicenseTypes
     * @param mixed $licenseTypes
     * @param bool $expected
     *
     * @dataProvider hasMangoLicenseTypesProvider
     */
    public function testHasMangoLicenseTypes($licenseTypes, bool $expected)
    {
        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $ret = TestReflection::callProtectedMethod($userMock, 'hasMangoLicenseTypes', [$licenseTypes]);
        $this->assertSame($expected, $ret);
    }

    /**
     * @covers ::isLicenseTypeModified
     * @dataProvider providerTestIsLicenseTypeModified
     * @param $oldTypes
     * @param $newTypes
     * @param $expected
     */
    public function testIsLicenseTypeModified($oldTypes, $newTypes, $expected)
    {
        // Create a User mock
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['getOldTypes', 'getNewTypes'])
            ->disableOriginalConstructor()
            ->getMock();

        // Mock the User's getOldTypes to use mock data
        $userMock->expects($this->once())
            ->method('getOldTypes')
            ->willReturn($oldTypes);

        // Mock the User's getOldTypes to use mock data
        $userMock->expects($this->once())
            ->method('getNewTypes')
            ->willReturn($newTypes);

        $result = $userMock->isLicenseTypeModified($newTypes);
        $this->assertEquals($expected, $result);
    }

    public function providerTestIsLicenseTypeModified()
    {
        return [
            [
                ['SERVE'],
                ['SERVE'],
                false,
            ],
            [
                ['SERVE'],
                ['SELL'],
                true,
            ],
            [
                ['SELL'],
                ['SERVE'],
                true,
            ],
            [
                ['SELL'],
                ['SELL', 'SERVE'],
                true,
            ],
            [
                ['SELL', 'SERVE'],
                ['SERVE'],
                true,
            ],
            [
                ['SELL', 'SERVE'],
                ['SELL', 'SERVE'],
                false,
            ],
        ];
    }

    /**
     *
     * @covers ::hasAdminAndDevPrivilege
     * @dataProvider providerTestHasAdminAndDevPrivilege
     * @param bool $isAdmin
     * @param bool $canAccess
     * @param int $adminAccess
     * @param bool $expected
     * @return void
     */
    public function testHasAdminAndDevPrivilege(bool $hasId, bool $isAdmin, bool $canAccess, int $adminAccess, bool $expected)
    {
        // Create a User mock
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['isAdmin', 'isUserAllowedModuleAccess', 'getUserAdminAccesslevel'])
            ->disableOriginalConstructor()
            ->getMock();

        $userMock->expects($this->any())
            ->method('isAdmin')
            ->willReturn($isAdmin);

        $userMock->expects($this->any())
            ->method('isUserAllowedModuleAccess')
            ->willReturn($canAccess);

        $userMock->expects($this->any())
            ->method('getUserAdminAccesslevel')
            ->willReturn($adminAccess);

        if ($hasId) {
            $userMock->id = '12345';
        }

        $result = $userMock->hasAdminAndDevPrivilege();
        self::assertEquals($expected, $result);
    }

    public function providerTestHasAdminAndDevPrivilege()
    {
        require_once 'modules/ACLActions/actiondefs.php';
        return [
            'admin, has admin-dev level' => [
                true,
                true,
                true,
                100,
                true,
            ],
            'admin, has no model access' => [
                true,
                true,
                false,
                100,
                true,
            ],
            'admin, has module access, but has no admin-dev level' => [
                true,
                true,
                true,
                0,
                true,
            ],
            'no id, has admin-dev level' => [
                false,
                false,
                true,
                100,
                false,
            ],
            'not admin, has admin-dev level' => [
                true,
                false,
                true,
                100,
                true,
            ],
            'not admin, has no access, but has admin-dev level' => [
                true,
                false,
                false,
                100,
                false,
            ],
            'not admin, has access, but has non admin-dev level' => [
                true,
                false,
                false,
                98,
                false,
            ],
        ];
    }
}
