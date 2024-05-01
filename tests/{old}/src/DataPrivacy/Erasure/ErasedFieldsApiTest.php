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

namespace Sugarcrm\SugarcrmTests\DataPrivacy\Erasure;

use ApiHelper;
use PHPUnit\Framework\TestCase;
use Contact;
use Lead;
use Note;
use SugarBean;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;
use SugarQuery;
use SugarTestContactUtilities;
use SugarTestHelper;
use SugarTestLeadUtilities;
use SugarTestNoteUtilities;
use SugarTestRestUtilities;
use BeanFactory;

class ErasedFieldsApiTest extends TestCase
{
    /**#@+
     * @var Contact
     */
    private static $contact1;
    private static $contact2;
    private static $contact3;
    /**#@-*/

    /**
     * @var Lead
     */
    private static $lead;

    /**
     * @var Note
     */
    private static $note;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user', [true, true]);

        self::$contact1 = SugarTestContactUtilities::createContact();
        self::$contact1->erase(FieldList::fromArray(['first_name']), false);

        self::$contact2 = SugarTestContactUtilities::createContact(null, [
            'reports_to_id' => self::$contact1->id,
        ]);
        self::$contact2->erase(FieldList::fromArray(['last_name']), false);

        self::$contact3 = SugarTestContactUtilities::createContact();
        self::$contact3->erase(FieldList::fromArray(['first_name', 'last_name']), false);

        self::$note = SugarTestNoteUtilities::createNote(null, [
            'contact_id' => self::$contact1->id,
            'parent_type' => self::$contact2->module_name,
            'parent_id' => self::$contact2->id,
        ]);

        self::$lead = SugarTestLeadUtilities::createLead(null, [
            'reports_to_id' => self::$contact3->id,
        ]);
    }

    /**
     * @test
     */
    public function ownFields()
    {
        $query = new SugarQuery();
        $query->from(self::$contact1, [
            'erased_fields' => true,
        ]);
        $query->where()->in('id', [
            self::$contact1->id,
            self::$contact2->id,
        ]);

        $contacts = self::$contact1->fetchFromQuery($query);
        $this->assertCount(2, $contacts);

        $data = array_map(function (SugarBean $bean) {
            return $this->format($bean);
        }, $contacts);

        $this->assertSame([
            'first_name',
        ], $data[self::$contact1->id]['_erased_fields']);
        $this->assertSame([
            'last_name',
        ], $data[self::$contact2->id]['_erased_fields']);
    }

    /**
     * @test
     */
    public function erasedFieldsAreOnlyDisplayedWnehPiiIsSelected()
    {
        $query = new SugarQuery();
        $query->from(self::$contact1, [
            'erased_fields' => true,
        ]);
        $query->select('id');
        $query->where()->equals('id', self::$contact1->id);

        $data = $query->execute();
        $this->assertCount(1, $data);

        $row = array_shift($data);
        $this->assertEquals(self::$contact1->id, $row['id']);

        $this->assertArrayNotHasKey('erased_fields', $row);
    }

    /**
     * @test
     * @dataProvider loaderProvider()
     */
    public function relateAndParentFields(callable $load)
    {
        $note = $load(self::$note, ['contact_name', 'parent_name']);
        $data = $this->format($note);

        $this->assertSame([
            'first_name',
        ], $data['contact']['_erased_fields']);
        $this->assertSame([
            'last_name',
        ], $data['parent']['_erased_fields']);
    }

    /**
     * @test
     * @dataProvider loaderProvider()
     */
    public function ownAndRelateFields(callable $load)
    {
        $contact = $load(self::$contact2, ['report_to_name']);
        $data = $this->format($contact);

        $this->assertSame([
            'last_name',
        ], $data['_erased_fields']);
        $this->assertSame([
            'first_name',
        ], $data['reports_to_link']['_erased_fields']);
    }

    /**
     * @test
     * @dataProvider loaderProvider()
     */
    public function relateFieldWithoutLink(callable $load)
    {
        $lead = $load(self::$lead, ['report_to_name']);
        $data = $this->format($lead);

        $this->assertSame([
            'report_to_name',
        ], $data['_erased_fields']);
    }

    public static function loaderProvider(): iterable
    {
        yield 'Via SugarQuery' => [
            function (SugarBean $bean, array $fields): SugarBean {
                $query = new SugarQuery();
                $query->from($bean, [
                    'erased_fields' => true,
                ])->select($fields);
                $query->where()->equals('id', $bean->id);

                $beans = $bean->fetchFromQuery($query);

                return array_shift($beans);
            },
        ];

        yield 'Via SugarBean::retrieve()' => [
            function (SugarBean $bean): SugarBean {
                return BeanFactory::retrieveBean($bean->module_name, $bean->id, [
                    'erased_fields' => true,
                    'use_cache' => false,
                ]);
            },
        ];
    }

    private function format(SugarBean $bean)
    {
        $api = SugarTestRestUtilities::getRestServiceMock();

        return ApiHelper::getHelper($api, $bean)->formatForApi($bean, [], [
            'args' => [
                'erased_fields' => true,
            ],
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestNoteUtilities::removeAllCreatedNotes();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }
}
