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

namespace Sugarcrm\SugarcrmTestsUnit\inc;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TimeDate
 */
class TimeDateTest extends TestCase
{
    public function getRegularExpressionProvider()
    {
        return [
            [
                'm/d/Y',
                '^\s*(0[1-9]|1[0-2])/(0[1-9]|[12]\d|3[01])/(\d{4})\s*$',
                [
                    'm' => 1,
                    'd' => 2,
                    'Y' => 3,
                ],
            ],
            [
                'Y-n-j',
                '^\s*(\d{4})-(1[0-2]|[1-9])-([12]\d|3[01]|[1-9])\s*$',
                [
                    'Y' => 1,
                    'n' => 2,
                    'j' => 3,
                ],
            ],
            [
                'h:i a',
                '^\s*(0[1-9]|1[0-2]):([0-5]\d) ([ ]*[ap]m)\s*$',
                [
                    'h' => 1,
                    'i' => 2,
                    'a' => 3,
                ],
            ],
            [
                'H.i A',
                '^\s*([01]\d|2[0-3]).([0-5]\d) ([ ]*[AP]M)\s*$',
                [
                    'H' => 1,
                    'i' => 2,
                    'A' => 3,
                ],
            ],
            [
                'g:i:s',
                '^\s*(1[0-2]|[1-9]):([0-5]\d):([0-5]\d)\s*$',
                [
                    'g' => 1,
                    'i' => 2,
                    's' => 3,
                ],
            ],
            [
                'G:i:s',
                '^\s*(1\d|2[0-3]|\d):([0-5]\d):([0-5]\d)\s*$',
                [
                    'G' => 1,
                    'i' => 2,
                    's' => 3,
                ],
            ],
            [
                'F j, Y',
                '^\s*(\w+) ([12]\d|3[01]|[1-9]), (\d{4})\s*$',
                [
                    'F' => 1,
                    'j' => 2,
                    'Y' => 3,
                ],
            ],
            [
                'M d',
                '^\s*([\w]{1,3}) (0[1-9]|[12]\d|3[01])\s*$',
                [
                    'M' => 1,
                    'd' => 2,
                ],
            ],
            [
                'd C \i',
                '^\s*(0[1-9]|[12]\d|3[01]) C \i\s*$',
                [
                    'd' => 1,
                ],
            ],
        ];
    }

    /**
     * @covers ::get_regular_expression
     * @dataProvider getRegularExpressionProvider
     * @param $input
     * @param $format
     * @param $positions
     */
    public function testGetRegularExpression($input, $format, $positions)
    {
        $regex = \TimeDate::get_regular_expression($input);
        $this->assertEquals($format, $regex['format'], 'The format does not match');
        $this->assertEquals($positions, $regex['positions'], 'The number of positions is incorrect');
    }

    public function formatToRegexProvider()
    {
        return [
            [
                'a',
                'am',
                1,
            ],
            [
                'a',
                ' am',
                1,
            ],
            [
                'a',
                'pm',
                1,
            ],
            [
                'a',
                ' pm',
                1,
            ],
            [
                'a',
                'tm',
                0,
            ],
            [
                'A',
                'AM',
                1,
            ],
            [
                'A',
                ' AM',
                1,
            ],
            [
                'A',
                'PM',
                1,
            ],
            [
                'A',
                ' PM',
                1,
            ],
            [
                'A',
                'TM',
                0,
            ],
            [
                'd',
                '00',
                0,
            ],
            [
                'd',
                '01',
                1,
            ],
            [
                'd',
                '09',
                1,
            ],
            [
                'd',
                '14',
                1,
            ],
            [
                'd',
                '27',
                1,
            ],
            [
                'd',
                '30',
                1,
            ],
            [
                'd',
                '31',
                1,
            ],
            [
                'd',
                '32',
                0,
            ],
            [
                'd',
                '40',
                0,
            ],
            [
                'd',
                '100',
                0,
            ],
            [
                'j',
                '0',
                0,
            ],
            [
                'j',
                '01',
                0,
            ],
            [
                'j',
                '1',
                1,
            ],
            [
                'j',
                '9',
                1,
            ],
            [
                'j',
                '10',
                1,
            ],
            [
                'j',
                '29',
                1,
            ],
            [
                'j',
                '30',
                1,
            ],
            [
                'j',
                '31',
                1,
            ],
            [
                'j',
                '32',
                0,
            ],
            [
                'j',
                '40',
                0,
            ],
            [
                'j',
                '100',
                0,
            ],
            [
                'h',
                '0',
                0,
            ],
            [
                'h',
                '1',
                0,
            ],
            [
                'h',
                '00',
                0,
            ],
            [
                'h',
                '01',
                1,
            ],
            [
                'h',
                '09',
                1,
            ],
            [
                'h',
                '10',
                1,
            ],
            [
                'h',
                '12',
                1,
            ],
            [
                'h',
                '13',
                0,
            ],
            [
                'h',
                '20',
                0,
            ],
            [
                'h',
                '100',
                0,
            ],
            [
                'H',
                '0',
                0,
            ],
            [
                'H',
                '1',
                0,
            ],
            [
                'H',
                '00',
                1,
            ],
            [
                'H',
                '03',
                1,
            ],
            [
                'H',
                '10',
                1,
            ],
            [
                'H',
                '17',
                1,
            ],
            [
                'H',
                '23',
                1,
            ],
            [
                'H',
                '24',
                0,
            ],
            [
                'H',
                '30',
                0,
            ],
            [
                'H',
                '100',
                0,
            ],
            [
                'g',
                '0',
                0,
            ],
            [
                'g',
                '00',
                0,
            ],
            [
                'g',
                '1',
                1,
            ],
            [
                'g',
                '10',
                1,
            ],
            [
                'g',
                '12',
                1,
            ],
            [
                'g',
                '13',
                0,
            ],
            [
                'g',
                '20',
                0,
            ],
            [
                'g',
                '100',
                0,
            ],
            [
                'G',
                '0',
                1,
            ],
            [
                'G',
                '00',
                0,
            ],
            [
                'G',
                '01',
                0,
            ],
            [
                'G',
                '1',
                1,
            ],
            [
                'G',
                '10',
                1,
            ],
            [
                'G',
                '20',
                1,
            ],
            [
                'G',
                '23',
                1,
            ],
            [
                'G',
                '24',
                0,
            ],
            [
                'G',
                '30',
                0,
            ],
            [
                'G',
                '100',
                0,
            ],
            [
                'i',
                '0',
                0,
            ],
            [
                'i',
                '00',
                1,
            ],
            [
                'i',
                '01',
                1,
            ],
            [
                'i',
                '1',
                0,
            ],
            [
                'i',
                '10',
                1,
            ],
            [
                'i',
                '29',
                1,
            ],
            [
                'i',
                '59',
                1,
            ],
            [
                'i',
                '60',
                0,
            ],
            [
                'i',
                '100',
                0,
            ],
            [
                'm',
                '0',
                0,
            ],
            [
                'm',
                '1',
                0,
            ],
            [
                'm',
                '00',
                0,
            ],
            [
                'm',
                '01',
                1,
            ],
            [
                'm',
                '09',
                1,
            ],
            [
                'm',
                '10',
                1,
            ],
            [
                'm',
                '12',
                1,
            ],
            [
                'm',
                '13',
                0,
            ],
            [
                'm',
                '20',
                0,
            ],
            [
                'm',
                '100',
                0,
            ],
            [
                'n',
                '0',
                0,
            ],
            [
                'n',
                '00',
                0,
            ],
            [
                'n',
                '1',
                1,
            ],
            [
                'n',
                '10',
                1,
            ],
            [
                'n',
                '12',
                1,
            ],
            [
                'n',
                '13',
                0,
            ],
            [
                'n',
                '20',
                0,
            ],
            [
                'n',
                '100',
                0,
            ],
            [
                'Y',
                '1',
                0,
            ],
            [
                'Y',
                '91',
                0,
            ],
            [
                'Y',
                '200',
                0,
            ],
            [
                'Y',
                '1987',
                1,
            ],
            [
                'Y',
                '2015',
                1,
            ],
            [
                'Y',
                '2031',
                1,
            ],
            [
                'Y',
                'abcd',
                0,
            ],
            [
                's',
                '0',
                0,
            ],
            [
                's',
                '00',
                1,
            ],
            [
                's',
                '01',
                1,
            ],
            [
                's',
                '1',
                0,
            ],
            [
                's',
                '10',
                1,
            ],
            [
                's',
                '29',
                1,
            ],
            [
                's',
                '59',
                1,
            ],
            [
                's',
                '60',
                0,
            ],
            [
                's',
                '100',
                0,
            ],
            [
                'i',
                '0',
                0,
            ],
            [
                'i',
                '00',
                1,
            ],
            [
                'i',
                '01',
                1,
            ],
            [
                'i',
                '1',
                0,
            ],
            [
                'i',
                '10',
                1,
            ],
            [
                'i',
                '29',
                1,
            ],
            [
                'i',
                '59',
                1,
            ],
            [
                'i',
                '60',
                0,
            ],
            [
                'F',
                'September',
                1,
            ],
            [
                'M',
                'March',
                0,
            ],
            [
                'M',
                'Feb',
                1,
            ],
            [
                'M',
                'Mar',
                1,
            ],
            [
                'm/d/Y',
                '       08/01/2012    ',
                1,
            ],
        ];
    }

    /**
     * @coversNothing
     * @dataProvider formatToRegexProvider
     * @param $format
     * @param $subject
     * @param $expected
     */
    public function testGetRegularExpression_VerifyTheRegularExpressions($format, $subject, $expected)
    {
        $regex = \TimeDate::get_regular_expression($format);
        $actual = preg_match("@{$regex['format']}@", $subject);
        $this->assertSame($expected, $actual);
    }
}
