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

class MetaDataManagerBugFixesTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests that relate fields do not contain the len array
     *
     * @group Bug59676
     */
    public function testBug59676Test()
    {
        $defs = [];
        $defs['fields']['aaa_test_c'] = [
            'type' => 'relate',
            'name' => 'aaa_test_c',
            'len' => '25',
        ];

        $mm = new MetaDataHacksBugFixes($GLOBALS['current_user']);
        $newdefs = $mm->getNormalizedFielddefs($defs);

        $this->assertFalse(array_key_exists('len', $newdefs));
    }
}

/**
 * Accessor class to the metadatamanager to allow access to protected methods
 */
class MetaDataHacksBugFixes extends MetaDataHacks
{
    public function getNormalizedFielddefs($defs)
    {
        return $this->normalizeFielddefs($defs);
    }
}
