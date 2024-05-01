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

namespace Sugarcrm\SugarcrmTestsUnit\clients\portal\api;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PortalPasswordApi
 */
class PortalPasswordApiTest extends TestCase
{
    /**
     * @covers ::resetEmailPortalPassword
     */
    public function testResetEmailPortalPassword()
    {
        $apiMock = $this->getMockBuilder(\PortalPasswordApi::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSugarQuery', 'getBean', 'getConfigValue', 'sendEmail'])
            ->getMock();

        $sqMock = $this->getMockBuilder(\SugarQuery::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'from', 'getOne'])
            ->getMock();

        $contactMock = $this->getMockBuilder(\Contact::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contactId = 'id1';

        $sqMock->method('getOne')->willReturn($contactId);
        $apiMock->method('getSugarQuery')->willReturn($sqMock);
        $apiMock->method('getBean')->willReturn($contactMock);

        $templateID = 'tplId';
        $configValue = ['lostpasswordtmpl' => $templateID];

        $apiMock->method('getConfigValue')->willReturn($configValue);
        $apiMock->method('sendEmail')->willReturn(true);

        $serviceBase = $this->createMock(\ServiceBase::class);

        $apiMock->expects($this->once())->method('sendEmail')
            ->with($templateID, $contactMock);

        $apiMock->resetEmailPortalPassword($serviceBase, ['username' => 'user1']);
    }
}
