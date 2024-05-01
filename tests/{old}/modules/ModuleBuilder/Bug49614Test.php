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

class Bug49614Test extends TestCase
{
    private $package;
    private $orgSoftFail;

    protected function setUp(): void
    {
        $this->package = new MBPackage('SugarTestPackage');
        $GLOBALS['sugar_config']['validation']['soft_fail'] = false;
    }

    protected function tearDown(): void
    {
        unset($this->package);
    }

    public function testPopulateFromPostKeyValueWithInvalidChars()
    {
        $_REQUEST = [
            'description' => '',
            'author' => 'Sugar CRM',
            'key' => ' keys$$',
            'readme' => '',
        ];

        $this->expectException(\Sugarcrm\Sugarcrm\Security\InputValidation\Exception\ViolationException::class);
        $this->package->populateFromPost();
    }

    public function testPopulateFromPostKeyValueWithoutSpaces()
    {
        $_REQUEST = [
            'description' => '',
            'author' => 'Sugar CRM',
            'key' => 'key',
            'readme' => '',
        ];

        $this->package->populateFromPost();
        $this->assertEquals('key', $this->package->key);
    }
}
