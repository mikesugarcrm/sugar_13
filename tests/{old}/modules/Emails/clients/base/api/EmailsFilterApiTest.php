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
 * @coversDefaultClass EmailsFilterApi
 * @group api
 * @group email
 */
class EmailsFilterApiTest extends TestCase
{
    public function testRegisterApiRest()
    {
        $api = new EmailsFilterApi();
        $endpoints = $api->registerApiRest();

        $path = implode('/', $endpoints['filterModuleGet']['path']);
        $this->assertEquals('Emails/filter', $path);
        $this->assertEquals(
            'modules/Emails/clients/base/api/help/emails_filter_get_help.html',
            $endpoints['filterModuleGet']['longHelp']
        );

        $path = implode('/', $endpoints['filterModuleAll']['path']);
        $this->assertEquals('Emails', $path);
        $this->assertEquals(
            'modules/Emails/clients/base/api/help/emails_filter_get_help.html',
            $endpoints['filterModuleAll']['longHelp']
        );

        $path = implode('/', $endpoints['filterModuleAllCount']['path']);
        $this->assertEquals('Emails/count', $path);
        $this->assertEquals(
            'modules/Emails/clients/base/api/help/emails_filter_get_help.html',
            $endpoints['filterModuleAllCount']['longHelp']
        );

        $path = implode('/', $endpoints['filterModulePost']['path']);
        $this->assertEquals('Emails/filter', $path);

        $path = implode('/', $endpoints['filterModulePostCount']['path']);
        $this->assertEquals('Emails/filter/count', $path);

        $path = implode('/', $endpoints['filterModuleCount']['path']);
        $this->assertEquals('Emails/filter/count', $path);
    }
}
