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

$viewdefs['Contacts']['EditView'] = [
    'templateMeta' => [
        'form' => [
            'hidden' => [
                '<input type="hidden" name="opportunity_id" value="{$smarty.request.opportunity_id}">',
                '<input type="hidden" name="case_id" value="{$smarty.request.case_id}">',
                '<input type="hidden" name="bug_id" value="{$smarty.request.bug_id}">',
                '<input type="hidden" name="email_id" value="{$smarty.request.email_id}">',
                '<input type="hidden" name="inbound_email_id" value="{$smarty.request.inbound_email_id}">',
            ],
        ],
        'maxColumns' => '2',
        'widths' => [
            ['label' => '10', 'field' => '30'],
            ['label' => '10', 'field' => '30'],
        ],
    ],
    'panels' => [
        'lbl_contact_information' => [
            [
                [
                    'name' => 'first_name',
                    'customCode' => '{html_options name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
                ],


            ],
            [
                [
                    'name' => 'last_name',
                    'displayParams' => [
                        'required' => true,
                    ],
                ],
                [
                    'name' => 'phone_mobile',
                    'comment' => 'Mobile phone number of the contact',
                    'label' => 'LBL_MOBILE_PHONE',
                ],
            ],

            [
                [
                    'name' => 'title',
                    'comment' => 'The title of the contact',
                    'label' => 'LBL_TITLE',
                ],

                [
                    'name' => 'phone_work',
                    'comment' => 'Work phone number of the contact',
                    'label' => 'LBL_OFFICE_PHONE',
                ],

            ],

            [
                'department',
                [
                    'name' => 'phone_fax',
                    'comment' => 'Contact fax number',
                    'label' => 'LBL_FAX_PHONE',
                ],
            ],
            [
                [
                    'name' => 'account_name',
                    'displayParams' => [
                        'key' => 'billing',
                        'copy' => 'primary',
                        'billingKey' => 'primary',
                        'additionalFields' => [
                            'phone_office' => 'phone_work',
                        ],
                    ],
                ],
            ],

            [
                [
                    'name' => 'primary_address_street',
                    'hideLabel' => true,
                    'type' => 'address',
                    'displayParams' => [
                        'key' => 'primary',
                        'rows' => 2,
                        'cols' => 30,
                        'maxlength' => 150,
                    ],
                ],

                [
                    'name' => 'alt_address_street',
                    'hideLabel' => true,
                    'colspan' => 2,
                    'type' => 'address',
                    'displayParams' => [
                        'key' => 'alt',
                        'copy' => 'primary',
                        'rows' => 2,
                        'cols' => 30,
                        'maxlength' => 150,
                    ],
                ],
            ],

            [
                [
                    'name' => 'email1',
                    'studio' => 'false',
                    'label' => 'LBL_EMAIL_ADDRESS',
                ],
            ],

            [
                [
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                ],
            ],
        ],


        'LBL_PANEL_ADVANCED' => [
            [

                [
                    'name' => 'report_to_name',
                    'label' => 'LBL_REPORTS_TO',
                ],

                [
                    'name' => 'sync_contact',
                    'comment' => 'Synch to outlook?  (Meta-Data only)',
                    'label' => 'LBL_SYNC_CONTACT',
                ],
            ],

            [
                [
                    'name' => 'lead_source',
                    'comment' => 'How did the contact come about',
                    'label' => 'LBL_LEAD_SOURCE',
                ],

                [
                    'name' => 'do_not_call',
                    'comment' => 'An indicator of whether contact can be called',
                    'label' => 'LBL_DO_NOT_CALL',
                ],
            ],

            [
                'campaign_name',
            ],
        ],


        'LBL_PANEL_ASSIGNMENT' => [
            [


                [
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ],
            ],
        ],
    ],
];
