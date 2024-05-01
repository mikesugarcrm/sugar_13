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

class Bug43551Test extends TestCase
{
    public function testSubStr()
    {
        $contact = new Contact();
        $contact->first_name = 'Fabio';
        $contact->last_name = 'Grande';

        // First 2 letters of first name - Fa
        $expr = 'subStr($first_name, 0, 2)';
        $result = Parser::evaluate($expr, $contact)->evaluate();
        $this->assertEquals('Fa', $result);

        // First 2 letters of last name - Gr
        $expr = 'subStr($last_name, 0, 2)';
        $result = Parser::evaluate($expr, $contact)->evaluate();
        $this->assertEquals('Gr', $result);

        // First 2 letters of last name concatenated to first 2 letters first name - GrFa
        $expr = 'concat(subStr($last_name, 0, 2), subStr($first_name, 0, 2))';
        $result = Parser::evaluate($expr, $contact)->evaluate();
        $this->assertEquals('GrFa', $result);

        // First 22 letters of last name concatenated to first 32 letters first name - GrandeFabio
        $expr = 'concat(subStr($last_name, 0, 22), subStr($first_name, 0, 32))';
        $result = Parser::evaluate($expr, $contact)->evaluate();
        $this->assertEquals('GrandeFabio', $result);

        // First 5 letters of last name concatenated to first name - Grand
        $expr = 'subStr(concat($last_name, $first_name), 0, 5)';
        $result = Parser::evaluate($expr, $contact)->evaluate();
        $this->assertEquals('Grand', $result);

        $contact->first_name = '';
        $contact->last_name = 'Grande';

        // First 2 letters of first name - is empty....
        $expr = 'subStr($first_name, 0, 2)';
        $result = Parser::evaluate($expr, $contact)->evaluate();
        $this->assertEquals('', $result);

        // First 2 letters of last name concatenated to first 2 letters first name (empty...) - Gr
        $expr = 'concat(subStr($last_name, 0, 2), subStr($first_name, 0, 2))';
        $result = Parser::evaluate($expr, $contact)->evaluate();
        $this->assertEquals('Gr', $result);
    }
}
