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

use Sugarcrm\Sugarcrm\Security\Validator\Validator;
use Sugarcrm\SugarcrmTestsUnit\TestDependencyInjectionHelper;

/**
 * Helper for initialization of global variables of SugarCRM
 *
 * @author mgusev@sugarcrm.com
 */
class SugarTestHelper
{
    /**
     * @var array array of registered vars. It allows helper to unregister them on tearDown
     */
    protected static $registeredVars = [];

    /**
     * Global variables to be restored after each test
     *
     * @var array<string,mixed>
     */
    private static $globals = [];

    /**
     * Global variables to be unset after each test
     *
     * @var array<string,null>
     */
    private static $unsetGlobals = [];

    /**
     * @var array of system preference of SugarCRM as theme etc. They are storing on init one time and restoring each tearDown
     */
    protected static $systemVars = [];

    /**
     * @var array of modules which we should refresh on tearDown.
     */
    protected static $cleanModules = [];

    /**
     * @var array of modules and their custom fields created during setup.
     */
    protected static $customFields = [];

    /**
     * @var bool is SugarTestHelper inited or not. Just to skip initialization on the second and others call of init method
     */
    protected static $isInited = false;

    /**
     * All methods are static because of it we disable constructor
     */
    private function __construct()
    {
    }

    /**
     * Initialization of main variables of SugarCRM in global scope
     */
    public static function init()
    {
        if (self::$isInited) {
            return;
        }

        SugarCache::instance()->flush();
        SugarConfig::getInstance()->clearCache();

        self::backupGlobal('sugar_config');
        self::backupGlobal('current_language');
        self::backupGlobal('current_user');
        self::backupGlobal('reload_vardefs');
        self::backupGlobal('locale');
        self::backupGlobal('service_object');
        self::backupGlobal('app_strings');
        self::backupGlobal('moduleList');
        self::backupGlobal('beanList');
        self::backupGlobal('beanFiles');
        self::backupGlobal('bwcModules');
        self::backupGlobal('modInvisList');
        self::backupGlobal('objectList');
        self::backupGlobal('modules_exempt_from_availability_check');
        self::backupGlobal('adminOnlyList');

        // list strings are only initialized in the MVC or API layers
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        self::backupGlobal('app_list_strings');

        // backup of SugarThemeRegistry
        self::$systemVars['SugarThemeRegistry'] = SugarThemeRegistry::current();

        self::$isInited = true;
    }

    private static function backupGlobal(string $name): void
    {
        if (array_key_exists($name, $GLOBALS)) {
            self::$globals[$name] = $GLOBALS[$name];
        } else {
            self::$unsetGlobals[$name] = null;
        }
    }

    private static function restoreGlobals(): void
    {
        foreach (self::$globals as $name => $value) {
            $GLOBALS[$name] = $value;
        }

        foreach (self::$unsetGlobals as $name => $_) {
            unset($GLOBALS[$name]);
        }
    }

    /**
     * Entry point for setup of global variable
     *
     * @static
     * @param string $varName name of global variable of SugarCRM
     * @param array $params some parameters for helper. For example for $mod_strings or $current_user
     * @return bool   is variable setuped or not
     */
    public static function setUp($varName, $params = [])
    {
        if (method_exists(self::class, 'setUp_' . $varName) == false) {
            throw new Exception('setUp for $' . $varName . ' is not implemented. ' . self::class . '::setUp_' . $varName);
        }

        return call_user_func(self::class . '::setUp_' . $varName, $params);
    }

    /**
     * Clean up all registered variables and restore $initVars and $systemVars
     */
    public static function tearDown(): void
    {
        // unregister variables in reverse order in order to have dependencies unregistered after dependants
        $unregisterVars = array_reverse(self::$registeredVars);

        // Handle current_user placing on the end since there are some things
        // that need current user for the clean up
        if (isset($unregisterVars['current_user'])) {
            $cu = $unregisterVars['current_user'];
            unset($unregisterVars['current_user']);
            $unregisterVars['current_user'] = $cu;
        }

        foreach ($unregisterVars as $varName => $isCalled) {
            if ($isCalled) {
                unset(self::$registeredVars[$varName]);
                if (method_exists(self::class, 'tearDown_' . $varName)) {
                    call_user_func(self::class . '::tearDown_' . $varName, []);
                } elseif (isset($GLOBALS[$varName])) {
                    unset($GLOBALS[$varName]);
                }
            }
        }

        self::restoreGlobals();

        // Restore the activity stream.
        Activity::restoreToPreviousState();

        // Restoring of theme
        SugarThemeRegistry::set(self::$systemVars['SugarThemeRegistry']->dirName);
        SugarCache::$isCacheReset = false;

        SugarConfig::getInstance()->clearCache();
        TimeDate::getInstance()->allow_cache = true;

        // Clear validator constraint factory caches. This is necessary as some of
        // the validators rely on system state like SugarConfig, moduleList, etc.
        Validator::clearValidatorsCache();

        TestDependencyInjectionHelper::resetContainer();
    }

    /**
     * Registration of $current_user in global scope
     *
     * @static
     * @param array $params parameters for SugarTestUserUtilities::createAnonymousUser method
     * @return bool  is variable setuped or not
     */
    protected static function setUp_current_user(array $params, $register = true)
    {
        if ($register) {
            self::$registeredVars['current_user'] = true;
        }
        $GLOBALS['current_user'] = call_user_func_array('SugarTestUserUtilities::createAnonymousUser', $params);

        BeanFactory::clearCache();

        return $GLOBALS['current_user'];
    }

    /**
     * Removal of $current_user from global scope
     *
     * @static
     * @return bool is variable removed or not
     */
    protected static function tearDown_current_user()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        return true;
    }

    /**
     * Registration of $beanList in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_beanList($params = [], $register = true)
    {
        if ($register) {
            self::$registeredVars['beanList'] = true;
        }
        global $beanList;
        require 'include/modules.php';

        return true;
    }

    /**
     * Registration of $beanFiles in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_beanFiles($params = [], $register = true)
    {
        if ($register) {
            self::$registeredVars['beanFiles'] = true;
        }
        global $beanFiles;
        require 'include/modules.php';

        return true;
    }

    /**
     * Registration of $bwcModules in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_bwcModules($params = [], $register = true)
    {
        if ($register) {
            self::$registeredVars['bwcModules'] = true;
        }
        global $bwcModules;
        require 'include/modules.php';

        return true;
    }

    /**
     * Registration of $moduleList in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_moduleList($params = [], $register = true)
    {
        if ($register) {
            self::$registeredVars['moduleList'] = true;
        }
        global $moduleList;
        require 'include/modules.php';

        return true;
    }

    /**
     * Reinitialization of $moduleList in global scope because we can't unset that variable
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function tearDown_moduleList()
    {
        return self::setUp_moduleList();
    }

    /**
     * Registration of $modListHeader in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_modListHeader()
    {
        self::$registeredVars['modListHeader'] = true;
        if (isset($GLOBALS['current_user']) == false) {
            self::setUp_current_user([
                true,
                1,
            ]);
        }
        $GLOBALS['modListHeader'] = query_module_access_list($GLOBALS['current_user']);

        return true;
    }

    /**
     * Registration of $app_strings in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_app_strings()
    {
        self::$registeredVars['app_strings'] = true;
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);

        return true;
    }

    /**
     * Setup the mock db helper,
     *
     * @param DBManager $mock
     * @return SugarTestDatabaseMock
     *
     * @deprecated
     */
    protected static function setUp_mock_db($mock = null)
    {
        if (!$mock) {
            $mock = new SugarTestDatabaseMock();
        }

        // as far as we mock the global object but don't know how to mock Doctrine connection,
        // leave it unmocked
        $doctrineConnection = DBManagerFactory::getConnection();
        SugarTestReflection::setProtectedValue($mock, 'conn', $doctrineConnection);

        self::$systemVars['db'] = DBManagerFactory::$instances;
        self::$registeredVars['mock_db'] = $mock;
        DBManagerFactory::$instances = ['' => $mock];

        return $mock;
    }

    /**
     * @deprecated
     */
    protected static function tearDown_mock_db()
    {
        DBManagerFactory::$instances = self::$systemVars['db'];
        unset(self::$systemVars['db'], self::$registeredVars['mock_db']);
    }

    /**
     * Registration of $app_list_strings in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_app_list_strings()
    {
        self::$registeredVars['app_list_strings'] = true;
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        return true;
    }

    /**
     * Registration of $timedate in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_timedate($params = [], $register = true)
    {
        if ($register) {
            self::$registeredVars['timedate'] = true;
        }
        $GLOBALS['timedate'] = TimeDate::getInstance();

        return true;
    }

    /**
     * Removal of $timedate from global scope
     *
     * @static
     * @return bool is variable removed or not
     */
    protected static function tearDown_timedate()
    {
        $GLOBALS['timedate']->clearCache();
        $GLOBALS['timedate']->allow_cache = true;

        return true;
    }

    /**
     * Registration of $mod_strings in global scope
     *
     * @static
     * @param array $params parameters for return_module_language function
     * @return bool  is variable setuped or not
     */
    protected static function setUp_mod_strings(array $params)
    {
        self::$registeredVars['mod_strings'] = true;
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], $params[0]);

        return true;
    }

    /**
     * Registration of $dictionary in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_dictionary()
    {
        self::setUp('beanFiles');
        self::setUp('beanList');
        self::setUp('bwcModules');
        self::$registeredVars['dictionary'] = true;

        global $dictionary;
        $dictionary = [];
        $moduleInstaller = new ModuleInstaller();
        $moduleInstaller->silent = true;
        $moduleInstaller->rebuild_tabledictionary();
        require 'modules/TableDictionary.php';

        foreach ($GLOBALS['beanList'] as $k => $v) {
            VardefManager::loadVardef($k, BeanFactory::getObjectName($k));
        }

        return true;
    }

    /**
     * Create custom field
     *
     * @static
     * @param string $module
     * @param array $vardefs
     *
     * @return TemplateField
     * @throws Exception
     */
    public static function setUpCustomField(string $module, array $vardefs): TemplateField
    {
        self::$registeredVars['custom_field'] = true;

        if (!isset($vardefs['type'])) {
            throw new Exception('Field type is not specified');
        }

        require_once 'modules/DynamicFields/FieldCases.php';
        $field = get_widget($vardefs['type']);

        foreach ($vardefs as $param => $value) {
            $field->{$param} = $value;
        }

        $bean = BeanFactory::newBean($module);

        if (!$bean) {
            throw new Exception(sprintf(
                '%s is not a valid module name',
                $module
            ));
        }

        $dynamicField = new DynamicField($module);
        $dynamicField->setup($bean);
        $field->save($dynamicField);

        $mi = new ModuleInstaller();
        $mi->silent = true;
        $mi->rebuild_vardefs();

        self::$customFields[] = [$dynamicField, $field];

        $objectName = BeanFactory::getObjectName($module);
        VardefManager::loadVardef($module, $objectName, true);

        if (!empty($vardefs['formula'])) {
            foreach (VardefManager::getLinkedModulesFromFormula($bean, $vardefs['formula']) as $m => $_) {
                if ($objectName = BeanFactory::getObjectName($m)) {
                    $mi->rebuild_dependencies();
                    VardefManager::loadVardef($m, $objectName, true);
                };
            }
        }

        return $field;
    }

    /**
     * @deprecated Use setUpCustomField() instead
     */
    protected static function setUp_custom_field(array $params)
    {
        if (count($params) < 2) {
            throw new Exception(sprintf(
                '%s requires 2 parameters, %d given',
                __METHOD__,
                count($params)
            ));
        }

        return self::setUpCustomField(...$params);
    }

    /**
     * Removal of custom fields
     */
    public static function tearDownCustomFields(): void
    {
        $mi = new ModuleInstaller();
        $mi->silent = true;

        foreach (self::$customFields as $data) {
            [$dynamicField, $field] = $data;
            $vardefs = $field->get_field_def();
            $field->delete($dynamicField);
            $mi->rebuild_vardefs();
            if (!empty($vardefs['formula'])) {
                $bean = $dynamicField->bean;
                foreach (VardefManager::getLinkedModulesFromFormula($bean, $vardefs['formula']) as $m => $_) {
                    if ($objectName = BeanFactory::getObjectName($m)) {
                        $mi->rebuild_dependencies();
                        VardefManager::loadVardef($m, $objectName, true);
                    };
                }
            }
            $module = $dynamicField->module;
            $objectName = BeanFactory::getObjectName($module);
            VardefManager::loadVardef($module, $objectName, true);
        }

        self::$customFields = [];
    }

    /**
     * @deprecated Use tearDownCustomFields() instead
     */
    protected static function tearDown_custom_field()
    {
        self::tearDownCustomFields();
    }

    public const NOFILE_DATA = '__NO_FILE__';
    public static $oldFiles;
    public static $oldDirs;

    /**
     * Setup tracking of the filesystem changes
     */
    public static function setUpFiles(): void
    {
        self::$oldFiles = [];
        self::$oldDirs = [];
        self::$registeredVars['files'] = true;
    }

    /**
     * @deprecated Use setUpFiles() instead
     */
    protected static function setUp_files()
    {
        self::setUpFiles();
    }

    /**
     * Preserve a file
     */
    public static function saveFile($filename)
    {
        if (is_array($filename)) {
            foreach ($filename as $file) {
                self::saveFile($file);
            }

            return;
        }
        if (file_exists($filename)) {
            self::$oldFiles[$filename] = file_get_contents($filename);
        } else {
            self::$oldFiles[$filename] = self::NOFILE_DATA;
        }
    }

    public static function ensureDir($dirname)
    {
        if (is_array($dirname)) {
            foreach ($dirname as $dir) {
                self::ensureDir($dir);
            }

            return;
        }
        $parts = explode('/', $dirname);
        while (!empty($parts)) {
            $path = implode('/', $parts);
            if (!is_dir($path)) {
                self::$oldDirs[] = $path;
            }
            array_pop($parts);
        }
        if (!is_dir($dirname)) {
            SugarAutoLoader::ensureDir($dirname);
        }
    }

    /**
     * Roll back tracked filesystem changes
     */
    public static function tearDownFiles(): void
    {
        foreach (self::$oldFiles as $filename => $filecontents) {
            if (defined('SHADOW_INSTANCE_DIR')) {
                if (substr($filename, 0, 7) != 'custom/' && substr($filename, 0, 6) != 'cache/' && $filename != 'config_override.php' && file_exists($filename)) {
                    // Delete shadow files always
                    @unlink($filename);
                    continue;
                }
            }
            if ($filecontents == self::NOFILE_DATA) {
                if (file_exists($filename)) {
                    unlink($filename);
                }
            } else {
                file_put_contents($filename, $filecontents);
            }
        }
        rsort(self::$oldDirs);
        foreach (self::$oldDirs as $dirname) {
            if (file_exists($dirname)) {
                rmdir($dirname);
            }
        }
    }

    /**
     * @deprecated Use tearDownFiles() instead
     */
    protected static function tearDown_files()
    {
        self::tearDownFiles();
    }

    /**
     * @var ACLAction
     */
    public static $aclAction;

    protected static function setUp_ACLStatic()
    {
        self::$aclAction = BeanFactory::newBean('ACLActions');
    }

    protected static function tearDown_ACLStatic()
    {
        self::$aclAction->clearACLCache();
    }

    public static function clearACLCache()
    {
        self::$aclAction->clearACLCache();
    }

    /**
     * Reinitialization of $dictionary in global scope because we can't unset that variable
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function tearDown_dictionary()
    {
        return self::setUp_dictionary();
    }

    /**
     * Cleaning caches and refreshing vardefs
     *
     * @static
     * @param array Relationship parameters
     * @return bool
     */
    protected static function setUp_relation(array $params)
    {
        if (empty($params[0]) || empty($params[1])) {
            throw new Exception('setUp("relation") requires two parameters');
        }

        [$lhs_module, $rhs_module] = $params;
        self::$registeredVars['relation'] = true;
        self::$cleanModules[] = $lhs_module;

        LanguageManager::clearLanguageCache($lhs_module);
        if ($lhs_module != $rhs_module) {
            self::$cleanModules[] = $rhs_module;
            LanguageManager::clearLanguageCache($rhs_module);
        }

        self::setUp('dictionary');

        VardefManager::$linkFields = [];
        VardefManager::clearVardef();
        VardefManager::refreshVardefs($lhs_module, BeanFactory::getObjectName($lhs_module));
        if ($lhs_module != $rhs_module) {
            VardefManager::refreshVardefs($rhs_module, BeanFactory::getObjectName($rhs_module));
        }
        SugarRelationshipFactory::rebuildCache();

        return true;
    }

    /**
     * Doing the same things like setUp but for initialized list of modules
     *
     * @static
     * @return bool are caches refreshed or not
     */
    protected static function tearDown_relation()
    {
        SugarRelationshipFactory::deleteCache();

        $modules = array_unique(self::$cleanModules);
        foreach ($modules as $module) {
            LanguageManager::clearLanguageCache($module);
        }

        self::tearDown();

        VardefManager::$linkFields = [];
        VardefManager::clearVardef();
        foreach ($modules as $module) {
            VardefManager::refreshVardefs($module, BeanFactory::getBeanClass($module));
        }
        SugarRelationshipFactory::rebuildCache();

        self::$cleanModules = [];

        return true;
    }

    protected static function setUp_theme()
    {
        self::$registeredVars['theme'] = true;
    }

    protected static function tearDown_theme()
    {
        SugarTestThemeUtilities::removeAllCreatedAnonymousThemes();
    }

    /**
     * Registration of $modInvisList in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_modInvisList($params = [], $register = true)
    {
        if ($register) {
            self::$registeredVars['modInvisList'] = true;
        }
        global $modInvisList;
        require 'include/modules.php';

        return true;
    }

    /**
     * Sets up the GLOBAL log variable
     * @param string $name The name of the log to instantiate
     */
    protected static function setUp_log($name = 'SugarCRM')
    {
        self::$registeredVars['log'] = true;
        $GLOBALS['log'] = LoggerManager::getLogger();
    }

    /**
     * Tears down the global log variable and replaces it with the OOTB one
     */
    protected static function tearDown_log()
    {
        $GLOBALS['log'] = LoggerManager::getLogger();
    }

    /**
     * Reinitialization of $modInvisList in global scope because we can't unset that variable
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function tearDown_modInvisList()
    {
        return self::setUp_modInvisList();
    }
}
