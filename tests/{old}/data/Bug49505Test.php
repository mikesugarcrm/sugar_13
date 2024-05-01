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
 * @ticket 49505
 */
class Bug49505Test extends TestCase
{
    /**
     * @var array
     */
    private $createdBeans = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        foreach ($this->createdBeans as $bean) {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }

        SugarTestHelper::tearDown();
    }

    public function testGetRelatedListFunctionWithLink2Class()
    {
        $focusModule = 'Accounts';
        $linkedModules = [
            'Bugs', // many-to-many
            'Contacts', // one-to-many
        ];

        $focus = BeanFactory::newBean($focusModule);
        $focus->name = 'bug49505';
        $focus->save();
        $this->createdBeans[] = $focus;

        foreach ($linkedModules as $v) {
            $linkedBean = BeanFactory::newBean($v);
            $linkedBean->name = 'bug49505';
            $linkedBean->save();
            $this->createdBeans[] = $linkedBean;

            $link = new Link2(strtolower($v), $focus);
            $link->add([$linkedBean]);

            // get relation from 'Link2' class
            $link2List = $focus->get_related_list($linkedBean, strtolower($v));

            $this->assertEquals($linkedBean->id, $link2List['list'][0]->id);
        }
    }
}
