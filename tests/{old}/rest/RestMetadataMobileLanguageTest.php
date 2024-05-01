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


class RestMetadataMobileLanguageTest extends RestTestBase
{
    /**
     * @group rest
     */
    public function testMobileMetadataLanguageFiles()
    {
        $restReply = $this->restCall('metadata?platform=mobile');
        $labels = $restReply['reply']['labels'];

        unset($labels['_hash']);
        $default = $labels['default'];
        unset($labels['default']);

        $output = [];

        foreach ($labels as $lang => $location) {
            $lang_file = json_decode(file_get_contents($GLOBALS['sugar_config']['site_url'] . '/' . $location));
            $this->assertNotEmpty($lang_file, 'Language File is empty');
        }
    }
}
