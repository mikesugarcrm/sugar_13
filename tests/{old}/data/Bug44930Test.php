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

/**
 * Bug #44930
 * Issue with the opportunity subpanel in Accounts
 *
 * @author mgusev@sugarcrm.com
 * @ticked 44930
 */
class Bug44930Test extends TestCase
{
    /**
     * Test tries to emulate changing of related field and assert correct result
     *
     * @group 44930
     * @return void
     */
    public function testChangingOfRelation()
    {
        $_REQUEST['relate_id'] = '2';
        $_REQUEST['relate_to'] = 'test';

        $bean = new SugarBean();
        $bean->id = '1';
        $bean->test_id = '3';
        $bean->field_defs = [
            'test' => [
                'type' => 'link',
                'relationship' => 'test',
                'link_class' => 'Link44930',
            ],
        ];
        $bean->relationship_fields = [
            'test_id' => 'test',
        ];

        $bean->save_relationship_changes(true);

        $this->assertEquals($bean->test_id, $bean->test->lastCall, 'Last relation should point to test_id instead of relate_id');
    }
}

/**
 * Emulation of link2 class
 */
class Link44930
{
    public $lastCall = '';

    public function __call($function, $arguments)
    {
        if ($function == 'add') {
            $this->lastCall = reset($arguments);
        }
    }
}
