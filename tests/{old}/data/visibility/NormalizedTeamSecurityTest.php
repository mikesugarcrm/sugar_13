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

namespace Sugarcrm\SugarcrmTestsUnit\data\visibility;

use BeanFactory;
use DBManagerFactory;
use MysqlManager;
use NormalizedTeamSecurity;
use PHPUnit\Framework\TestCase;
use SugarBean;
use SugarQuery;
use SugarTestHelper;
use SugarTestReflection;

/**
 * @coversDefaultClass NormalizedTeamSecurity
 */
class NormalizedTeamSecurityTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests the Subquery optimizer hint in case it's allowed
     * NOTE: For the deprecated Visibility Methods which take a query as a string the hint is disabled anyway
     *
     * @covers ::addVisibility
     */
    public function testAddVisibilityWithOptimizerHintForQueryAsString()
    {
        if (!$this->isSubQueryOptimizerHintApplicable()) {
            $this->markTestSkipped();
        }

        $strategy = $this->getNormalizedTeamSecurity(BeanFactory::getBean('Emails'), false);

        $query = 'some sql';

        $expectedHint = '/*+ SUBQUERY(MATERIALIZATION) */';
        $queryWithVisibility = $strategy->addVisibilityFrom($query);
        $this->assertStringNotContainsString($expectedHint, $queryWithVisibility);

        $queryWithVisibility = $strategy->addVisibilityWhere($query);
        $this->assertStringNotContainsString($expectedHint, $queryWithVisibility);
    }

    /**
     * Tests the Subquery optimizer hint in case it's disabled
     * NOTE: For the deprecated Visibility Methods which take a query as a string the hint is disabled anyway
     *
     * @covers ::addVisibility
     */
    public function testAddVisibilityWithOptimizerHintDisabled()
    {
        if (!$this->isSubQueryOptimizerHintApplicable()) {
            $this->markTestSkipped();
        }

        $strategy = $this->getNormalizedTeamSecurity(BeanFactory::getBean('Emails'), true);

        $query = 'some sql';

        $expectedHint = '/*+ SUBQUERY(MATERIALIZATION) */';
        $queryWithVisibility = $strategy->addVisibilityFrom($query);
        $this->assertStringNotContainsString($expectedHint, $queryWithVisibility);

        $queryWithVisibility = $strategy->addVisibilityWhere($query);
        $this->assertStringNotContainsString($expectedHint, $queryWithVisibility);
    }

    /**
     * Tests the Subquery optimizer hint in different cases
     *
     * @dataProvider providerVisibilityQuery
     */
    public function testVisibilityQuery(string $method, SugarBean $bean, NormalizedTeamSecurity $strategy, bool $isHintExpected)
    {
        if (!$this->isSubQueryOptimizerHintApplicable()) {
            $this->markTestSkipped();
        }

        $expectedHint = '/*+ SUBQUERY(MATERIALIZATION) */';
        $query = new SugarQuery();
        $query->from($bean, ['team_security' => false]);
        $strategy->$method($query);

        if ($isHintExpected) {
            $this->assertStringContainsString($expectedHint, $query->compile()->getSql());
        } else {
            $this->assertStringNotContainsString($expectedHint, $query->compile()->getSql());
        }
    }

    public function providerVisibilityQuery()
    {
        $bean = BeanFactory::getBean('Emails');
        $withHintDisabled = $this->getNormalizedTeamSecurity($bean, true);
        $withHintEnabled = $this->getNormalizedTeamSecurity($bean, false);

        return [
            ['addVisibilityWhereQuery', $bean, $withHintDisabled, false],
            ['addVisibilityFromQuery', $bean, $withHintDisabled, false],
            ['addVisibilityQuery', $bean, $withHintDisabled, false],
            ['addVisibilityWhereQuery', $bean, $withHintEnabled, true],
            ['addVisibilityFromQuery', $bean, $withHintEnabled, false],
            ['addVisibilityQuery', $bean, $withHintEnabled, true],
        ];
    }

    private function getNormalizedTeamSecurity(SugarBean $bean, bool $hintDisabled): NormalizedTeamSecurity
    {
        $nsc = new NormalizedTeamSecurity($bean);
        $options = SugarTestReflection::getProtectedValue($nsc, 'options');
        if ($hintDisabled) {
            $options['disable_subquery_optimizer_hint'] = true;
        } else {
            unset($options['disable_subquery_optimizer_hint']);
        }
        SugarTestReflection::setProtectedValue($nsc, 'options', $options);

        return $nsc;
    }

    private function isSubQueryOptimizerHintApplicable(): bool
    {
        return DBManagerFactory::getInstance() instanceof MysqlManager;
    }
}
