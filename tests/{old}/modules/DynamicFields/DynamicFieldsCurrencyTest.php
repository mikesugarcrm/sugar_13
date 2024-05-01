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
 * @group DynamicFieldsCurrencyTests
 */
class DynamicFieldsCurrencyTest extends TestCase
{
    private $moduleName = 'Accounts';
    private $originalDbType = '';
    private $field;

    protected function setUp(): void
    {
        // Set Original Global dbType
        $this->originalDbType = $GLOBALS['db']->dbType;

        $this->field = get_widget('currency');
        $this->field->id = $this->moduleName . 'foofighter_c';
        $this->field->name = 'foofighter_c';
        $this->field->comments = null;
        $this->field->help = null;
        $this->field->custom_module = $this->moduleName;
        $this->field->type = 'currency';
        $this->field->len = 18;
        $this->field->precision = 6;
        $this->field->required = 0;
        $this->field->default_value = null;
        $this->field->date_modified = '2010-12-22 01:01:01';
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

    protected function tearDown(): void
    {
        // Reset Original Global dbType
        $GLOBALS['db']->dbType = $this->originalDbType;
    }

    public function testCurrencyDbType()
    {
        $type = 'decimal';
        if ($GLOBALS['db']->dbType == 'oci8') {
            $type = 'number';
        }
        $this->field->len = null;
        $dbTypeString = $this->field->get_db_type();
        $this->assertMatchesRegularExpression('/' . $type . ' *\(/', $dbTypeString);
        $dbTypeString = $this->field->get_db_type();
        $this->field->len = 20;
        $this->assertMatchesRegularExpression('/' . $type . ' *\(/', $dbTypeString);
    }
}
