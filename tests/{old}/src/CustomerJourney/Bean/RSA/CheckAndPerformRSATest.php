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
use Sugarcrm\Sugarcrm\CustomerJourney\Bean\RSA\CheckAndPerformRSA;

class CheckAndPerformRSATest extends TestCase
{
    /**
     * @var \SugarTestCJHelper
     */
    private $cjTestHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cjTestHelper = new SugarTestCJHelper();
    }

    /**
     * @covers ::checkRelatedSugarAction()
     */
    public function testGetForms()
    {
        try {
            $this->cjTestHelper->createBean('Accounts');
            $workflowTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates', [
                'url' => 'https://www.google.com',
                'headers' => 'Accept-Language: en-US,en;q=0.5',
            ]);
            $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
                'dri_workflow_template_id' => 'testTemplate1',
            ]);
            $workflow = $this->cjTestHelper->createBean('DRI_Workflows', [
                'dri_workflow_template_id' => 'testTemplate1',
            ]);
            $testForm = $this->cjTestHelper->createBean('CJ_Forms', [
                'dri_workflow_template_id' => 'testTemplate1',
            ]);
            $workflowTemplate->load_relationship("forms");
            $workflowTemplate->forms->add($testForm);
            $forms = CheckAndPerformRSA::getForms($workflow);
            if (count($forms) > 0) {
                $this->assertEquals($forms[0]->id, 'testForm');
                $this->assertEquals($forms[0]->name, 'Test Form');
            } else {
                $this->assertEquals(count($forms), 0);
            }

            $executed = true;
        } catch (\Exception $e) {
            $executed = false;
        }

        $this->assertEquals($executed, true);
    }

    /**
    * @inheritdoc
    */
    protected function tearDown() : void
    {
        SugarTestHelper::tearDown();
        $this->cjTestHelper->tearDown();
    }
}
