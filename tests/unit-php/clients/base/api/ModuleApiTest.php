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

namespace Sugarcrm\SugarcrmTestsUnit\clients\base\api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SugarApiExceptionNotAuthorized;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

require_once 'include/utils.php';

/**
 * @coversDefaultClass \ModuleApi
 */
class ModuleApiTest extends TestCase
{
    /**
     * @var \ServiceBase|MockObject
     */
    protected $api;

    /**
     * @var \ModuleApi|MockObject
     */
    protected $moduleApi;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->api = $this->createMock(\ServiceBase::class);
        $this->moduleApi = $this->getMockBuilder(\ModuleApi::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIDMModeEnabled'])
            ->getMock();
        $this->moduleApi->expects($this->any())->method('isIDMModeEnabled')->willReturn(true);
    }

    /**
     * module provider
     * @return array
     */
    public function moduleProvider()
    {
        return [
            ['Users'],
            ['Employees'],
        ];
    }

    /**
     * @dataProvider moduleProvider
     * @param string $module
     * @covers ::createBean
     */
    public function testCreateBeanException($module)
    {
        TestReflection::setProtectedValue($this->moduleApi, 'idmModeDisabledModules', ['Users', 'Employees']);
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->moduleApi->createBean($this->api, ['module' => $module]);
    }

    /**
     * @dataProvider moduleProvider
     * @param string $module
     * @covers ::deleteRecord
     */
    public function testDeleteRecordException($module)
    {
        TestReflection::setProtectedValue($this->moduleApi, 'idmModeDisabledModules', ['Users', 'Employees']);
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->moduleApi->deleteRecord($this->api, ['module' => $module, 'record' => 'not_exist']);
    }
}
