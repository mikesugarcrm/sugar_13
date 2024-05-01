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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

class EmailsApiIntegrationTestCase extends TestCase
{
    protected $service;

    public static function setUpBeforeClass(): void
    {
        OutboundEmailConfigurationTestHelper::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        OutboundEmailConfigurationTestHelper::tearDown();
    }

    protected function tearDown(): void
    {
        // Clean up any dangling beans that need to be resaved.
        SugarRelationship::resaveRelatedBeans(false);
    }

    /**
     * Creates a new Emails record through {@link EmailsApi::createRecord()} that will be deleted during tear down.
     *
     * @param array $args
     * @return array The API response from creating the Emails record.
     */
    protected function createRecord(array $args)
    {
        $args['module'] = 'Emails';
        $args['name'] = 'Sugar Email' . Uuid::uuid1();
        $args['description'] = 'blah blah blah';
        $args['description_html'] = 'blah <b>blah</b> <i>blah</i>';
        $api = new EmailsApi();
        $record = $api->createRecord($this->service, $args);
        SugarTestEmailUtilities::setCreatedEmail($record['id']);
        return $record;
    }

    /**
     * Updates an existing Emails record through {@link EmailsApi::updateRecord()}.
     *
     * @param string $id The ID of the record to update.
     * @param array $args
     * @return array The API response from updating the Emails record.
     */
    protected function updateRecord($id, array $args)
    {
        $args['module'] = 'Emails';
        $args['record'] = $id;
        $api = new EmailsApi();
        return $api->updateRecord($this->service, $args);
    }

    /**
     * Delete an existing Emails record through {@link EmailsApi::deleteRecord()}.
     *
     * @param string $id The ID of the record to delete.
     * @param array $args
     * @return array The API response from deleting the Emails record.
     */
    protected function deleteRecord($id, $args = [])
    {
        $args['module'] = 'Emails';
        $args['record'] = $id;
        $api = new EmailsApi();
        return $api->deleteRecord($this->service, $args);
    }

    /**
     * Asserts that the specified collection contains the expected records.
     *
     * @param array $expected API-formatted records that are expected.
     * @param array $collection The collection of records linked to the Emails record.
     * @param string $message The message to display when the assertion fails.
     */
    protected function assertRecords(array $expected, array $collection, $message = '')
    {
        /**
         * Sorts the array of records by it's "parent_name" attribute.
         *
         * @param array $a
         * @param array $b
         * @return int
         */
        $rsort = function (array $a, array $b) {
            return ($a['parent_name'] < $b['parent_name']) ? -1 : 1;
        };

        // Sort the records so they can be compared with confidence. We don't care so much about asserting that the API
        // responded with the records in a certain order.
        usort($expected, $rsort);
        usort($collection['records'], $rsort);

        // Testing for these attributes is unnecessary.
        foreach ($collection['records'] as &$record) {
            unset($record['_acl']);
            unset($record['locked_fields']);
            unset($record['id']);
            unset($record['date_entered']);
            unset($record['date_modified']);

            if (isset($record['parent'])) {
                unset($record['parent']['_acl']);
            }

            if (isset($record['email_addresses'])) {
                unset($record['email_addresses']['_acl']);
            }
        }

        $this->assertEquals($expected, $collection['records'], $message);
    }

    /**
     * Asserts that each attachment's corresponding file exists.
     *
     * @param array $attachments The records from the response retrieved using
     * {@link EmailsApiIntegrationTestCase::getRelatedRecords()}.
     */
    protected function assertFiles(array $attachments)
    {
        foreach ($attachments as $attachment) {
            if (empty($attachment['upload_id'])) {
                $this->assertFileExists(
                    "upload://{$attachment['id']}",
                    "The file {$attachment['id']} should exist"
                );
            } else {
                $this->assertFileExists(
                    "upload://{$attachment['upload_id']}",
                    "The file {$attachment['upload_id']} should exist"
                );
                $this->assertFileDoesNotExist(
                    "upload://{$attachment['id']}",
                    "The file {$attachment['id']} should not exist"
                );
            }
        }
    }

    /**
     * Retrieves the specified collection for an Emails record using {@link RelateCollectionApi::getCollection()} as a
     * convenience for use in assertions.
     *
     * @param string $id The ID of the Emails record that contains the collection.
     * @param string $collection The name of the collection field.
     * @return array
     */
    protected function getCollection($id, $collection)
    {
        $args = [
            'module' => 'Emails',
            'record' => $id,
            'collection_name' => $collection,
            'fields' => [
                'parent_name',
                'email_address_id',
                'email_address',
            ],
        ];
        $api = new RelateCollectionApi();
        return $api->getCollection($this->service, $args);
    }

    /**
     * Retrieves an Emails record's linked beans using {@link RelateApi::filterRelated()} as a convenience for use in
     * assertions.
     *
     * @param string $id The ID of the Emails record.
     * @param string $link The name of the link field.
     * @return array
     */
    protected function getRelatedRecords($id, $link)
    {
        $args = [
            'module' => 'Emails',
            'record' => $id,
            'link_name' => $link,
        ];
        $api = new RelateApi();
        return $api->filterRelated($this->service, $args);
    }

    /**
     * Load data from the emails_text table for the record specified by ID.
     *
     * @param string $id
     * @return null|SugarBean
     */
    protected function retrieveEmailText($id)
    {
        $bean = BeanFactory::retrieveBean('Emails', $id);
        $bean->retrieveEmailText();
        return $bean;
    }

    /**
     * Sets up an EmailParticipants bean data from the data on the bean and the email address so that it is ready to add
     * to link using the REST API.
     *
     * @param null|SugarBean $bean
     * @param null|SugarBean $address
     * @return array
     */
    protected function createEmailParticipant($bean, $address = null)
    {
        $data = [];

        if ($bean) {
            $data['parent_type'] = $bean->getModuleName();
            $data['parent_id'] = $bean->id;
        }

        if ($address) {
            $data['email_address_id'] = $address->id;
        }

        return $data;
    }

    /**
     * Returns the ID of an email address.
     *
     * @param string $address
     * @return string
     */
    protected function getEmailAddressId($address)
    {
        $ea = BeanFactory::newBean('EmailAddresses');
        return $ea->getGuid($address);
    }
}
