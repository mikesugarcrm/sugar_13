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
$viewdefs ['Contacts'] =
    [
        'EditView' => [
            'templateMeta' => [
                'form' => [
                    'hidden' => [
                        0 => '<input type="hidden" name="opportunity_id" value="{$smarty.request.opportunity_id}">',
                        1 => '<input type="hidden" name="case_id" value="{$smarty.request.case_id}">',
                        2 => '<input type="hidden" name="bug_id" value="{$smarty.request.bug_id}">',
                        3 => '<input type="hidden" name="email_id" value="{$smarty.request.email_id}">',
                        4 => '<input type="hidden" name="inbound_email_id" value="{$smarty.request.inbound_email_id}">',
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
                'lbl_contact_information' => [
                    0 => [
                        0 => [
                            'name' => 'first_name',
                            'customCode' => '{html_options name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
                        ],
                        1 => [
                            'name' => 'phone_work',
                            'comment' => 'Work phone number of the contact',
                            'label' => 'LBL_OFFICE_PHONE',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'last_name',
                            'displayParams' => [
                                'required' => true,
                            ],
                        ],
                        1 => [
                            'name' => 'phone_mobile',
                            'comment' => 'Mobile phone number of the contact',
                            'label' => 'LBL_MOBILE_PHONE',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'test_c',
                            'label' => 'LBL_TEST',
                        ],
                    ],
                    3 => [
                        0 => [
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
                        1 => [
                            'name' => 'phone_home',
                            'comment' => 'Home phone number of the contact',
                            'label' => 'LBL_HOME_PHONE',
                        ],
                    ],
                    4 => [
                        0 => [
                            'name' => 'lead_source',
                            'comment' => 'How did the contact come about',
                            'label' => 'LBL_LEAD_SOURCE',
                        ],
                        1 => [
                            'name' => 'phone_other',
                            'comment' => 'Other phone number for the contact',
                            'label' => 'LBL_OTHER_PHONE',
                        ],
                    ],
                    5 => [
                        0 => [
                            'name' => 'campaign_name',
                            'comment' => 'The first campaign name for Contact (Meta-data only)',
                            'label' => 'LBL_CAMPAIGN',
                        ],
                        1 => [
                            'name' => 'phone_fax',
                            'comment' => 'Contact fax number',
                            'label' => 'LBL_FAX_PHONE',
                        ],
                    ],
                    6 => [
                        0 => [
                            'name' => 'title',
                            'comment' => 'The title of the contact',
                            'label' => 'LBL_TITLE',
                        ],
                        1 => [
                            'name' => 'birthdate',
                            'comment' => 'The birthdate of the contact',
                            'label' => 'LBL_BIRTHDATE',
                        ],
                    ],
                    7 => [
                        0 => [
                            'name' => 'department',
                            'comment' => 'The department of the contact',
                            'label' => 'LBL_DEPARTMENT',
                        ],
                    ],
                    8 => [
                        0 => [
                            'name' => 'report_to_name',
                            'label' => 'LBL_REPORTS_TO',
                        ],
                        1 => [
                            'name' => 'assistant',
                            'comment' => 'Name of the assistant of the contact',
                            'label' => 'LBL_ASSISTANT',
                        ],
                    ],
                    9 => [
                        0 => [
                            'name' => 'sync_contact',
                            'comment' => 'Synch to outlook?  (Meta-Data only)',
                            'label' => 'LBL_SYNC_CONTACT',
                        ],
                        1 => [
                            'name' => 'assistant_phone',
                            'comment' => 'Phone number of the assistant of the contact',
                            'label' => 'LBL_ASSISTANT_PHONE',
                        ],
                    ],
                    10 => [
                        0 => [
                            'name' => 'do_not_call',
                            'comment' => 'An indicator of whether contact can be called',
                            'label' => 'LBL_DO_NOT_CALL',
                        ],
                    ],
                    11 => [
                        0 => [
                            'name' => 'team_name',
                            'displayParams' => [
                                'display' => true,
                            ],
                        ],
                    ],
                    12 => [
                        0 => [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO_NAME',
                        ],
                    ],
                ],
                'lbl_email_addresses' => [
                    0 => [
                        0 => [
                            'name' => 'email1',
                            'studio' => 'false',
                            'label' => 'LBL_EMAIL_ADDRESS',
                        ],
                    ],
                ],
                'lbl_address_information' => [
                    0 => [
                        0 => [
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
                        1 => [
                            'name' => 'alt_address_street',
                            'hideLabel' => true,
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
                ],
                'lbl_description_information' => [
                    0 => [
                        0 => [
                            'name' => 'description',
                            'displayParams' => [
                                'rows' => 6,
                                'cols' => 80,
                            ],
                            'label' => 'LBL_DESCRIPTION',
                        ],

                        [
                            'name' => 'custom_description_c',
                            'displayParams' => [
                                'rows' => 10,
                                'cols' => 100,
                            ],
                            'label' => 'LBL_DESCRIPTION',
                        ],
                    ],
                ],
            ],
        ],
    ];
