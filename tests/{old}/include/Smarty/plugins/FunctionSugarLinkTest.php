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

require_once 'include/SugarSmarty/plugins/function.sugar_link.php';

class FunctionSugarLinkTest extends TestCase
{
    /**
     * @var Sugar_Smarty
     */
    private $smarty;

    protected function setUp(): void
    {
        $this->smarty = new Sugar_Smarty();
    }

    public function testReturnModuleLinkOnly()
    {
        $this->assertStringContainsString(
            'index.php?module=Dog&action=index',
            smarty_function_sugar_link([
                'module' => 'Dog',
                'link_only' => '1',
            ], $this->smarty)
        );
    }

    public function testReturnModuleLinkWithAction()
    {
        $this->assertStringContainsString(
            'index.php?module=Dog&action=cat',
            smarty_function_sugar_link([
                'module' => 'Dog',
                'action' => 'cat',
                'link_only' => '1',
            ], $this->smarty)
        );
    }

    public function testReturnModuleLinkWithActionAndExtraParams()
    {
        $this->assertStringContainsString(
            'index.php?module=Dog&action=cat&foo=bar',
            smarty_function_sugar_link([
                'module' => 'Dog',
                'action' => 'cat',
                'extraparams' => 'foo=bar',
                'link_only' => '1',
            ], $this->smarty)
        );
    }

    /**
     * @ticket 33909
     */
    public function testReturnLinkWhenPassingData()
    {
        $data = [
            '63edeacd-6ba5-b658-5e2a-4af9a5d682be',
            'http://localhost',
            'all',
            'iFrames',
            'Foo',
        ];

        $this->assertStringContainsString(
            'index.php?module=iFrames&action=index&record=63edeacd-6ba5-b658-5e2a-4af9a5d682be&tab=true',
            smarty_function_sugar_link([
                'module' => 'Dog',
                'data' => $data,
                'link_only' => '1',
            ], $this->smarty)
        );
    }

    public function testCreatingFullLink()
    {
        $this->assertStringContainsString(
            '<a href="index.php?module=Dog&action=cat" id="foo1" class="foo4" style="color:red;"'
            . ' scope="row" title="foo2" module="Dog">foo3</a>',
            smarty_function_sugar_link([
                'module' => 'Dog',
                'action' => 'cat',
                'id' => 'foo1',
                'class' => 'foo4',
                'style' => 'color:red;',
                'title' => 'foo2',
                'accesskey' => 'B',
                'options' => 'scope="row"',
                'label' => 'foo3',
            ], $this->smarty)
        );
    }
}
