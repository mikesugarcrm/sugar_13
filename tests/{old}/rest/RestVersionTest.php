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


class RestVersionTest extends RestTestBase
{
    /**
     * @group rest
     */
    public function testVersion()
    {
        // verify a run will work
        $restReply = $this->restCall('Accounts');
        $this->assertEquals($restReply['info']['http_code'], '200', "Incorrect HTTP Code, instead of 200 we receieved {$restReply['info']['http_code']}");

        // set the version lower than current
        $this->version = 5;

        $restReply = $this->restCall('Accounts');
        $this->assertEquals($restReply['info']['http_code'], '301', "Incorrect HTTP Code, instead of 301 we receieved {$restReply['info']['http_code']}");

        // set the version higher than current
        $this->version = 50;
        $restReply = $this->restCall('Accounts');
        $this->assertEquals($restReply['info']['http_code'], '301', "Incorrect HTTP Code, instead of 301 we receieved {$restReply['info']['http_code']}");
    }
}
