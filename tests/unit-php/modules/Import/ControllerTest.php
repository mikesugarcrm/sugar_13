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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Import;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \ImportController
 */
class ControllerTest extends TestCase
{
    /**
     * @var \importController | MockObject
     */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = $this->getMockBuilder(\ImportController::class)
            ->onlyMethods(['isLimitedForModuleInIdmMode'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return array|array[]
     */
    public function actionStep1Provider(): array
    {
        return [
            'ModuleNotLimitedImportFromAdminWizard' => [
                'isLimited' => false,
                'importModule' => 'Administration',
                'bean' => $this->createMock(\Administration::class),
                'view' => 'step1',
            ],
            'ModuleNotLimitedImportFromModule' => [
                'isLimited' => false,
                'importModule' => 'Accounts',
                'bean' => $this->createMock(\Account::class),
                'view' => 'step2',
            ],
            'ModuleNotLimitedImportFromAnyPersonModule' => [
                'isLimited' => false,
                'importModule' => 'Contacts',
                'bean' => $this->createMock(\Contact::class),
                'view' => 'step2',
            ],
            'ModuleNotLimitedImportFromAdminWizard' => [
                'isLimited' => false,
                'importModule' => 'Administration',
                'bean' => $this->createMock(\Administration::class),
                'view' => 'step1',
            ],
            'ModuleIsLimitedImportFromUserModule' => [
                'isLimited' => true,
                'importModule' => 'Users',
                'bean' => $this->createMock(\User::class),
                'view' => 'step2',
            ],
            'NoModule' => [
                'isLimited' => false,
                'importModule' => '',
                'bean' => $this->createMock(\User::class),
                'view' => 'step1',
            ],
            'ModuleNotLimitedImportFromAnyPersonModule' => [
                'isLimited' => false,
                'importModule' => 'Contacts',
                'bean' => $this->createMock(\Contact::class),
                'view' => 'step1',
            ],
        ];
    }

    /**
     * @param bool $isLimited
     * @param string $importModule
     * @param \SugarBean $bean
     * @param string $view
     *
     * @covers ::action_Step1
     *
     * @dataProvider actionStep1Provider
     */
    public function testActionStep1(
        bool       $isLimited,
        string     $importModule,
        \SugarBean $bean,
        string     $view
    ): void {

        $this->controller->bean = $bean;
        TestReflection::setProtectedValue($this->controller, 'importModule', $importModule);

        $this->controller->method('isLimitedForModuleInIdmMode')
            ->with($importModule)
            ->willReturn($isLimited);

        $this->controller->action_Step1();

        $this->assertEquals($view, $this->controller->view);
    }
}
