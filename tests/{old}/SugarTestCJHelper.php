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
use Sugarcrm\Sugarcrm\CustomerJourney\Bean\Activity\Helper\ChildActivityHelper;

class SugarTestCJHelper
{
    /**
     * @var \User
     */
    private $currentUser;

    /**
     * @var Array
     */
    private $deleteAssets;

    /**
     * @var Array
     */
    private $defaultValues = [
        'Users' => [
            'new_with_id' => true,
            'user_name' => 'Test User',
        ],
        'Calls' => [
            'new_with_id' => true,
            'id' => 'testCall',
            'name' => 'Test Call',
        ],
        'Meetings' => [
            'new_with_id' => true,
            'id' => 'testMeeting',
            'name' => 'Test Meeting',
        ],
        'forms' => [
            'new_with_id' => true,
            'id' => 'testForm',
            'name' => 'Test Form',
        ],
        'CJ_Forms' => [
            'new_with_id' => true,
            'id' => 'testForm',
            'name' => 'Test Form',
        ],
        'CJ_WebHooks' => [
            'new_with_id' => true,
            'id' => 'testWebHook',
            'name' => 'Test WebHook',
        ],
        'Accounts' => [
            'new_with_id' => true,
            'id' => 'testAccount',
            'name' => 'Test Account',
        ],
        'DRI_Workflow_Templates' => [
            'new_with_id' => true,
            'id' => 'testTemplate1',
            'name' => 'Test Workflow Template',
            'available_modules' => '^Accounts^,^Cases^,^Opportunities^',
            'stage_numbering' => 1,
        ],
        'DRI_Workflow_Task_Templates' => [
            'new_with_id' => true,
            'id' => 'testTaskTemplate',
            'name' => 'Test Workflow Task Template',
            'available_modules' => '^Accounts^,^Cases^,^Opportunities^',
            'module' => 'DRI_Workflow_Task_Templates',
        ],
        'DRI_SubWorkflow_Templates' => [
            'new_with_id' => true,
            'id' => 'testSubWorkflowTemplate',
            'name' => 'Test Subworkflow Template',
        ],
        'DRI_Workflows' => [
            'new_with_id' => true,
            'id' => 'testJourney',
            'name' => 'Test Journey',
            'dri_workflow_template_id' => 'testTemplate',
            'account_id' => 'testAccount',
            'table_name' => 'dri_workflows',
            'available_modules' => '^Accounts^,^Cases^,^Opportunities^',
        ],
        'DRI_SubWorkflows' => [
            'new_with_id' => true,
            'id' => 'testStage',
            'name' => 'Test Subworkflow Template',
            'dri_workflow_id' => 'testJourney',
        ],
    ];

    /**
     * Create a bean for CJ related module
     *
     * @param String $module
     * @param Array $beanValues
     * @return SugarBean $bean
     */
    public function createBean($module, $beanValues = [])
    {
        $bean = BeanFactory::newBean($module);
        // set default values for specific bean
        foreach ($this->defaultValues[$module] as $property => $value) {
            $bean->$property = $value;
        }

        foreach ($beanValues as $property => $value) {
            $bean->$property = $value;
        }

        $bean->save();
        $this->addBeanToDeleteAssets($bean);

        return $bean;
    }

    /**
     * Set current user to mock user
     *
     * @param \User $mockUser
     */
    public function setCurrentUser($mockUser)
    {
        global $current_user;

        $this->currentUser = $current_user;
        $current_user = $mockUser;
    }

    /**
     * Tracks which beans were added so that they can be deleted later
     *
     * @param SugarBean $bean
     */
    public function addBeanToDeleteAssets($bean): void
    {
        $this->deleteAssets[$bean->getTableName()][] = $bean->id;
        if ($bean->module_name === 'DRI_Workflows') {
            $this->addStageAndActivitiesToDeleteAssets($bean);
        }
    }

    /**
     * Adds Subworkflow and Activities records to delete assets
     *
     * @param SugarBean $bean
     */
    public function addStageAndActivitiesToDeleteAssets($bean): void
    {
        $childActivityHelper = new ChildActivityHelper();
        $stages = $bean->getStages();
        foreach ($stages as $stage) {
            $this->deleteAssets[$stage->getTableName()][] = $stage->id;
            $activities = $stage->getActivities();
            foreach ($activities as $activity) {
                $this->deleteAssets[$activity->getTableName()][] = $activity->id;
                $childActivities = $childActivityHelper->getChildren($activity);
                foreach ($childActivities as $childActivity) {
                    $this->deleteAssets[$childActivity->getTableName()][] = $childActivity->id;
                }
            }
        }
    }

    /**
     * Release resources if any
     */
    public function tearDown(): void
    {
        if (!empty($this->deleteAssets)) {
            $this->cleanUp();
        }
        if ($this->currentUser) {
            global $current_user;

            $current_user = $this->currentUser;
            $this->currentUser = null;
        }
    }

    /**
     * CleanUp function to remove demo data
     *
     * @return void
     */
    private function cleanUp(): void
    {
        foreach ($this->deleteAssets as $table => $ids) {
            $qb = \DBManagerFactory::getInstance()->getConnection()->createQueryBuilder();
            $qb->delete($table)->where(
                $qb->expr()->in(
                    'id',
                    $qb->createPositionalParameter($ids, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                )
            );
            $qb->executeQuery();
        }
    }
}
