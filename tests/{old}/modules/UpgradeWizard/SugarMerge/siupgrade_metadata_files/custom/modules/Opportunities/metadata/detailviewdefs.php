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
$viewdefs = [
    'Opportunities' => [
        'DetailView' => [
            'templateMeta' => [
                'form' => [
                    'buttons' => [
                        0 => 'EDIT',
                        1 => 'DUPLICATE',
                        2 => 'DELETE',
                        3 => [
                            'customCode' => '<input title="Close Wizard" accesskey="Z" class="button" onclick="this.form.return_module.value=\'Opportunities\';this.form.return_action.value=\'DetailView\';this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'OpportunityWizard\'; this.form.module.value=\'Opportunities\';" name="button" value="Close Wizard" type="submit">',
                        ],
                    ],
                ],
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
            ],
            'panels' => [
                'default' => [
                    0 => [
                        0 => [
                            'name' => 'name',
                            'label' => 'LBL_OPPORTUNITY_NAME',
                        ],
                        1 => [
                            'name' => 'amount',
                            'label' => '{$MOD.LBL_AMOUNT} ({$CURRENCY})',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'account_name',
                            'label' => 'LBL_ACCOUNT_NAME',
                        ],
                        1 => [
                            'name' => 'date_closed',
                            'label' => 'LBL_DATE_CLOSED',
                        ],
                    ],
                    '1.5' => [
                        0 => [
                            'name' => null,
                            'displayParams' => [],
                        ],
                        1 => [
                            'name' => 'discount_code_c',
                            'label' => 'LBL_DISCOUNT_CODE',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'opportunity_type',
                            'label' => 'LBL_TYPE',
                        ],
                        1 => [
                            'name' => 'users',
                            'label' => 'LBL_USERS_1',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'email_client',
                            'label' => 'LBL_EMAIL_CLIENT',
                        ],
                        1 => [
                            'name' => 'additional_support_cases_c',
                            'label' => 'Additional_Support_Cases__c',
                        ],
                    ],
                    4 => [
                        0 => [
                            'name' => 'processed_by_moofcart_c',
                            'label' => 'LBL_PROCESSED_BY_MOOFCART',
                        ],
                        1 => [
                            'name' => 'additional_training_credits_c',
                            'label' => 'Learning_Credits__c',
                        ],
                    ],
                    5 => [
                        0 => [
                            'name' => 'trial_name_c',
                            'label' => 'Trial URL',
                        ],
                        1 => [
                            'name' => 'sales_stage',
                            'label' => 'LBL_SALES_STAGE',
                        ],
                    ],
                    6 => [
                        0 => [
                            'name' => 'trial_expiration_c',
                            'label' => 'Trial Expiration',
                            'customCode' => '{php} global $timedate; $this->assign("jmo_trialExpirationDB", $timeDate->to_db($this->_tpl_vars["fields"]["trial_expiration_c"]["value"])); {/php} {$fields.trial_expiration_c.value}{if $fields.trial_extended_c.value eq 0 && !empty($fields.trial_name_c.value) && strtotime("now") < $jmo_trialExpirationDB|date_format:"%s"} &nbsp; <button type="submit" onClick="document.location=\'/scripts/7daytrials/trials.php?opportunity_id={$fields.id.value}\';">Extend Trial</button>{/if}',
                        ],
                        1 => [
                            'name' => 'probability',
                            'label' => 'LBL_PROBABILITY',
                        ],
                    ],
                    7 => [
                        0 => [
                            'name' => 'campaign_name',
                            'label' => 'LBL_CAMPAIGN',
                        ],
                        1 => 'score_c',
                    ],
                    8 => [
                        0 => [
                            'name' => 'lead_source',
                            'label' => 'LBL_LEAD_SOURCE',
                        ],
                        1 => null,
                    ],
                    9 => [
                        0 => [
                            'name' => 'current_solution',
                            'label' => 'LBL_CURRENT_SOLUTION',
                        ],
                        1 => [
                            'name' => 'Term_c',
                            'label' => 'Term__c',
                        ],
                    ],
                    10 => [
                        0 => [
                            'name' => 'competitor_1',
                            'label' => 'LBL_COMPETITOR_1',
                        ],
                        1 => [
                            'name' => 'Revenue_Type_c',
                            'label' => 'Revenue_Type__c',
                        ],
                    ],
                    11 => [
                        0 => [
                            'name' => 'competitor_2',
                            'label' => 'LBL_COMPETITOR_2',
                        ],
                        1 => [
                            'name' => 'renewal_date_c',
                            'label' => 'Renewal_Date_c',
                        ],
                    ],
                    12 => [
                        0 => [
                            'name' => 'competitor_3',
                            'label' => 'LBL_COMPETITOR_3',
                        ],
                        1 => [
                            'name' => 'order_number',
                            'label' => 'LBL_ORDER_NUMBER',
                            'customCode' => '<a href="http://www.sugarcrm.com/sugarshop/admin/order.php?orderid={$fields.order_number.value}">{$fields.order_number.value}</a>',
                        ],
                    ],
                    13 => [
                        0 => [
                            'name' => 'competitor_expiration_c',
                            'label' => 'LBL_COMPETITOR_EXPIRATION',
                        ],
                        1 => [
                            'name' => 'order_type_c',
                            'label' => 'LBL_ORDER_TYPE_C',
                        ],
                    ],
                    14 => [
                        0 => null,
                        1 => [
                            'name' => 'next_step',
                            'label' => 'LBL_NEXT_STEP',
                        ],
                    ],
                    15 => [
                        0 => [
                            'name' => 'demo_c',
                            'label' => 'Demo_1',
                        ],
                        1 => [
                            'name' => 'next_step_due_date',
                            'label' => 'LBL_NEXT_STEP_DUE_DATE',
                        ],
                    ],
                    16 => [
                        0 => [
                            'name' => 'demo_date_c',
                            'label' => 'Demo Date',
                        ],
                        1 => [
                            'name' => 'top20deal_c',
                            'label' => 'LBL_TOP20DEAL',
                        ],
                    ],
                    17 => [
                        0 => [
                            'name' => 'evaluation',
                            'label' => 'LBL_EVALUATION',
                        ],
                        1 => [
                            'name' => 'closed_lost_reason_c',
                            'label' => 'LBL_CLOSED_LOST_REASON_C',
                        ],
                    ],
                    18 => [
                        0 => [
                            'name' => 'evaluation_start_date',
                            'label' => 'LBL_EVALUATION_START_DATE',
                        ],
                        1 => [
                            'name' => 'closed_lost_reason_detail_c',
                            'label' => 'LBL_CLOSED_LOST_REASON_DETAIL',
                        ],
                    ],
                    19 => [
                        0 => [
                            'name' => 'Evaluation_Close_Date_c',
                            'label' => 'Evaluation_Close_Date__c',
                        ],
                        1 => [
                            'name' => 'primary_reason_competitor_c',
                            'label' => 'LBL_PRIMARY_REASON_COMPETITOR',
                        ],
                    ],
                    20 => [
                        0 => [],
                        1 => [
                            'name' => 'closed_lost_description',
                            'label' => 'LBL_CLOSED_LOST_DESCRIPTION',
                        ],
                    ],
                    21 => [
                        0 => [
                            'name' => 'partner_assigned_to_c',
                            'label' => 'Partner_Assigned_To_c',
                            'customCode' => '{assign var=partner_assigned_to_key value=$fields.partner_assigned_to_c.value}<a href="index.php?module=Accounts&action=DetailView&record={$partner_assigned_to_key}">{$APP_LIST_STRINGS.partner_assigned_to.$partner_assigned_to_key}</a>',
                        ],
                        1 => [
                            'name' => 'accepted_by_partner_c',
                            'label' => 'LBL_ACCEPTED_BY_PARTNER',
                        ],
                    ],
                    22 => [
                        0 => [
                            'name' => 'team_name',
                            'label' => 'LBL_TEAM',
                        ],
                        1 => [
                            'name' => 'partner_contact_c',
                            'label' => 'LBL_PARTNER_CONTACT',
                        ],
                    ],
                    23 => [
                        0 => [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO_NAME',
                        ],
                        1 => [
                            'name' => 'date_modified',
                            'label' => 'LBL_DATE_MODIFIED',
                            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                        ],
                    ],
                    24 => [
                        0 => [
                            'name' => 'associated_rep_c',
                            'label' => 'Associated_Rep_c',
                        ],
                        1 => [
                            'name' => 'date_entered',
                            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                            'label' => 'LBL_DATE_ENTERED',
                        ],
                    ],
                    25 => [
                        0 => [
                            'name' => 'description',
                            'nl2br' => true,
                            'label' => 'LBL_DESCRIPTION',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
