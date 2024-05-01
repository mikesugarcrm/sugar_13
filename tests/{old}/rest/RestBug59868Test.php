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

/**
 * Bug 59868 - clients dont agree on how to handle quoted int app string keys
 */
class RestBug59868Test extends RestTestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear the metadata cache to ensure a fresh load of data
        $this->clearMetadataCache();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @group Bug59868
     * @group rest
     */
    public function testAppListStringsConvertedCorrectlyInMetadataRequest()
    {
        $this->clearMetadataCache();
        $reply = $this->restCall('metadata');

        $json = file_get_contents($GLOBALS['sugar_config']['site_url'] . '/' . $reply['reply']['labels']['en_us']);

        $object = json_decode($json);
        $this->assertTrue(is_object($object->app_list_strings->Elastic_boost_options), 'App list string wasnt cast to object');
        $this->assertTrue(isset($object->app_list_strings->industry_dom->_empty_), 'App list string wasnt left as an array');
    }
}
