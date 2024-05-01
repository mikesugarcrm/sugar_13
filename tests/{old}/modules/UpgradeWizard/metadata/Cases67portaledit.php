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

$viewdefs ['Cases']['portal']['view']['edit'] =
    [
        'buttons' => [
            [
                'name' => 'cancel_button',
                'type' => 'button',
                'label' => 'LBL_CANCEL_BUTTON_LABEL',
                'value' => 'cancel',
                'events' => [
                    'click' => 'function(){ window.history.back(); }',
                ],
                'css_class' => 'btn-invisible btn-link',
            ],
            [
                'name' => 'save_button',
                'type' => 'button',
                'label' => 'LBL_SAVE_BUTTON_LABEL',
                'value' => 'save',
                'css_class' => 'btn-primary',
            ],
        ],
        'templateMeta' => [
            'maxColumns' => '2',
            'widths' => [
                [
                    'label' => '10',
                    'field' => '30',
                ],
                [
                    'label' => '10',
                    'field' => '30',
                ],
            ],
            'formId' => 'CaseEditView',
            'formName' => 'CaseEditView',
            'hiddenInputs' => [
                'module' => 'Cases',
                'returnmodule' => 'Cases',
                'returnaction' => 'DetailView',
                'action' => 'Save',
            ],
            'hiddenFields' => [
                [
                    'name' => 'portal_viewable',
                    'operator' => '=',
                    'value' => '1',
                ],
            ],
            'useTabs' => false,
        ],
        'panels' => [
            [
                'label' => 'LBL_PANEL_DEFAULT',
                'fields' => [
                    [
                        'name' => 'name',
                        'displayParams' => [
                            'colspan' => 2,
                        ],
                    ],
                    [
                        'name' => 'description',
                        'displayParams' => [
                            'colspan' => 2,
                        ],
                    ],
                    'type',
                    'priority',
                    'id',
                ],
            ],
        ],
    ];
