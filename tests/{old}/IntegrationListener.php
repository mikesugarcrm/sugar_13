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

use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;
use Sugarcrm\Sugarcrm\SugarConnect\Configuration\Configuration as SugarConnectConfiguration;

class IntegrationListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * @var int
     */
    private $maxExecutionTime;

    /**
     * @deprecated Will be removed in the next release.
     * @var bool
     */
    private $isSugarConnectEnabled;

    public function startTestSuite(TestSuite $suite): void
    {
        if ($suite instanceof DataProviderTestSuite) {
            return;
        }

        SugarTestHelper::init();
    }

    public function startTest(Test $test): void
    {
        // Prevent the activity stream from creating messages.
        Activity::disable();

        // shared bean definitions may contain properties like $disable_row_level_security
        // set on them by previous tests which shouldn't be shared between tests
        BeanFactory::clearCache();

        //track the original max execution time limit
        $this->maxExecutionTime = ini_get('max_execution_time');

        // Disable Sugar Connect for every test to prevent notifications from
        // being sent to the Sugar Connect webhook.
        $config = new SugarConnectConfiguration();
        $this->isSugarConnectEnabled = $config->isEnabled();
        $config->disable();
    }

    public function endTest(Test $test, float $time): void
    {
        $_GET = $_POST = $_REQUEST = [];

        //sometimes individual tests change the max time execution limit, reset back to original
        set_time_limit($this->maxExecutionTime);

        restore_error_handler();

        SugarRelationship::resaveRelatedBeans();

        // clean up prepared statements
        $connection = DBManagerFactory::getConnection()->getWrappedConnection();

        $ro = new ReflectionObject($connection);

        if ($ro->hasProperty('statements')) {
            $rp = $ro->getProperty('statements');
            $rp->setAccessible(true);
            $rp->setValue($connection, []);
        }

        // Restore the Sugar Connect configuration.
        $config = new SugarConnectConfiguration();

        if ($this->isSugarConnectEnabled) {
            $config->enable();
        } else {
            $config->disable();
        }
    }

    public function endTestSuite(TestSuite $suite): void
    {
        if ($suite instanceof DataProviderTestSuite) {
            return;
        }

        unset($GLOBALS['disable_date_format']);
        SugarBean::resetOperations();
        $GLOBALS['timedate']->clearCache();

        SugarTestHelper::tearDown();
    }
}
