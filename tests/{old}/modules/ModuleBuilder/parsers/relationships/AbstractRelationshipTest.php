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
 * Test for AbstractRelationship class
 */
class AbstractRelationshipTest extends TestCase
{
    /**
     * @var \ModuleBuilderController|mixed
     */
    public $mbController;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $_REQUEST['name'] = 'test';
        $_REQUEST['view'] = 'advanced_search';
        $_REQUEST['view_package'] = 'test';
        $_REQUEST['view_module'] = 'test';

        $this->mbController = new ModuleBuilderController();
        $_REQUEST['description'] = '';
        $_REQUEST['author'] = '';
        $_REQUEST['readme'] = '';
        $_REQUEST['label'] = 'test';
        $_REQUEST['key'] = 'test';
        $this->mbController->action_SavePackage();

        $_REQUEST['type'] = 'person';
        $this->mbController->action_SaveModule();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $_REQUEST['package'] = 'test';
        $_REQUEST['module'] = 'test';
        $_REQUEST['view_module'] = 'test';
        $_REQUEST['view_package'] = 'test';
        $this->mbController->action_DeleteModule();
        unset($_REQUEST['view_module']);
        unset($_REQUEST['module']);
        $this->mbController->action_DeletePackage();

        SugarTestHelper::tearDown();
    }

    /**
     * Test the functionality using OOB module
     *
     * @covers       AbstractRelationship::getRelateFieldDefinition
     * @dataProvider relateFieldDefinitionRNameProvider
     */
    public function testRelateFieldDefinitionRName($module, $relationshipName, $expected)
    {
        $ar = new AbstractRelationship([]);

        $definition = SugarTestReflection::callProtectedMethod(
            $ar,
            'getRelateFieldDefinition',
            [$module, $relationshipName]
        );
        $this->assertEquals($expected, $definition['rname']);
    }

    public static function relateFieldDefinitionRNameProvider()
    {
        return [
            'person' => [
                'Contacts',
                'contacts_documents',
                'full_name',
            ],
            'basic' => [
                'Accounts',
                'accounts_documents',
                'name',
            ],
            'non-deployed' => [
                'test_test',
                'test_documents',
                'full_name',
            ],
        ];
    }
}
