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


class RestLabelsTest extends RestTestBase
{
    /**
     * @group rest
     */
    public function testLabels()
    {
        $restReply = $this->restCall('metadata?type_filter=labels');
        $this->assertArrayHasKey('en_us', $restReply['reply']['labels']);
        $fileLoc = ltrim($GLOBALS['sugar_config']['site_url'], $restReply['reply']['labels']['en_us']);
        $en_us = json_decode(file_get_contents($restReply['reply']['labels']['en_us']), true);
        $this->assertNotEmpty($en_us['app_strings']['LBL_ADD'], "Could not find the label for the add button (LBL_ADD), probably didn't get the app strings (/metadata)");
        $this->assertNotEmpty($en_us['mod_strings']['Contacts']['LBL_ACCOUNT_NAME']);
    }

    /**
     * @group rest
     */
    public function testAppListLabels()
    {
        $restReply = $this->restCall('metadata?type_filter=labels');
        $this->assertArrayHasKey('en_us', $restReply['reply']['labels']);
        $fileLoc = ltrim($GLOBALS['sugar_config']['site_url'], $restReply['reply']['labels']['en_us']);
        $en_us = json_decode(file_get_contents($restReply['reply']['labels']['en_us']), true);

        $this->assertNotEmpty($en_us['app_list_strings']['checkbox_dom'], "Could not find the label for the checkbox dropdown, these don't look like app_list_strings to me (/metadata)");
        $this->assertNotEmpty($en_us['app_list_strings']['available_language_dom'], 'Could not find the list of available languages in appListStrings. (/metadata)');
    }
}
