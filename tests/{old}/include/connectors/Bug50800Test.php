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
 * This test makes sure that connectors::getConnectors() can handle a badly formed custom metadata file that is either
 * missing the connectors array or the array has been defined as a string
 * @ticket 50800
 */
class Bug50800Test extends TestCase
{
    public $custom_path = 'custom/modules/Connectors/metadata';
    public $custom_contents;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_strings');
        if (file_exists($this->custom_path . '/connectors.php')) {
            $this->custom_contents = file_get_contents($this->custom_path . '/connectors.php');
            unlink($this->custom_path . '/connectors.php');
        } else {
            mkdir_recursive($this->custom_path);
        }
    }

    protected function tearDown(): void
    {
        //remove connector file
        if (!empty($this->custom_contents)) {
            file_put_contents($this->custom_path . '/connectors.php', $this->custom_contents);
        } else {
            unlink($this->custom_path . '/connectors.php');
        }

        SugarTestHelper::tearDown();
    }

    public function testConnectorFailsStringGracefully()
    {
        //now write a connector file with a string instead of an array for the connector var
        file_put_contents($this->custom_path . '/connectors.php', "<?php\n \$connector = 'Connector String ';");

        //create the connector and call getConnectors
        $cu = new ConnectorUtils();
        $this->assertIsArray(
            $cu->getConnectors(true),
            'ConnectorsUtils::getConnectors() failed to return an array when $connectors is a string'
        );
    }

    public function testConnectorFailsNullGracefully()
    {
        //now write a connector file with missing array info instead of an array for the connector var
        file_put_contents($this->custom_path . '/connectors.php', "<?php\n ");

        //create the connector and call getConnectors
        $cu = new ConnectorUtils();
        $this->assertIsArray(
            $cu->getConnectors(true),
            'ConnectorsUtils::getConnectors() failed to return an array when connectors array was missing.'
        );
    }
}
