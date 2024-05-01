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
 * Bug #49772
 *
 * [IBM RTC 3001] XSS - Administration, Rename Modules, Singular Label
 * @ticket 49772
 * @author arymarchik@sugarcrm.com
 */
class Bug49772Test extends TestCase
{
    private $oldLabel = '';
    private $testLabel = 'LBL_ACCOUNT_NAME';
    private $testModule = 'Contacts';
    private $lang = 'en_us';

    /**
     * Generating new label with HTML tags
     * @group 43069
     */
    public function testLabelSaving()
    {
        $mod_strings = return_module_language($this->lang, $this->testModule);
        $this->oldLabel = $mod_strings[$this->testLabel];
        $pref = '<img alt="<script>" src="www.test.com/img.png" ="alert(7001)" width="1" height="1"/>';
        $prepared_pref = to_html(strip_tags(from_html($pref)));
        $new_label = $prepared_pref . ' ' . $this->oldLabel;

        // save the new label to the language file
        ParserLabel::addLabels($this->lang, [$this->testLabel => $new_label], $this->testModule);

        // read the language file to get the new value
        include "custom/modules/{$this->testModule}/Ext/Language/{$this->lang}.lang.ext.php";

        $this->assertEquals($new_label, $mod_strings[$this->testLabel]);
        $this->assertNotEquals($pref . ' ' . $this->oldLabel, $mod_strings[$this->testLabel]);
    }

    protected function tearDown(): void
    {
        ParserLabel::addLabels($this->lang, [$this->testLabel => $this->oldLabel], $this->testModule);
    }
}
