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

namespace Sugarcrm\SugarcrmTestsUnit\ScheduledTasks;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \UserUtilitiesJob
 */
class UserUtilitiesJobTest extends TestCase
{
    /**
     * @covers ::getJobData
     */
    public function testGetJobData()
    {
        //serialized base64-encoded representation of Injection class
        $payload = 'Tzo1MToiU3VnYXJjcm1cU3VnYXJjcm1UZXN0c1VuaXRcU2NoZWR1bGVkVGFza3NcSW5qZWN0aW9uIjowOnt9';

        require_once './Ext/ScheduledTasks/userutils.php';
        /**
         * Injection::__desctruct() will be called on unserialize() if the second param ['allowed_classes' => false]
         * is not provided
         */
        $job = new class extends \UserUtilitiesJob {

            public function getJobData($data)
            {
                return parent::getJobData($data);
            }
        };

        $schedulerJob = $this->getMockBuilder(\SchedulersJob::class)
            ->disableOriginalConstructor()
            ->getMock();
        $job->setJob($schedulerJob);
        ob_start();
        $job->getJobData($payload);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertStringNotContainsString('INJECTION', $output);
    }
}

class Injection
{
    public function __destruct()
    {
        $this->test();
    }

    protected function test()
    {
        echo 'INJECTION';
    }
}
