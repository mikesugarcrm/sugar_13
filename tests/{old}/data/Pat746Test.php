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

class Pat746Test extends TestCase
{
    private $lead;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->lead = new Pat746Test_SugarBean();
    }

    protected function tearDown(): void
    {
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestHelper::tearDown();
    }

    public function testPopulateFromRow()
    {
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'f l');

        $this->lead->populateFromRow(
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ]
        );

        $this->assertEquals('John Doe', $this->lead->name);
    }
}

class Pat746Test_SugarBean extends SugarBean
{
    public $object_name = 'Pat746Test';
    public $createLocaleFormattedName = true;
    public $module_name = 'Leads';
    public $field_defs = [
        'name' => [
            'name' => 'name',
            'rname' => 'name',
            'type' => 'fullname',
        ],
    ];
    public $name_format_map = [
        'f' => 'first_name',
        'l' => 'last_name',
        's' => 'salutation',
        't' => 'title',
    ];
}
