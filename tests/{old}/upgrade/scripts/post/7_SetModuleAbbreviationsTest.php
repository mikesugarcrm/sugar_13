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
require_once 'upgrade/scripts/post/7_SetModuleAbbreviations.php';

/**
 * @coversDefaultClass SugarUpgradeSetModuleAbbreviations
 */
class SugarUpgradeSetModuleAbbreviationsTest extends UpgradeTestCase
{
    /**
     * @covers ::updateCustomLanguageFile
     * @dataProvider providerTestUpdateCustomLanguageFile
     * @param string $language The target language
     * @param array $coreStrings The core app_list_strings for the target language
     * @param array|null $customStrings The custom app_list_strings for the target language
     * @param array|null $expected the expected call arguments to updateCustomLanguageFileAppListStrings
     */
    public function testUpdateCustomLanguageFile($language, $coreStrings, $customStrings, $expected)
    {
        $isLanguageCustomized = !is_null($customStrings);

        $mockUpgrader = $this->getMockBuilder('SugarUpgradeSetModuleAbbreviations')
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isLanguageCustomized',
                'getCoreAppListStrings',
                'getCustomAppListStrings',
                'updateCustomLanguageFileAppListStrings',
            ])
            ->getMock();

        $mockUpgrader->method('isLanguageCustomized')
            ->willReturn($isLanguageCustomized);
        $mockUpgrader->method('getCoreAppListStrings')
            ->willReturn($coreStrings);
        $mockUpgrader->method('getCustomAppListStrings')
            ->willReturn($customStrings);

        if ($isLanguageCustomized && !empty($expected)) {
            $mockUpgrader->expects($this->once())
                ->method('updateCustomLanguageFileAppListStrings')
                ->with(...$expected);
        } else {
            $mockUpgrader->expects($this->never())
                ->method('updateCustomLanguageFileAppListStrings');
        }


        SugarTestReflection::callProtectedMethod($mockUpgrader, 'updateCustomLanguageFile', [$language]);
    }

    /**
     * @return array[] test values for testUpdateExtensions
     */
    public function providerTestUpdateCustomLanguageFile()
    {
        return [
            // No custom language file
            [
                'en_us',
                [
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                        'Contacts' => 'ZZ',
                    ],
                ],
                null,
                null,
            ],
            // Custom moduleIconList is empty
            [
                'en_us',
                [
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                        'Contacts' => 'ZZ',
                    ],
                ],
                [
                    'moduleIconList' => [],
                ],
                [
                    'en_us',
                    [
                        'moduleIconList' => [
                            'Accounts' => 'Ac',
                            'Contacts' => 'ZZ',
                        ],
                    ],
                ],
            ],
            // Custom moduleIconList has some entries already
            [
                'fr_FR',
                [
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                        'Contacts' => 'ZZ',
                    ],
                ],
                [
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                    ],
                ],
                [
                    'fr_FR',
                    [
                        'moduleIconList' => [
                            'Contacts' => 'ZZ',
                        ],
                    ],
                ],
            ],
            // Custom moduleIconList has all entries already
            [
                'fr_FR',
                [
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                        'Contacts' => 'ZZ',
                    ],
                ],
                [
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                        'Contacts' => 'Co',
                    ],
                ],
                null,
            ],
        ];
    }

    /**
     * @covers ::updateModuleAbbreviations
     * @dataProvider providerTestUpdateExtensions
     * @param string $language The target language
     * @param array $appListStrings The app_list_strings for the target language
     * @param array|null $expected the expected call arguments to updateDropdownExtensions
     */
    public function testUpdateExtensions($language, $coreAppListStrings, $appListStrings, $expected)
    {
        $mockUpgrader = $this->getMockBuilder('SugarUpgradeSetModuleAbbreviations')
            ->disableOriginalConstructor()
            ->onlyMethods(
                ['getAvailableLanguages', 'getCoreAppListStrings', 'getAllAppListStrings', 'updateDropdownExtensions']
            )
            ->getMock();

        $mockUpgrader->method('getCoreAppListStrings')
            ->willReturn($coreAppListStrings);
        $mockUpgrader->method('getAllAppListStrings')
            ->willReturn($appListStrings);

        if (!empty($expected)) {
            $mockUpgrader->expects($this->once())
                ->method('updateDropdownExtensions')
                ->with(...$expected);
        } else {
            $mockUpgrader->expects($this->never())
                ->method('updateDropdownExtensions');
        }

        SugarTestReflection::callProtectedMethod($mockUpgrader, 'updateExtensions', [$language]);
    }

    /**
     * @return array[] test values for testUpdateExtensions
     */
    public function providerTestUpdateExtensions()
    {
        return [
            // No existing moduleIconList entries
            [
                'en_us',
                [
                    'moduleList' => [
                        'Accounts' => 'Accounts',
                        'Contacts' => 'Contacts',
                    ],
                    'moduleIconList' => [],
                ],
                [
                    'moduleList' => [
                        'Accounts' => 'Accounts',
                        'Contacts' => 'Contacts',
                    ],
                    'moduleIconList' => [],
                ],
                [
                    'en_us',
                    'moduleIconList',
                    [
                        'Accounts' => 'Ac',
                        'Contacts' => 'Co',
                    ],
                ],
            ],
            // One existing core moduleIconList entry
            [
                'en_us',
                [
                    'moduleList' => [
                        'Accounts' => 'Accounts',
                        'Contacts' => 'Contacts',
                    ],
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                    ],
                ],
                [
                    'moduleList' => [
                        'Accounts' => 'Accounts',
                        'Contacts' => 'Contacts',
                    ],
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                    ],
                ],
                [
                    'en_us',
                    'moduleIconList',
                    [
                        'Accounts' => 'Ac',
                        'Contacts' => 'Co',
                    ],
                ],
            ],
            // All existing core moduleIconList entries
            [
                'en_us',
                [
                    'moduleList' => [
                        'Accounts' => 'Accounts',
                        'Contacts' => 'Contacts',
                    ],
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                        'Contacts' => 'Co',
                    ],
                ],
                [
                    'moduleList' => [
                        'Accounts' => 'Accounts',
                        'Contacts' => 'Contacts',
                    ],
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                        'Contacts' => 'Co',
                    ],
                ],
                [
                    'en_us',
                    'moduleIconList',
                    [
                        'Accounts' => 'Ac',
                        'Contacts' => 'Co',
                    ],
                ],
            ],
            // Custom moduleIconList entry
            [
                'en_us',
                [
                    'moduleList' => [
                        'Accounts' => 'Accounts',
                        'Contacts' => 'Contacts',
                    ],
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                        'Contacts' => 'Co',
                    ],
                ],
                [
                    'moduleList' => [
                        'Accounts' => 'Accounts',
                        'Contacts' => 'Contacts',
                    ],
                    'moduleIconList' => [
                        'Accounts' => 'ZZ',
                        'Contacts' => 'Co',
                    ],
                ],
                [
                    'en_us',
                    'moduleIconList',
                    [
                        'Accounts' => 'ZZ',
                        'Contacts' => 'Co',
                    ],
                ],
            ],
            // Custom extra long moduleIconList entry
            [
                'en_us',
                [
                    'moduleList' => [
                        'Accounts' => 'Accounts',
                        'Contacts' => 'Contacts',
                    ],
                    'moduleIconList' => [
                        'Accounts' => 'Ac',
                        'Contacts' => 'Co',
                    ],
                ],
                [
                    'moduleList' => [
                        'Accounts' => 'Accounts',
                        'Contacts' => 'Contacts',
                    ],
                    'moduleIconList' => [
                        'Accounts' => 'ThisIsALongAbbreviation',
                        'Contacts' => 'Co',
                    ],
                ],
                [
                    'en_us',
                    'moduleIconList',
                    [
                        'Accounts' => 'Th',
                        'Contacts' => 'Co',
                    ],
                ],
            ],
        ];
    }
}
