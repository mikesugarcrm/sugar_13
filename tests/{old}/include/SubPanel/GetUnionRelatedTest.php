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
 * test get_union_related_list() with subpanels, functions, distinct clause
 */
class GetUnionRelatedTest extends TestCase
{
    /**
     * Bean to use for tests
     * @var SugarBean
     */
    protected $bean;

    protected function setUp(): void
    {
        global $moduleList, $beanList, $beanFiles;
        require 'include/modules.php';
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->bean = new Contact();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testGetUnionRelatedList()
    {
        $subpanel = [
            'order' => 20,
            'sort_order' => 'desc',
            'sort_by' => 'date_entered',
            'type' => 'collection',
            'subpanel_name' => 'history',   //this values is not associated with a physical file.
            'top_buttons' => [],
            'collection_list' => [
                'meetings' => [
                    'module' => 'Meetings',
                    'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'meetings',
                ],
                'emails' => [
                    'module' => 'Emails',
                    'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'emails',
                    'get_distinct_data' => true,
                ],
                'linkedemails_contacts' => [
                    'module' => 'Emails',
                    'subpanel_name' => 'ForHistory',
                    'generate_select' => true,
                    'get_distinct_data' => true,
                    'get_subpanel_data' => 'function:GetUnionRelatedTest_get_select',
                    'function_parameters' => ['import_function_file' => __FILE__],
                ],
            ],
        ];
        $subpanel_def = new aSubPanel('testpanel', $subpanel, $this->bean);
        $query = $this->bean->get_union_related_list($this->bean, '', '', '', 0, 5, -1, 0, $subpanel_def);
        $result = $this->bean->db->query($query['query']);
        $this->assertTrue($result != false, "Bad query: {$query['query']}");
    }
}

function GetUnionRelatedTest_get_select()
{
    $return_array = [];
    $return_array['select'] = 'SELECT DISTINCT emails.id';
    $return_array['from'] = 'FROM emails ';
    $return_array['join'] = " JOIN emails_email_addr_rel eear ON eear.email_id = emails.id AND eear.deleted=0
		    	JOIN email_addr_bean_rel eabr ON eabr.email_address_id=eear.email_address_id AND eabr.bean_module = 'Contacts'
		    		AND eabr.deleted=0 AND eabr.bean_id = '1'";
    $return_array['where'] = '';
    $return_array['join_tables'] = [];
    return $return_array;
}
