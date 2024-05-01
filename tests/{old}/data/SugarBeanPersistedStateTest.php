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
 * @coversDefaultClass SugarBean
 */
class SugarBeanPersistedStateTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public function testStateChangesAfterCreation()
    {
        // $bean - is just created bean
        $bean = $this->createTestBean();
        $bean->name = 'Test';
        // changed a field, so proper size of changes should be 1
        $this->assertCount(1, $bean->getStateChanges());
    }

    public function testStateChangesAfterRetrieving()
    {
        $bean = $this->createTestBean();
        $bean->retrieve();
        $bean->name = 'Test';
        // changed a field, so proper size of changes should be 1
        $this->assertCount(1, $bean->getStateChanges());
    }

    /**
     * After a bean creation we should have an empty changes
     * @covers ::getStateChanges
     */
    public function testPersistedStateInitial(): SugarBean
    {
        $bean = $this->createTestBean();
        $this->assertNoStateChanges($bean);

        return $bean;
    }

    /**
     * Change a varchar field and ensure that this was reflected in the changes
     * @covers ::getStateChanges
     */
    public function testStateChangesForStrings()
    {
        $bean = $this->createTestBean();
        $value = 'TEST_NAME';
        $before = $bean->name;
        $bean->name = $value;
        $changes = $bean->getStateChanges();
        $this->assertArrayHasKey('name', $changes);

        $expect = [
            'field_name' => 'name',
            'data_type' => 'varchar',
            'before' => $before,
            'after' => $value,
        ];
        $this->assertEquals($expect, $changes['name']);
    }

    /**
     * Save and ensure that the state was persisted (changes is empty)
     * @covers ::getStateChanges
     */
    public function testStateChangesEmptyAfterSave()
    {
        $bean = $this->createTestBean();
        $bean->save();
        $this->assertNoStateChanges($bean);
    }

    /**
     * Change relate field and relate ID field and ensure that this was reflected in the changes
     * @covers ::getStateChanges
     */
    public function testStateChangesForRelateFields()
    {
        $bean = $this->createTestBean();

        $beforeRelateField = $bean->parent_name;
        $beforeRelateIdField = $bean->parent_id;
        $bean->parent_name = 'relateFieldValue';
        $bean->parent_id = 'relateIdFieldValue';
        $changes = $bean->getStateChanges();
        $this->assertArrayHasKey('parent_name', $changes);
        $this->assertArrayHasKey('parent_id', $changes);

        $expect = [
            'parent_name' => [
                'field_name' => 'parent_name',
                'data_type' => 'relate',
                'before' => $beforeRelateField,
                'after' => $bean->parent_name,
            ],
            'parent_id' => [
                'field_name' => 'parent_id',
                'data_type' => 'id',
                'before' => $beforeRelateIdField,
                'after' => $bean->parent_id,
            ],
        ];
        $this->assertEquals($expect, $changes);
    }

    /**
     * Retrieve should populate persisted_state so the changes should be empty again
     * @covers ::getStateChanges
     */
    public function testStateChangesEmptyAfterRetrieve()
    {
        $bean = $this->createTestBean();
        $bean->retrieve();
        $this->assertNoStateChanges($bean);
    }

    /**
     * Change an integer field and ensure that this was reflected in the changes
     * @covers ::getStateChanges
     */
    public function testStateChangesForIntField()
    {
        $bean = $this->createTestBean();
        $value = 1;
        $before = $bean->deleted;
        $bean->deleted = $value;
        $changes = $bean->getStateChanges();
        $this->assertArrayHasKey('deleted', $changes);

        $expect = [
            'field_name' => 'deleted',
            'data_type' => 'bool',
            'before' => $before,
            'after' => $value,
        ];
        $this->assertEquals($expect, $changes['deleted']);
    }

    /**
     * Change an integer field and ensure that this was reflected in the changes
     * @covers ::getStateChanges
     */
    public function testStateChangesForDateField()
    {
        $bean = $this->createTestBean();
        $value = '2000-01-01 22:33:44';
        $before = $bean->date_modified;
        $bean->date_modified = $value;
        $changes = $bean->getStateChanges();
        $this->assertArrayHasKey('date_modified', $changes);

        $expect = [
            'field_name' => 'date_modified',
            'data_type' => 'datetime',
            'before' => $before,
            'after' => $value,
        ];
        $this->assertEquals($expect, $changes['date_modified']);
    }

    /**
     * Change an email field and ensure that this was reflected in the changes
     * @covers ::getStateChanges
     */
    public function testStateChangesForEmailField()
    {
        $bean = $this->createTestBean();
        $before = $bean->emailAddress->addresses;
        $bean->emailAddress->addAddress('test1@sugar.com');
        $changes = $bean->getStateChanges();
        $this->assertNotEmpty($changes);
        $this->assertArrayHasKey('email', $changes);

        $expect = [
            'field_name' => 'email',
            'data_type' => 'email',
            'before' => $before,
            'after' => $bean->emailAddress->addresses,
        ];
        $this->assertEquals($expect, $changes['email']);
    }

    private function createTestBean(): SugarBean
    {
        return SugarTestAccountUtilities::createAccount();
    }

    private function assertNoStateChanges(SugarBean $bean): void
    {
        $changes = $bean->getStateChanges();
        $this->assertEquals([], $changes);
    }
}
