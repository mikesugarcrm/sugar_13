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

require_once 'include/utils.php';

class ConnectorsOriginalMapping extends Sugar_Connectors_TestCase
{
    /**
     * @var string|mixed
     */
    public $customMappingFile;

    protected function setUp(): void
    {
        $this->customMappingFile = 'custom/modules/Connectors/connectors/sources/ext/rest/twitter/mapping.php';
        $mapping = [];
        write_array_to_file('mapping', $mapping, $this->customMappingFile);
    }

    protected function tearDown(): void
    {
        unlink($this->customMappingFile);
    }

    public function testOriginalMapping()
    {
        $mapping = null;
        $source = SourceFactory::getSource('ext_rest_twitter');
        $originalMapping = $source->getOriginalMapping();

        // Sets $mapping
        require 'modules/Connectors/connectors/sources/ext/rest/twitter/mapping.php';

        $this->assertEquals($mapping, $originalMapping);
    }
}
