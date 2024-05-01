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
 * @ticket 59144
 */
class Bug59144Test extends TestCase
{
    /**
     * @var Lead
     */
    private $lead;

    /**
     * @var Call
     */
    private $call;

    protected $has_disable_count_query_enabled;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        global $sugar_config;
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->lead = SugarTestLeadUtilities::createLead();
        $this->call = SugarTestCallUtilities::createCall();

        $this->lead->load_relationship('calls_parent');
        $this->lead->calls_parent->add($this->call);
        $this->has_disable_count_query_enabled = !empty($sugar_config['disable_count_query']);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        global $sugar_config;
        if ($this->has_disable_count_query_enabled) {
            $sugar_config['disable_count_query'] = true;
        } else {
            unset($sugar_config['disable_count_query']);
        }
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestCallUtilities::removeAllCreatedCalls();

        SugarTestHelper::tearDown();
    }

    public function testQueryIsNotBroken()
    {
        global $sugar_config;
        unset($sugar_config['disable_count_query']);

        $lead = new Lead();
        $lead->retrieve($this->lead->id);
        $lead->load_relationship('calls_parent');
        $calls = $lead->calls_parent->getBeans(
            [
                'enforce_teams' => true,
            ]
        );

        $this->assertIsArray($calls);
        $this->assertEquals(1, count($calls));

        $call = array_shift($calls);
        $this->assertEquals($this->call->id, $call->id);
        // now without count query
        $sugar_config['disable_count_query'] = true;
        $calls = $lead->calls_parent->getBeans(
            [
                'enforce_teams' => true,
            ]
        );

        $this->assertIsArray($calls);
        $this->assertEquals(1, count($calls));

        $call = array_shift($calls);
        $this->assertEquals($this->call->id, $call->id);
    }
}
