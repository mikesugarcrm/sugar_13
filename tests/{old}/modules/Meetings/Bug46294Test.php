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

use PHPUnit\Framework\TestCase;

require_once 'modules/Meetings/Meeting.php';

class Bug46294Test extends TestCase
{
    public $dictionaryOptionsNotSet = [
        'Meeting' => [
            'fields' => [
                'type' => [
                    'options' => '',
                ],
            ],
        ],
    ];

    public $dictionaryOptionsEmpty = [
        'Meeting' => [
            'fields' => [
                'type' => [],
                //empty
            ],
        ],
    ];

    public $dictionaryOptionsSet = [
        'Meeting' => [
            'fields' => [
                'type' => [
                    'options' => 'type_list',
                ],
            ],
        ],
    ];

    public $dictionaryTypeListNotExists = [
        'Meeting' => [
            'fields' => [
                'type' => [
                    'options' => 'type_not_exists',
                ],
            ],
        ],
    ];

    public $appListStrings = [
        'type_list' => [
            'breakfast' => 'breakfast',
            'lunch' => 'lunch',
            'dinner' => 'dinner',
        ],
    ];

    public $appListStringsEmpty = ['type_list' => []];

    /**
     * @dataProvider provider
     */
    public function testGetMeetingTypeOptions($dictionary, $appList, $isEmpty)
    {
        $result = getMeetingTypeOptions($dictionary, $appList);
        $this->assertEquals($isEmpty, empty($result));
    }

    public function provider()
    {
        return [
            [$this->dictionaryOptionsSet, $this->appListStrings, false],
            [$this->dictionaryOptionsNotSet, $this->appListStrings, true],
            [$this->dictionaryOptionsEmpty, $this->appListStrings, true],
            [$this->dictionaryTypeListNotExists, $this->appListStrings, true],
            [$this->dictionaryOptionsSet, $this->appListStringsEmpty, true],
        ];
    }
}
