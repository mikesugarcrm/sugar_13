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
 * Bug46098Test
 *
 * This class contains the unit test to check that the repairSearchFields method will create a SearchFields.php file
 * to correctly handle range searching for date fields.
 */

require_once 'modules/UpgradeWizard/uw_utils.php';

class Bug46028Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile('custom/modules/Opportunities/metadata/SearchFields.php');
        SugarTestHelper::saveFile('modules/Opportunities/metadata/SearchFields.php');

        $searchFieldContents = <<<EOQ
<?php
\$searchFields['Opportunities'] =
array (
    'name' => array( 'query_type'=>'default'),
    'account_name'=> array('query_type'=>'default','db_field'=>array('accounts.name')),
    'amount'=> array('query_type'=>'default'),
    'next_step'=> array('query_type'=>'default'),
    'probability'=> array('query_type'=>'default'),
    'lead_source'=> array('query_type'=>'default', 'operator'=>'=', 'options' => 'lead_source_dom', 'template_var' => 'LEAD_SOURCE_OPTIONS'),
    'opportunity_type'=> array('query_type'=>'default', 'operator'=>'=', 'options' => 'opportunity_type_dom', 'template_var' => 'TYPE_OPTIONS'),
    'sales_stage'=> array('query_type'=>'default', 'operator'=>'=', 'options' => 'sales_stage_dom', 'template_var' => 'SALES_STAGE_OPTIONS', 'options_add_blank' => true),
    'current_user_only'=> array('query_type'=>'default','db_field'=>array('assigned_user_id'),'my_items'=>true, 'vname' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
    'assigned_user_id'=> array('query_type'=>'default'),
    'favorites_only' => array(
    'query_type'=>'format',
                'operator' => 'subquery',
                'subquery' => 'SELECT sugarfavorites.record_id FROM sugarfavorites
                                    WHERE sugarfavorites.deleted=0
                                        and sugarfavorites.module = \'Opportunities\'
                                        and sugarfavorites.assigned_user_id = \'{0}\'',
                'db_field'=>array('id')),
);
?>
EOQ;

        file_put_contents('modules/Opportunities/metadata/SearchFields.php', $searchFieldContents);
    }

    public function testRepairSearchFields()
    {
        $searchFields = [];
        repairSearchFields('modules/Opportunities/metadata/SearchFields.php');
        $this->assertFileExists('custom/modules/Opportunities/metadata/SearchFields.php');

        require 'custom/modules/Opportunities/metadata/SearchFields.php';

        $this->assertArrayHasKey('range_date_entered', $searchFields['Opportunities']);
        $this->assertArrayHasKey('start_range_date_entered', $searchFields['Opportunities']);
        $this->assertArrayHasKey('end_range_date_entered', $searchFields['Opportunities']);
        $this->assertArrayHasKey('range_date_modified', $searchFields['Opportunities']);
        $this->assertArrayHasKey('start_range_date_modified', $searchFields['Opportunities']);
        $this->assertArrayHasKey('end_range_date_modified', $searchFields['Opportunities']);
    }
}
