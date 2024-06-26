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

$dictionary['tag_bean_rel'] = [
    'table' => 'tag_bean_rel',
    'relationships' => [
    ],
    'fields' => [
        'id' => [
            'name' => 'id',
            'type' => 'id',
            'required' => true,
        ],
        'tag_id' => [
            'name' => 'tag_id',
            'type' => 'id',
            'required' => true,
        ],
        'bean_id' => [
            'name' => 'bean_id',
            'type' => 'id',
            'required' => true,
        ],
        'bean_module' => [
            'name' => 'bean_module',
            'type' => 'varchar',
            'len' => 100,
        ],
        'date_modified' => [
            'name' => 'date_modified',
            'type' => 'datetime',
        ],
        'deleted' => [
            'name' => 'deleted',
            'type' => 'bool',
            'default' => '0',
        ],
    ],
    'indices' => [
        [
            'name' => 'tags_bean_relpk',
            'type' => 'primary',
            'fields' => [
                'id',
            ],
        ],
        [
            'name' => 'idx_tagsrel_tagid_beanid',
            'type' => 'index',
            'fields' => [
                'tag_id',
                'bean_id',
            ],
        ],
        [
            'name' => 'idx_tag_bean_rel_del_bean_module_beanid',
            'type' => 'index',
            'fields' => [
                'deleted',
                'bean_module',
                'bean_id',
            ],
        ],
        [
            'name' => 'idx_del_tagid_beanid',
            'type' => 'index',
            'fields' => [
                'deleted',
                'tag_id',
                'bean_id',
            ],
        ],
        [
            'name' => 'idx_bid_tid_bm_del',
            'type' => 'index',
            'fields' => [
                'bean_id',
                'tag_id',
                'bean_module',
                'deleted',
            ],
        ],
    ],
];
