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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Employees\views;

use EmployeesViewDetail;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use User;

/**
 * @coversDefaultClass EmployeesViewDetail
 */
class ViewDetailTest extends TestCase
{
    /**
     * @var User
     */
    protected $savedUser;

    /**
     * @var array
     */
    protected $savedRequest;

    /**
     * @var array
     */
    protected $savedModuleStrings;

    /**
     * @var EmployeesViewDetail
     */
    protected $view;

    /**
     * @var User | MockObject
     */
    protected $currentUser;

    /**
     * @var User | MockObject
     */
    protected $viewUser;

    /**
     * @var Config | MockObject
     */
    protected $idpConfig;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->currentUser = $this->createMock(User::class);
        $this->viewUser = $this->createMock(User::class);
        $this->idpConfig = $this->createMock(Config::class);

        $this->savedRequest = $_REQUEST;
        $this->savedUser = $GLOBALS['current_user'] ?? null;
        $this->savedModuleStrings = $GLOBALS['mod_strings'] ?? null;

        $_REQUEST = [];
        $GLOBALS['current_user'] = $this->currentUser;

        $this->view = $this->createPartialMock(EmployeesViewDetail::class, ['getIdmConfig']);
        $this->view->ss = $this->createMock(\Sugar_Smarty::class);
        $this->view->dv = $this->createMock(\DetailView2::class);
        $this->view->bean = $this->viewUser;

        $this->view->method('getIdmConfig')->willReturn($this->idpConfig);

        $GLOBALS['mod_strings']['LBL_DELETE_EMPLOYEE_CONFIRM'] = 'employeeDel';
        $GLOBALS['mod_strings']['LBL_DELETE_USER_CONFIRM'] = 'userDel';
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $_REQUEST = $this->savedRequest;
        if ($this->savedUser) {
            $GLOBALS['current_user'] = $this->savedUser;
        }
        if ($this->savedModuleStrings) {
            $GLOBALS['mod_strings'] = $this->savedModuleStrings;
        }

        parent::tearDown();
    }

    /**
     * Provides data for testDisplay
     * @return array
     */
    public function displayDataProvider(): array
    {
        return [
            'currentUserIsNotAdminViewUserIsUser' => [
                'currentUserId' => 'uid-1',
                'viewUserId' => 'uid-2',
                'viewUserCanBeAuthenticated' => true,
                'currentUserIdAdmin' => false,
                'currentUserIsAdminForModule' => false,
                'idmModeEnabled' => false,
                'expectedAssertion' => static function (ViewDetailTest $test, MockObject $smarty) {
                    $smarty->expects($test->once())->method('assign')->with('DISPLAY_DELETE', false);
                },
            ],
            'currentUserIsAdminButNotAdminForModuleViewUserIsUser' => [
                'currentUserId' => 'uid-1',
                'viewUserId' => 'uid-2',
                'viewUserCanBeAuthenticated' => true,
                'currentUserIdAdmin' => true,
                'currentUserIsAdminForModule' => false,
                'idmModeEnabled' => false,
                'expectedAssertion' => static function (ViewDetailTest $test, MockObject $smarty) {
                    $smarty->expects($test->exactly(3))
                        ->method('assign')
                        ->withConsecutive(
                            ['DISPLAY_EDIT', true],
                            ['DISPLAY_DUPLICATE', true],
                            ['DISPLAY_DELETE', false]
                        );
                },
            ],
            'currentUserIsAdminAndAdminForModuleViewUserIsUser' => [
                'currentUserId' => 'uid-1',
                'viewUserId' => 'uid-2',
                'viewUserCanBeAuthenticated' => true,
                'currentUserIdAdmin' => true,
                'currentUserIsAdminForModule' => true,
                'idmModeEnabled' => false,
                'expectedAssertion' => static function (ViewDetailTest $test, MockObject $smarty) {
                    $smarty->expects($test->exactly(4))
                        ->method('assign')
                        ->withConsecutive(
                            ['DISPLAY_EDIT', true],
                            ['DISPLAY_DUPLICATE', true],
                            ['DELETE_WARNING', 'userDel'],
                            ['DISPLAY_DELETE', true],
                        );
                },
            ],
            'CurrentUserIsAdminAndAdminForModuleViewUserIsEmployee' => [
                'currentUserId' => 'uid-1',
                'viewUserId' => 'uid-2',
                'viewUserCanBeAuthenticated' => false,
                'currentUserIdAdmin' => true,
                'currentUserIsAdminForModule' => true,
                'idmModeEnabled' => false,
                'expectedAssertion' => static function (ViewDetailTest $test, MockObject $smarty) {
                    $smarty->expects($test->exactly(4))
                        ->method('assign')
                        ->withConsecutive(
                            ['DISPLAY_EDIT', true],
                            ['DISPLAY_DUPLICATE', true],
                            ['DELETE_WARNING', 'employeeDel'],
                            ['DISPLAY_DELETE', true],
                        );
                },
            ],
            'IDMMode_CurrentUserIsAdminAndAdminForModuleViewUserIsEmployee' => [
                'currentUserId' => 'uid-1',
                'viewUserId' => 'uid-2',
                'viewUserCanBeAuthenticated' => false,
                'currentUserIdAdmin' => true,
                'currentUserIsAdminForModule' => true,
                'idmModeEnabled' => true,
                'expectedAssertion' => static function (ViewDetailTest $test, MockObject $smarty) {
                    $smarty->expects($test->exactly(4))
                        ->method('assign')
                        ->withConsecutive(
                            ['DISPLAY_EDIT', true],
                            ['DISPLAY_DUPLICATE', true],
                            ['DELETE_WARNING', 'employeeDel'],
                            ['DISPLAY_DELETE', true],
                        );
                },
            ],
            'IDMMode_CurrentUserIsAdminAndAdminForModuleViewUserIsUser' => [
                'currentUserId' => 'uid-1',
                'viewUserId' => 'uid-2',
                'viewUserCanBeAuthenticated' => true,
                'currentUserIdAdmin' => true,
                'currentUserIsAdminForModule' => true,
                'idmModeEnabled' => true,
                'expectedAssertion' => static function (ViewDetailTest $test, MockObject $smarty) {
                    $smarty->expects($test->exactly(3))
                        ->method('assign')
                        ->withConsecutive(
                            ['DISPLAY_EDIT', true],
                            ['DELETE_WARNING', 'userDel'],
                            ['DISPLAY_DELETE', false],
                        );
                },
            ],
        ];
    }

    /**
     * @param string $currentUserId
     * @param string $viewUserId
     * @param string $viewUserUsername
     * @param bool $viewUserCanBeAuthenticated
     * @param bool $currentUserIdAdmin
     * @param bool $currentUserIsAdminForModule
     * @param bool $idmModeEnabled
     * @param \Closure $expectedAssertion
     *
     * @dataProvider displayDataProvider
     *
     * @covers ::display
     */
    public function testDisplay(
        string   $currentUserId,
        string   $viewUserId,
        bool     $viewUserCanBeAuthenticated,
        bool     $currentUserIdAdmin,
        bool     $currentUserIsAdminForModule,
        bool     $idmModeEnabled,
        \Closure $expectedAssertion
    ): void {

        $this->currentUser->method('isAdmin')->willReturn($currentUserIdAdmin);
        $this->currentUser->method('isAdminForModule')->willReturn($currentUserIsAdminForModule);
        $this->idpConfig->method('isIDMModeEnabled')->willReturn($idmModeEnabled);

        $this->currentUser->id = $currentUserId;

        $this->viewUser->id = $viewUserId;
        $this->viewUser->expects($this->once())
            ->method('canBeAuthenticated')
            ->willReturn($viewUserCanBeAuthenticated);

        $_REQUEST['record'] = $viewUserId;

        $expectedAssertion($this, $this->view->ss);
        $this->view->display();
    }
}
