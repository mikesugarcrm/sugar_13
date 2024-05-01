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
 *  RS-44: Prepare RegisterLead Api.
 */
class RS44Test extends TestCase
{
    /**
     * @var Lead
     */
    protected static $testBean;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, false]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();

        // Delete the test bean data now
        if (static::$testBean) {
            // soft delete just in case some additional related records are left behind
            static::$testBean->mark_deleted(static::$testBean->id);
            // hard delete the record as well
            $qb = static::$testBean->db->getConnection()->createQueryBuilder();
            $qb->delete(
                static::$testBean->table_name
            )->where(
                $qb->expr()->eq(
                    'id',
                    $qb->createPositionalParameter(static::$testBean->id)
                )
            )->execute();
        }
    }

    public function testCreateLead()
    {
        $api = new RegisterLeadApi();
        $rest = SugarTestRestUtilities::getRestServiceMock();

        $result = $api->createLeadRecord($rest, [
            'last_name' => 'RS44Test',
            'lead_source' => 'Self Generated',
        ]);

        // Begin assertions
        $this->assertNotEmpty($result);

        static::$testBean = BeanFactory::getBean('Leads', $result);
        $this->assertEquals('RS44Test', static::$testBean->last_name);
    }
}
