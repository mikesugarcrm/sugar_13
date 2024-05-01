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
$viewdefs ['Quotes'] =
    [
        'DetailView' => [
            'templateMeta' => [
                'form' => [
                    'closeFormBeforeCustomButtons' => true,
                    'links' => [
                        0 => '{$MOD.PDF_FORMAT} <select name="layout" id="layout">{$LAYOUT_OPTIONS}</select></form>',
                    ],
                    'buttons' => [
                        0 => 'EDIT',
                        1 => 'DUPLICATE',
                        2 => 'DELETE',
                        3 => [
                            'customCode' => '<form action="index.php" method="POST" name="Quote2Opp" id="form"><input type="hidden" name="module" value="Quotes"><input type="hidden" name="record" value="{$fields.id.value}"><input type="hidden" name="user_id" value="{$current_user->id}"><input type="hidden" name="team_id" value="{$fields.team_id.value}"><input type="hidden" name="user_name" value="{$current_user->user_name}"><input type="hidden" name="action" value="QuoteToOpportunity"><input type="hidden" name="opportunity_subject" value="{$fields.name.value}"><input type="hidden" name="opportunity_name" value="{$fields.name.value}"><input type="hidden" name="opportunity_id" value="{$fields.billing_account_id.value}"><input type="hidden" name="amount" value="{$fields.total.value}"><input type="hidden" name="valid_until" value="{$fields.date_quote_expected_closed.value}"><input type="hidden" name="currency_id" value="{$fields.currency_id.value}"><input title="{$APP.LBL_QUOTE_TO_OPPORTUNITY_TITLE}" accessKey="{$APP.LBL_QUOTE_TO_OPPORTUNITY_KEY}" class="button" type="submit" name="opp_to_quote_button" value="{$APP.LBL_QUOTE_TO_OPPORTUNITY_LABEL}"></form>',
                        ],
                        4 => [
                            'customCode' => '<form action="index.php" method="{$PDFMETHOD}" name="ViewPDF" id="form"><input type="hidden" name="module" value="Quotes"><input type="hidden" name="record" value="{$fields.id.value}"><input type="hidden" name="action" value="Layouts"><input type="hidden" name="entryPoint" value="pdf"><input type="hidden" name="email_action"><input title="{$APP.LBL_EMAIL_PDF_BUTTON_TITLE}" accessKey="{$APP.LBL_EMAIL_PDF_BUTTON_KEY}" class="button" type="submit" name="button" value="{$APP.LBL_EMAIL_PDF_BUTTON_LABEL}" onclick="this.form.email_action.value=\'EmailLayout\';"> <input title="{$APP.LBL_VIEW_PDF_BUTTON_TITLE}" accessKey="{$APP.LBL_VIEW_PDF_BUTTON_KEY}" class="button" type="submit" name="button" value="{$APP.LBL_VIEW_PDF_BUTTON_LABEL}">',
                        ],
                    ],
                    'footerTpl' => 'modules/Quotes/tpls/DetailViewFooter.tpl',
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
                            'label' => 'LBL_QUOTE_NAME',
                        ],
                        1 => [
                            'name' => 'opportunity_name',
                            'label' => 'LBL_OPPORTUNITY_NAME',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'quote_num',
                            'label' => 'LBL_QUOTE_NUM',
                        ],
                        1 => [
                            'name' => 'quote_stage',
                            'label' => 'LBL_QUOTE_STAGE',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'purchase_order_num',
                            'label' => 'LBL_PURCHASE_ORDER_NUM',
                        ],
                        1 => [
                            'name' => 'date_quote_expected_closed',
                            'label' => 'LBL_DATE_QUOTE_EXPECTED_CLOSED',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'payment_terms',
                            'label' => 'LBL_PAYMENT_TERMS',
                        ],
                        1 => [
                            'name' => 'original_po_date',
                            'label' => 'LBL_ORIGINAL_PO_DATE',
                        ],
                    ],
                    4 => [
                        0 => [
                            'name' => 'team_name',
                            'label' => 'LBL_TEAM',
                        ],
                        1 => [
                            'name' => 'date_entered',
                            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                            'label' => 'LBL_DATE_ENTERED',
                        ],
                    ],
                    5 => [
                        0 => [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO_NAME',
                        ],
                        1 => [
                            'name' => 'date_modified',
                            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                            'label' => 'LBL_DATE_MODIFIED',
                        ],
                    ],
                    6 => [
                        0 => [
                            'name' => 'billing_account_name',
                            'label' => 'LBL_BILLING_ACCOUNT_NAME',
                        ],
                        1 => [
                            'name' => 'shipping_account_name',
                            'label' => 'LBL_SHIPPING_ACCOUNT_NAME',
                        ],
                    ],
                    7 => [
                        0 => [
                            'name' => 'billing_contact_name',
                            'label' => 'LBL_BILLING_CONTACT_NAME',
                        ],
                        1 => [
                            'name' => 'shipping_contact_name',
                            'label' => 'LBL_SHIPPING_CONTACT_NAME',
                        ],
                    ],
                    8 => [
                        0 => [
                            'name' => 'billing_address_street',
                            'label' => 'LBL_BILL_TO',
                            'type' => 'address',
                            'displayParams' => [
                                'key' => 'billing',
                            ],
                        ],
                        1 => [
                            'name' => 'shipping_address_street',
                            'label' => 'LBL_SHIP_TO',
                            'type' => 'address',
                            'displayParams' => [
                                'key' => 'shipping',
                            ],
                        ],
                    ],
                    9 => [
                        0 => [
                            'name' => 'description',
                            'label' => 'LBL_DESCRIPTION',
                        ],
                        1 => [
                            'name' => 'contacts_quotes_name',
                            'label' => 'LBL_CONTACTS_QUOTES_FROM_CONTACTS_TITLE',
                        ],
                    ],
                    10 => [
                        0 => [
                            'name' => 'contacts_quotes_1_name',
                            'label' => 'LBL_CONTACTS_QUOTES_1_FROM_CONTACTS_TITLE',
                        ],
                        1 => [
                            'name' => 'contacts_quotes_2_name',
                        ],
                    ],
                ],
            ],
        ],
    ];
