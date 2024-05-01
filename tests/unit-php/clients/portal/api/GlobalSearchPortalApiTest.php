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
use SugarApiExceptionNoMethod;

/**
 * @coversDefaultClass \GlobalSearchPortalApi
 */
class GlobalSearchPortalApiTest extends TestCase
{
    /**
     * @covers ::globalSearchPortal
     */
    public function testGlobalSearchPortal()
    {
        $sut = $this->getGlobalSearchPortalApiMock();
        $api = $this->getRestServiceMock();

        $this->expectException(SugarApiExceptionNoMethod::class);
        $sut->globalSearchPortal($api, []);
    }

    /**
     * @param null|array $methods
     * @return \GlobalSearchApi
     */
    protected function getGlobalSearchPortalApiMock($methods = null)
    {
        return $this->getMockBuilder(\GlobalSearchPortalApi::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param null|array $methods
     * @return \RestService
     */
    protected function getRestServiceMock($methods = null)
    {
        return $this->getMockBuilder('RestService')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
