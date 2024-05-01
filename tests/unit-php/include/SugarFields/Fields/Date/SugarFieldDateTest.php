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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarFields\Fields\Date;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \SugarFieldDate
 */
class SugarFieldDateTest extends TestCase
{
    /**
     * @var \SugarBean|MockObject
     */
    protected $focus;

    /**
     * @var \SugarFieldDate
     */
    protected $field;

    /**
     * @var \ImportFieldSanitize
     */
    protected $settings;

    /**
     * test set up
     */
    protected function setUp(): void
    {
        $this->focus = $this->createMock(\SugarBean::class);
        $this->field = new \SugarFieldDate('date');
        $this->settings = new \ImportFieldSanitize();
        $this->settings->timezone = 'America/Los_Angeles';
    }

    public function providerTestImportSanitize()
    {
        return [
            'good format' => ['m/d/Y', '03/03/2019', '2019-03-03'],
            'short year syntax, expected long' => ['m/d/Y', '03/03/19', false],
            'wrong date' => ['m/d/Y', 'wrong date', false],
            'just-after-midnight' => [
                'm/d/Y H:i:s',
                '04/23/2019 00:00:01',
                '2019-04-23',
            ],
            'just-before-midnight' => [
                'm/d/Y H:i:s',
                '04/23/2019 23:59:59',
                '2019-04-23',
            ],
        ];
    }

    /**
     * @dataProvider providerTestImportSanitize
     * @covers ::importSanitize
     * @param $format
     * @param $value
     * @param $expected
     */
    public function testImportSanitize($format, $value, $expected)
    {
        $this->settings->dateformat = $format;
        $this->assertEquals($expected, $this->field->importSanitize($value, [], $this->focus, $this->settings));
    }
}
