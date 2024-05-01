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

/**
 * @group bug32797
 */
class Bug32797Test extends TestCase
{
    private $oldSugarConfig = null;

    protected function setUp(): void
    {
        $this->oldSugarConfig = $GLOBALS['sugar_config'];
        $GLOBALS['sugar_config'] = ['require_accounts' => false];
    }

    protected function tearDown(): void
    {
        $config = SugarConfig::getInstance();
        $config->clearCache();
        $GLOBALS['sugar_config'] = $this->oldSugarConfig;
    }

    public function vardefProvider()
    {
        return [
            [
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => true]]],
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => false]]],
            ],
            [
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => false]]],
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => false]]],
            ],
            [
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => null]]],
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => false]]],
            ],
            [
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => true]]],
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => true]]],
            ],
            [
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => false]]],
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => false]]],
            ],
            [
                ['fields' => ['account_name' => []]],
                ['fields' => ['account_name' => []]],
            ],
            [
                ['fields' => []],
                ['fields' => []],
            ],
        ];
    }

    /**
     * @dataProvider vardefProvider
     */
    public function testApplyGlobalAccountRequirements($vardef, $vardefToCompare)
    {
        $this->assertEquals($vardefToCompare, VardefManager::applyGlobalAccountRequirements($vardef));
    }

    public function vardefProvider1()
    {
        return [
            [
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => true]]],
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => true]]],
            ],
            [
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => false]]],
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => true]]],
            ],
            [
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => true]]],
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => true]]],
            ],
            [
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => false]]],
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => false]]],
            ],
        ];
    }

    /**
     * @dataProvider vardefProvider1
     */
    public function testApplyGlobalAccountRequirements1($vardef, $vardefToCompare)
    {
        $GLOBALS['sugar_config']['require_accounts'] = true;
        $this->assertEquals($vardefToCompare, VardefManager::applyGlobalAccountRequirements($vardef));
    }

    public function vardefProvider2()
    {
        return [
            [
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => true]]],
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => true]]],
            ],
            [
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => false]]],
                ['fields' => ['account_name' => ['type' => 'relate', 'required' => false]]],
            ],
            [
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => false]]],
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => false]]],
            ],
            [
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => true]]],
                ['fields' => ['account_name' => ['type' => 'varchar', 'required' => true]]],
            ],
        ];
    }

    /**
     * @dataProvider vardefProvider2
     */
    public function testApplyGlobalAccountRequirements2($vardef, $vardefToCompare)
    {
        unset($GLOBALS['sugar_config']['require_accounts']);
        $this->assertEquals($vardefToCompare, VardefManager::applyGlobalAccountRequirements($vardef));
    }
}
