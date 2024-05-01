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

class TemplateDateTest extends TestCase
{
    private $hasExistingCustomSearchFields = false;

    protected function setUp(): void
    {
        if (file_exists('custom/modules/Opportunities/metadata/SearchFields.php')) {
            $this->hasExistingCustomSearchFields = true;
            copy('custom/modules/Opportunities/metadata/SearchFields.php', 'custom/modules/Opportunities/metadata/SearchFields.php.bak');
            unlink('custom/modules/Opportunities/metadata/SearchFields.php');
        } elseif (!file_exists('custom/modules/Opportunities/metadata')) {
            SugarAutoLoader::ensureDir('custom/modules/Opportunities/metadata');
        }
    }

    protected function tearDown(): void
    {
        if (!$this->hasExistingCustomSearchFields) {
            unlink('custom/modules/Opportunities/metadata/SearchFields.php');
        }

        if (file_exists('custom/modules/Opportunities/metadata/SearchFields.php.bak')) {
            copy('custom/modules/Opportunities/metadata/SearchFields.php.bak', 'custom/modules/Opportunities/metadata/SearchFields.php');
            unlink('custom/modules/Opportunities/metadata/SearchFields.php.bak');
        }
    }

    public function testEnableRangeSearchInt()
    {
        $searchFields = [];
        $_REQUEST['view_module'] = 'Opportunities';
        $_REQUEST['name'] = 'probability';
        $templateDate = new TemplateInt();
        $templateDate->enable_range_search = true;
        $templateDate->populateFromPost();
        $this->assertTrue(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'));
        include 'custom/modules/Opportunities/metadata/SearchFields.php';
        $this->assertTrue(isset($searchFields['Opportunities']['range_probability']));
        $this->assertTrue(isset($searchFields['Opportunities']['start_range_probability']));
        $this->assertTrue(isset($searchFields['Opportunities']['end_range_probability']));
        $this->assertTrue(!isset($searchFields['Opportunities']['range_probability']['is_date_field']));
        $this->assertTrue(!isset($searchFields['Opportunities']['start_range_probability']['is_date_field']));
        $this->assertTrue(!isset($searchFields['Opportunities']['end_range_probability']['is_date_field']));
    }

    public function testEnableRangeSearchDate()
    {
        $searchFields = [];
        $_REQUEST['view_module'] = 'Opportunities';
        $_REQUEST['name'] = 'date_closed';
        $templateDate = new TemplateDate();
        $templateDate->enable_range_search = true;
        $templateDate->populateFromPost();
        $this->assertTrue(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'));
        include 'custom/modules/Opportunities/metadata/SearchFields.php';
        $this->assertTrue(isset($searchFields['Opportunities']['range_date_closed']));
        $this->assertTrue(isset($searchFields['Opportunities']['start_range_date_closed']));
        $this->assertTrue(isset($searchFields['Opportunities']['end_range_date_closed']));
        $this->assertTrue(isset($searchFields['Opportunities']['range_date_closed']['is_date_field']));
        $this->assertTrue(isset($searchFields['Opportunities']['start_range_date_closed']['is_date_field']));
        $this->assertTrue(isset($searchFields['Opportunities']['end_range_date_closed']['is_date_field']));
    }
}
