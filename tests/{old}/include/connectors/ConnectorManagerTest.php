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

class SourceTest
{
    public $name;
    private $id;

    public function __construct($param)
    {
        $this->id = $param;
        $this->name = $param;
    }

    public function hasTestingEnabled()
    {
        if ($this->id == 'ValidTestingDisabledAuth') {
            return false;
        }
        return true;
    }

    public function test()
    {
        if ($this->id == 'ValidTestingEnabledAuth' || $this->id == 'ValidTestingEnabledUnAuth') {
            return true;
        }
        if ($this->id == 'ThrowsErrors') {
            throw new Exception('this connector has problems');
        }
        return false;
    }

    public function getMapping()
    {
        return [
            'testfield1' => 'value',
            'testfield2' => 'value',
        ];
    }
}


class ConnectorManagerTest extends ConnectorManager
{
    public function getConnectorList()
    {
        return [
            'ValidTestingDisabledAuth' => ['id' => 'ValidTestingDisabledAuth'],
            'ValidTestingEnabledAuth' => ['id' => 'ValidTestingEnabledAuth'],
            'ValidTestingEnabledUnAuth' => ['id' => 'ValidTestingEnabledUnAuth'],
            'InvalidTestFails' => ['id' => 'InvalidTestFails'],
            'InvalidNoSource' => ['id' => 'InvalidNoSource'],
            'ThrowsErrors' => ['id' => 'ThrowsErrors'],
        ];
    }

    public function getEAPMForConnector($connector)
    {
        if ($connector['id'] == 'ValidTestingDisabledAuth' || $connector['id'] == 'ValidTestingEnabledAuth') {
            $toRet = new stdClass();
            $toRet->id = 1;
            return $toRet;
        } else {
            return null;
        }
    }

    public function getSourceForConnector($connector)
    {
        if ($connector['id'] == 'InvalidNoSource') {
            return null;
        } else {
            return new SourceTest($connector['id']);
        }
    }
}

class ConnectorsValidTest extends TestCase
{
    protected function tearDown(): void
    {
        $cacheFile = sugar_cached('api/metadata/connectors.php');
        if (file_exists($cacheFile)) {
            // delete the current file because it has trash data in it
            unlink($cacheFile);
        }
    }

    /*
     * test get connectors and initial cache
     */
    public function testGetConnectors()
    {
        $expectedOut = [
            'ValidTestingDisabledAuth' => [
                'id' => 'ValidTestingDisabledAuth',
                'testing_enabled' => false,
                'test_passed' => false,
                'eapm_bean' => false,
                'field_mapping' => [],
            ],
            'ValidTestingEnabledAuth' => [
                'id' => 'ValidTestingEnabledAuth',
                'testing_enabled' => true,
                'test_passed' => true,
                'eapm_bean' => false,
                'field_mapping' => [],
            ],
            'ValidTestingEnabledUnAuth' => [
                'id' => 'ValidTestingEnabledUnAuth',
                'testing_enabled' => true,
                'test_passed' => true,
                'eapm_bean' => false,
                'field_mapping' => [],
            ],
            'InvalidTestFails' => [
                'id' => 'InvalidTestFails',
                'testing_enabled' => true,
                'test_passed' => false,
                'eapm_bean' => false,
                'field_mapping' => [],
            ],
            'InvalidNoSource' => [
                'id' => 'InvalidNoSource',
                'testing_enabled' => false,
                'test_passed' => false,
                'eapm_bean' => false,
                'field_mapping' => [],
            ],
            'ThrowsErrors' => [
                'id' => 'ThrowsErrors',
                'testing_enabled' => true,
                'test_passed' => false,
                'eapm_bean' => false,
                'field_mapping' => [],
            ],
        ];

        $connectorManager = new ConnectorManagerTest();
        $connectors = $connectorManager->buildConnectorsMeta();

        // should get valid connectors with a hash
        $this->assertTrue(!empty($connectors['_hash']));
        $currentHash = $connectors['_hash'];
        unset($connectors['_hash']);
        $this->assertEquals($connectors, $expectedOut);

        // should create cache file
        // Handle the cache file
        $cacheFile = sugar_cached('api/metadata/connectors.php');
        if (file_exists($cacheFile)) {
            require $cacheFile;
        }
        $this->assertEquals($currentHash, $connectors['_hash']);
    }

    /**
     * test getting current hash
     */
    public function testHashes()
    {
        $connectorManager = new ConnectorManagerTest();

        $connectors = $connectorManager->getUserConnectors();

        // should get valid connectors with a hash
        $this->assertTrue(!empty($connectors['_hash']));


        $currentUserHash = $connectors['_hash'];
        unset($connectors['_hash']);

        $this->assertTrue($connectorManager->isHashValid($currentUserHash));

        $this->assertFalse($connectorManager->isHashValid('invalidHash'));
    }
}
