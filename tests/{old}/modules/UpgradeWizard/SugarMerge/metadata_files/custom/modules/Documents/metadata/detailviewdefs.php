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

$viewdefs['Documents']['DetailView'] = [
    'templateMeta' => ['maxColumns' => '2',
        'form' => ['hidden' => ['<input type="hidden" name="old_id" value="{$fields.document_revision_id.value}">']],
        'widths' => [
            ['label' => '10', 'field' => '30'],
            ['label' => '10', 'field' => '30'],
        ],
    ],
    'panels' => [
        'lbl_document_information' => [
            [
                [
                    'name' => 'filename',
                    'displayParams' => [
                        'link' => 'filename',
                        'id' => 'document_revision_id',
                    ],
                ],
                'status',
            ],

            [
                [
                    'name' => 'document_name',
                    'label' => 'LBL_DOC_NAME',
                ],
                [
                    'name' => 'revision',
                    'label' => 'LBL_DOC_VERSION',
                ],
            ],

            [
                [
                    'name' => 'template_type',
                    'label' => 'LBL_DET_TEMPLATE_TYPE',
                ],
                [
                    'name' => 'is_template',
                    'label' => 'LBL_DET_IS_TEMPLATE',
                ],
            ],

            [
                'active_date',
                'category_id',
            ],

            [
                'exp_date',
                'subcategory_id',
            ],

            [
                [
                    'name' => 'description',
                    'label' => 'LBL_DOC_DESCRIPTION',
                ],
            ],

            [
                'related_doc_name',
                'related_doc_rev_number',
            ],

            [
                'team_name',
            ],

        ],
        'LBL_REVISIONS_PANEL' => [
            [
                0 => 'last_rev_created_name',
                1 => 'last_rev_create_date',
            ],
        ],
    ],

];
