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

class InternalPhpFunctionsDeprecationTest extends TestCase
{
    public function testModifierConstant()
    {
        define('TEST_1234ABC_CONSTANT', 'test_val');

        $content = $this->getProcessedTemplate('constant:{"TEST_1234ABC_CONSTANT"|constant}');

        $this->assertEquals('constant:test_val', $content);
    }

    public function testModifierRealpath()
    {
        $testPath = __DIR__ . '/../';
        $expectedPath = realpath($testPath);
        $content = $this->getProcessedTemplate('realpath:{$val|realpath}', ['val' => $testPath]);

        $this->assertEquals('realpath:' . $expectedPath, $content);
    }

    public function testModifierHtmlentities()
    {
        $html = '<test>&$@!</test>ÄÖÜ';
        $expectedHtml = htmlentities($html);
        $content = $this->getProcessedTemplate(
            'htmlentities:{$val|htmlentities:$smarty.const.ENT_QUOTES:"utf-8"}',
            ['val' => $html]
        );

        $this->assertEquals('htmlentities:' . $expectedHtml, $content);
    }

    public function testModifierSubstr()
    {
        $testString = '123456789';
        $offset = 3;
        $len = 4;
        $expectedString = substr($testString, $offset, $len);
        $content = $this->getProcessedTemplate(
            'substr:{$val|substr:$offset:$len}',
            ['val' => $testString, 'offset' => $offset, 'len' => $len]
        );

        $this->assertEquals('substr:' . $expectedString, $content);
    }

    public function testModifierUrl2html()
    {
        $testString = 'https://test.test';
        $expectedString = url2html($testString);
        $content = $this->getProcessedTemplate(
            'url2html:{$val|url2html}',
            ['val' => $testString]
        );

        $this->assertEquals('url2html:' . $expectedString, $content);
    }

    public function testModifierTrim()
    {
        $testString = '  test ';
        $expectedString = trim($testString);
        $content = $this->getProcessedTemplate(
            'trim:{$val|trim}',
            ['val' => $testString]
        );

        $this->assertEquals('trim:' . $expectedString, $content);
    }

    public function testModifierIntval()
    {
        $testString = ' 123a ';
        $expectedString = intval($testString);
        $content = $this->getProcessedTemplate(
            'intval:{$val|intval}',
            ['val' => $testString]
        );

        $this->assertEquals('intval:' . $expectedString, $content);
    }

    public function testModifierStrstr()
    {
        $testString = ' abcdefghijklmno';
        $needleString = 'def';
        $expectedString = strstr($testString, $needleString);
        $content = $this->getProcessedTemplate(
            'strstr:{$val|strstr:$needle}',
            ['val' => $testString, 'needle' => $needleString]
        );

        $this->assertEquals('strstr:' . $expectedString, $content);
    }

    public function testModifierMd5()
    {
        $testString = 'test';
        $expectedString = md5($testString);
        $content = $this->getProcessedTemplate(
            'md5:{$val|md5}',
            ['val' => $testString]
        );

        $this->assertEquals('md5:' . $expectedString, $content);
    }

    private function getProcessedTemplate(string $tpl, array $variables = []): string
    {
        $ss = new Sugar_Smarty();
        $ss->force_compile = true;
        foreach ($variables as $varName => $varValue) {
            $ss->assign($varName, $varValue);
        }
        ob_start();
        $ss->display('string:' . $tpl);

        $lastError = error_get_last();
        $deprecationErrorFound = false;
        if (strstr($lastError['file'] ?? '', 'smarty_internal_compile_private_modifier.php')) {
            error_clear_last();
            $deprecationErrorFound = true;
        }
        $this->assertFalse(
            $deprecationErrorFound,
            'An unexpected deprecation error appeared: ' . ($lastError['message'] ?? '')
        );

        return ob_get_clean();
    }
}
