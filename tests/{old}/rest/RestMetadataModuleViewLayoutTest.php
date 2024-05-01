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


class RestMetadataModuleViewLayoutTest extends RestTestBase
{
    /**
     * @var mixed[]
     */
    public $oldFiles;
    /**
     * @var mixed
     */
    public $mobileAuthToken;
    public $baseAuthToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oldFiles = [];

        $this->restLogin('', '', 'mobile');
        $this->mobileAuthToken = $this->authToken;
        $this->restLogin('', '', 'base');
        $this->baseAuthToken = $this->authToken;
    }

    /**
     * @group rest
     */
    public function testMetadataSugarFields()
    {
        $this->clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->restCall('metadata?type_filter=modules');

        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['views']), 'No views for the cases module');
    }

    /**
     * @group rest
     */
    public function testMetadataModuleLayout()
    {
        $filesToCheck = [
            'modules/Cases/clients/mobile/layouts/edit/edit.php',
            'custom/modules/Cases/clients/mobile/layouts/edit/edit.php',
        ];
        SugarTestHelper::saveFile($filesToCheck);

        $dirsToMake = [
            'modules/Cases/clients/mobile/layouts/edit',
            'custom/modules/Cases/clients/mobile/layouts/edit',
        ];

        foreach ($dirsToMake as $dir) {
            SugarAutoLoader::ensureDir($dir);
        }

        // Make sure we get it when we ask for mobile
        file_put_contents(
            $filesToCheck[0],
            '<' . "?php\n\$viewdefs['Cases']['mobile']['layout']['edit'] = array('unit_test'=>'Standard Dir');\n"
        );
        $this->clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir', $restReply['reply']['modules']['Cases']['layouts']['edit']['meta']['unit_test'], "Didn't get the mobile layout");

        // Make sure we get the custom file
        file_put_contents(
            $filesToCheck[1],
            '<' . "?php\n\$viewdefs['Cases']['mobile']['layout']['edit'] = array('unit_test'=>'Custom Dir');\n"
        );
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Custom Dir', $restReply['reply']['modules']['Cases']['layouts']['edit']['meta']['unit_test'], "Didn't get the custom mobile layout");

        // Make sure it flops back to the standard file
        unlink($filesToCheck[1]);
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir', $restReply['reply']['modules']['Cases']['layouts']['edit']['meta']['unit_test'], "Didn't get the mobile layout");
    }

    /**
     * @group rest
     */
    public function testMetadataSubPanels()
    {
        $this->clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->restCall('metadata?type_filter=modules');
        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['subpanels']), 'No subpanels for the cases module');
    }

    /**
     * @group rest
     */
    public function testMetadataFTS()
    {
        $this->clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->restCall('metadata?typeFilter=modules');
        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['ftsEnabled']), 'No ftsEnabled for the cases module');
    }

    /**
     * @group rest
     */
    public function testMetadataFavorites()
    {
        $this->clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->restCall('metadata?typeFilter=modules');
        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['favoritesEnabled']), 'No favoritesEnabled for the cases module');
    }

    /**
     * @group rest
     */
    public function testMetadataModuleViews()
    {
        $filesToCheck = [
            'modules/Cases/clients/mobile/views/edit/edit.php',
            'custom/modules/Cases/clients/mobile/views/edit/edit.php',
        ];
        SugarTestHelper::saveFile($filesToCheck);

        $dirsToMake = [
            'modules/Cases/clients/mobile/views/edit',
            'custom/modules/Cases/clients/mobile/views/edit',
        ];

        foreach ($dirsToMake as $dir) {
            SugarAutoLoader::ensureDir($dir);
        }

        // Make sure we get it when we ask for mobile
        file_put_contents(
            $filesToCheck[0],
            '<' . "?php\n\$viewdefs['Cases']['mobile']['view']['edit'] = array('unit_test'=>'Standard Dir');\n"
        );
        $this->clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir', $restReply['reply']['modules']['Cases']['views']['edit']['meta']['unit_test'], "Didn't get the mobile view");

        // Make sure we get the custom file
        file_put_contents(
            $filesToCheck[1],
            '<' . "?php\n\$viewdefs['Cases']['mobile']['view']['edit'] = array('unit_test'=>'Custom Dir');\n"
        );
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Custom Dir', $restReply['reply']['modules']['Cases']['views']['edit']['meta']['unit_test'], "Didn't get the custom mobile view");

        // Make sure it flops back to the standard file
        unlink($filesToCheck[1]);
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir', $restReply['reply']['modules']['Cases']['views']['edit']['meta']['unit_test'], "Didn't get the mobile view");
    }

    /**
     * Test addresses a case related to the metadata location move that caused
     * metadatamanager to not roll up to sugar objects properly
     *
     * @group rest
     */
    public function testMobileMetaDataRollsUp()
    {
        $this->authToken = $this->mobileAuthToken;
        $reply = $this->restCall('metadata?typeFilter=modules&moduleFilter=Contacts');
        $this->assertNotEmpty($reply['reply']['modules']['Contacts']['views']['list']['meta'], 'Contacts list view metadata was not fetched from SugarObjects');
    }
}
