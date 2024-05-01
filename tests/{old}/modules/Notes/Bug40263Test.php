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
 * @group bug40263
 */
class Bug40263Test extends TestCase
{
    public $user;
    public $note;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestNoteUtilities::removeAllCreatedNotes();
    }

    public function testGetListViewQueryCreatedBy()
    {
        $note = SugarTestNoteUtilities::createNote();

        include 'modules/Notes/metadata/listviewdefs.php';
        $displayColumns = [
            'NAME' => [
                'width' => '40%',
                'label' => 'LBL_LIST_SUBJECT',
                'link' => true,
                'default' => true,
            ],
            'CREATED_BY_NAME' => [
                'type' => 'relate',
                'label' => 'LBL_CREATED_BY',
                'width' => '10%',
                'default' => true,
            ],
        ];
        $lvd = new ListViewDisplay();
        $lvd->displayColumns = $displayColumns;
        $fields = $lvd->setupFilterFields();
        $query = $note->create_new_list_query('', 'id="' . $note->id . '"', $fields);

        $this->assertMatchesRegularExpression(
            '/select.* created_by_name.*LEFT JOIN\s*users jt\d ON\s*notes.created_by\s*=\s*jt\d\.id.*/si',
            $query
        );
    }
}
