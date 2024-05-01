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
$viewdefs ['Opportunities'] =
    [
        'EditView' => [
            'templateMeta' => [
                'maxColumns' => '2',
                'widths' => [
                    0 => [
                        'label' => '10',
                        'field' => '30',
                    ],
                    1 => [
                        'label' => '10',
                        'field' => '30',
                    ],
                ],
                'javascript' => '{$PROBABILITY_SCRIPT}',
            ],
            'panels' => [
                'default' => [
                    0 => [
                        0 => [
                            'name' => 'name',
                            'displayParams' => [
                                'required' => true,
                            ],
                            'label' => 'LBL_OPPORTUNITY_NAME',
                        ],
                        1 => [
                            'name' => 'currency_id',
                            'label' => 'LBL_CURRENCY',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'account_name',
                            'label' => 'LBL_ACCOUNT_NAME',
                        ],
                        1 => [
                            'name' => 'amount',
                            'displayParams' => [
                                'required' => true,
                            ],
                            'label' => 'LBL_AMOUNT',
                        ],
                    ],
                    // BEGIN jostrow MoofCart customization
                    // See ITRequest #9622

                    '1.5' => [
                        null,
                        [
                            'name' => 'discount_code_c',
                            'label' => 'LBL_DISCOUNT_CODE',
                        ],
                    ],

                    // END jostrow MoofCart customization

                    2 => [
                        0 => [
                            'name' => 'opportunity_type',
                            'label' => 'LBL_TYPE',
                        ],
                        1 => [
                            'name' => 'date_closed',
                            'displayParams' => [
                                'required' => true,
                            ],
                            'label' => 'LBL_DATE_CLOSED',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'operating_system',
                            'label' => 'LBL_OPERATING_SYSTEM',
                        ],
                        1 => [
                            'name' => 'users',
                            'label' => 'LBL_USERS_1',
                        ],
                    ],
                    4 => [
                        0 => [
                            'name' => 'campaign_name',
                            'label' => 'LBL_CAMPAIGN',
                        ],
                        1 => [
                            'name' => 'additional_support_cases_c',
                            'label' => 'Additional_Support_Cases__c',
                        ],
                    ],
                    5 => [
                        1 => [
                            'name' => 'additional_training_credits_c',
                            'label' => 'Learning_Credits__c',
                        ],
                    ],
                    6 => [
                        0 => [
                            'name' => 'email_client',
                            'label' => 'LBL_EMAIL_CLIENT',
                        ],
                        1 => [
                            'name' => 'sales_stage',
                            'displayParams' => [
                                'required' => true,
                            ],
                            'label' => 'LBL_SALES_STAGE',
                            'customCode' => '
	    	<script src=\'custom/include/javascript/custom_javascript.js\'></script>
		{html_options id="sales_stage" name="sales_stage" options=$fields.sales_stage.options selected=$fields.sales_stage.value  onChange=\'checkOpportunitySalesStage()\'}
	    ',
                        ],
                    ],
                    7 => [
                        1 => [
                            'name' => 'probability',
                            'label' => 'LBL_PROBABILITY',
                        ],
                    ],
                    8 => [],
                    9 => [
                        1 => [
                            'name' => 'Term_c',
                            'label' => 'Term__c',
                        ],
                    ],
                    10 => [
                        0 => [
                            'name' => 'lead_source',
                            'label' => 'LBL_LEAD_SOURCE',
                        ],
                        1 => [
                            'name' => 'Revenue_Type_c',
                            'label' => 'Revenue_Type__c',
                        ],
                    ],
                    11 => [
                        0 => [
                            'name' => 'partner_name',
                            'label' => 'LBL_PARTNER_NAME',
                        ],
                        1 => [
                            'name' => 'renewal_date_c',
                            'label' => 'Renewal_Date_c',
                        ],
                    ],
                    12 => [
                        0 => [
                            'name' => 'current_solution',
                            'label' => 'LBL_CURRENT_SOLUTION',
                        ],
                        1 => [
                            'name' => 'order_number',
                            'label' => 'LBL_ORDER_NUMBER',
                        ],
                    ],
                    13 => [
                        1 => [
                            'name' => 'order_type_c',
                            'label' => 'LBL_ORDER_TYPE_C',
                        ],
                    ],
                    14 => [
                        0 => [
                            'name' => 'competitor_1',
                            'label' => 'LBL_COMPETITOR_1',
                        ],
                        1 => [
                            'name' => 'true_up_c',
                            'label' => 'LBL_TRUE_UP',
                        ],
                    ],
                    15 => [
                        0 => [
                            'name' => 'competitor_2',
                            'label' => 'LBL_COMPETITOR_2',
                        ],
                        1 => [
                            'name' => 'next_step',
                            'label' => 'LBL_NEXT_STEP',
                            'customCode' => '<textarea id="{$fields.next_step.name}" name="{$fields.next_step.name}" rows="4" cols="60" title=\'\' tabindex="1">{$fields.next_step.value}</textarea>',
                        ],
                    ],
                    16 => [
                        0 => [
                            'name' => 'competitor_3',
                            'label' => 'LBL_COMPETITOR_3',
                        ],
                        1 => [
                            'name' => 'next_step_due_date',
                            'label' => 'LBL_NEXT_STEP_DUE_DATE',
                        ],
                    ],
                    17 => [
                        0 => [
                            'name' => 'competitor_expiration_c',
                            'label' => 'LBL_COMPETITOR_EXPIRATION',
                        ],
                    ],
                    18 => [
                        0 => [
                            'name' => 'demo_c',
                            'label' => 'Demo_1',
                        ],
                        1 => [
                            'name' => 'top20deal_c',
                            'label' => 'LBL_TOP20DEAL',
                        ],
                    ],
                    19 => [
                        0 => [
                            'name' => 'demo_date_c',
                            'label' => 'Demo Date',
                        ],
                    ],
                    20 => [
                        0 => [
                            'name' => 'evaluation',
                            'label' => 'LBL_EVALUATION',
                        ],
                        1 => [
                            'name' => 'closed_lost_reason_c',
                            'label' => 'LBL_CLOSED_LOST_REASON_C',
//** BEGIN  CUSTOMIZATION EDDY :: ITTix 13077
                            'customCode' => '
<script src=\'custom/include/javascript/custom_javascript.js\'></script>
	{html_options id="closed_lost_reason_c" name="closed_lost_reason_c" options=$fields.closed_lost_reason_c.options selected=$fields.closed_lost_reason_c.value  onChange=\'checkOppClosedReasonDependentDropdown("closed_lost_reason_detail_c", true)\' }
',
//** END  CUSTOMIZATION EDDY :: ITTix 13077
                        ],
                    ],
                    21 => [
                        0 => [
                            'name' => 'evaluation_start_date',
                            'label' => 'LBL_EVALUATION_START_DATE',
                        ],
                        1 => [
                            'name' => 'closed_lost_reason_detail_c',
                            'label' => 'LBL_CLOSED_LOST_REASON_DETAIL',

                        ],
                    ],
                    22 => [
                        0 => [
                            'name' => 'Evaluation_Close_Date_c',
                            'label' => 'Evaluation_Close_Date__c',
                        ],
                        1 => [
                            'name' => 'primary_reason_competitor_c',
                            'label' => 'LBL_PRIMARY_REASON_COMPETITOR',
                        ],
                    ],
                    23 => [
                        0 => [],
                        1 => [
                            'name' => 'closed_lost_description',
                            'label' => 'LBL_CLOSED_LOST_DESCRIPTION',
//** BEGIN  CUSTOMIZATION EDDY :: ITTix 13077
                            'customCode' => '
<textarea id="{$fields.closed_lost_description.name}" onChange=\'checkOppClosedReasonDependentDropdown("closed_lost_reason_detail_c", true)\'  cols="60" rows="4" name="{$fields.closed_lost_description.name}">{$fields.closed_lost_description.value}</textarea>
<script>
detail2val = \'{$fields.closed_lost_reason_detail_c.value}\';
checkOppClosedReasonDependentDropdown("{$fields.closed_lost_reason_detail_c.name}", false,detail2val);//call initial drop down rendering
</script>
        ',
//** END  CUSTOMIZATION EDDY :: ITTix 13077

                        ],
                    ],
                    24 => [
                        0 => [
                            'name' => 'partner_assigned_to_c',
                            'label' => 'Partner_Assigned_To_c',
                        ],
                        1 => [
                            'name' => 'accepted_by_partner_c',
                            'label' => 'LBL_ACCEPTED_BY_PARTNER',
                        ],
                    ],
                    25 => [
                        0 => [
                            'name' => 'team_name',
                            'displayParams' => [
                                'required' => true,
                            ],
                            'label' => 'LBL_TEAM',
                        ],
                        1 => [
                            'name' => 'partner_contact_c',
                            'label' => 'LBL_PARTNER_CONTACT',
                        ],
                    ],
                    26 => [
                        0 => [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO_NAME',
                        ],
                        1 => [
                            'name' => 'associated_rep_c',
                            'label' => 'Associated_Rep_c',
                        ],
                    ],
                    27 => [
                        0 => [
                            'name' => 'description',
                            'label' => 'LBL_DESCRIPTION',
                        ],
                    ],
                ],
            ],
        ],
    ];
