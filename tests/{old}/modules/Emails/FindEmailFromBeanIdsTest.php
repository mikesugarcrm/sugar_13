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
 * Test cases for Bug 9755
 */
class FindEmailFromBeanIdsTest extends TestCase
{
    private $emailUI;
    private $beanIds;
    private $beanType;
    private $whereArr;
    private $expectedQuery;

    protected function setUp(): void
    {
        global $current_user, $currentModule;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $this->emailUI = new EmailUI();
        $this->beanIds[] = '8744c7d9-9e4b-2338-cb76-4ab0a3d0a651';
        $this->beanIds[] = '8749a110-1d85-4562-fa23-4ab0a3c65e12';
        $this->beanIds[] = '874c1242-4645-898d-238a-4ab0a3f7e7c3';
        $this->beanType = 'users';
        $this->whereArr['first_name'] = 'testfn';
        $this->whereArr['last_name'] = 'testln';
        $this->whereArr['email_address'] = 'test@example.com';
        // @codingStandardsIgnoreStart
        $this->expectedQuery = <<<EOQ
(SELECT users.id, users.first_name, users.last_name, eabr.primary_address, ea.id AS email_address_id, ea.email_address, ea.opt_out, 'Users' module FROM users JOIN email_addr_bean_rel eabr ON (users.id = eabr.bean_id and eabr.deleted=0) JOIN email_addresses ea ON (eabr.email_address_id = ea.id)  WHERE (users.deleted = 0 AND eabr.primary_address = 1 AND users.id in ('8744c7d9-9e4b-2338-cb76-4ab0a3d0a651','8749a110-1d85-4562-fa23-4ab0a3c65e12','874c1242-4645-898d-238a-4ab0a3f7e7c3')) AND (first_name LIKE 'testfn%') AND ea.invalid_email = 0)\n UNION \n(SELECT users.id, users.first_name, users.last_name, eabr.primary_address, ea.id AS email_address_id, ea.email_address, ea.opt_out, 'Users' module FROM users JOIN email_addr_bean_rel eabr ON (users.id = eabr.bean_id and eabr.deleted=0) JOIN email_addresses ea ON (eabr.email_address_id = ea.id)  WHERE (users.deleted = 0 AND eabr.primary_address = 1 AND users.id in ('8744c7d9-9e4b-2338-cb76-4ab0a3d0a651','8749a110-1d85-4562-fa23-4ab0a3c65e12','874c1242-4645-898d-238a-4ab0a3f7e7c3')) AND (last_name LIKE 'testln%') AND ea.invalid_email = 0)\n UNION \n(SELECT users.id, users.first_name, users.last_name, eabr.primary_address, ea.id AS email_address_id, ea.email_address, ea.opt_out, 'Users' module FROM users JOIN email_addr_bean_rel eabr ON (users.id = eabr.bean_id and eabr.deleted=0) JOIN email_addresses ea ON (eabr.email_address_id = ea.id)  WHERE (users.deleted = 0 AND eabr.primary_address = 1 AND users.id in ('8744c7d9-9e4b-2338-cb76-4ab0a3d0a651','8749a110-1d85-4562-fa23-4ab0a3c65e12','874c1242-4645-898d-238a-4ab0a3f7e7c3')) AND (email_address LIKE 'test@example.com%') AND ea.invalid_email = 0)
EOQ;
        // @codingStandardsIgnoreEnd
    }

    protected function tearDown(): void
    {
        unset($this->emailUI);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testFindEmailFromBeanIdTest()
    {
        $resultQuery = $this->emailUI->findEmailFromBeanIds($this->beanIds, $this->beanType, $this->whereArr);
        $this->assertEquals($this->expectedQuery, $resultQuery);
    }
}
