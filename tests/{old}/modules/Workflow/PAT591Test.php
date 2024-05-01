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

include_once 'include/workflow/alert_utils.php';

/**
 * PAT-591
 * Attributes in workflow alerts do not correlate to the correct contact
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket PAT-591
 */
class BugPAT591Test extends TestCase
{
    public $opportunity;
    public $contact1;
    public $contact2;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->contact1 = SugarTestContactUtilities::createContact();
        $this->contact1->first_name = 'BugPAT591Test';
        $this->contact1->last_name = 'A';
        $this->contact1->save();

        $this->contact2 = SugarTestContactUtilities::createContact();
        $this->contact2->first_name = 'BugPAT591Test';
        $this->contact2->last_name = 'B';
        $this->contact2->save();

        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $this->opportunity->load_relationship('contacts');
        $this->opportunity->contacts->add($this->contact1);
        $this->opportunity->contacts->add($this->contact2);
        $this->opportunity->save();
    }

    protected function tearDown(): void
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    /**
     * Provider for alert_user_array parameter for the function reconstruct_target_body()
     */
    public function alertUserArrayProvider()
    {
        $alert_user_arr1 = [
            [
                'user_type' => 'rel_user_custom',
                'address_type' => 'to',
                'array_type' => 'future',
                'relate_type' => 'Self',
                'field_value' => 'full_name',
                'where_filter' => '0',
                'rel_module1' => 'contacts',
                'rel_module2' => '',
                'rel_module1_type' => 'all',
                'rel_module2_type' => 'all',
                'rel_email_value' => 'email1',
                'user_display_type' => '',
                'expression' => [
                    'lhs_module' => 'Contacts',
                    'lhs_field' => 'name',
                    'operator' => 'Equals',
                    'rhs_value' => 'BugPAT591Test A',
                ],
            ],
        ];

        $alert_user_arr2 = [
            [
                'user_type' => 'rel_user_custom',
                'address_type' => 'to',
                'array_type' => 'future',
                'relate_type' => 'Self',
                'field_value' => 'full_name',
                'where_filter' => '0',
                'rel_module1' => 'contacts',
                'rel_module2' => '',
                'rel_module1_type' => 'all',
                'rel_module2_type' => 'all',
                'rel_email_value' => 'email1',
                'user_display_type' => '',
                'expression' => [
                    'lhs_module' => 'Contacts',
                    'lhs_field' => 'name',
                    'operator' => 'Equals',
                    'rhs_value' => 'BugPAT591Test B',
                ],
            ],
        ];

        return [
            [$alert_user_arr1, 'BugPAT591Test A'],
            [$alert_user_arr2, 'BugPAT591Test B'],
        ];
    }

    /**
     * Test that the function reconstruct_target_body() properly applies filters.
     *
     * @dataProvider alertUserArrayProvider
     */
    public function testReconstructTargetBodyFilter($alert_user_array, $name)
    {
        $target_body = '{::future::Opportunities::contacts::full_name::}';

        $component_array = [
            'Opportunities' => [],
            'contacts' => [
                'full_name' => [
                    'name' => 'full_name',
                    'value_type' => 'future',
                    'original' => '{::future::Opportunities::contacts::full_name::}',
                ],
            ],
        ];

        $res = reconstruct_target_body($this->opportunity, $target_body, $component_array, '', $alert_user_array);
        $this->assertEquals($name, $res);
    }
}
