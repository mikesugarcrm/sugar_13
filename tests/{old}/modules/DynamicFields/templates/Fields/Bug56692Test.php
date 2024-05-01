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
 * Bug #56692
 *
 * Module Builder | Editing Stock Fields Causes SQL Errors When Deploying Custom Modules
 * @ticket 56692
 */
class Bug56692Test extends TestCase
{
    /**
     * Test that field with type 'link' has source 'non-db', but not 'custom_fields'
     *
     * @group 56692
     * @return void
     */
    public function testDisplayFields()
    {
        $field = get_widget('link');
        $vardefs = $field->get_field_def();

        $this->assertEquals('non-db', $vardefs['source']);
    }
}
