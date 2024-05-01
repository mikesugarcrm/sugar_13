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

require_once 'include/utils.php';

class UtilsStringFormatTest extends TestCase
{
    public function testArrayStringFormat()
    {
        $output = string_format(
            'I am {0} feet tall, my name is {1} and I like {2}',
            [7, 'Hans', 'finger puppets', 'Llama licks the world']
        );
        $this->assertEquals(
            'I am 7 feet tall, my name is Hans and I like finger puppets',
            $output,
            'String format failed to replace some variables.'
        );
    }

    public function testAssocStringFormat()
    {
        $output = string_format(
            'I am {feetTall} feet tall, my name is {firstName} and I like {thingILike}',
            [
                'feetTall' => 7,
                'firstName' => 'Hans',
                'lastName' => 'Ironsmithson',
                'thingILike' => 'finger puppets',
                'thingIHate' => 'Llama licks the world',
            ]
        );
        $this->assertEquals(
            'I am 7 feet tall, my name is Hans and I like finger puppets',
            $output,
            'String format failed to replace some variables from an associative array'
        );
    }
}
