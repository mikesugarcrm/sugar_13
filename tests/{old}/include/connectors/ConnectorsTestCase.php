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

class Sugar_Connectors_TestCase extends TestCase
{
    public $original_modules_sources;
    public $original_searchdefs;
    public $original_connectors;

    protected function setUp(): void
    {
        $modules_sources = null;
        ConnectorUtils::getDisplayConfig();
        require CONNECTOR_DISPLAY_CONFIG_FILE;
        $this->original_modules_sources = $modules_sources;

        //Remove the current file and rebuild with default
        unlink(CONNECTOR_DISPLAY_CONFIG_FILE);
        $this->original_searchdefs = ConnectorUtils::getSearchDefs(true);

        $this->original_connectors = ConnectorUtils::getConnectors(true);
    }

    protected function tearDown(): void
    {
        if ($this->original_modules_sources != null) {
            write_array_to_file('modules_sources', $this->original_modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE);
        }
        if ($this->original_searchdefs != null) {
            write_array_to_file('searchdefs', $this->original_searchdefs, 'custom/modules/Connectors/metadata/searchdefs.php');
        }
        if ($this->original_connectors != null) {
            ConnectorUtils::saveConnectors($this->original_connectors);
        }
    }
}
