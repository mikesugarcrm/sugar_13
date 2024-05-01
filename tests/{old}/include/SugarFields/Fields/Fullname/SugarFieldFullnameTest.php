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
 * @covers SugarFieldFullname
 */
class SugarFieldFullnameTest extends TestCase
{
    /**
     * @var SugarFieldFullname
     */
    private $sf;

    protected function setUp(): void
    {
        $this->sf = new SugarFieldFullname('fullname');
    }

    public function testNameFormatFieldsAreConsidered()
    {
        global $locale;

        $locale = $this->getMockBuilder('Localization')
            ->setMethods(['getNameFormatFields'])
            ->disableOriginalConstructor()
            ->getMock();
        $locale->expects($this->once())
            ->method('getNameFormatFields')
            ->with('TheModule')
            ->willReturn(['foo', 'bar']);

        /** @var ViewIterator|MockObject $it */
        $it = $this->getMockBuilder('ViewIterator')
            ->disableOriginalConstructor()
            ->setMethods(['dummy'])
            ->getMock();

        $fields = [];
        $this->sf->setModule('TheModule');
        $this->sf->iterateViewField($it, [
            'name' => 'full_name',
        ], function ($field) use (&$fields) {
            $fields[] = $field;
        });

        $this->assertEquals([
            [
                'name' => 'foo',
            ],
            [
                'name' => 'bar',
            ],
        ], $fields);
    }
}
