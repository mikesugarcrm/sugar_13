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

use PHPUnit\Framework\TestCase;

require_once 'modules/Administration/updater_utils.php';

class Bug38912 extends TestCase
{
    /**
     * Test whitelist of modules and actions
     * @var array
     */
    private $whiteList;

    private $state = 'LICENSE_KEY';

    private $whiteListModuleAllActions = 'SomeWhiteListModuleAllActions';
    private $whiteListModule = 'SomeWhiteListModule';
    private $whiteListAction = 'SomeWhiteListAction';
    private $nonWhiteListModule = 'SomeNonWhiteListModule';
    private $nonWhiteListAction = 'SomeNonWhiteListAction';

    protected function setUp(): void
    {
        // read format in function getModuleWhiteListForLicenseCheck() description
        $this->whiteList = [
            $this->whiteListModule => [$this->whiteListAction],
            $this->whiteListModuleAllActions => 'all',
        ];
    }

    public function testUserNeedsRedirectModuleNotInWhiteListNoAction()
    {
        $this->assertTrue(
            isNeedRedirectDependingOnUserAndSystemState(
                $this->state,
                $this->nonWhiteListModule,
                null,
                $this->whiteList
            ),
            'Assert that we need redirect for User on module not in whitelist'
        );
    }

    public function testUserNeedsRedirectModuleNotInWhiteListActionNotInWhiteList()
    {
        $this->assertTrue(
            isNeedRedirectDependingOnUserAndSystemState(
                $this->state,
                $this->nonWhiteListModule,
                $this->nonWhiteListAction,
                $this->whiteList
            ),
            'Assert that we need redirect for User on module and action not in whitelist'
        );
    }

    public function testUserNeedsRedirectModuleInWhiteListActionNotInWhiteList()
    {
        $this->assertTrue(
            isNeedRedirectDependingOnUserAndSystemState(
                $this->state,
                $this->whiteListModule,
                $this->nonWhiteListAction,
                $this->whiteList
            ),
            'Assert that we need redirect for User on module in whitelist and action not in whitelist'
        );
    }

    public function testUserDontNeedRedirectModuleInWhiteListActionInWhiteList()
    {
        $this->assertFalse(
            isNeedRedirectDependingOnUserAndSystemState(
                $this->state,
                $this->whiteListModule,
                $this->whiteListAction,
                $this->whiteList
            ),
            'Assert that we dont need redirect for User on module in whitelist and action in whitelist'
        );
    }

    public function testUserDontNeedRedirectModuleInWhiteListForAllActions()
    {
        $this->assertFalse(
            isNeedRedirectDependingOnUserAndSystemState(
                $this->state,
                $this->whiteListModuleAllActions,
                $this->nonWhiteListAction,
                $this->whiteList
            ),
            'Assert that we dont need redirect for User on module in whitelist for all actions'
        );
    }
}
