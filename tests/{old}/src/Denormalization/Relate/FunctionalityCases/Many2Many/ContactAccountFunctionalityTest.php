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

namespace Sugarcrm\SugarcrmTests\Denormalization\Relate\FunctionalityCases\Many2Many;

use SugarBean;
use SugarTestAccountUtilities;

class ContactAccountFunctionalityTest extends AbstractFunctionalityTest
{
    protected static $options = [
        'primary_module' => 'Contacts',
        'primary_link_name' => 'contacts',
        'relate_link_name' => 'accounts',
        'field_name' => 'account_name',
        'relate_field_name' => 'name',
    ];

    /**
     * Setting account_id on a new contact and saving should update denorm_account_name
     */
    public function testRelationshipCreationDuringSave(): void
    {
        $account = $this->createLinkedBean();
        $contact = \SugarTestContactUtilities::createContact('', ['account_id' => $account->id]);

        $this->assertEquals($account->{static::$options['relate_field_name']}, $contact->{$this->getDenormFieldName()});

        // reload and re-check to ensure that changes saved
        $contact = $this->reloadBean($contact);
        $this->assertEquals($account->{static::$options['relate_field_name']}, $contact->{$this->getDenormFieldName()});
    }

    protected function createPrimaryBean(?SugarBean $linkedBean): SugarBean
    {
        $contact = \SugarTestContactUtilities::createContact();
        if ($linkedBean) {
            $contact->account_id = $linkedBean->id;
            $contact->account_name = $linkedBean->name;
            $contact->save();
        }

        return $contact;
    }

    protected function createLinkedBean(): SugarBean
    {
        return SugarTestAccountUtilities::createAccount();
    }

    protected static function removeCreatedBeans(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        \SugarTestContactUtilities::removeAllCreatedContacts();
    }
}
