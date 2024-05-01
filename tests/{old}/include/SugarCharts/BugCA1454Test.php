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
 * Bug CA-1454: Matrix report with grouping on two different field types throws an error on drilldown
 *
 * @ticket CA-1454
 */
class BugCA1454Test extends TestCase
{
    /**
     * @var SugarChart
     */
    protected $sugarChartObject;

    protected function setUp(): void
    {
        $this->sugarChartObject = new SugarChart();
    }

    /**
     * Test correct XML
     *
     * @dataProvider xmlTestsProvider
     */
    public function testCorrectXml($xmlContent)
    {
        $newXmlContent = $this->sugarChartObject->cleanupXML($xmlContent);
        $wrongCharactersWereRemoved = strpos($newXmlContent, '&reg;') === false;
        $this->assertEquals(true, $wrongCharactersWereRemoved);
    }

    public function xmlTestsProvider()
    {
        return [
            [
                '<?xml version="1.0" encoding="UTF-8"?>
                <sugarcharts version="1.0">
                    <data>
                        <group>
                            <title>test</title>
                            <value>1</value>
                            <label>4</label>
                        </group>
                    </data>
                </sugarcharts>',
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
                <sugarcharts version="1.0">
                    <data>
                        <group>
                            <title>test &reg;</title>
                            <value>1</value>
                            <label>4</label>
                        </group>
                    </data>
                </sugarcharts>',
            ],
        ];
    }
}
