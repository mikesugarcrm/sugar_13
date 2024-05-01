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

namespace Sugarcrm\Sugarcrm\SearchEngine;

use Sugarcrm\Sugarcrm\SearchEngine\Engine\Elastic;

/**
 *
 * This module gets and saves data for the FTS settings admin page.
 *
 */
class AdminSettings
{
    /**
     * The name of the ext file
     * @var string
     */
    public const EXT_FILE_NAME = 'full_text_search_admin.php';

    /**
     * name of disabled FTS modules in config table
     * @var string
     */
    private const DISABLED_FTS_MODULES = 'disabledFtsModules';

    /**
     * The list of in-eligible modules to be removed from the modules listed in the studio page
     * @var array
     */
    protected static $ModulesToRemove = [
        'Users',
    ];

    /**
     * Get the list of enabled and disabled modules for display in Admin page
     * @return array
     */
    public function getModuleList() : array
    {
        [$enabled, $disabled] = $this->getModules();

        $list = [];
        $list['enabled_modules'] = $this->getModuleLabel($enabled);
        $list['disabled_modules'] = $this->getModuleLabel($disabled);
        return $list;
    }

    /**
     * Get the list of enabled and disabled modules
     * @return array
     */
    public function getModules() : array
    {
        $list = [];

        $engine = $this->getSearchEngine();
        if (empty($engine)) {
            return [$list, $list];
        }

        //Get the full list
        $modules = $this->getFullModuleList();

        //Get the enabled list from MetaDataHelper
        $enabled = $this->getAllFtsEnabledModules();
        $enabled = array_intersect($enabled, $modules);
        sort($enabled);

        //Disabled = Full list - enabled + disabled list in config table
        $disabled = array_diff($modules, $enabled);
        $disabled = array_unique(array_merge($disabled, $this->getDisabledFtsModuleList()));

        sort($disabled);
        return [$enabled, $disabled];
    }

    /**
     * Get the label for each module in the module list
     * @param array $modules the list of modules
     * @return array
     */
    protected function getModuleLabel(array $modules) : array
    {
        global $app_list_strings;

        $list = [];
        foreach ($modules as $module) {
            $label = $app_list_strings['moduleList'][$module] ?? $module;

            $list[] = ['module' => $module, 'label' => $label];
        }
        return $list;
    }

    /**
     * Compose the full list of FTS modules, which is composed of
     * 1) the list of modules listed on Admin -> Studio page;
     * 2) plus the list of modules with FTS enabled in vardefs
     * 3) minus the list of modules defined in self::$ModulesToRemove
     * @return array
     */
    public function getFullModuleList() : array
    {
        //include all the modules listed in the studio page
        $browser = new \StudioBrowser();
        $browser->loadModules();

        $modules = array_keys($browser->modules);
        $enabled = $this->getAllFtsEnabledModules();

        $modules = array_merge($modules, $enabled);
        return array_diff($modules, self::$ModulesToRemove);
    }

    /**
     * Get SearchEngine
     * @return Elastic
     */
    protected function getSearchEngine()
    {
        return SearchEngine::getInstance()->getEngine();
    }

    /**
     * get all FTS enabled modules from MetaDataManager
     * @return array
     */
    protected function getAllFtsEnabledModules() : array
    {
        $engine = $this->getSearchEngine();
        $enabled = [];
        if (!empty($engine)) {
            $enabled = $engine->getMetaDataHelper()->getAllEnabledModules();
        }

        return $enabled;
    }
    /**
     * Save the modules to the extension files.
     * @param array $enabledModules the list of enabled modules
     * @param array $disabledModules the list of disabled modules
     * @param boolean $toRebuild a flag to rebuild the cache or not
     */
    public function saveFTSModuleListSettings($enabledModules, $disabledModules, $toRebuild = true)
    {
        $this->writeFTSSettingsToModules($enabledModules, true);
        $this->writeFTSSettingsToModules($disabledModules, false);

        // save disabled modules in Config table
        $this->saveDisableFtsdModuleList($disabledModules);

        if ($toRebuild === true) {
            $modules = array_merge($enabledModules, $disabledModules);
            include_once 'modules/Administration/QuickRepairAndRebuild.php';
            $repair = new \RepairAndClear();
            $repair->repairAndClearAll(['rebuildExtensions'], $modules, true, false);
        }
    }

    /**
     * Write FTS settings for a list of modules
     * @param array $modules the list of modules
     * @param boolean $isEnabled the module is enabled or not
     */
    public function writeFTSSettingsToModules($modules, $isEnabled)
    {
        foreach ($modules as $module) {
            $this->writeFTSToVardefFile($module, $isEnabled);
        }
    }

    /**
     * Write the FTS setting to a module's extension file.
     * @param string $module the name of the module
     * @param boolean $isEnabled the module is enabled or not
     * @return bool
     */
    public function writeFTSToVardefFile($module, $isEnabled)
    {
        if (empty($module)) {
            return false;
        }

        //compose the content to write
        $moduleName = \BeanFactory::getObjectName($module);
        $out = "<?php\n // created: " . date('Y-m-d H:i:s') . "\n";
        $out .= override_value_to_string_recursive([$moduleName, 'full_text_search'], 'dictionary', $isEnabled);
        $out .= "\n";

        //write to the file
        $dir = 'custom/Extension/modules/' . $module . '/Ext/Vardefs';
        mkdir_recursive($dir);
        $file = $dir . '/' . self::EXT_FILE_NAME;
        sugar_file_put_contents_atomic($file, $out);
    }

    /**
     * get disabled FTS module list
     * @return array
     */
    protected function getDisabledFtsModuleList() : array
    {
        $admin = \Administration::getSettings('system', true);
        $ret = [];
        if (!empty($admin->settings['system_' . $this::DISABLED_FTS_MODULES])) {
            $ret = $admin->settings['system_' . $this::DISABLED_FTS_MODULES];
        }
        return $ret;
    }

    /**
     * Save disabled FTS module list in json_encoded array format
     *
     * @param array|null $disabledModules
     * @return void
     */
    protected function saveDisableFtsdModuleList(?array $disabledModules) : void
    {
        if (empty($disabledModules)) {
            $disabledModules = [];
        }

        // due to module ACL, some disabled modules may not be showing in the list
        $admin = new \Administration();
        $admin->saveSetting('system', $this::DISABLED_FTS_MODULES, $disabledModules);
    }
}
