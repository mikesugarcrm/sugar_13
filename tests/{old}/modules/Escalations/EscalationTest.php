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
 * Class EscalationTest
 * @coversDefaultClass Escalation
 */
class EscalationTest extends TestCase
{
    /**
     * @var mixed|\Escalation
     */
    public $escalation;

    public function tearDown(): void
    {
        if (isset($this->escalation->id)) {
            $GLOBALS['db']->query("DELETE FROM escalations WHERE id = '{$this->escalation->id}'");
        }
        unset($this->escalation);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestBugUtilities::removeAllCreatedBugs();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestEscalationUtilities::removeAllCreatedEscalations();
        SugarTestHelper::tearDown();
    }

    private static function deleteRelatedAccounts($escObj, $ids)
    {
        $escObj->load_relationship('accounts');
        foreach ($ids as $id) {
            $escObj->accounts->delete($id);
        }
    }

    /**
     * Tests the automatic linking of parent's account to the escalation
     * @covers ::linkToAccountOfParent($accountId, $linkName)
     * @dataProvider dataProviderlinkToAccountOfParent
     * @throws SugarApiExceptionNotFound
     **/
    public function testlinkToAccountOfParent($accountAttr, $preAssignAcc, $parentAttrs, $escalationAttrs, $expected)
    {
        // Create account
        $acc = SugarTestAccountUtilities::createAccount($accountAttr['id'], ['name' => $accountAttr['name']]);

        // Create a parent bean to be escalated
        switch ($escalationAttrs['parent_type']) {
            case 'Cases':
                $parent = SugarTestCaseUtilities::createCase(
                    $parentAttrs['id'],
                    ['account_id' => $parentAttrs['account_id']]
                );
                break;
            case 'Opportunities':
                $parent = SugarTestOpportunityUtilities::createOpportunity(
                    $parentAttrs['id'],
                    $acc
                );
                break;
            case 'Accounts':
                $parent = SugarTestAccountUtilities::createAccount(
                    $parentAttrs['id'],
                    ['parent_id' => $parentAttrs['parent_id']]
                );
                break;
            case 'Bugs':
                $parent = SugarTestBugUtilities::createBug(
                    $parentAttrs['id'],
                    ['name' => $parentAttrs['name'], 'type' => $parentAttrs['type']]
                );
        }

        if (!empty($preAssignAcc)) {
            $this->escalation = new Escalation();
            $this->escalation->id = $escalationAttrs['id'];
            $this->escalation->parent_type = $escalationAttrs['parent_type'];
            $this->escalation->parent_id = $escalationAttrs['parent_id'];
            $this->escalation->load_relationship('accounts');

            if ($preAssignAcc['id'] != $acc->id) {
                $preAssignAcc = SugarTestAccountUtilities::createAccount(
                    $preAssignAcc['id'],
                    ['name' => $preAssignAcc['name']]
                );
                $this->escalation->accounts->add([$preAssignAcc]);
            } else {
                $this->escalation->accounts->add([$acc]);
            }

            $this->escalation->save();
            $accountIds = $this->escalation->accounts->get();

            // Remove linked accounts for next test
            if (!empty($accountIds)) {
                self::deleteRelatedAccounts($this->escalation, $accountIds);
            }
        } else {
            // Create escalation bean with above parent
            $escalation = SugarTestEscalationUtilities::createEscalation(
                $escalationAttrs['id'],
                ['parent_type' => $escalationAttrs['parent_type'], 'parent_id' => $escalationAttrs['parent_id']]
            );

            $escalation->load_relationship('accounts');
            $accountIds = $escalation->accounts->get();
            // Remove linked accounts for next test
            if (!empty($accountIds)) {
                self::deleteRelatedAccounts($escalation, $accountIds);
            }
        }

        $this->assertEquals($expected, $accountIds);
    }

    public function dataProviderlinkToAccountOfParent()
    {
        return [
            [
                ['id' => 'acc1', 'name' => 'Test Account1',],
                [],
                ['id' => 'case1', 'account_id' => 'acc1',],
                ['id' => 'esc1', 'parent_type' => 'Cases', 'parent_id' => 'case1',],
                ['acc1'],
            ],
            [
                ['id' => 'acc1', 'name' => 'Test Account1',],
                ['id' => 'acc1', 'name' => 'Test Account1',],
                ['id' => 'case1', 'account_id' => 'acc1',],
                ['id' => 'esc1', 'parent_type' => 'Cases', 'parent_id' => 'case1',],
                ['acc1'],
            ],
            [
                ['id' => 'acc1', 'name' => 'Test Account1',],
                [],
                ['id' => 'opp1',],
                ['id' => 'esc1', 'parent_type' => 'Opportunities', 'parent_id' => 'opp1',],
                ['acc1'],
            ],
            [
                ['id' => 'acc1', 'name' => 'Test Account1',],
                [],
                ['id' => 'acc2', 'parent_id' => 'acc1',],
                ['id' => 'esc1', 'parent_type' => 'Accounts', 'parent_id' => 'acc2',],
                ['acc1'],
            ],
            [
                ['id' => 'acc1', 'name' => 'Test Account1',],
                [],
                ['id' => 'bug1', 'name' => 'Test Bug1', 'type' => 'Defect',],
                ['id' => 'esc1', 'parent_type' => 'Bugs', 'parent_id' => 'bug1',],
                [],
            ],
            [
                ['id' => 'acc1', 'name' => 'Test Account1',],
                ['id' => 'acc2', 'name' => 'Test Account2',],
                ['id' => 'case1', 'account_id' => 'acc1',],
                ['id' => 'esc1', 'parent_type' => 'Cases', 'parent_id' => 'case1',],
                ['acc2', 'acc1'],
            ],
            [
                ['id' => 'acc1', 'name' => 'Test Account1',],
                ['id' => 'acc2', 'name' => 'Test Account2',],
                ['id' => 'bug1', 'name' => 'Test Bug1', 'type' => 'Defect'],
                ['id' => 'esc1', 'parent_type' => 'Bugs', 'parent_id' => 'bug1',],
                ['acc2'],
            ],
        ];
    }
}
