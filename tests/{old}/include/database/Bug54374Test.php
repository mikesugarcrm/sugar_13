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
 * Bug54374Test.php
 * This is a test for the massageValue function.  There was a problem with the IBMDB2Manager implementation in that some
 * code we had assumed that this function would return a value, but the IBMDB2Manager implementation had a few mistakes
 * in that the code was never written correctly and there was no guarantee that a value would be returned form massageValue.
 */
class Bug54374Test extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        if (empty($this->db)) {
            $this->db = DBManagerFactory::getInstance();
        }
    }

    /**
     * This is the provider function it returns an array of arrays.  The keys to the nested array correspond to a value,
     * a vardef entry and an expected value
     *
     * @return array
     */
    public function provider()
    {
        return [
            [
                'hello',
                [
                    'name' => 'name',
                    'type' => 'name',
                    'dbType' => 'varchar',
                    'vname' => 'LBL_NAME',
                    'len' => 150,
                    'comment' => 'Name of the Company',
                    'unified_search' => true,
                    'full_text_search' => ['enabled' => true, 'boost' => 3],
                    'audited' => true,
                    'required' => true,
                    'importable' => 'required',
                    'merge_filter' => 'selected',
                ],
                "'hello'",
            ],
        ];
    }

    /**
     * @dataProvider provider
     *
     * @param $val Value of data
     * @param $fieldDef Field definition array
     */
    public function testMessageValue($val, $fieldDef, $expected)
    {
        $val = $this->db->massageValue($val, $fieldDef);
        $this->assertEquals($expected, $val, "Assert that {$expected} is equal to {$val} after massageValue");
    }
}
