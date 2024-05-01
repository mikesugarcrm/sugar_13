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

require_once 'include/dir_inc.php';

class SugarTestThemeUtilities
{
    private static $createdThemes = [];

    private function __construct()
    {
    }

    public static function createAnonymousTheme()
    {
        $themename = 'TestTheme' . random_int(0, mt_getrandmax());

        sugar_mkdir("themes/$themename/images", null, true);
        sugar_mkdir("themes/$themename/css", null, true);
        sugar_mkdir("themes/$themename/js", null, true);
        sugar_mkdir("themes/$themename/tpls", null, true);

        sugar_file_put_contents("themes/$themename/css/style.css", 'h2 { display: inline; }');
        sugar_file_put_contents("themes/$themename/css/yui.css", '.yui { display: inline; }');
        sugar_file_put_contents("themes/$themename/js/style.js", 'var dog = "cat";');
        sugar_touch("themes/$themename/images/Accounts.gif");
        sugar_touch("themes/$themename/images/fonts.big.icon.gif");
        sugar_touch("themes/$themename/tpls/header.tpl");

        $themedef = "<?php\n";
        $themedef .= "\$themedef = array(\n";
        $themedef .= "'name'  => '$themename',";
        $themedef .= "'dirName'  => '$themename',";
        $themedef .= "'description' => '$themename',";
        $themedef .= "'version' => array('regex_matches' => array('.*')),";
        $themedef .= ');';
        sugar_file_put_contents("themes/$themename/themedef.php", $themedef);

        self::$createdThemes[] = $themename;

        SugarAutoLoader::buildCache();
        SugarThemeRegistry::buildRegistry();

        return $themename;
    }

    public static function createAnonymousOldTheme()
    {
        $themename = 'TestTheme' . random_int(0, mt_getrandmax());

        sugar_mkdir("themes/$themename/images", null, true);
        sugar_mkdir("themes/$themename/css", null, true);
        sugar_mkdir("themes/$themename/js", null, true);
        sugar_mkdir("themes/$themename/tpls", null, true);

        sugar_file_put_contents("themes/$themename/css/style.css", 'h2 { display: inline; }');
        sugar_file_put_contents("themes/$themename/css/yui.css", '.yui { display: inline; }');
        sugar_file_put_contents("themes/$themename/js/style.js", 'var dog = "cat";');
        sugar_touch("themes/$themename/images/Accounts.gif");
        sugar_touch("themes/$themename/images/fonts.big.icon.gif");
        sugar_touch("themes/$themename/tpls/header.tpl");

        $themedef = "<?php\n";
        $themedef .= "\$themedef = array(\n";
        $themedef .= "'name'  => '$themename',";
        $themedef .= "'dirName'  => '$themename',";
        $themedef .= "'description' => '$themename',";
        $themedef .= "'version' => array('exact_matches' => array('5.5.1')),";
        $themedef .= ');';
        sugar_file_put_contents("themes/$themename/themedef.php", $themedef);

        self::$createdThemes[] = $themename;

        SugarAutoLoader::buildCache();
        SugarThemeRegistry::buildRegistry();

        return $themename;
    }

    public static function createAnonymousCustomTheme(
        $themename = ''
    ) {

        if (empty($themename)) {
            $themename = 'TestThemeCustom' . random_int(0, mt_getrandmax());
        }

        create_custom_directory("themes/$themename/images/");
        create_custom_directory("themes/$themename/css/");
        create_custom_directory("themes/$themename/js/");

        sugar_touch("custom/themes/$themename/css/style.css");
        sugar_touch("custom/themes/$themename/js/style.js");
        sugar_touch("custom/themes/$themename/images/Accounts.gif");
        sugar_touch("custom/themes/$themename/images/fonts.big.icon.gif");

        $themedef = "<?php\n";
        $themedef .= "\$themedef = array(\n";
        $themedef .= "'name'  => 'custom $themename',";
        $themedef .= "'dirName'  => '$themename',";
        $themedef .= "'description' => 'custom $themename',";
        $themedef .= "'version' => array('regex_matches' => array('.*')),";
        $themedef .= ');';
        sugar_file_put_contents("custom/themes/$themename/themedef.php", $themedef);

        self::$createdThemes[] = $themename;

        SugarAutoLoader::buildCache();
        SugarThemeRegistry::buildRegistry();

        return $themename;
    }

    public static function createAnonymousChildTheme(
        $parentTheme
    ) {

        $themename = 'TestThemeChild' . random_int(0, mt_getrandmax());

        sugar_mkdir("themes/$themename/images", null, true);
        sugar_mkdir("themes/$themename/css", null, true);
        sugar_mkdir("themes/$themename/js", null, true);

        sugar_file_put_contents("themes/$themename/css/style.css", 'h3 { display: inline; }');
        sugar_file_put_contents("themes/$themename/css/yui.css", '.yui { display: inline; }');
        sugar_file_put_contents("themes/$themename/js/style.js", 'var bird = "frog";');

        $themedef = "<?php\n";
        $themedef .= "\$themedef = array(\n";
        $themedef .= "'name'  => '$themename',";
        $themedef .= "'dirName' => '$themename',";
        $themedef .= "'parentTheme' => '" . $parentTheme . "',";
        $themedef .= "'description' => '$themename',";
        $themedef .= "'version' => array('regex_matches' => array('.*')),";
        $themedef .= ');';
        sugar_file_put_contents("themes/$themename/themedef.php", $themedef);

        self::$createdThemes[] = $themename;

        SugarAutoLoader::buildCache();
        SugarThemeRegistry::buildRegistry();

        return $themename;
    }

    public static function createAnonymousRTLTheme()
    {
        $themename = 'TestTheme' . random_int(0, mt_getrandmax());

        sugar_mkdir("themes/$themename/images", null, true);
        sugar_mkdir("themes/$themename/css", null, true);
        sugar_mkdir("themes/$themename/js", null, true);
        sugar_mkdir("themes/$themename/tpls", null, true);

        sugar_file_put_contents("themes/$themename/css/style.css", 'h2 { display: inline; }');
        sugar_file_put_contents("themes/$themename/css/yui.css", '.yui { display: inline; }');
        sugar_file_put_contents("themes/$themename/js/style.js", 'var dog = "cat";');
        sugar_touch("themes/$themename/images/Accounts.gif");
        sugar_touch("themes/$themename/images/fonts.big.icon.gif");
        sugar_touch("themes/$themename/tpls/header.tpl");

        $themedef = "<?php\n";
        $themedef .= "\$themedef = array(\n";
        $themedef .= "'name'  => '$themename',";
        $themedef .= "'dirName'  => '$themename',";
        $themedef .= "'description' => '$themename',";
        $themedef .= "'directionality' => 'rtl',";
        $themedef .= "'version' => array('regex_matches' => array('.*')),";
        $themedef .= ');';
        sugar_file_put_contents("themes/$themename/themedef.php", $themedef);

        self::$createdThemes[] = $themename;

        SugarAutoLoader::buildCache();
        SugarThemeRegistry::buildRegistry();

        return $themename;
    }

    public static function removeAllCreatedAnonymousThemes()
    {
        foreach (self::getCreatedThemeNames() as $name) {
            if (is_dir('themes/' . $name)) {
                rmdir_recursive('themes/' . $name);
            }
            if (is_dir('custom/themes/' . $name)) {
                rmdir_recursive('custom/themes/' . $name);
            }
            if (is_dir(sugar_cached('themes/') . $name)) {
                rmdir_recursive(sugar_cached('themes/') . $name);
            }
        }

        SugarAutoLoader::buildCache();
        SugarThemeRegistry::buildRegistry();
    }

    public static function getCreatedThemeNames()
    {
        return self::$createdThemes;
    }
}
