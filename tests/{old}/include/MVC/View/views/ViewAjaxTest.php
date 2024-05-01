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

class ViewAjaxTest extends TestCase
{
    public function testConstructor()
    {
        $view = new ViewAjax();

        $this->assertFalse($view->options['show_title']);
        $this->assertFalse($view->options['show_header']);
        $this->assertFalse($view->options['show_javascript']);
        $this->assertFalse($view->options['show_subpanels']);
        $this->assertFalse($view->options['show_search']);
    }
}
