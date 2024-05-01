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

include_once 'modules/SugarLive/clients/base/api/SugarLiveConfigModuleApi.php';

/**
 * @coversDefaultClass SugarLiveConfigModuleApi
 */
class SugarLiveConfigModuleApiTest extends TestCase
{
    protected $callMetaFile = 'custom/modules/Calls/clients/base/views/omnichannel-detail/omnichannel-detail.php';
    protected $messageMetaFile = 'custom/modules/Messages/clients/base/views/omnichannel-detail/omnichannel-detail.php';

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);
        SugarTestHelper::setUp('app_list_strings');
    }

    protected function tearDown(): void
    {
        foreach (['Calls', 'Messages'] as $mod) {
            $filename = 'custom/modules/' . $mod . '/clients/base/views/omnichannel-detail/omnichannel-detail.php';
            if ($this->deleteTestFile($filename)) {
                MetaDataFiles::clearModuleClientCache($mod, 'view');
                TemplateHandler::clearCache($mod);
            }
        }
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::configSaveMetaFiles
     */
    public function testConfigSaveMetaFiles()
    {
        $viewdefs = [];
        // begin test data
        $args = ['module' => 'SugarLive'];
        $args['viewdefs'] = [];
        $args['viewdefs']['Messages'] = [
            'base' => [
                'view' => [
                    'omnichannel-detail' => [
                        'fields' => [
                            [
                                'name' => 'name',
                                'label' => 'LBL_MESSAGE_SUBJECT',
                                'type' => 'name',
                            ],
                            [
                                'name' => 'description',
                                'label' => 'LBL_DESCRIPTION',
                                'type' => 'textarea',
                            ],
                            [
                                'name' => 'status',
                                'label' => 'LBL_STATUS',
                                'type' => 'enum',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $args['viewdefs']['Calls'] = [
            'base' => [
                'view' => [
                    'omnichannel-detail' => [
                        'fields' => [
                            [
                                'name' => 'name',
                                'label' => 'LBL_MESSAGE_SUBJECT',
                                'type' => 'name',
                            ],
                            [
                                'name' => 'description',
                                'label' => 'LBL_DESCRIPTION',
                                'type' => 'textarea',
                            ],
                            [
                                'name' => 'date_modified',
                                'label' => 'LBL_DATE_MODIFIED',
                                'type' => 'datetimecombo',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        //end test data

        $this->deleteTestFile($this->callMetaFile);
        $this->deleteTestFile($this->messageMetaFile);

        $configApi = $this->createPartialMock('SugarLiveConfigModuleApi', []);
        SugarTestReflection::callProtectedMethod($configApi, 'configSaveMetaFiles', [$args, 'omnichannel-detail']);

        $this->assertTrue(file_exists($this->callMetaFile));
        $this->assertTrue(file_exists($this->messageMetaFile));

        require "$this->callMetaFile";
        $this->assertEquals($args['viewdefs']['Calls'], $viewdefs['Calls']);

        require "$this->messageMetaFile";
        $this->assertEquals($args['viewdefs']['Messages'], $viewdefs['Messages']);
    }

    /**
     * @param $filename
     * @return bool
     */
    protected function deleteTestFile($filename): bool
    {
        if (file_exists($filename)) {
            unlink($filename);
            return true;
        }
        return false;
    }
}
