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

$viewdefs['Cases']['editview'] = [
    'templateMeta' => ['maxColumns' => '2',
        'widths' => [
            ['label' => '10', 'field' => '30'],
            ['label' => '10', 'field' => '30'],
        ],
        'formId' => 'CaseEditView',
        'formName' => 'CaseEditView',
        'hiddenInputs' => ['module' => 'Cases',
            'returnmodule' => 'Cases',
            'returnaction' => 'DetailView',
            'contact_id' => '{$fields.contact_id.value}',
            'bug_id' => '{$fields.bug_id.value}',
            'email_id' => '{$fields.email_id.value}',
            'action' => 'Save',
            'type' => '{$fields.type.value}',
            'status' => 'New',
        ],
        'hiddenFields' => [
            [
                'name' => 'portal_viewable',
                'operator' => '=',
                'value' => '1',
            ],
        ],
    ],
    'data' => [
        [['field' => 'case_number', 'readOnly' => true]],
        ['priority', 'status', 'id'],
        [['field' => 'name', 'displayParams' => ['size' => 60], 'required' => true]],
        [['field' => 'description', 'displayParams' => ['rows' => '15', 'cols' => '100']]],
    ],
];
