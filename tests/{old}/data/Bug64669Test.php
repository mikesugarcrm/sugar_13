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

require_once 'include/export_utils.php';


/**
 * Bug #64669
 * @ticket 64669
 */
class Bug64669Test extends TestCase
{
    private $createdBeans;
    public $export_query;
    private $campaign_name = 'sugar test campaign 64669 ';

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['Contacts']);
        SugarTestHelper::setUp('current_user', [true, 1]);

        $camp = BeanFactory::newBean('Campaigns');
        $camp->name = $this->campaign_name;
        $camp->save();
        $this->createdBeans[] = $camp;

        $bean = BeanFactory::newBean('Contacts');
        $bean->last_name = 'last64669';
        $bean->campaign_id = $camp->id;
        $bean->save();
        $this->createdBeans[] = $bean;

        //simulate call from export_utils to retrieve export query with a filter on related field 'campaign_name'
        $this->export_query = $bean->create_export_query('', "campaign_name like 'sugar%'");
    }


    protected function tearDown(): void
    {
        foreach ($this->createdBeans as $bean) {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }
    }

    /**
     * Tests that create_export_query, which uses 'create_new_list_query' can create a valid query when
     * there is a filter using a related field
     */
    public function testListQuery()
    {
        //query with filter on related field has been created,
        //make sure there is a Left Join with Campaigns in the returned query
        $this->assertMatchesRegularExpression(
            '/LEFT JOIN\s.campaigns/',
            $this->export_query,
            ' Left Join with Campaigns table was not found, where statement is not being processed correctly'
        );
    }

    /**
     * Tests that get_field_order_mapping() in export_utils will remove fields from the list of columns to be exported
     */
    public function testExcludeFieldOrderMapping()
    {
        $removeMe = ['campaign_id', 'campaign_name', 'description'];

        //use the query with filter on related campaigns to get the result and fields array for the search
        $result = $GLOBALS['db']->query($this->export_query);
        $fields_array = $GLOBALS['db']->getFieldsArray($result, true);

        //create the filter exclude array, we will remove related fields campaign_id and campaign_name, as well as description field
        //from the array that determines columns will be exported
        $fields_exclude_array = ['contacts' => $removeMe];
        $mstr_fields_exclude_array = get_field_order_mapping('contacts', $fields_array, true, $fields_exclude_array);

        //make sure campaign_id field has been removed from the list of columns to be exported
        foreach ($removeMe as $removed) {
            $this->assertArrayNotHasKey($removed, $mstr_fields_exclude_array, "field $removed was not excluded from fields list: " . var_export($mstr_fields_exclude_array, true));
        }
    }


    /**
     * Tests that export() in export_utils makes proper use of order mapping to remove related fields that are joined
     * for search purposes
     */
    public function testExportExcludesRelatedField()
    {
        //set up variables and request array for export function
        $type = 'Contacts';
        $reArr = [
            'module' => 'Contacts',
            'action' => 'index',
            'searchFormTab' => 'basic_search',
            'query' => true,
            'orderBy' => '',
            'sortOrder' => '',
            'campaign_name_basic' => $this->campaign_name,
            'search_name_basic' => '',
            'current_user_only_basic' => 0,
            'favorites_only_basic' => 0,
        ];
        $reArr = serialize($reArr);
        $_REQUEST['current_post'] = base64_encode($reArr);
        $_REQUEST['entrypoint'] = 'export';
        $_REQUEST['module'] = 'Campaigns';

        //call export function
        $exportContents = export($type);

        //check that there are no traces of campaign ("Campaign Name", "Campaign ID", "campaign_name_mod", "campaign_name_owner")
        //in the returned contents
        $this->assertDoesNotMatchRegularExpression(
            '/Campaign/i',
            $exportContents,
            "A column with string 'Campaign' ('Campaign Name', 'Campaign ID', 'campaign_name_mod', 'campaign_name_owner') was found, exclusion logic is not working as expected "
        );
    }
}
