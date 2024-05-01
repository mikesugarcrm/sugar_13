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

namespace Sugarcrm\SugarcrmTests\Denormalization\Relate;

use BeanFactory;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Denormalization\Relate\Hook\Configuration;
use Sugarcrm\Sugarcrm\Denormalization\Relate\Hook;
use Sugarcrm\Sugarcrm\Denormalization\Relate\Hook\EventHandler;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Denormalization\Relate\Hook
 */
class HookTest extends TestCase
{
    public function testBeforeUpdateTriggered()
    {
        $eventHandler = $this->createMock(EventHandler::class);
        $configMock = $this->createConfiguredMock(
            Configuration::class,
            ['getModuleConfiguration' => ['test_field' => ['rel' => ['module' => 'test_module', 'is_main' => false]]]]
        );
        $eventHandler->expects($this->once())->method('handleBeforeUpdate');
        $arguments = [
            'isUpdate' => true,
            'dataChanges' => ['test_field' => true],
        ];
        $hook = new Hook($eventHandler, $configMock);
        $hook->handleBeforeUpdate(BeanFactory::getBean('Accounts'), 'test_event', $arguments);
    }

    public function testBeforeUpdateSkipped()
    {
        $eventHandler = $this->createMock(EventHandler::class);
        $configMock = $this->createConfiguredMock(
            Configuration::class,
            ['getModuleConfiguration' => ['test_field' => ['rel' => ['module' => 'test_module', 'is_main' => true]]]]
        );
        $eventHandler->expects($this->never())->method('handleBeforeUpdate');
        $hook = new Hook($eventHandler, $configMock);
        $hook->handleBeforeUpdate(BeanFactory::getBean('Accounts'), 'test_event', []);
    }

    public function testAfterUpdateIfTargetFieldChanged()
    {
        $eventHandler = $this->createMock(EventHandler::class);
        $configMock = $this->createConfiguredMock(
            Configuration::class,
            ['getModuleConfiguration' => ['test_field' => ['rel' => ['module' => 'test_module', 'is_main' => true]]]]
        );

        $eventHandler->expects($this->once())->method('handleAfterUpdateSourceField');
        $hook = new Hook($eventHandler, $configMock);
        $arguments = [
            'isUpdate' => true,
            'dataChanges' => ['test_field' => true],
        ];
        $hook->handleAfterUpdate(BeanFactory::getBean('Accounts'), 'test_event', $arguments);
    }

    public function testAfterUpdateIfTargetFieldUnchanged()
    {
        $eventHandler = $this->createMock(EventHandler::class);
        $configMock = $this->createConfiguredMock(
            Configuration::class,
            ['getModuleConfiguration' => ['test_field' => ['rel' => ['module' => 'test_module', 'is_main' => true]]]]
        );

        $eventHandler->expects($this->never())->method('handleAfterUpdateSourceField');
        $hook = new Hook($eventHandler, $configMock);
        $arguments = [
            'isUpdate' => true,
            'dataChanges' => [],
        ];
        $hook->handleAfterUpdate(BeanFactory::getBean('Accounts'), 'test_event', $arguments);
    }

    public function testDeleteRelationshipTriggered()
    {
        $eventHandler = $this->createMock(EventHandler::class);
        $configMock = $this->createConfiguredMock(
            Configuration::class,
            ['getModuleConfiguration' => ['test_field' => ['rel' => ['module' => 'test_module', 'is_main' => false]]]]
        );
        $eventHandler->expects($this->once())->method('handleDeleteRelationship');
        $hook = new Hook($eventHandler, $configMock);
        $hook->handleDeleteRelationship(
            BeanFactory::getBean('Accounts'),
            'test_event',
            ['related_module' => 'test_module', 'related_id' => 'test_id']
        );
    }

    public function testDeleteRelationshipSkipped()
    {
        $eventHandler = $this->createMock(EventHandler::class);
        $configMock = $this->createConfiguredMock(
            Configuration::class,
            ['getModuleConfiguration' => ['test_field' => ['rel' => ['module' => 'test_module', 'is_main' => true]]]]
        );
        $eventHandler->expects($this->never())->method('handleDeleteRelationship');
        $hook = new Hook($eventHandler, $configMock);
        $hook->handleDeleteRelationship(
            BeanFactory::getBean('Accounts'),
            'test_event',
            ['related_module' => '', 'related_id' => 'test_id']
        );
        $hook->handleDeleteRelationship(
            BeanFactory::getBean('Accounts'),
            'test_event',
            ['related_module' => 'test_module', 'related_id' => 'test_id']
        );
    }

    public function testAddRelationshipTriggered()
    {
        $eventHandler = $this->createMock(EventHandler::class);
        $configMock = $this->createConfiguredMock(
            Configuration::class,
            ['getModuleConfiguration' => ['test_field' => ['rel' => ['module' => 'test_module', 'is_main' => false]]]]
        );
        $eventHandler->expects($this->once())->method('handleAddRelationship');
        $hook = new Hook($eventHandler, $configMock);
        $hook->handleAddRelationship(
            BeanFactory::getBean('Accounts'),
            'test_event',
            ['related_module' => 'test_module', 'related_id' => 'test_id']
        );
    }

    public function testAddRelationshipWithValueTriggered()
    {
        $eventHandler = $this->createMock(EventHandler::class);
        $configMock = $this->createConfiguredMock(
            Configuration::class,
            ['getModuleConfiguration' => ['test_field' => ['rel' => ['module' => 'test_module', 'is_main' => true]]]]
        );
        $eventHandler->expects($this->once())->method('handleAddRelationshipWithValue');
        $hook = new Hook($eventHandler, $configMock);
        $hook->handleAddRelationship(
            BeanFactory::getBean('Accounts'),
            'test_event',
            ['related_module' => 'test_module', 'related_id' => 'test_id']
        );
    }

    public function testAddRelationshipSkipped()
    {
        $eventHandler = $this->createMock(EventHandler::class);
        $configMock = $this->createConfiguredMock(
            Configuration::class,
            ['getModuleConfiguration' => ['test_field' => ['rel' => ['module' => 'test_module', 'is_main' => false]]]]
        );
        $eventHandler->expects($this->never())->method('handleAddRelationship');
        $eventHandler->expects($this->never())->method('handleAddRelationshipWithValue');
        $hook = new Hook($eventHandler, $configMock);
        $hook->handleAddRelationship(
            BeanFactory::getBean('Accounts'),
            'test_event',
            ['related_module' => '', 'related_id' => 'test_id']
        );
    }

    public function testHandleRelationshipNameMatch()
    {
        $eventHandler = $this->createMock(EventHandler::class);
        $configMock = $this->createConfiguredMock(
            Configuration::class,
            ['getModuleConfiguration' => ['test_field' => ['rel' => [
                'module' => 'test_module',
                'is_main' => false,
                'link' => ['relationship_name' => 'test_relationship'],
            ]]]]
        );
        $eventHandler->expects($this->once())->method('handleAddRelationship');
        $hook = new Hook($eventHandler, $configMock);
        $hook->handleAddRelationship(
            BeanFactory::getBean('Accounts'),
            'test_event',
            ['related_module' => 'test_module', 'related_id' => 'test_id', 'relationship' => 'test_relationship']
        );
    }

    public function testHandleRelationshipNameMismatch()
    {
        $eventHandler = $this->createMock(EventHandler::class);
        $configMock = $this->createConfiguredMock(
            Configuration::class,
            ['getModuleConfiguration' => ['test_field' => ['rel' => [
                'module' => 'test_module',
                'is_main' => false,
                'link' => ['relationship_name' => 'test_relationship'],
            ]]]]
        );
        $eventHandler->expects($this->never())->method('handleAddRelationship');
        $eventHandler->expects($this->never())->method('handleAddRelationshipWithValue');
        $hook = new Hook($eventHandler, $configMock);
        $hook->handleAddRelationship(
            BeanFactory::getBean('Accounts'),
            'test_event',
            ['related_module' => 'test_module', 'related_id' => 'test_id', 'relationship' => 'another']
        );
    }
}
