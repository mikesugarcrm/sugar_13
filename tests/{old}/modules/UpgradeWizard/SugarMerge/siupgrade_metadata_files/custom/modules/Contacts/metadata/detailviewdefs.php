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
    'Contacts' => [
        'DetailView' => [
            'templateMeta' => [
                'preForm' => '<form name="vcard" action="index.php"><input type="hidden" name="entryPoint" value="vCard"><input type="hidden" name="contact_id" value="{$fields.id.value}"><input type="hidden" name="module" value="Contacts"></form>',
                'form' => [
                    'buttons' => [
                        0 => 'EDIT',
                        1 => 'DUPLICATE',
                        2 => 'DELETE',
                        3 => 'FIND_DUPLICATES',
                        4 => [
                            'customCode' => '<input title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" class="button" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\';" type="submit" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}">',
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
                'includes' => [
                    0 => [
                        'file' => 'modules/Leads/Lead.js',
                    ],
                ],
            ],
            'panels' => [
                'default' => [
                    0 => [
                        0 => [
                            'name' => 'full_name',
                            'customCode' => '{$fields.full_name.value}&nbsp;&nbsp;<input type="button" class="button" name="vCardButton" value="{$MOD.LBL_VCARD}" onClick="document.vcard.submit();">',
                            'label' => 'LBL_NAME',
                            'displayParams' => [],
                        ],
                        1 => [
                            'name' => 'phone_work',
                            'label' => 'LBL_OFFICE_PHONE',
                        ],
                    ],
                    1 => [
                        0 => 'score_c',
                        1 => [
                            'name' => 'phone_mobile',
                            'label' => 'LBL_MOBILE_PHONE',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'account_name',
                            'label' => 'LBL_ACCOUNT_NAME',
                        ],
                        1 => [
                            'name' => 'phone_home',
                            'label' => 'LBL_HOME_PHONE',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'lead_source',
                            'label' => 'LBL_LEAD_SOURCE',
                        ],
                        1 => [
                            'name' => 'phone_other',
                            'label' => 'LBL_OTHER_PHONE',
                        ],
                    ],
                    4 => [
                        0 => [
                            'name' => 'title',
                            'label' => 'LBL_TITLE',
                        ],
                        1 => [
                            'name' => 'phone_fax',
                            'label' => 'LBL_FAX_PHONE',
                        ],
                    ],
                    5 => [
                        0 => [
                            'name' => 'department',
                            'label' => 'LBL_DEPARTMENT',
                        ],
                        1 => [
                            'name' => 'email1',
                            'label' => 'LBL_EMAIL_ADDRESS',
                        ],
                    ],
                    6 => [
                        0 => [
                            'name' => 'birthdate',
                            'label' => 'LBL_BIRTHDATE',
                        ],
                        1 => null,
                    ],
                    7 => [
                        0 => [
                            'name' => 'report_to_name',
                            'label' => 'LBL_REPORTS_TO',
                        ],
                        1 => [
                            'name' => 'assistant',
                            'label' => 'LBL_ASSISTANT',
                        ],
                    ],
                    8 => [
                        0 => [
                            'name' => 'technical_proficiency_',
                            'label' => 'LBL_TECHNICAL_PROFICIENCY_',
                        ],
                        1 => [
                            'name' => 'assistant_phone',
                            'label' => 'LBL_ASSISTANT_PHONE',
                        ],
                    ],
                    9 => [
                        0 => [
                            'name' => 'do_not_call',
                            'label' => 'LBL_DO_NOT_CALL',
                        ],
                        1 => null,
                    ],
                    10 => [
                        0 => [
                            'name' => 'sync_contact',
                            'label' => 'LBL_SYNC_CONTACT',
                        ],
                        1 => null,
                    ],
                    11 => [
                        0 => null,
                        1 => [
                            'name' => 'primary_business_c',
                            'label' => 'Primary_Business_Contact__c',
                        ],
                    ],
                    12 => [
                        0 => null,
                        1 => [
                            'name' => 'support_authorized_c',
                            'label' => 'Support_Authorized_Contact__c',
                        ],
                    ],
                    13 => [
                        0 => null,
                        1 => [
                            'name' => 'university_enabled_c',
                            'label' => 'LBL_UNIVERSITY_ENABLED',
                        ],
                    ],
                    14 => [
                        0 => null,
                        1 => [
                            'name' => 'billing_contact_c',
                            'label' => 'Billing_Contact__c',
                        ],
                    ],
                    15 => [
                        0 => null,
                        1 => [
                            'name' => 'oppq_active_c',
                            'label' => 'LBL_OPPQ_ACTIVE_C',
                        ],
                    ],
                    16 => [
                        0 => [
                            'name' => 'team_name',
                            'label' => 'LBL_TEAM',
                        ],
                        1 => [
                            'name' => 'date_modified',
                            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                            'label' => 'LBL_DATE_MODIFIED',
                        ],
                    ],
                    17 => [
                        0 => [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO_NAME',
                        ],
                        1 => [
                            'name' => 'date_entered',
                            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                            'label' => 'LBL_DATE_ENTERED',
                        ],
                    ],
                    18 => [
                        0 => [
                            'name' => 'primary_address_street',
                            'label' => 'LBL_PRIMARY_ADDRESS',
                            'type' => 'address',
                            'displayParams' => [
                                'key' => 'primary',
                            ],
                        ],
                        1 => [
                            'name' => 'alt_address_street',
                            'label' => 'LBL_ALTERNATE_ADDRESS',
                            'type' => 'address',
                            'displayParams' => [
                                'key' => 'alt',
                            ],
                        ],
                    ],
                    19 => [
                        0 => [
                            'name' => 'portal_name',
                            'customCode' => '{if $PORTAL_ENABLED}{$fields.portal_name.value}{/if}',
                            'customLabel' => '{if $PORTAL_ENABLED}{sugar_translate label="LBL_PORTAL_NAME" module="Contacts"}{/if}',
                            'label' => 'LBL_PORTAL_NAME',
                        ],
                        1 => [
                            'name' => 'portal_active',
                            'customCode' => '{if $PORTAL_ENABLED}
	          		         {if strval($fields.portal_active.value) == "1" || strval($fields.portal_active.value) == "yes" || strval($fields.portal_active.value) == "on"}
	          		         {assign var="checked" value="CHECKED"}
                             {else}
                             {assign var="checked" value=""}
                             {/if}
                             <input type="checkbox" class="checkbox" name="{$fields.portal_active.name}" size="{$displayParams.size}" disabled="true" {$checked}>
                             {/if}',
                            'customLabel' => '{if $PORTAL_ENABLED}{sugar_translate label="LBL_PORTAL_ACTIVE" module="Contacts"}{/if}',
                            'label' => 'LBL_PORTAL_ACTIVE',
                        ],
                    ],
                    20 => [
                        0 => [
                            'name' => 'description',
                            'label' => 'LBL_DESCRIPTION',
                        ],
                    ],
                ],
                'lbl_panel1' => [
                    0 => [
                        0 => [
                            'name' => 'dce_user_name_c',
                            'label' => 'LBL_DCE_USER_NAME',
                        ],
                        1 => [
                            'name' => 'licensing_rights_c',
                            'label' => 'LBL_LICENSING_RIGHTS',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
