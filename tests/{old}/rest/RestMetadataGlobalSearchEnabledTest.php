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

class RestMetadataGlobalSearchEnabledTest extends RestTestBase
{
    /**
     * Tests the getGlobalSearchEnabled method in the MetadataManager
     * @dataProvider moduleVardefDataProvider
     */
    public function testGlobalSearchEnabled($platform, $seed, $vardefs, $expects, $failMessage)
    {
        $mm = MetaDataManager::getManager([$platform]);
        $actual = $mm->getGlobalSearchEnabled($seed, $vardefs, $platform);
        $this->assertEquals($expects, $actual, $failMessage);
    }

    // Please see `failMessage` property to see what each run is testing for
    public function moduleVardefDataProvider()
    {
        return [
            [
                'platform' => 'base',
                'seed' => true,
                'vardefs' => [],
                'expects' => true,
                'failMessage' => 'When globalSearchEnabled not provided, should check if $seed is Bean; if so should return true',
            ],
            [
                'platform' => 'base',
                'seed' => false,
                'vardefs' => [],
                'expects' => false,
                'failMessage' => 'When globalSearchEnabled not provided, should check if $seed is Bean; if NOT should return false',
            ],
            [
                'platform' => 'base',
                'seed' => true,
                'vardefs' => [
                    'globalSearchEnabled' => true,
                ],
                'expects' => true,
                'failMessage' => "When globalSearchEnabled used as 'global boolean', that value should be returned (truthy)",
            ],
            [
                'platform' => 'base',
                'seed' => true,
                'vardefs' => [
                    'globalSearchEnabled' => false,
                ],
                'expects' => false,
                'failMessage' => "When globalSearchEnabled used as 'global boolean', that value should be returned (falsy)",
            ],
            [
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => [
                    'globalSearchEnabled' => [
                        'portal' => true,
                        'base' => false,
                    ],
                ],
                'expects' => true,
                'failMessage' => 'When globalSearchEnabled used as array with platform, should use value for current platform if exists (truthy)',
            ],
            [
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => [
                    'globalSearchEnabled' => [
                        'portal' => false,
                        'base' => true,
                    ],
                ],
                'expects' => false,
                'failMessage' => 'When globalSearchEnabled used as array with platform, should use value for current platform if exists (falsy) (even if another platform is truthy)',
            ],
            [
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => [
                    'globalSearchEnabled' => [
                        'base' => true,
                    ],
                ],
                'expects' => true,
                'failMessage' => 'When globalSearchEnabled used as array with platform and the platform is not set in the meta, it should check to see if base is set; if so, it should return THAT value (truthy check)',
            ],
            [
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => [
                    'globalSearchEnabled' => [
                        'base' => false,
                    ],
                ],
                'expects' => false,
                'failMessage' => 'When globalSearchEnabled used as array with platform and the platform is not set in the meta, it should check to see if base is set; if so, it should return THAT value (false check)',
            ],
            [
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => [
                    'globalSearchEnabled' => [
                        'notportal1' => false,
                        'notportal2' => false,
                    ],
                ],
                'expects' => true,
                'failMessage' => 'When globalSearchEnabled used as array but current platform not found should fallback to true ignoring other platform settings',
            ],
        ];
    }
}
