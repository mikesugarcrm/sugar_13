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

class Bug58282Test extends TestCase
{
    /** @var Account */
    private $account;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', [true, 1]);

        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp(
            'custom_field',
            [
                'Opportunities',
                [
                    'formula' => 'strToUpper(related($accounts,"name"))',
                    'name' => 'custom_58282',
                    'calculated' => true,
                    'type' => 'text',
                    'label' => 'LBL_CUSTOM_FIELD',
                    'module' => 'ModuleBuilder',
                    'view_module' => 'Opportunities',
                ],
            ]
        );
    }

    protected function setUp(): void
    {
        $this->account = SugarTestAccountUtilities::createAccount();
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    public function testRelatedBeanIsSaved()
    {
        $account = new Account();
        $account->retrieve($this->account->id);

        $link = $this->getLinkMock($account, true);
        $account->opportunities = $link;

        $account->name = 'Name has been changed';
        $account->save();
    }

    public function testRelatedBeanIsNotSaved()
    {
        $account = new Account();
        $account->retrieve($this->account->id);

        $link = $this->getLinkMock($account, false);
        $account->opportunities = $link;

        $account->description = 'Added new description';
        $account->save();
    }

    /**
     * Creates mock of Link2 object with specified number of related beans
     *
     * @param SugarBean $focus
     * @param boolean $shouldBeanBeSaved
     * @return Link2
     */
    protected function getLinkMock(SugarBean $focus, $shouldBeanBeSaved)
    {
        $bean = $this->createPartialMock('SugarBean', ['save']);
        $bean->id = 'Bug58282Test';

        if ($shouldBeanBeSaved) {
            $bean->expects($this->atLeastOnce())
                ->method('save');
        } else {
            $bean->expects($this->never())
                ->method('save');
        }

        $beans = [$bean];

        $mock = $this->getMockBuilder('Link2')
            ->setMethods(['getbeans'])
            ->setConstructorArgs(['opportunities', $focus])
            ->getMock();
        $mock->expects($this->any())
            ->method('getBeans')
            ->will($this->returnValue($beans));
        return $mock;
    }
}
