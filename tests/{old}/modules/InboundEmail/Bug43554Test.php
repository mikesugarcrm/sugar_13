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
 * @ticket 43554
 */
class Bug43554Test extends TestCase
{
    private static $ie;
    private static $user;

    public static function setUpBeforeClass(): void
    {
        self::$user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = self::$user;

        self::$ie = new InboundEmail();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function getUrls()
    {
        return [
            ['http://localhost:8888/sugarent/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1'],
            ['http://localhost:8888/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1'],
            [to_html('http://localhost:8888/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1')],
            ['/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1'],
            ['index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1'],
            ['/?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1'],
            ['https://localhost/?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1'],
        ];
    }

    /**
     * @dataProvider getUrls
     * @param string $url
     */
    public function testEmailCleanup($url)
    {
        $data = "Test: <img src=\"$url\">";
        $res = str_replace('<img />', '', SugarCleaner::cleanHtml($data));
        $this->assertStringNotContainsString('<img', $res);
    }
}
