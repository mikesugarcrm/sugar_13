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

class Bug49719Test extends TestCase
{
    private $quote;
    private $contact1;
    private $contact2;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->quote = SugarTestQuoteUtilities::createQuote();
        $this->contact1 = SugarTestContactUtilities::createContact();
        $this->contact2 = SugarTestContactUtilities::createContact();
    }

    protected function tearDown(): void
    {
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestContactUtilities::removeAllCreatedContacts();
        unset($this->quote, $this->contact1, $this->contact2);
    }

    public function testQuoteShipContact()
    {
        $this->quote->shipping_contact_name = $this->contact1->name;
        $this->quote->shipping_contact_id = $this->contact1->id;
        $this->quote->billing_contact_name = $this->contact1->name;
        $this->quote->billing_contact_id = $this->contact1->id;
        $this->quote->save();

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Ship To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Bill To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);

        $this->quote->shipping_contact_name = $this->contact2->name;
        $this->quote->shipping_contact_id = $this->contact2->id;
        $this->quote->billing_contact_name = $this->contact2->name;
        $this->quote->billing_contact_id = $this->contact2->id;
        $this->quote->save();

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Ship To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Bill To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);

        $this->quote->shipping_contact_name = $this->contact1->name;
        $this->quote->shipping_contact_id = $this->contact1->id;
        $this->quote->billing_contact_name = $this->contact1->name;
        $this->quote->billing_contact_id = $this->contact1->id;
        $this->quote->save();

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Ship To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Bill To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);
    }
}
