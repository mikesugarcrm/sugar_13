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
 * Bug 58926
 */
class Bug58926Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests if an app_list_string or app_string has special html characters in it if it will be decoded properly in the MetadataManager
     * when requested
     *
     * @group Bug58926
     */
    public function testAppStringsWithSpecialChars()
    {
        $test = [];
        $result = [
            'app_list_strings' => [
                'moduleList' => [
                    'Leads' => "Lead's Are Special",
                ],
                'moduleListSingular' => [
                    'Leads' => "Leads' Are Special",
                ],
            ],
            'app_strings' => [
                'LBL_NEXT' => "Next's Are the worst",
            ],
        ];

        $mm = new MetaDataManagerBug58926();

        $test['app_list_strings']['moduleList']['Leads'] = $mm->getDecodeStrings('Lead&#39;s Are Special');
        $test['app_list_strings']['moduleListSingular']['Leads'] = $mm->getDecodeStrings('Leads&#39; Are Special');
        $test['app_strings']['LBL_NEXT'] = $mm->getDecodeStrings('Next&#39;s Are the worst');


        $this->assertEquals($test, $result, 'Decoding did not work');
    }
}

/**
 * Accessor class to the metadatamanager to allow access to protected methods
 */
class MetaDataManagerBug58926 extends MetaDataManager
{
    public function getDecodeStrings($data)
    {
        return $this->decodeStrings($data);
    }
}
