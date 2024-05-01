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

class MetaDataHacksTest extends TestCase
{
    /**
     * The metadata hacks class
     * @var MetaDataHacks
     */
    protected $mdh;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $this->mdh = new MetaDataHacks();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testFixRelateFields()
    {
        $fieldDefs = [
            'name' => [
                'type' => 'string',
                'dbType' => 'varchar',
            ],
            'myawesome_id' => [
                'type' => 'relate',
                'dbType' => 'id',
            ],
        ];

        $fieldDefsNew = $this->mdh->fixRelateFields($fieldDefs);
        $this->assertEquals(
            $fieldDefs['name']['type'],
            $fieldDefsNew['name']['type'],
            "Name changed, it shouldn't have."
        );
        $this->assertNotEquals(
            $fieldDefs['myawesome_id']['type'],
            $fieldDefsNew['myawesome_id']['type'],
            "the id field didn't change, it should have."
        );
        $this->assertEquals('id', $fieldDefsNew['myawesome_id']['type'], 'Type field of ID is not correct.');
    }
}
