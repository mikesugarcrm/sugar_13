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

class Bug46152_P3Test extends TestCase
{
    private $dynamicField;
    private $module = 'Notes';
    private $relatedModule = 'Opportunities';
    private $idLabelName;

    /**
     * Test saving Label of id field.
     *
     * @group 46152
     */
    public function testSaveIdLabel()
    {
        $field = new TemplateRelatedTextFieldMockB46152_P3();
        $field->ext2 = $this->relatedModule;
        $field->label_value = 'TestField' . time();

        $this->idLabelName = 'LBL_TEST_FIELD_ID_LABEL_B46152';

        $field->saveIdLabel($this->idLabelName, $this->dynamicField);

        SugarTestHelper::setUp('mod_strings', [$this->module]);

        $this->assertArrayHasKey($this->idLabelName, $GLOBALS['mod_strings']);
    }


    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);
        SugarTestHelper::setUp('current_user');

        $this->dynamicField = new DynamicField($this->module);
        $this->dynamicField->setup(BeanFactory::newBean($this->module));
    }

    protected function tearDown(): void
    {
        ParserLabel::removeLabel(
            $GLOBALS['current_language'],
            $this->idLabelName,
            $GLOBALS['mod_strings'][$this->idLabelName],
            $this->module
        );

        SugarTestHelper::tearDown();
    }
}

class TemplateRelatedTextFieldMockB46152_P3 extends TemplateRelatedTextField
{
    public function saveIdLabel($idLabelName, $df)
    {
        parent::saveIdLabel($idLabelName, $df);
    }
}
