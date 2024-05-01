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

class Bug39598Test extends TestCase
{
    private $relationshipFileDir;
    private $relationshipFilePath;

    protected function setUp(): void
    {
        $this->relationshipFilePath = 'custom/modulebuilder/packages/Bug39598Test/relationships.php';
        $this->relationshipFileDir = dirname($this->relationshipFilePath);
        sugar_mkdir($this->relationshipFileDir, null, true);

        $contents = '<?php  $relationships = array (
           \'pk8_mod11_accounts\' =>
           array (
           \'rhs_label\' => \'Accounts\',
           \'lhs_label\' => \'Pk8_Mod11\',
           \'lhs_subpanel\' => \'default\',
           \'rhs_subpanel\' => \'default\',
           \'lhs_module\' => \'Pk8_Mod11\',
           \'rhs_module\' => \'Accounts\',
           \'relationship_type\' => \'many-to-many\',
           \'readonly\' => false,
           \'deleted\' => false,
           \'relationship_only\' => false,
           \'for_activities\' => false,
           \'is_custom\' => false,
           \'from_studio\' => false,
           \'relationship_name\' => \'pk8_mod11_accounts\',
           ),
           \'pk8_mod11_pk8_mod11\' =>
           array (
           \'rhs_label\' => \'Pk8_Mod11\',
           \'lhs_label\' => \'Pk8_Mod11\',
           \'lhs_subpanel\' => \'default\',
           \'rhs_subpanel\' => \'default\',
           \'lhs_module\' => \'Pk8_Mod11\',
           \'rhs_module\' => \'Pk8_Mod11\',
           \'relationship_type\' => \'one-to-one\',
           \'readonly\' => false,
           \'deleted\' => false,
           \'relationship_only\' => false,
           \'for_activities\' => false,
           \'is_custom\' => false,
           \'from_studio\' => false,
           \'relationship_name\' => \'pk8_mod11_pk8_mod11\',
           ),
        );';
        file_put_contents($this->relationshipFilePath, $contents);
    }

    protected function tearDown(): void
    {
        unlink($this->relationshipFilePath);
        rmdir($this->relationshipFileDir);
    }

    public function testRelationshipName()
    {
        $mbModule = new MBModule('NewMod', $this->relationshipFileDir, 'test', 'test');
        $expectedNewName = 'test_NewMod';
        $mbModule->renameRelationships($this->relationshipFileDir, 'Pk8_Mod11', $expectedNewName);
        $relationships = [];
        include $this->relationshipFilePath;

        // check many-to-many lhs module name and relationship name change
        $this->assertEquals(
            $expectedNewName,
            $relationships['test_newmod_accounts']['lhs_module'],
            'Lhs module name replaced correctly in relationships metadata.'
        );

        // check many-to-many rhs - should not change
        $this->assertEquals(
            'Accounts',
            $relationships['test_newmod_accounts']['rhs_module'],
            'Rhs module name not changed.'
        );

        // check one-to-one rhs - should change
        $this->assertEquals(
            $expectedNewName,
            $relationships['test_newmod_test_newmod']['rhs_module'],
            'Module name replaced correctly in relationships metadata'
        );
    }
}
