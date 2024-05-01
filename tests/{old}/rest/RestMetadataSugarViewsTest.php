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


class RestMetadataSugarViewsTest extends RestTestBase
{
    /**
     * @var mixed[]
     */
    public $oldFiles;
    /**
     * @var mixed
     */
    public $mobileAuthToken;
    /**
     * @var mixed
     */
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
    public function testMetadataSugarViews()
    {
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata?type_filter=views');

        $this->assertTrue(isset($restReply['reply']['views']['_hash']), 'SugarView hash is missing.');
    }

    /**
     * @group rest
     */
    public function testMetadataSugarViewsTemplates()
    {
        $filesToCheck = [
            'clients/base/views/address/editView.hbs',
            'clients/base/views/address/detailView.hbs',
            'custom/clients/base/views/address/editView.hbs',
            'custom/clients/base/views/address/detailView.hbs',
            'clients/mobile/views/address/editView.hbs',
            'clients/mobile/views/address/detailView.hbs',
            'clients/mobile/views/address/editView.hbs',
            'clients/mobile/views/address/detailView.hbs',
            'custom/clients/mobile/views/address/editView.hbs',
            'custom/clients/mobile/views/address/detailView.hbs',
            'custom/clients/portal/views/address/editView.hbs',
            'custom/clients/portal/views/address/detailView.hbs',
            'clients/portal/views/address/editView.hbs',
            'clients/portal/views/address/detailView.hbs',
        ];
        SugarTestHelper::saveFile($filesToCheck);

        $dirsToMake = [
            'clients/base/views/address',
            'custom/clients/base/views/address',
            'clients/mobile/views/address',
            'custom/clients/mobile/views/address',
            'clients/portal/views/address',
            'custom/clients/portal/views/address',
        ];

        foreach ($dirsToMake as $dir) {
            SugarAutoLoader::ensureDir($dir);
        }
        // Make sure we get it when we ask for mobile
        file_put_contents('clients/mobile/views/address/editView.hbs', 'MOBILE EDITVIEW');
        $this->clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->restCall('metadata/?type_filter=views&platform=mobile');
        $this->assertEquals('MOBILE EDITVIEW', $restReply['reply']['views']['address']['templates']['editView'], "Didn't get mobile code when that was the direct option");


        file_put_contents('clients/mobile/views/address/editView.hbs', 'MOBILE EDITVIEW');
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata/?type_filter=views&platform=mobile');
        $this->assertEquals('MOBILE EDITVIEW', $restReply['reply']['views']['address']['templates']['editView'], "Didn't get mobile code when that was the direct option");


        // Make sure we get it when we ask for mobile, even though there is base code there
        file_put_contents('clients/base/views/address/editView.hbs', 'BASE EDITVIEW');
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata/?type_filter=views&platform=mobile');
        $this->assertEquals('MOBILE EDITVIEW', $restReply['reply']['views']['address']['templates']['editView'], "Didn't get mobile code when base code was there.");


        // Make sure we get the base code when we ask for it.
        $this->clearMetadataCache();
        $this->authToken = $this->baseAuthToken;
        $restReply = $this->restCall('metadata/?type_filter=views&platform=base');
        $this->assertEquals('BASE EDITVIEW', $restReply['reply']['views']['address']['templates']['editView'], "Didn't get base code when it was the direct option");

        // Delete the mobile address and make sure it falls back to base
        unlink('clients/mobile/views/address/editView.hbs');
        $this->clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->restCall('metadata/?type_filter=views&platform=mobile');
        $this->assertEquals('BASE EDITVIEW', $restReply['reply']['views']['address']['templates']['editView'], "Didn't fall back to base code when mobile code wasn't there.");


        // Make sure the mobile code is loaded before the non-custom base code
        file_put_contents('custom/clients/mobile/views/address/editView.hbs', 'CUSTOM MOBILE EDITVIEW');
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata/?type_filter=views&platform=mobile');
        $this->assertEquals('CUSTOM MOBILE EDITVIEW', $restReply['reply']['views']['address']['templates']['editView'], "Didn't use the custom mobile code.");

        // Make sure custom base code works
        file_put_contents('custom/clients/base/views/address/editView.hbs', 'CUSTOM BASE EDITVIEW');
        $this->clearMetadataCache();
        $this->authToken = $this->baseAuthToken;
        $restReply = $this->restCall('metadata/?type_filter=views&platform=base');
        $this->assertEquals('CUSTOM BASE EDITVIEW', $restReply['reply']['views']['address']['templates']['editView'], "Didn't use the custom base code.");
    }
}
