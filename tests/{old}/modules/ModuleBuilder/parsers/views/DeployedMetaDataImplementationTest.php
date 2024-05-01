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
 * @covers DeployedMetaDataImplementation
 */
class DeployedMetaDataImplementationTest extends TestCase
{
    protected $recordViewFile;
    protected $targetViewFile;
    protected $implementation;

    protected function setUp(): void
    {
        $this->recordViewFile = 'custom/modules/ProductTemplates/clients/base/views/record/record.php';
        $this->targetViewFile = 'custom/modules/ProductTemplates/clients/base/views/' .
            'product-catalog-dashlet-drawer-record/product-catalog-dashlet-drawer-record.php';

        $this->implementation = new DeployedMetaDataImplementationMock(
            'recordview',
            'ProductTemplates',
            'base',
            []
        );
    }

    protected function tearDown(): void
    {
        if (file_exists($this->recordViewFile)) {
            unlink($this->recordViewFile);
        }
        if (file_exists($this->targetViewFile)) {
            unlink($this->targetViewFile);
        }
        SugarTestHelper::tearDown();
    }

    /**
     * Checks updatePanelsFromRecordLayout function
     *
     * @covers ::updatePanelsFromRecordLayout
     */
    public function testUpdatePanelsFromRecordLayout()
    {
        $targetViewdefs = [];
        $recordViewdefs = [];
        $viewdefs = [];
        $targetViewdefs['base']['view']['product-catalog-dashlet-drawer-record'] = [
            'panels' => [
                [
                    'name' => 'panel_body',
                    'fields' => [
                        'status',
                        'tax_class',
                    ],
                ],
            ],
        ];
        $recordViewdefs['base']['view']['record'] = [
            'panels' => [
                [
                    'name' => 'panel_body',
                    'fields' => [
                        'status',
                        'custom_field_c',
                        'active_status',
                    ],
                ],
            ],
        ];

        $this->implementation->saveToFile($this->recordViewFile, $recordViewdefs);
        $this->implementation->saveToFile($this->targetViewFile, $targetViewdefs);

        SugarTestReflection::callProtectedMethod(
            $this->implementation,
            'updatePanelsFromRecordLayout',
            [
                'ProductTemplates',
                'product-catalog-dashlet-drawer-record',
                $recordViewdefs,
            ]
        );
        require_once $this->targetViewFile;
        $fields = $viewdefs['ProductTemplates']['base']['view']['product-catalog-dashlet-drawer-record']
        ['panels'][0]['fields'];

        $this->assertContains('custom_field_c', $fields);
        $this->assertContains('status', $fields);
        $this->assertNotContains('tax_class', $fields);
    }
}

class DeployedMetaDataImplementationMock extends DeployedMetaDataImplementation
{
    public function saveToFile($filename, $defs, $useVariables = true, $forPopup = false)
    {
        return parent::_saveToFile($filename, $defs, $useVariables = true, $forPopup = false);
    }
}
