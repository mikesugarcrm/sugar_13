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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\ServiceAccount\ServiceAccount;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\ServiceAccount\ServiceAccount
 */
class ServiceAccountTest extends TestCase
{
    /**
     * @var ServiceAccount|MockObject
     */
    protected $serviceAccount;

    /**
     * @var \User|MockObject
     */
    protected $userBean;

    /**
     * @var \User|MockObject
     */
    protected $systemUser;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceAccount = $this->getMockBuilder(ServiceAccount::class)
            ->setMethods(['getUserBean'])
            ->getMock();
        $this->userBean = $this->createMock(\User::class);
        $this->serviceAccount->method('getUserBean')->willReturn($this->userBean);

        $this->systemUser = $this->createMock(\User::class);
        $this->systemUser->id = 'systemId';
    }

    /**
     * @covers ::isServiceAccount
     */
    public function testIsServiceAccount(): void
    {
        $this->assertTrue($this->serviceAccount->isServiceAccount());
    }

    /**
     * @covers ::getSugarUser
     */
    public function testGetSugarUserSystemUser(): void
    {
        $this->serviceAccount->setDataSourceSRN('');
        $this->userBean->expects($this->once())->method('getSystemUser')->willReturn($this->systemUser);
        $sugarUser = $this->serviceAccount->getSugarUser();
        $this->assertEquals('systemId', $sugarUser->id);
        $repeatedSugarUser = $this->serviceAccount->getSugarUser();
        $this->assertEquals('systemId', $repeatedSugarUser->id);
    }

    /**
     * @covers ::getSugarUser
     */
    public function testGetSugarUserDataSourceUserWrongSRN(): void
    {
        $this->serviceAccount->setDataSourceSRN('wrong-srn');
        $this->userBean->expects($this->once())->method('getSystemUser')->willReturn($this->systemUser);
        $sugarUser = $this->serviceAccount->getSugarUser();
        $this->assertEquals('systemId', $sugarUser->id);
    }

    /**
     * @covers ::getSugarUser
     */
    public function testGetSugarUserDataSourceEmptyResourceType(): void
    {
        $this->serviceAccount->setDataSourceSRN('srn:dev:iam:na:1225636081::');
        $this->userBean->expects($this->once())->method('getSystemUser')->willReturn($this->systemUser);
        $sugarUser = $this->serviceAccount->getSugarUser();
        $this->assertEquals('systemId', $sugarUser->id);
    }

    /**
     * @covers ::getSugarUser
     */
    public function testGetSugarUserDataSourceResourceTypeIsNotUser(): void
    {
        $this->serviceAccount->setDataSourceSRN('srn:dev:iam:na:1225636081:sa:');
        $this->userBean->expects($this->once())->method('getSystemUser')->willReturn($this->systemUser);
        $sugarUser = $this->serviceAccount->getSugarUser();
        $this->assertEquals('systemId', $sugarUser->id);
    }

    /**
     * @covers ::getSugarUser
     */
    public function testGetSugarUserDataSourceEmptyUserId(): void
    {
        $this->serviceAccount->setDataSourceSRN('srn:dev:iam:na:1225636081:user:');
        $this->userBean->expects($this->once())->method('getSystemUser')->willReturn($this->systemUser);
        $sugarUser = $this->serviceAccount->getSugarUser();
        $this->assertEquals('systemId', $sugarUser->id);
    }

    /**
     * @covers ::getSugarUser
     */
    public function testGetSugarUserDataSourceUserDoesNotExist(): void
    {
        $this->userBean->id = null;
        $this->serviceAccount->setDataSourceSRN('srn:dev:iam:na:1225636081:user:12345');
        $this->userBean->expects($this->once())->method('retrieve')->with('12345', true, false);
        $this->userBean->expects($this->once())->method('getSystemUser')->willReturn($this->systemUser);
        $sugarUser = $this->serviceAccount->getSugarUser();
        $this->assertEquals('systemId', $sugarUser->id);
    }

    /**
     * @covers ::getSugarUser
     */
    public function testGetSugarUserDataSourceUserIsNotDeveloperForAnyModule(): void
    {
        $this->userBean->id = '12345';
        $this->serviceAccount->setDataSourceSRN('srn:dev:iam:na:1225636081:user:12345');
        $this->userBean->expects($this->once())->method('retrieve')->with('12345', true, false);
        $this->userBean->expects($this->once())->method('isDeveloperForAnyModule')->willReturn(false);
        $this->userBean->expects($this->once())->method('getSystemUser')->willReturn($this->systemUser);
        $sugarUser = $this->serviceAccount->getSugarUser();
        $this->assertEquals('systemId', $sugarUser->id);
    }

    /**
     * @covers ::getSugarUser
     */
    public function testGetSugarUserDataSourceUser(): void
    {
        $this->userBean->id = '12345';
        $this->serviceAccount->setDataSourceSRN('srn:dev:iam:na:1225636081:user:12345');
        $this->userBean->expects($this->once())->method('retrieve')->with('12345', true, false);
        $this->userBean->expects($this->once())->method('isDeveloperForAnyModule')->willReturn(true);
        $this->userBean->expects($this->never())->method('getSystemUser');
        $sugarUser = $this->serviceAccount->getSugarUser();
        $this->assertEquals('12345', $sugarUser->id);
    }

    /**
     * @covers ::setDataSourceSRN
     * @covers ::getDataSourceSRN
     */
    public function testDataSourceSRN(): void
    {
        $srn = 'srn:dev:iam:na:1225636081:user:12345';
        $this->serviceAccount->setDataSourceSRN($srn);
        $this->assertEquals($srn, $this->serviceAccount->getDataSourceSRN());
    }

    /**
     * @covers ::setDataSourceName
     * @covers ::getDataSourceName
     */
    public function testDataSourceName(): void
    {
        $name = 'data source name';
        $this->serviceAccount->setDataSourceName($name);
        $this->assertEquals($name, $this->serviceAccount->getDataSourceName());
    }
}
