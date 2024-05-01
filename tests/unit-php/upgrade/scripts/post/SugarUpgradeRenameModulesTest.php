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

namespace Sugarcrm\SugarcrmTestsUnit\upgrade\scripts\post;

require_once 'upgrade/scripts/post/7_RenameModules.php';

/**
 * @coversDefaultClass \SugarUpgradeRenameModules
 */
class SugarUpgradeRenameModulesTest extends \PHPUnit\Framework\TestCase
{
    private $savedConfig = [];

    /**
     * @covers ::run
     */
    public function testApostropheEscaping()
    {
        $renameModulesMock = new RenameModulesMock();
        $upgradeScriptMock = new SugarUpgradeRenameModulesMock($renameModulesMock);

        $renamedList = $upgradeScriptMock->run();

        $this->assertNotEmpty($renamedList['en_us'], 'True rename should trigger processing');
        $this->assertEmpty($renamedList['fr_FR'], 'Different apostrophe escaping should not trigger processing');
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (array_key_exists('default_language', $GLOBALS['sugar_config'])) {
            $this->savedConfig['default_language'] = $GLOBALS['sugar_config']['default_language'];
        }
        $GLOBALS['sugar_config']['default_language'] = 'en_us';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (array_key_exists('default_language', $this->savedConfig)) {
            $GLOBALS['sugar_config']['default_language'] = $this->savedConfig['default_language'];
        } else {
            unset($GLOBALS['sugar_config']['default_language']);
        }
    }
}

class SugarUpgradeRenameModulesMock extends \SugarUpgradeRenameModules
{
    private $languages = [
        'en_us' => 'English (US)',
        'fr_FR' => 'Français',
    ];

    /**
     * $app_list_strings with customizations applied
     */
    private $appListStrings = [
        'en_us' => [
            'moduleList' => [
                'BusinessCenters' => 'Trade Centers',
            ],
            'moduleListSingular' => [
                'BusinessCenters' => 'Business Center',
            ],
        ],
        'fr_FR' => [
            'moduleList' => [
                'BusinessCenters' => 'Centres d&#39;affaires',
            ],
            'moduleListSingular' => [
                'BusinessCenters' => 'Centre d&#39;affaires',
            ],
        ],
    ];

    /**
     * OOTB $app_list_strings, see include/language/
     */
    private $coreAppListStrings = [
        'en_us' => [
            'moduleList' => [
                'BusinessCenters' => 'Business Centers',
            ],
            'moduleListSingular' => [
                'BusinessCenters' => 'Business Center',
            ],
        ],
        'fr_FR' => [
            'moduleList' => [
                'BusinessCenters' => 'Centres d\'affaires',
            ],
            'moduleListSingular' => [
                'BusinessCenters' => 'Centre d\'affaires',
            ],
        ],
    ];

    /** @var \RenameModules */
    private $renameModulesInstance;

    public function __construct(\RenameModules $renameModulesInstance)
    {
        $this->renameModulesInstance = $renameModulesInstance;
    }

    public function log($msg)
    {
        return;
    }

    protected function getRenameModulesInstance()
    {
        return $this->renameModulesInstance;
    }

    protected function getLanguages()
    {
        return $this->languages;
    }

    protected function getAppListStrings($lang)
    {
        if (isset($this->appListStrings[$lang])) {
            return $this->appListStrings[$lang];
        } else {
            throw new \InvalidArgumentException('Language not defined in test');
        }
    }

    protected function getCoreAppListStrings($lang)
    {
        if (isset($this->coreAppListStrings[$lang])) {
            return $this->coreAppListStrings[$lang];
        } else {
            throw new \InvalidArgumentException('Language not defined in test');
        }
    }
}

class RenameModulesMock extends \RenameModules
{
    public $renamedModules = [];

    public function __construct()
    {
    }

    public function getModuleSingularKey($moduleName)
    {
        $names = [
            'BusinessCenters' => 'BusinessCenters',
        ];
        if (isset($names[$moduleName])) {
            return $names[$moduleName];
        } else {
            throw new \InvalidArgumentException('Module not defined in test');
        }
    }

    public function changeModuleModStrings($moduleName, $replacementLabels)
    {
        $this->renamedModules[$moduleName] = true;
        return [];
    }

    public function changeStringsInRelatedModules()
    {
        return;
    }

    public function getRenamedModules()
    {
        return $this->renamedModules;
    }
}
