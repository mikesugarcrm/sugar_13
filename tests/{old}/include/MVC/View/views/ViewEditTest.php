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

class ViewEditTest extends TestCase
{
    public function testConstructor()
    {
        $view = new ViewEdit();
        $this->assertEquals('edit', $view->type);
    }

    public function testSubclasses()
    {
        $view = new MockViewEditDirect();
        $this->assertEquals('edit', $view->type);

        $view = new MockViewEditConstructor();
        $this->assertEquals('edit', $view->type);
    }
}

class MockViewEditDirect extends ViewEdit
{
}

class MockViewEditConstructor extends ViewEdit
{
}
