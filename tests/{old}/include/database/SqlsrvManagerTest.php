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

class SqlsrvManagerTest extends MssqlManagerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new SqlsrvManager();
    }

    /**
     * @covers ::getMaxFieldSize
     * @dataProvider getMaxFieldSizeProvider
     */
    public function testGetMaxFieldSize($def, $expected)
    {
        $this->assertEquals($expected, $this->db->getMaxFieldSize($def));
    }

    public function getMaxFieldSizeProvider()
    {
        return [
            [['type' => 'html'], -1],
            [['type' => 'text'], -1],
            [['type' => 'bool'], -1],
            [['type' => 'unknown'], -1],
            [['unknown' => 'unknown'], -1],
        ];
    }
}
