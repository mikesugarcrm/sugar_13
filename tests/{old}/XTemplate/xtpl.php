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

class XTemplateTest extends TestCase
{
    protected $files;

    protected function setUp(): void
    {
        $file1 = 'upload/' . uniqid() . '.xtpl';
        $fp = fopen($file1, 'w');
        fputs($fp, 'xtemplate recursive include works');
        fclose($fp);

        $file2 = 'upload/' . uniqid() . '.xtpl';
        $fp = fopen($file2, 'w');
        fputs($fp, '<!-- BEGIN: main -->{FILE "' . $file1 . '"}<!-- END: main -->');
        fclose($fp);

        $this->files['primary'] = $file2;
        $this->files['secondary'] = $file1;
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
    }

    public function testRecursiveParse()
    {
        $xtpl = new XTemplate($this->files['primary']);
        $xtpl->rparse('main.inc');
        $xtpl->parse('main');

        $this->assertEquals('xtemplate recursive include works', $xtpl->text('main'));
    }
}
