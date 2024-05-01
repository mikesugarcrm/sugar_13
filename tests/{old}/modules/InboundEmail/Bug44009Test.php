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
 * @ticket 44009
 */
class Bug44009Test extends TestCase
{
    protected $ie = null;

    protected function setUp(): void
    {
        $this->ie = new InboundEmail();
    }

    public function getData()
    {
        return [
            ['test<b>test</b>', 'test<b>test</b>'],
            ['<html>test<b>test</b></html>', 'test<b>test</b>'],
            ['<html><head></head><body>test<b>test</b></body></html>', 'test<b>test</b>'],
            ['<html><head><style>test</style></head><body>test<b>test</b></body></html>', 'test<b>test</b>'],
            ["<html><head></head><body><script language=\"javascript\">alert('test!');</script>test<b>test</b></body></html>", 'test<b>test</b>'],
            ['<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /><title>test 12345</title></head><body><p>test<b>test</b></body></html>', '<p>test<b>test</b></p>'],
        ];
    }

    /**
     * @dataProvider getData
     * @param string $url
     */
    public function testEmailCleanup($data, $res)
    {
        $this->assertEquals($res, SugarCleaner::cleanHtml($data));
    }
}
