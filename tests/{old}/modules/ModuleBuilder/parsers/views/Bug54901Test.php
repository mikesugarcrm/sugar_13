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
 * Accessor class, in the event the parsers public properties go protected, which
 * they are slated to do.
 */
class Bug54901TestListParser extends SidecarPortalListLayoutMetaDataParser
{
    public function changeFieldType($field, $type)
    {
        $this->_fielddefs[$field]['type'] = $type;
    }
}

class Bug54901TestGridParser extends SidecarGridLayoutMetaDataParser
{
    public function changeFieldType($field, $type)
    {
        $this->_fielddefs[$field]['type'] = $type;
    }

    public function isAvailableFieldName($name, $fields)
    {
        foreach ($fields as $field) {
            if (isset($field['name']) && $field['name'] == $name) {
                return true;
            }
        }

        return false;
    }
}

class Bug54901Test extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testPortalListLayoutDoesNotIncludeInvalidFields()
    {
        // Build the parser
        $list = new Bug54901TestListParser(MB_PORTALLISTVIEW, 'Cases', '', MB_PORTAL);

        // Massage the field defs
        $list->changeFieldType('resolution', 'iframe');
        $list->changeFieldType('portal_viewable', 'relate');

        // Get our fields
        $fields = $list->getAvailableFields();

        // Run the assertions
        $this->assertArrayNotHasKey('resolution', $fields, 'The resolution field was not excluded');
        $this->assertArrayHasKey('portal_viewable', $fields, 'portal_viewable was excluded but a relate type should not be excluded');
        $this->assertArrayHasKey('description', $fields, 'Description is showing as not available');
    }

    public function testPortalRecordLayoutDoesNotIncludeInvalidFields()
    {
        // Build the parser
        $grid = new Bug54901TestGridParser(MB_PORTALRECORDVIEW, 'Cases', '', MB_PORTAL);

        // Massage the field defs
        $grid->changeFieldType('resolution', 'parent');
        $grid->changeFieldType('work_log', 'relate');

        // Get our fields
        $fields = $grid->getAvailableFields();

        // Run the assertions
        $available = $grid->isAvailableFieldName('resolution', $fields);
        $this->assertFalse($available, 'The resolution field was not excluded');

        $available = $grid->isAvailableFieldName('work_log', $fields);
        $this->assertFalse($available, 'Work Log was not excluded');
    }
}
