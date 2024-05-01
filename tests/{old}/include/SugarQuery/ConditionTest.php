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

class ConditionTest extends TestCase
{
    protected static $products = [];
    protected static $prodIds = [];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $db = DBManagerFactory::getInstance();

        // "Delete" all the products that may currently exist
        $sql = 'SELECT id FROM products WHERE deleted = 0';
        $res = $db->query($sql);
        while ($row = $db->fetchRow($res)) {
            self::$prodIds[] = $row['id'];
        }

        if (self::$prodIds) {
            $sql = "UPDATE products SET deleted = 1 WHERE id IN ('" . implode("','", self::$prodIds) . "')";
            $db->query($sql);
        }

        for ($x = 1; $x <= 4; $x++) {
            self::$products[] = SugarTestProductUtilities::createProduct(null, [
                'name' => "SugarQuery Unit Test {$x}",
                'quantity' => $x,
            ]);
        }
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();

        $db = DBManagerFactory::getInstance();

        if (!empty(self::$products)) {
            $oppList = [];
            foreach (self::$products as $opp) {
                $oppList[] = $opp->id;
            }

            $db->query("DELETE FROM products WHERE id IN ('" . implode("','", $oppList) . "')");

            if ($db->tableExists('products_cstm')) {
                $db->query("DELETE FROM products_cstm WHERE id_c IN ('" . implode("','", $oppList) . "')");
            }
        }

        if (self::$prodIds) {
            $sql = "UPDATE products SET deleted = 0 WHERE id IN ('" . implode("','", self::$prodIds) . "')";
            $db->query($sql);
        }
    }

    public function testEquals()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->equals('quantity', 3);

        $result = $sq->execute();

        $this->assertResult([3], $result);
    }

    public function testContains()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->contains('name', 'Query Unit Test 2');

        $result = $sq->execute();

        $this->assertResult([2], $result);
    }

    public function testStartsWith()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->starts('name', 'SugarQuery Unit Test 3');

        $result = $sq->execute();

        $this->assertResult([3], $result);
    }

    public function testLessThan()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->lt('quantity', 3);

        $result = $sq->execute();

        $this->assertResult([1, 2], $result);
    }

    public function testLessThanEquals()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->lte('quantity', 3);

        $result = $sq->execute();

        $this->assertResult([1, 2, 3], $result);
    }

    public function testGreaterThan()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->gt('quantity', 2);

        $result = $sq->execute();

        $this->assertResult([3, 4], $result);
    }

    public function testGreaterThanEquals()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->gte('quantity', 2);

        $result = $sq->execute();

        $this->assertResult([2, 3, 4], $result);
    }

    public function testDateRange()
    {
        $sq = new SugarQuery();

        $sq->select(['name', 'date_modified']);
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->dateRange('date_entered', 'last_7_days');

        $result = $sq->execute();

        $this->assertGreaterThanOrEqual(
            1,
            count($result),
            'Wrong row count, actually received: ' . count($result) . ' back.'
        );

        foreach ($result as $opp) {
            $this->assertGreaterThanOrEqual(
                gmdate('Y-m-d H:i:s', gmmktime(0, 0, 0, gmdate('m'), gmdate('d') - 7, gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
            $this->assertLessThanOrEqual(
                gmdate('Y-m-d H:i:s', gmmktime(23, 59, 59, gmdate('m'), gmdate('d'), gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
        }
    }

    public function testDateBetween()
    {
        $sq = new SugarQuery();

        $sq->select(['name', 'date_modified']);
        $sq->from(BeanFactory::newBean('Products'));
        $params = [gmdate('Y-m-d', gmmktime(0, 0, 0, gmdate('m'), gmdate('d') - 1, gmdate('Y'))), gmdate('Y-m-d')];
        $sq->where()->dateBetween('date_entered', $params);

        $result = $sq->execute();

        $this->assertGreaterThanOrEqual(
            1,
            count($result),
            'Wrong row count, actually received: ' . count($result) . ' back.'
        );

        foreach ($result as $opp) {
            $this->assertGreaterThanOrEqual(
                gmdate('Y-m-d H:i:s', gmmktime(0, 0, 0, gmdate('m'), gmdate('d') - 1, gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
            $this->assertLessThanOrEqual(
                gmdate('Y-m-d H:i:s', gmmktime(23, 59, 59, gmdate('m'), gmdate('d'), gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
        }
    }

    /**
     * @dataProvider inProvider
     */
    public function testIn(array $in, array $expected)
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->in('quantity', $in);

        $result = $sq->execute();

        $this->assertResult($expected, $result);
    }

    public static function inProvider()
    {
        return [
            'only-non-null' => [
                [1, 3],
                [1, 3],
            ],
            'null-and-non-null' => [
                ['', 1, 3],
                [1, 3],
            ],
            'only-null' => [
                [''],
                [],
            ],
        ];
    }

    /**
     * @dataProvider notInProvider
     */
    public function testNotIn(array $notIn, array $expected)
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->notIn('quantity', $notIn);

        $result = $sq->execute();

        $this->assertResult($expected, $result);
    }

    public static function notInProvider()
    {
        return [
            'only-non-null' => [
                [1, 3],
                [2, 4],
            ],
            'null-and-non-null' => [
                ['', 1, 3],
                [2, 4],
            ],
            'only-null' => [
                [''],
                [1, 2, 3, 4],
            ],
        ];
    }

    public function testBetween()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->between('quantity', 2, 4);

        $result = $sq->execute();

        $this->assertResult([2, 3, 4], $result);
    }

    public function testNotNull()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->notNull('quantity');

        $result = $sq->execute();

        $this->assertResult([1, 2, 3, 4], $result);
    }

    public function testNull()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->isNull('quantity');

        $result = $sq->execute();

        $this->assertResult([], $result);
    }

    public function testNotEmptyNotRequiredDate()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->isNotEmpty('date_entered');

        $result = $sq->execute();

        $this->assertResult([1, 2, 3, 4], $result);
    }

    public function testRaw()
    {
        $sq = new SugarQuery();

        $sq->select('quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->whereRaw("name = 'SugarQuery Unit Test 2'");

        $result = $sq->execute();

        $this->assertResult([2], $result);
    }

    public function testOrderByLimit()
    {
        $sq = new SugarQuery();
        $sq->select('name', 'quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->orderBy('quantity', 'ASC');
        $sq->limit(2);

        $result = $sq->execute();

        $this->assertCount(2, $result);

        $low = $result[0]['quantity'];
        $high = $result[1]['quantity'];

        $this->assertGreaterThan($low, $high, "{$high} is not greater than {$low}");

        $sq = new SugarQuery();
        $sq->select('name', 'quantity');
        $sq->from(BeanFactory::newBean('Products'));
        $sq->orderBy('quantity', 'ASC');
        $sq->limit(2);
        $sq->offset(1);

        $result = $sq->execute();

        $this->assertCount(2, $result);

        $low = $result[0]['quantity'];
        $high = $result[1]['quantity'];

        $this->assertGreaterThan($low, $high, "{$high} is not greater than {$low}");
    }

    private function assertResult(array $expected, array $actual)
    {
        $actual = array_column($actual, 'quantity');
        sort($actual);
        $this->assertEquals($expected, $actual);
    }
}
