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


class RestMetadataViewTemplatesTest extends RestTestBase
{
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

        $this->restLogin('', '', 'mobile');
        $this->mobileAuthToken = $this->authToken;
        $this->restLogin('', '', 'base');
        $this->baseAuthToken = $this->authToken;
    }

    /**
     * @group rest
     */
    public function testMetadataViewTemplates()
    {
        $restReply = $this->restCall('metadata?type_filter=views');

        $this->assertTrue(isset($restReply['reply']['views']['_hash']), 'Views hash is missing.');
    }

    /**
     * @group rest
     */
    public function testMetadataViewTemplatesHbs()
    {
        $filesToCheck = [
            'base' => [
                'clients/base/views/edit/edit.hbs',
                'custom/clients/base/views/edit/edit.hbs',
            ],
            'mobile' => [
                'clients/mobile/views/edit/edit.hbs',
                'custom/clients/mobile/views/edit/edit.hbs',
            ],
        ];
        SugarTestHelper::saveFile($filesToCheck['base']);
        SugarTestHelper::saveFile($filesToCheck['mobile']);

        $dirsToMake = [
            'base' => [
                'clients/base/views/edit',
                'custom/clients/base/views/edit',
            ],
            'mobile' => [
                'clients/mobile/views/edit',
                'custom/clients/mobile/views/edit',
            ],
        ];

        foreach ($dirsToMake as $client => $dirs) {
            foreach ($dirs as $dir) {
                SugarAutoLoader::ensureDir($dir);
            }
        }

        // Make sure we get it when we ask for mobile
        file_put_contents($filesToCheck['mobile'][0], 'MOBILE CODE');
        $this->clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->restCall('metadata/?type_filter=views');
        $this->assertEquals('MOBILE CODE', $restReply['reply']['views']['edit']['templates']['edit'], "Didn't get mobile code when that was the direct option");


        // Make sure we get it when we ask for mobile, even though there is base code there
        file_put_contents($filesToCheck['base'][0], 'BASE CODE');
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata/?type_filter=views');
        $this->assertEquals('MOBILE CODE', $restReply['reply']['views']['edit']['templates']['edit'], "Didn't get mobile code when base code was there.");

        // Make sure we get the base code when we ask for it.
        file_put_contents($filesToCheck['base'][0], 'BASE CODE');
        $this->clearMetadataCache();
        $this->authToken = $this->baseAuthToken;
        $restReply = $this->restCall('metadata/?type_filter=views');
        $this->assertEquals('BASE CODE', $restReply['reply']['views']['edit']['templates']['edit'], "Didn't get base code when it was the direct option");

        // Delete the mobile template and make sure it falls back to base
        unlink($filesToCheck['mobile'][0]);
        $this->clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->restCall('metadata/?type_filter=views');
        $this->assertEquals('BASE CODE', $restReply['reply']['views']['edit']['templates']['edit'], "Didn't fall back to base code when mobile code wasn't there.");

        // Make sure the mobile code is loaded before the non-custom base code
        file_put_contents($filesToCheck['mobile'][1], 'CUSTOM MOBILE CODE');
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata/?type_filter=views');
        $this->assertEquals('CUSTOM MOBILE CODE', $restReply['reply']['views']['edit']['templates']['edit'], "Didn't use the custom mobile code.");

        // Make sure custom base code works
        file_put_contents($filesToCheck['base'][1], 'CUSTOM BASE CODE');
        $this->clearMetadataCache();
        $this->authToken = $this->baseAuthToken;
        $restReply = $this->restCall('metadata/?type_filter=views');
        $this->assertEquals('CUSTOM BASE CODE', $restReply['reply']['views']['edit']['templates']['edit'], "Didn't use the custom base code.");
    }
}
