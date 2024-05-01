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


class RestMetadataViewDefsTest extends RestTestPortalBase
{
    public $testMetaDataFiles = [
        'contacts' => 'custom/modules/Contacts/clients/portal/layouts/banana/banana.php',
        'cases' => 'modules/Cases/clients/portal/views/ghostrider/ghostrider.php',
    ];

    protected function tearDown(): void
    {
        foreach ($this->testMetaDataFiles as $file) {
            if (file_exists($file)) {
                // Ignore the warning on this, the file stat cache causes the file_exist to trigger even when it's not really there
                @unlink($file);

                // Remove the stray directory since metadata manager will pick it up
                $dirname = dirname($file);
                rmdir($dirname);
            }
        }
        parent::tearDown();
    }


    /**
     * @group rest
     */
    public function testDefaultPortalLayoutMetaData()
    {
        // FIXME TY-1298: investigate why this test fails
        $restReply = $this->restCall('metadata?type_filter=modules&module_filter=Contacts');
        // Hash should always be set
        $this->assertTrue(isset($restReply['reply']['modules']['Contacts']['layouts']['_hash']), 'Portal layouts missing hash empty');
        unset($restReply['reply']['modules']['Contacts']['layouts']['_hash']);

        // Now the layouts should be empty
        $this->assertTrue(empty($restReply['reply']['modules']['Contacts']['layouts']), 'Portal layouts are not empty');
    }

    /**
     * @group rest
     */
    public function testDefaultPortalViewMetaData()
    {
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata?type_filter=modules&module_filter=Cases');
        $this->assertTrue(empty($restReply['reply']['modules']['Cases']['views']['ghostrider']), 'Test file found unexpectedly');
    }

    /**
     * @group rest
     */
    public function testAdditionalPortalLayoutMetaData()
    {
        // FIXME TY-1298: investigate why this test fails
        SugarAutoLoader::ensureDir(dirname($this->testMetaDataFiles['contacts']));
        file_put_contents(
            $this->testMetaDataFiles['contacts'],
            "<?php\n\$viewdefs['Contacts']['portal']['layout']['banana'] = array('yummy' => 'Banana Split');"
        );

        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata?type_filter=modules&module_filter=Contacts');
        $this->assertEquals('Banana Split', $restReply['reply']['modules']['Contacts']['layouts']['banana']['meta']['yummy'], 'Failed to retrieve all layout metadata');
    }

    /**
     * @group rest
     */
    public function testAdditionalPortalViewMetaData()
    {
        // FIXME TY-1298: investigate why this test fails
        SugarAutoLoader::ensureDir(dirname($this->testMetaDataFiles['cases']));
        file_put_contents(
            $this->testMetaDataFiles['cases'],
            "<?php\n\$viewdefs['Cases']['portal']['view']['ghostrider'] = array('pattern' => 'Full');"
        );

        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Full', $restReply['reply']['modules']['Cases']['views']['ghostrider']['meta']['pattern'], 'Failed to retrieve all view metadata');
    }

    /**
     * @group rest
     */
    public function testMetadataCacheBuild()
    {
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata/public?type_filter=config&platform=portal');
        $this->assertArrayHasKey('_hash', $restReply['reply'], 'Did not have a _hash on the first run');

        $restReply = $this->restCall('metadata/public?type_filter=config&platform=portal');
        $this->assertArrayHasKey('_hash', $restReply['reply'], 'Did not have a _hash on the second run');
    }

}
