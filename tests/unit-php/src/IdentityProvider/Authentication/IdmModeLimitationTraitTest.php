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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\IdmModeLimitationTrait;

use function Aws\flatmap;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\IdmModeLimitationTrait
 */
class IdmModeLimitationTraitTest extends TestCase
{
    /**
     * @var Config | MockObject
     */
    private $idpConfig;

    /**
     * @var IdmModeLimitationTrait | MockObject
     */
    private $trait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->idpConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isIDMModeEnabled', 'getUserLicenseTypeIdmModeLock'])
            ->getMock();

        $this->trait = $this->getMockBuilder(IdmModeLimitationTrait::class)
            ->onlyMethods(['getIdpConfig'])
            ->getMockForTrait();

        $this->trait->method('getIdpConfig')->willReturn($this->idpConfig);
    }

    /**
     * @return array
     */
    public function isLimitedForModuleInIdmModeProvider(): array
    {
        return [
            'idmModeDisabled' => [
                'idmModeEnabled' => false,
                'module' => '',
                'limitedForModule' => false,
            ],
            'idmModeEnabledNoModule' => [
                'idmModeEnabled' => true,
                'module' => '',
                'limitedForModule' => false,
            ],
            'idmModeEnabledModuleNotDisabledForIdm' => [
                'idmModeEnabled' => true,
                'module' => 'Leads',
                'limitedForModule' => false,
            ],
            'idmModeEnabledModuleDisabledForIdm' => [
                'idmModeEnabled' => true,
                'module' => 'Users',
                'limitedForModule' => true,
            ],
            'idmModeDisabledModuleDisabledForIdm' => [
                'idmModeEnabled' => false,
                'module' => 'Users',
                'limitedForModule' => false,
            ],
        ];
    }

    /**
     * @param bool $idmModeEnabled
     * @param string $module
     * @param bool $limitedForModule
     *
     * @covers ::isLimitedForModuleInIdmMode
     *
     * @dataProvider isLimitedForModuleInIdmModeProvider
     */
    public function testIsLimitedForModuleInIdmMode(
        bool   $idmModeEnabled,
        string $module,
        bool   $limitedForModule
    ): void {

        $this->idpConfig->expects($this->once())
            ->method('isIDMModeEnabled')
            ->willReturn($idmModeEnabled);
        $this->assertEquals($limitedForModule, $this->trait->isLimitedForModuleInIdmMode($module));
    }

    /**
     * @return array
     */
    public function isLimitedForFieldInIdmModeProvider(): array
    {
        return [
            'idmModeDisabled' => [
                'idmModeEnabled' => false,
                'module' => 'Users',
                'fieldDefs' => [
                    'idm_mode_disabled' => false,
                    'name' => 'fieldName',
                ],
                false,
                'limitedForField' => false,
            ],
            'idmModeDisabledFiledIsLimited' => [
                'idmModeEnabled' => false,
                'module' => 'Users',
                'fieldDefs' => [
                    'idm_mode_disabled' => true,
                    'name' => 'fieldName',
                ],
                false,
                'limitedForField' => false,
            ],
            'idmModeEnabledFiledIsNotLimited' => [
                'idmModeEnabled' => true,
                'module' => 'Users',
                'fieldDefs' => [
                    'idm_mode_disabled' => false,
                    'name' => 'fieldName',
                ],
                false,
                'limitedForField' => false,
            ],
            'idmModeEnabledFiledIsLimited' => [
                'idmModeEnabled' => true,
                'module' => 'Users',
                'fieldDefs' => [
                    'idm_mode_disabled' => true,
                    'name' => 'fieldName',
                ],
                false,
                'limitedForField' => true,
            ],
            'idmModeEnabledModuleNotLimitedFiledIsLimited' => [
                'idmModeEnabled' => false,
                'module' => 'Accounts',
                'fieldDefs' => [
                    'idm_mode_disabled' => true,
                    'name' => 'fieldName',
                ],
                false,
                'limitedForField' => false,
            ],
            'idmModeDisabledUserTypeFiled' => [
                'idmModeEnabled' => false,
                'module' => 'Users',
                'fieldDefs' => [
                    'idm_mode_disabled' => false,
                    'name' => 'UserType',
                ],
                false,
                'limitedForField' => false,
            ],
            'idmModeEnabledUserTypeFiled' => [
                'idmModeEnabled' => true,
                'module' => 'Users',
                'fieldDefs' => [
                    'idm_mode_disabled' => false,
                    'name' => 'UserType',
                ],
                false,
                'limitedForField' => true,
            ],
            'idmModeEnabledUserTypeFiledModuleNotLimited' => [
                'idmModeEnabled' => true,
                'module' => 'Accounts',
                'fieldDefs' => [
                    'idm_mode_disabled' => false,
                    'name' => 'UserType',
                ],
                false,
                'limitedForField' => false,
            ],
            'idmModeEnabledLicenseTypeLCLockIsOffFiledModuleNotLimited' => [
                'idmModeEnabled' => true,
                'module' => 'Users',
                'fieldDefs' => [
                    'idm_mode_disabled' => true,
                    'name' => 'license_type',
                ],
                false,
                'limitedForField' => false,
            ],
            'idmModeEnabledLicenseTypeLCLockIsOnFiledModuleNotLimited' => [
                'idmModeEnabled' => true,
                'module' => 'Users',
                'fieldDefs' => [
                    'idm_mode_disabled' => true,
                    'name' => 'license_type',
                ],
                true,
                'limitedForField' => true,
            ],
        ];
    }

    /**
     * @param bool $idmModeEnabled
     * @param string $module
     * @param array $fieldDefs
     * @param bool $limitedForField
     *
     * @covers ::isLimitedForFieldInIdmMode
     *
     * @dataProvider isLimitedForFieldInIdmModeProvider
     */
    public function testIsLimitedForFieldInIdmMode(
        bool   $idmModeEnabled,
        string $module,
        array  $fieldDefs,
        bool   $licenseTypeLock,
        bool   $limitedForField
    ): void {

        $this->idpConfig->expects($this->once())
            ->method('isIDMModeEnabled')
            ->willReturn($idmModeEnabled);
        $this->idpConfig->expects($this->any())
            ->method('getUserLicenseTypeIdmModeLock')
            ->willReturn($licenseTypeLock);
        $this->assertEquals($limitedForField, $this->trait->isLimitedForFieldInIdmMode($module, $fieldDefs));
    }
}
