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
 * RS-78 Prepare TeamNotices Module.
 */
class RS78Test extends TestCase
{
    /**
     * @var TeamNotice
     */
    protected $bean;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, false]);

        $this->bean = BeanFactory::newBean('TeamNotices');
        $this->bean->name = 'RS78Test';
        $this->bean->description = 'RS78TestDesc';
        $this->bean->save();
    }

    protected function tearDown(): void
    {
        $this->bean->mark_deleted($this->bean->id);
        SugarTestHelper::tearDown();
    }

    public function testTeamNotices()
    {
        $this->bean->fill_in_additional_list_fields();
        $this->assertEquals('RS78TestDesc', $this->bean->description);
        $list = $this->bean->get_list_view_data();
        $this->assertNotEmpty($list);
        $where = $this->bean->build_generic_where_clause('RS78Test');
        $this->assertNotEmpty($where);
    }
}
