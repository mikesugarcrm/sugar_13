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

namespace Sugarcrm\SugarcrmTestsUnit\inc\generic\SugarWidgets;

use PHPUnit\Framework\TestCase;

/**
 * Class SugarWidgetFieldnameTest
 *
 * @coversDefaultClass \SugarWidgetFieldname
 */
class SugarWidgetFieldnameTest extends TestCase
{
    /**
     * @covers ::displayList
     */
    public function testDisplayList()
    {
        $reporter = new \stdClass();
        $reporter->embeddedData = true;

        $lm = $this->createPartialMock('LayoutManager', []);
        $lm->setAttributePtr('reporter', $reporter);

        $sugarWidgetFieldname = new \SugarWidgetFieldName($lm);

        $layout_def = [
            'name' => 'name',
            'label' => 'Name',
            'table_key' => 'self',
            'table_alias' => 'accounts',
            'column_key' => 'self:name',
            'type' => 'name',
            'fields' =>
            [
                'PRIMARYID' => '1',
                'ACCOUNTS_NAME' => 'scheduler',
                'ACCOUNTS__ALLCOUNT' => '1',
                'ACCOUNTS__COUNT' => '1',
            ],
        ];

        $reporter->all_fields['self:name'] = [
            'name' => 'name',
            'type' => 'name',
            'dbType' => 'varchar',
            'vname' => 'LBL_NAME',
            'len' => 255,
            'comment' => 'Name of the Company',
            'unified_search' => true,
            'full_text_search' =>
            [
                'enabled' => true,
                'searchable' => true,
                'boost' => 1.91,
            ],
            'audited' => true,
            'required' => true,
            'importable' => 'required',
            'duplicate_on_record_copy' => 'always',
            'merge_filter' => 'selected',
            'module' => 'Accounts',
            'real_table' => 'accounts',
            'rep_rel_name' => 'name_0',
        ];

        $result = $sugarWidgetFieldname->displayList($layout_def);

        $this->assertStringNotContainsString('<a target="_blank"', $result);

        $reporter->embeddedData = false;
        $resultWithHTMLTags = $sugarWidgetFieldname->displayList($layout_def);

        $this->assertStringContainsString('<a target="_blank"', $resultWithHTMLTags);
    }
}
