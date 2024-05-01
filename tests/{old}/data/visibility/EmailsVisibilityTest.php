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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass EmailsVisibility
 */
class EmailsVisibilityTest extends TestCase
{
    protected static $subject;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');

        static::$subject = 'subject: ' . Uuid::uuid1();

        $data = [
            'name' => static::$subject,
            'state' => Email::STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        SugarTestEmailUtilities::createEmail('', $data);

        $data = [
            'name' => static::$subject,
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        SugarTestEmailUtilities::createEmail('', $data);

        $data = [
            'name' => static::$subject,
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => Uuid::uuid1(),
        ];
        SugarTestEmailUtilities::createEmail('', $data);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
    }

    /**
     * @covers ::addVisibilityWhere
     */
    public function testAddVisibilityWhere()
    {
        $bean = BeanFactory::newBean('Emails');
        $where = "emails.name='" . static::$subject . "'";
        $emails = (array)$bean->get_full_list('', $where);

        $this->assertCount(3, $emails, '3 of 4 emails should have been returned');
    }

    /**
     * @covers ::addVisibilityWhereQuery
     */
    public function testAddVisibilityWhereQuery()
    {
        $bean = BeanFactory::newBean('Emails');

        $q = new SugarQuery();
        $q->select('id');
        $q->from($bean);
        $q->where()->equals('name', static::$subject);

        $emails = $bean->fetchFromQuery($q);

        $this->assertCount(3, $emails, '3 of 4 emails should have been returned');
    }
}
