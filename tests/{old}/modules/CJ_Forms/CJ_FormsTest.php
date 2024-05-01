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
 * Class CJ_FormsTest
 * @coversDefaultClass \CJ_Forms
 */
class CJ_FormsTest extends TestCase
{
    /**
     * @var \SugarBean
     */
    private $form;

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
        $this->form = $this->createPartialMock(
            CJ_Form::class,
            ['save']
        );
        $this->cjTestHelper = new SugarTestCJHelper();
    }

    /**
     * @covers ::bean_implements
     */
    public function testBean_implements(): void
    {
        $this->assertEquals($this->form->bean_implements('ACL'), true);
        $this->assertEquals($this->form->bean_implements(''), false);
    }

    /**
     * @covers ::getRelationshipEnumValues
     */
    public function testGetRelationshipEnumValues(): void
    {
        $response = CJ_Form::getRelationshipEnumValues();

        $this->assertEquals($response['Calls:accounts'], 'Calls › Account (accounts)');
        $this->assertEquals($response['Meetings:contacts'], 'Meetings › Contacts (contacts)');
        $this->assertEquals($response['Tasks:dri_workflow_link'], 'Tasks › Smart Guide (dri_workflow_link)');
    }

    /**
     * @covers ::addValuesForModule
     */
    public function testAddValuesForModule(): void
    {
        $response = CJ_Form::addValuesForModule('Tasks', 'Tasks', 'Tasks', null);

        $this->assertEquals($response['Tasks:messages'], 'Tasks › Messages (messages)');
        $this->assertEquals($response['Tasks:notes'], 'Tasks › Notes (notes)');
    }

    public function providerTestSetTargetValues()
    {
        $data = [
            ['id' => '1', 'actualFieldName' => 'description', 'type' => 'varchar', 'value' => 'Test'],
            ['id' => '2', 'actualFieldName' => 'available_modules', 'type' => 'varchar', 'value' => ['Contacts']],
            ['id' => '3', 'actualFieldName' => 'dri_subworkflows', 'type' => 'relate', 'value' => '', 'actual_id_name' => 'dri_subworkflow_id', 'id_value' => 'Test_Stage'],
            ['id' => '4', 'actualFieldName' => 'price', 'type' => 'currency', 'value' => '', 'id_name' => 'currency_id', 'id_value' => 'Test_Price'],
            ['id' => '5', 'actualFieldName' => 'date_entered', 'type' => 'date', 'value' => '2022-11-29', 'childFieldsData' => ['selective_date' => ['value' => 'relative'], 'int_date' => ['value' => '2'], 'relative_date' => ['value' => '2022-11-22']]],
        ];
        return [
            [json_encode($data, JSON_UNESCAPED_SLASHES)],
        ];
    }

    /**
     * @covers ::setTargetValues
     * @dataProvider providerTestSetTargetValues
     */
    public function testSetTargetValues($populateField): void
    {
        $testForm = $this->cjTestHelper->createBean('CJ_Forms', ['populate_fields' => $populateField]);
        $testActivity = $this->cjTestHelper->createBean('Calls');

        CJ_Form::setTargetValues($testActivity, $testForm);

        $this->assertEquals($testActivity->description, 'Test');
        $this->assertEquals($testActivity->dri_subworkflow_id, 'Test_Stage');
        $this->assertEquals($testActivity->currency_id, 'Test_Price');
        $this->assertEquals($testActivity->date_entered, '2022-11-22');
        $this->assertEquals($testActivity->available_modules, '^Contacts^');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        $this->cjTestHelper->tearDown();
    }
}
