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
use Sugarcrm\Sugarcrm\Util\Uuid;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Security\Subject\User;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient\Rest as RestApiClient;

class ActivityErasureTest extends TestCase
{
    private $dp;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = $GLOBALS['current_user']->getSystemUser();
        $this->dp = [];
        $GLOBALS['db']->query('DELETE FROM activities');
        $GLOBALS['db']->query('DELETE FROM comments');
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();

        if (!empty($this->dp)) {
            $GLOBALS['db']->query("DELETE FROM data_privacy WHERE id IN ('" . implode("','", $this->dp) . "')");
        }
        $this->dp = [];
    }

    public function testEraseActivities_Successful()
    {
        Activity::enable();

        $contactValues = [
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'primary_address_street' => '123 Main Street',
            'primary_address_city' => 'Milwaukee',
            'primary_address_state' => 'Wisconsin',
        ];

        $contact = SugarTestContactUtilities::createContact('', $contactValues);

        $contact->primary_address_street = '123 Park Avenue';
        $contact->primary_address_city = 'Chicago';
        $contact->primary_address_State = 'Illinois';
        $contact->save();

        $nonEmailFieldsToErase = ['first_name', 'last_name', 'primary_address_city'];
        $fieldsToErase = array_merge($nonEmailFieldsToErase, ['email']);
        $dp = $this->createDpErasureRecord($contact, $fieldsToErase);
        $GLOBALS['db']->commit();

        Activity::restoreToPreviousState();
        $activityErasure = new ActivityErasure();
        $result = $activityErasure->process([$dp->id]);

        $this->assertSame(
            0,
            $result['commentsUpdated'],
            'Not expecting any comments to have been updated'
        );
        $this->assertGreaterThan(
            0,
            $result['activitiesUpdated'],
            'Expecting one or more activities to have been updated'
        );

        $lastActivityUpdateData = json_decode($result['lastActivityUpdate']['Contacts'][$contact->id], true);
        $this->assertArrayHasKey(
            'object',
            $lastActivityUpdateData,
            'last Activity Update missing object'
        );
        $this->assertArrayHasKey(
            'name',
            $lastActivityUpdateData['object'],
            'last Activity Update [object] missing name'
        );
        $this->assertSame(
            'Contacts',
            $lastActivityUpdateData['object']['module'],
            'last Activity Update [object][module] should be Contacts'
        );
        $this->assertSame(
            $contact->id,
            $lastActivityUpdateData['object']['id'],
            'last Activity Update [object][id] should be same as Contact id'
        );
        $this->assertSame(
            'LBL_VALUE_ERASED',
            $lastActivityUpdateData['object']['name'],
            'last Activity Update [object][name] should be LBL_VALUE_ERASED'
        );
        $this->assertArrayHasKey(
            $contact->id,
            $result['changedNames']['Contacts'],
            'Contact Id not Found in changedNames array'
        );
        $this->assertSame(
            'LBL_VALUE_ERASED',
            $result['changedNames']['Contacts'][$contact->id],
            'Contact Name should appear in changedNames array as LBL_VALUE_ERASED'
        );
        $this->assertArrayHasKey(
            'changes',
            $lastActivityUpdateData,
            'last Activity Update missing changes'
        );
        $this->assertArrayHasKey(
            'email',
            $lastActivityUpdateData['changes'],
            'last Activity Update [changes] missing email'
        );
        $this->assertSame(
            'LBL_VALUE_ERASED',
            $lastActivityUpdateData['changes']['email']['before'],
            'last Activity Update [changes][email][before] should be LBL_VALUE_ERASED'
        );
        $this->assertSame(
            'LBL_VALUE_ERASED',
            $lastActivityUpdateData['changes']['email']['after'][0]['email_address'],
            'last Activity Update [changes][email][after][0][email_address] should be LBL_VALUE_ERASED'
        );

        foreach ($nonEmailFieldsToErase as $field) {
            $this->assertArrayHasKey(
                $field,
                $lastActivityUpdateData['changes'],
                'last Activity Update [changes] missing ' . $field
            );
            $this->assertSame(
                'LBL_VALUE_ERASED',
                $lastActivityUpdateData['changes'][$field]['before'],
                'last Activity Update [changes][' . $field . '][before] should be LBL_VALUE_ERASED'
            );
            $this->assertSame(
                'LBL_VALUE_ERASED',
                $lastActivityUpdateData['changes'][$field]['after'],
                'last Activity Update [changes][' . $field . '][after] should be LBL_VALUE_ERASED'
            );
        }
    }

    private function createDpErasureRecord($bean, $erasedFields)
    {
        $dp = BeanFactory::newBean('DataPrivacy');
        $dp->id = Uuid::uuid1();
        $dp->new_with_id = true;
        $dp->name = 'Data Privacy Test';
        $dp->type = 'Request to Erase Information';
        $dp->status = 'Open';
        $dp->priority = 'Low';
        $dp->assigned_user_id = $GLOBALS['current_user']->id;
        $dp->date_opened = $GLOBALS['timedate']->getDatePart($GLOBALS['timedate']->nowDb());
        $dp->date_due = $GLOBALS['timedate']->getDatePart($GLOBALS['timedate']->nowDb());
        $dp->save();

        $module = BeanFactory::getModuleName($bean);
        $linkName = strtolower($module);
        $dp->load_relationship($linkName);
        $dp->$linkName->add([$bean]);

        $options = ['use_cache' => false, 'encode' => false, 'disable_row_level_security' => true];
        $dp = BeanFactory::retrieveBean('DataPrivacy', $dp->id, $options);
        $dp->status = 'Closed';

        $fieldInfo = implode('","', $erasedFields);
        $dp->fields_to_erase = '{"' . $linkName . '":{"' . $bean->id . '":["' . $fieldInfo . '"]}}';

        $context = Container::getInstance()->get(Context::class);
        $subject = new User($GLOBALS['current_user'], new RestApiClient());
        $context->activateSubject($subject);
        $context->setAttribute('platform', 'base');

        $dp->save();
        $this->dp[] = $dp->id;
        return $dp;
    }
}
