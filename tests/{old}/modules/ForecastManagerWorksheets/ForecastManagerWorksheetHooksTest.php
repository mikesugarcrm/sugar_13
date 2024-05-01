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

class ForecastManagerWorksheetHooksTest extends TestCase
{
    /**
     * @dataProvider dataProviderSetManagerSavedFlag
     * @param array $data
     * @param boolean $expected
     */
    public function testSetManagerSavedFlag($data, $expected)
    {
        /** @var ForecastManagerWorksheet $worksheet */
        $worksheet = $this->createPartialMock('ForecastManagerWorksheet', ['save']);

        foreach ($data as $key => $value) {
            $worksheet->$key = $value;
        }

        ForecastManagerWorksheetHooks::setManagerSavedFlag($worksheet, 'before_save');

        $this->assertEquals($expected, $worksheet->manager_saved);
    }

    public static function dataProviderSetManagerSavedFlag()
    {
        /**
         * known draft_save_types:
         *  assign_quota - assigning quota to a manager sheet,
         *  draft - saving a manager draft so only applies with draft = 1,
         *  commit - commit a manager sheet,
         *  <blank> - everything else?
         *
         *  worksheet - unknown, original test value
         */
        return [
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => false,
                ],
                false, // was originally true but should be false if it functions as <blank>
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 0,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => false,
                ],
                false,
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => true,
                ],
                true,
            ],
            [
                [
                    'assigned_user_id' => 'test_user_1',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => false,
                ],
                false,
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'assign_quota',
                    'manager_saved' => false,
                ],
                false,
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 0,
                    'draft_save_type' => 'assign_quota',
                    'manager_saved' => false,
                ],
                false,
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'draft',
                    'manager_saved' => false,
                ],
                true,
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'draft',
                    'manager_saved' => true,
                ],
                true,
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'commit',
                    'manager_saved' => false,
                ],
                true,
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 0,
                    'draft_save_type' => 'commit',
                    'manager_saved' => false,
                ],
                false,
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'commit',
                    'manager_saved' => true,
                ],
                true,
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => null,
                    'manager_saved' => false,
                ],
                false,
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 0,
                    'draft_save_type' => null,
                    'manager_saved' => false,
                ],
                false,
            ],
            [
                [
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => null,
                    'manager_saved' => true,
                ],
                true,
            ],
        ];
    }
}
