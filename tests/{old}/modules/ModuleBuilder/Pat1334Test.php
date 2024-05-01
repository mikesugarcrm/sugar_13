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
 * PAT-1334
 * Email field missing in Accounts for listview search
 */
class PAT1334Test extends TestCase
{
    protected function setUp(): void
    {
        VardefManager::loadVardef('Contacts', 'Contact');
    }

    /**
     * Check email/email1 field for mobile client
     */
    public function testEmailFieldsForMobileClient()
    {
        global $dictionary;

        $def = $dictionary['Contact']['fields']['email'];
        $this->assertTrue(AbstractMetaDataParser::validField($def, 'edit', 'mobile'));
        $this->assertTrue(AbstractMetaDataParser::validField($def, 'detail', 'mobile'));
        $this->assertTrue(AbstractMetaDataParser::validField($def, 'list', 'mobile'));

        $def = $dictionary['Contact']['fields']['email1'];
        $this->assertFalse(AbstractMetaDataParser::validField($def, 'edit', 'mobile'));
        $this->assertFalse(AbstractMetaDataParser::validField($def, 'detail', 'mobile'));
        $this->assertFalse(AbstractMetaDataParser::validField($def, 'list', 'mobile'));
    }

    /**
     * Check email/email1 field for mobile client
     */
    public function testEmailFieldsForBaseClient()
    {
        global $dictionary;

        $def = $dictionary['Contact']['fields']['email'];
        $this->assertTrue(AbstractMetaDataParser::validField($def, 'edit', 'base'));
        $this->assertTrue(AbstractMetaDataParser::validField($def, 'detail', 'base'));
        $this->assertTrue(AbstractMetaDataParser::validField($def, 'list', 'base'));
        $this->assertTrue(AbstractMetaDataParser::validField($def, 'search', 'base'));

        $def = $dictionary['Contact']['fields']['email1'];
        $this->assertFalse(AbstractMetaDataParser::validField($def, 'edit', 'base'));
        $this->assertFalse(AbstractMetaDataParser::validField($def, 'detail', 'base'));
        $this->assertFalse(AbstractMetaDataParser::validField($def, 'list', 'base'));
        $this->assertFalse(AbstractMetaDataParser::validField($def, 'search', 'base'));
    }
}
