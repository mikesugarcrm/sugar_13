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

require_once 'modules/DynamicFields/FieldCases.php';

class TemplateActionButtonTest extends TestCase
{
    /**
     * @var mixed
     */
    public $field;
    public $custom_module;
    private $moduleName = 'Accounts';

    protected function setUp(): void
    {
        $this->field = get_widget('actionbutton');
        $this->field->id = $this->moduleName . 'test_actionbutton_c';
        $this->field->name = 'test_actionbutton_c';
        $this->field->vname = 'LBL_TEST_ACTIONBUTTON';
        $this->field->comments = null;
        $this->field->help = null;
        $this->custom_module = $this->moduleName;
        $this->field->type = 'actionbutton';
        $this->field->len = 50;
        $this->field->required = 0;
        $this->field->default_value = null;
        $this->field->date_modified = '2021-09-14 09:25:25';
        $this->field->deleted = 0;
        $this->field->audited = 0;
        $this->field->massupdate = 0;
        $this->field->duplicate_merge = 0;
        $this->field->reportable = 1;
        $this->field->importable = 'false';
        $this->field->ext1 = null;
        $this->field->ext2 = null;
        $this->field->ext3 = null;
        $this->field->ext4 = null;
    }

    public function providerTestActionButtonGetFieldDef()
    {
        return [
            [
                [
                    'loaded' => '{"settings":{"type":"button","showFieldLabel":false,"showInRecordHeader":true,"hideOnEdit":false,"size":"default"},"buttons":{"1558bc78-b228-4ced-9af7-ad546ab8cd98":{"active":true,"buttonId":"1558bc78-b228-4ced-9af7-ad546ab8cd98","orderNumber":0,"properties":{"label":"Create Contact","description":"","showLabel":true,"showIcon":true,"colorScheme":"green","icon":"sicon-plus","isDependent":false,"formula":""},"actions":{"fce99fd9-eeb8-44e9-9cc3-ebc2119a768c":{"actionType":"createRecord","orderNumber":0,"properties":{"attributes":{},"parentAttributes":{},"module":"Contacts","link":"contacts","mustLinkRecord":true,"copyFromParent":false,"autoCreate":false}}}}}}',
                    'saved'  => 'jZHNTgMhFIVfxbCeSWztzzA7U21s0qhRX4CfOy2RAQIXTW367gIztYkbnQ3hwJxzz8eRBEBUZhdIeyR4cEBawiOiNaQiYW8/1wq03DIOmrQd0wEGeWNeQFgvH4BJ8KRFH9PJXkl4MvdS4eWy+sqeEjoWNZJTNdqXwMl83nCxbGo+nTb1TICsKeuWNZPz2YLxRkja5HtMoPqAc8pgsJHJ9l8GFUmDgn+MPc+jXlfEeevAo4IyhR7akZUHhnC1sgZTYPpNQhBeOVSJRktGICOLYZSCQuTjYS+stv5V7KHPpXceIHNU5UZCkdba6RiyFu7AgZFgLqw66/uoWc5KnHLpkVMngNJO0hqAp5ozoDUV4qYGLqaTCWXLRSPOnKx5G55RlDrDM/3JgCF6lcCWXQp3zKfJbn+pvZVRZ++RUS6ilXnPaReljwG3SR2jf9C4w9rb/rk4/3RmEe0AfpRO5fsG',
                ],
            ],
        ];
    }

    /**
     * @covers ::get_field_def
     * @dataProvider providerTestActionButtonGetFieldDef
     */
    public function testActionButtonGetFieldDef(array $ext4Data)
    {
        $this->field->ext4 = $ext4Data['saved'];

        $this->field->get_field_def();

        $this->assertEquals(
            $ext4Data['loaded'],
            $this->field->ext4,
            'The loaded ext4 value does not match the saved one.'
        );
    }
}
