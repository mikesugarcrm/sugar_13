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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Studio\wizards;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \RenameModules
 */
class RenameModulesTest extends TestCase
{
    /**
     * @covers ::replaceSingleLabel
     */
    public function testReplacementInTheMiddleOfAWord()
    {
        $mock = $this->getMockBuilder(\RenameModules::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkDefaultsForSubstring'])
            ->getMock();
        $mock->method('checkDefaultsForSubstring')->willReturn(true);

        $replacementLabels = [
            'singular' => 'Feature',
            'plural' => 'Features',
            'prev_singular' => 'Bug',
            'prev_plural' => 'Bugs',
            'key_plural' => 'Bugs',
            'key_singular' => 'Bug',
        ];

        $args = [
            'List of Bugs',
            $replacementLabels,
            ['name' => 'LBL_SOME_TEST_LABEL', 'type' => 'singular',],
        ];
        $newString = TestReflection::callProtectedMethod($mock, 'replaceSingleLabel', $args);
        $this->assertEquals('List of Bugs', $newString, 'It should not replace part of the word');

        $args = [
            'List of Bugs',
            $replacementLabels,
            ['name' => 'LBL_SOME_TEST_LABEL', 'type' => 'plural',],
        ];
        $newString = TestReflection::callProtectedMethod($mock, 'replaceSingleLabel', $args);
        $this->assertEquals('List of Features', $newString, 'It should replace whole word');
    }
}
