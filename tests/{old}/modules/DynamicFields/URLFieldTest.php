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

/**
 * Test cases for URL Field
 */
class URLFieldTest extends TestCase
{
    /**
     * @var mixed
     */
    public $field;
    private $moduleName = 'Accounts';

    protected function setUp(): void
    {
        $this->field = get_widget('url');
        $this->field->id = $this->moduleName . 'foo_c';
        $this->field->name = 'foo_c';
        $this->field->comments = null;
        $this->field->help = null;
        $this->field->custom_module = $this->moduleName;
        $this->field->type = 'url';
        $this->field->len = 255;
        $this->field->required = 0;
        $this->field->default_value = null;
        $this->field->date_modified = '2009-09-14 02:23:23';
        $this->field->deleted = 0;
        $this->field->audited = 0;
        $this->field->massupdate = 0;
        $this->field->duplicate_merge = 0;
        $this->field->reportable = 1;
        $this->field->importable = 'true';
        $this->field->ext1 = null;
        $this->field->ext2 = null;
        $this->field->ext3 = null;
        $this->field->ext4 = null;
    }

    public function testURLFieldsInVardef()
    {
        $this->field->ext4 = '_self';
        $vardef = $this->field->get_field_def();
        $this->assertEquals($vardef['link_target'], '_self');
    }
}
