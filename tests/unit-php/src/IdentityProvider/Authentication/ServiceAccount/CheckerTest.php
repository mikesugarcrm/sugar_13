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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\ServiceAccount;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\ServiceAccount\Checker;
use Sugarcrm\Sugarcrm\SugarCloud\AuthZ;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\ServiceAccount\Checker
 */
class CheckerTest extends TestCase
{
    /**
     * @var MockObject|AuthZ
     */
    private $authZ;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $accessToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authZ = $this->createMock(AuthZ::class);
        $this->accessToken = 'token';
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * @return array
     */
    public function isAllowedUsingAllowedSasDataProvider(): array
    {
        return [
            'emptySAsSubjectNotInTenant' => [
                'tokenInfo' => [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                'config' => [
                    'tid' => 'srn:cluster:idm:eu:0000000002:tenant',
                ],
                'expected' => false,
            ],
            'notAllowedSAsSubjectNotInTenant' => [
                'tokenInfo' => [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                'config' => [
                    'tid' => 'srn:cluster:idm:eu:0000000002:tenant',
                    'allowedSAs' => ['srn:cluster:idm:eu:0000000004:tenant'],
                ],
                'expected' => false,
            ],
            'allowedSAsSubjectNotInTenant' => [
                'tokenInfo' => [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                'config' => [
                    'tid' => 'srn:cluster:idm:eu:0000000002:tenant',
                    'allowedSAs' => ['srn:cluster:idm:eu:0000000001:tenant'],
                ],
                'expected' => true,
            ],
        ];
    }

    /**
     * @param array $accessTokenInfo
     * @param array $config
     * @param bool $expected
     *
     * @covers ::isAllowed()
     * @dataProvider isAllowedUsingAllowedSasDataProvider
     */
    public function testIsAllowedUsingAllowedSas(
        array $accessTokenInfo,
        array $config,
        bool  $expected
    ): void {

        $checker = new Checker($config, $this->authZ, $this->logger);
        $this->authZ->expects(self::never())->method('checkPermission');
        self::assertEquals($expected, $checker->isAllowed($this->accessToken, $accessTokenInfo));
    }

    /**
     * @return array
     */
    public function isAllowedUsingAuthZDataProvider(): array
    {
        return [
            'subjectInTenantNotAllowedByAuthZ' => [
                'tokenInfo' => [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                'config' => [
                    'tid' => 'srn:cluster:idm:eu:0000000001:tenant',
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                    ],
                ],
                'authZResult' => false,
                'expected' => false,
            ],
            'subjectInTenantAllowedByAuthZ' => [
                'tokenInfo' => [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                'config' => [
                    'tid' => 'srn:cluster:idm:eu:0000000001:tenant',
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                    ],
                ],
                'authZResult' => true,
                'expected' => true,
            ],
            'tokenExtraInTenantSubjectNotInTheTenantNotAllowedByAuthZ' => [
                'tokenInfo' => [
                    'sub' => 'srn:cluster:idm:eu:9999999999:sa',
                    'ext' => [
                        'tid' => 'srn:cluster:idm:eu:0000000001:tenant',
                    ],
                ],
                'config' => [
                    'tid' => 'srn:cluster:idm:eu:0000000001:tenant',
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                    ],
                ],
                'authZResult' => true,
                'expected' => true,
            ],
            'tokenExtraInTenantSubjectNotInTheTenantAllowedByAuthZ' => [
                'tokenInfo' => [
                    'sub' => 'srn:cluster:idm:eu:9999999999:sa',
                    'ext' => [
                        'tid' => 'srn:cluster:idm:eu:0000000001:tenant',
                    ],
                ],
                'config' => [
                    'tid' => 'srn:cluster:idm:eu:0000000001:tenant',
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                    ],
                ],
                'authZResult' => false,
                'expected' => false,
            ],
        ];
    }

    /**
     * @param array $accessTokenInfo
     * @param array $config
     * @param bool $authZResult
     * @param bool $expected
     *
     * @covers ::isAllowed()
     * @dataProvider isAllowedUsingAuthZDataProvider
     */
    public function testIsAllowedUsingAuthZ(
        array $accessTokenInfo,
        array $config,
        bool  $authZResult,
        bool  $expected
    ): void {

        $checker = new Checker($config, $this->authZ, $this->logger);
        $this->authZ->expects(self::once())
            ->method('checkPermission')
            ->with($this->accessToken, $config['tid'], $config['serviceAccountPermissions'])
            ->willReturn($authZResult);
        self::assertEquals($expected, $checker->isAllowed($this->accessToken, $accessTokenInfo));
    }

    /**
     * @return array
     */
    public function badOrMissingSRNsIsAllowedDataProvider(): array
    {
        return [
            'empty' => [
                [],
                [],
            ],
            'noOwnTenant' => [
                [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                [],
            ],
            'incorrectOwnTID' => [
                [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                [
                    'tid' => 'srn:cluster:idm:eu:WRONG:tenant',
                ],
            ],
        ];
    }

    /**
     * @param array $accessTokenInfo
     * @param array $config
     *
     * @covers ::isAllowed()
     * @dataProvider badOrMissingSRNsIsAllowedDataProvider
     */
    public function testIsAllowedThrowsExceptionWhenGivenInvalidSRNs(array $accessTokenInfo, array $config): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->authZ->expects(self::never())->method('checkPermission');
        $checker = new Checker($config, $this->authZ, $this->logger);
        $checker->isAllowed($this->accessToken, $accessTokenInfo);
    }
}
