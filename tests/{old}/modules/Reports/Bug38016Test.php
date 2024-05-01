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

class Bug38016Test extends TestCase
{
    protected static $fixturesPath;

    private $report;
    private $summaryView;

    public static function setUpBeforeClass(): void
    {
        self::$fixturesPath = __DIR__ . '/Fixtures/';
    }

    protected function setUp(): void
    {
        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $fixture = file_get_contents(self::$fixturesPath . get_class($this) . '.json');
        $this->report = new Report($fixture);
        $GLOBALS['module'] = 'Reports';
        $this->summaryView = new ReportsSugarpdfSummary();
        $this->summaryView->bean = &$this->report;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['module']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }

    public function testSummationQueryMadeWithoutCountColumn()
    {
        // FIXME we shouldn't be suppressing errors
        @$this->summaryView->display();
        $this->assertTrue(!empty($this->report->total_query));
    }
}
