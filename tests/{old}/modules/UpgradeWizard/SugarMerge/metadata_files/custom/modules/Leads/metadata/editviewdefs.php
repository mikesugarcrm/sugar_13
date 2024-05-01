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
$viewdefs['Leads']['EditView'] = [
    'templateMeta' => ['form' => ['hidden' => ['<input type="hidden" name="prospect_id" value="{if isset($smarty.request.prospect_id)}{$smarty.request.prospect_id}{else}{$bean->prospect_id}{/if}">',
        '<input type="hidden" name="account_id" value="{if isset($smarty.request.account_id)}{$smarty.request.account_id}{else}{$bean->account_id}{/if}">',
        '<input type="hidden" name="contact_id" value="{if isset($smarty.request.contact_id)}{$smarty.request.contact_id}{else}{$bean->contact_id}{/if}">',
        '<input type="hidden" name="opportunity_id" value="{if isset($smarty.request.opportunity_id)}{$smarty.request.opportunity_id}{else}{$bean->opportunity_id}{/if}">',
    ],
        'buttons' => [
            'SAVE',
            'CANCEL',
        ],
    ],
        'maxColumns' => '2',
        'widths' => [
            ['label' => '10', 'field' => '30'],
            ['label' => '10', 'field' => '30'],
        ],
        'javascript' => '<script type="text/javascript" language="Javascript">function copyAddressRight(form)  {ldelim} form.alt_address_street.value = form.primary_address_street.value;form.alt_address_city.value = form.primary_address_city.value;form.alt_address_state.value = form.primary_address_state.value;form.alt_address_postalcode.value = form.primary_address_postalcode.value;form.alt_address_country.value = form.primary_address_country.value;return true; {rdelim} function copyAddressLeft(form)  {ldelim} form.primary_address_street.value =form.alt_address_street.value;form.primary_address_city.value = form.alt_address_city.value;form.primary_address_state.value = form.alt_address_state.value;form.primary_address_postalcode.value =form.alt_address_postalcode.value;form.primary_address_country.value = form.alt_address_country.value;return true; {rdelim} </script>',
    ],
    'panels' => [
        'lbl_contact_information' => [
            [
                'lead_source',
                'status',
            ],

            [
                ['name' => 'lead_source_description', 'displayParams' => (['rows' => 4, 'cols' => 40])],
                ['name' => 'status_description', 'displayParams' => (['rows' => 4, 'cols' => 40])],
            ],

            ['campaign_name', 'opportunity_amount'],

            [
                'refered_by',

            ],

            [

                [
                    'name' => 'first_name',
                    'customCode' => '{html_options id="salutation" name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input id="first_name" name="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
                ],
                'phone_work',
            ],

            [
                ['name' => 'last_name',
                    'displayParams' => ['required' => true],
                ],
                'phone_mobile',
            ],

            [
                'birthdate',
                'phone_home',
            ],

            [
                ['name' => 'account_name', 'type' => 'varchar', 'validateDependency' => false, 'customCode' => '<input name="account_name" {if ($fields.converted.value == 1)}disabled="true"{/if} size="30" maxlength="255" type="text" value="{$fields.account_name.value}">'],
                'phone_other',
            ],

            [
                null,
                'phone_fax',
            ],

            [
                'title', 'do_not_call',
            ],

            [
                'department',
            ],

            [
                ['name' => 'team_name', 'displayParams' => ['required' => true]],
                '',
            ],

            [
                'assigned_user_name',
                '',
            ],
        ],

        'lbl_email_addresses' => [
            ['email1'],
        ],

        'lbl_address_information' => [
            [
                [
                    'name' => 'primary_address_street',
                    'hideLabel' => true,
                    'type' => 'address',
                    'displayParams' => ['key' => 'primary', 'rows' => 2, 'cols' => 30, 'maxlength' => 150],
                ],

                [
                    'name' => 'alt_address_street',
                    'hideLabel' => true,
                    'type' => 'address',
                    'displayParams' => ['key' => 'alt', 'copy' => 'primary', 'rows' => 2, 'cols' => 30, 'maxlength' => 150],
                ],
            ],
        ],

        'lbl_description_information' => [
            [
                'test_c',
                'description',
            ],
        ],
    ],
];
