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
 * @coversDefaultClass M2MRelationship
 */
class M2MRelationshipLoadTest extends TestCase
{
    private static $opportunity;
    private static $opportunity2;
    private static $opportunity3;
    private static $contact;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        self::$contact = SugarTestContactUtilities::createContact();
        $assignedUserId = create_guid();
        self::$opportunity = SugarTestOpportunityUtilities::createOpportunity();
        self::$opportunity->assigned_user_id = $assignedUserId;
        self::$opportunity->save();
        self::$opportunity2 = SugarTestOpportunityUtilities::createOpportunity();
        self::$opportunity2->assigned_user_id = $assignedUserId;
        self::$opportunity2->save();
        self::$opportunity3 = SugarTestOpportunityUtilities::createOpportunity();
        self::$opportunity3->assigned_user_id = $assignedUserId;
        self::$opportunity3->save();

        self::$opportunity->load_relationship('contacts');
        self::$opportunity2->load_relationship('contacts');
        self::$opportunity3->load_relationship('contacts');
        self::$opportunity->contacts->add(self::$contact, ['contact_role' => 'test1']);
        self::$opportunity2->contacts->add(self::$contact, ['contact_role' => 'test2']);
        self::$opportunity3->contacts->add(self::$contact, ['contact_role' => 'test3']);
        self::$opportunity3->mark_deleted(self::$opportunity3->id);
        self::$contact->load_relationship('opportunities');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::load
     */
    public function testLoad()
    {
        $opportunities = self::$contact->opportunities->query([]);
        $this->assertCount(2, $opportunities['rows']);
    }

    public function whereDataProvider()
    {
        return [
            [1, 'test1'],
            [1, 'test2'],
            [0, 'test3'],
            [0, 'test4'],
        ];
    }

    /**
     * @dataProvider whereDataProvider
     * @covers ::load
     */
    public function testWhere($expectedCount, $value)
    {
        $opportunities = self::$contact->opportunities->query(
            ['where' => ['lhs_field' => 'contact_role', 'operator' => '=', 'rhs_value' => $value]]
        );
        $this->assertCount($expectedCount, $opportunities['rows']);
    }

    /**
     * @covers ::load
     */
    public function testOrder()
    {
        $opportunities = self::$contact->opportunities->query(
            ['where' => ['lhs_field' => 'contact_role', 'operator' => '=', 'rhs_value' => 'test1']]
        );
        $relId1 = key($opportunities['rows']);
        $opportunities = self::$contact->opportunities->query(
            ['where' => ['lhs_field' => 'contact_role', 'operator' => '=', 'rhs_value' => 'test2']]
        );
        $relId2 = key($opportunities['rows']);

        $opportunities = self::$contact->opportunities->query(['orderby' => 'contact_role DESC']);

        $this->assertCount(2, $opportunities['rows']);

        $row1 = $opportunities['rows'][$relId1];
        $this->assertSame($relId1, $row1['id']);
        $this->assertSame('test1', $row1['contact_role']);

        $row2 = $opportunities['rows'][$relId2];
        $this->assertSame($relId2, $row2['id']);
        $this->assertSame('test2', $row2['contact_role']);
    }

    /**
     * @covers ::load
     */
    public function testLimit()
    {
        $opportunities = self::$contact->opportunities->query(
            ['where' => ['lhs_field' => 'contact_role', 'operator' => '=', 'rhs_value' => 'test2']]
        );
        $relId2 = key($opportunities['rows']);

        $opportunities = self::$contact->opportunities->query(
            ['limit' => 1, 'offset' => 1, 'orderby' => 'contact_role ASC']
        );

        $row2 = $opportunities['rows'][$relId2];
        $this->assertEquals($relId2, $row2['id']);
        $this->assertEquals('test2', $row2['contact_role']);
    }

    /**
     * @covers ::load
     */
    public function testNoLimit()
    {
        $opportunities = self::$contact->opportunities->query(
            ['limit' => -1, 'offset' => 0]
        );
        $this->assertCount(2, $opportunities['rows'], 'Limit = -1 means that no limit is set');
    }

    /**
     * @covers ::getSugarQuery
     */
    public function testRelatedOwnerId()
    {
        $opportunities = self::$contact->opportunities->query(
            ['limit' => 1, 'offset' => 0]
        );
        $relId = key($opportunities['rows']);

        $this->assertEquals(
            self::$opportunity->assigned_user_id,
            $opportunities['rows'][$relId]['related_owner_id'],
            'Expected related_owner_id to be added to the list of related fields in the select'
        );
    }

    /**
     * @covers ::load
     *
     */
    public function testOffsetException()
    {
        $this->expectException(Exception::class);
        self::$contact->opportunities->query(
            ['limit' => 1, 'offset' => -1]
        );
    }

    public function deletedDataProvider()
    {
        return [
            [2, 0],
            [1, 1],
        ];
    }

    /**
     * @dataProvider deletedDataProvider
     * @covers ::load
     */
    public function testDeleted($expectedCount, $deleted)
    {
        $opportunities = self::$contact->opportunities->query(['deleted' => $deleted]);
        $this->assertCount($expectedCount, $opportunities['rows']);
    }
}
