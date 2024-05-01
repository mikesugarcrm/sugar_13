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

require_once 'include/dir_inc.php';

class SugarThemeTest extends TestCase
{
    private $themeDef;
    /**
     * @var SugarTheme
     */
    private $themeObject;
    private $themeDefChild;
    private $themeObjectChild;

    protected function setUp(): void
    {
        $themedef = [];
        include 'themes/' . SugarTestThemeUtilities::createAnonymousTheme() . '/themedef.php';

        $this->themeDef = $themedef;
        SugarThemeRegistry::add($this->themeDef);
        $this->themeObject = SugarThemeRegistry::get($this->themeDef['dirName']);

        $themedef = [];
        include 'themes/' . SugarTestThemeUtilities::createAnonymousChildTheme($this->themeObject->__toString()) . '/themedef.php';

        $this->themeDefChild = $themedef;
        SugarThemeRegistry::add($this->themeDefChild);
        $this->themeObjectChild = SugarThemeRegistry::get($this->themeDefChild['dirName']);

        $GLOBALS['sugar_config']['developerMode'] = false;
        $GLOBALS['sugar_config']['minify_resources'] = true;
    }

    public function testMagicIssetWorks()
    {
        $this->assertTrue(isset($this->themeObject->dirName));
    }

    protected function tearDown(): void
    {
        SugarTestThemeUtilities::removeAllCreatedAnonymousThemes();
    }

    public function testCaching()
    {
        $this->themeObject->getCSSURL('style.css');
        $themename = $this->themeObject->__toString();
        $pathname = "themes/{$themename}/css/style.css";

        // test if it's in the local cache
        $this->assertTrue(isset($this->themeObject->_cssCache['style.css']));
        $this->assertEquals($pathname, $this->themeObject->_cssCache['style.css']);

        // destroy object
        $this->themeObject->__destruct();
        unset($this->themeObject);

        // now recreate object
        SugarThemeRegistry::add($this->themeDef);
        $this->themeObject = SugarThemeRegistry::get($this->themeDef['dirName']);

        // should still be in local cache
        $this->assertTrue(isset($this->themeObject->_cssCache['style.css']));
        $this->assertEquals($pathname, $this->themeObject->_cssCache['style.css']);

        // now, let's tell the theme we want to clear the cache on destroy
        $this->themeObject->clearCache();

        // destroy object
        $this->themeObject->__destruct();
        unset($this->themeObject);

        // now recreate object
        SugarThemeRegistry::add($this->themeDef);
        $this->themeObject = SugarThemeRegistry::get($this->themeDef['dirName']);

        // should not be in local cache
        $this->assertFalse(isset($this->themeObject->_cssCache['style.css']));
    }

    public function testClearImageCache()
    {
        // populate image cache first
        $this->themeObject->getAllImages();
        $this->themeObject->clearImageCache();

        $this->assertCount(0, SugarTestReflection::getProtectedValue($this->themeObject, '_imageCache'));
    }

    public function testCreateInstance()
    {
        foreach ($this->themeDef as $key => $value) {
            $this->assertEquals($this->themeObject->$key, $value);
        }
    }

    public function testGetFilePath()
    {
        $this->assertEquals(
            $this->themeObject->getFilePath(),
            'themes/' . $this->themeDef['name']
        );
    }

    public function testGetImagePath()
    {
        $this->assertEquals(
            $this->themeObject->getImagePath(),
            'themes/' . $this->themeDef['name'] . '/images'
        );
    }

    public function testGetCSSPath()
    {
        $this->assertEquals(
            $this->themeObject->getCSSPath(),
            'themes/' . $this->themeDef['name'] . '/css'
        );
    }

    public function testGetCSS()
    {
        $matches = [];
        preg_match_all('/href="([^"]+)"/', $this->themeObject->getCSS(), $matches);
        $i = 0;
        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObject->__toString() . '\/css\/yui.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/include\/javascript\/jquery\/themes\/base\/jquery-ui.min\.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObject->__toString() . '\/css\/deprecated.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObject->__toString() . '\/css\/style.css/',
            $matches[1][$i++]
        );

        $output = file_get_contents(sugar_cached('themes/') . $this->themeObject->__toString() . '/css/style.css');
        $this->assertMatchesRegularExpression('/h2\{display:inline\}/', $output);
    }

    public function testGetCSSWithParams()
    {
        $matches = [];
        preg_match_all('/href="([^"]+)"/', $this->themeObject->getCSS('blue', 'small'), $matches);
        $i = 0;
        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObject->__toString() . '\/css\/yui.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/include\/javascript\/jquery\/themes\/base\/jquery-ui.min\.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObject->__toString() . '\/css\/deprecated.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObject->__toString() . '\/css\/style.css/',
            $matches[1][$i++]
        );

        $output = file_get_contents(sugar_cached('themes/') . $this->themeObject->__toString() . '/css/style.css');
        $this->assertMatchesRegularExpression('/h2\{display:inline\}/', $output);
    }

    public function testGetCSSWithCustomStyleCSS()
    {
        create_custom_directory('themes/' . $this->themeObject->__toString() . '/css/');
        sugar_file_put_contents('custom/themes/' . $this->themeObject->__toString() . '/css/style.css', 'h3 { color: red; }');

        $matches = [];
        preg_match_all('/href="([^"]+)"/', $this->themeObject->getCSS(), $matches);
        $i = 0;

        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObject->__toString() . '\/css\/yui.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/include\/javascript\/jquery\/themes\/base\/jquery-ui.min\.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObject->__toString() . '\/css\/deprecated.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObject->__toString() . '\/css\/style.css/',
            $matches[1][$i++]
        );

        $output = file_get_contents(sugar_cached('themes/') . $this->themeObject->__toString() . '/css/style.css');
        $this->assertMatchesRegularExpression('/h2\{display:inline\}h3\{color:red\}/', $output);
    }

    public function testGetCSSWithParentTheme()
    {
        $matches = [];
        preg_match_all('/href="([^"]+)"/', $this->themeObjectChild->getCSS(), $matches);
        $i = 0;

        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObjectChild->__toString() . '\/css\/yui.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/include\/javascript\/jquery\/themes\/base\/jquery-ui.min\.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObjectChild->__toString() . '\/css\/deprecated.css/',
            $matches[1][$i++]
        );
        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObjectChild->__toString() . '\/css\/style.css/',
            $matches[1][$i++]
        );

        $output = file_get_contents(sugar_cached('themes/') . $this->themeObjectChild->__toString() . '/css/style.css');
        $this->assertMatchesRegularExpression('/h2\{display:inline\}h3\{display:inline\}/', $output);
    }

    public function testGetCSSURLWithInvalidFileSpecifed()
    {
        $this->assertFalse($this->themeObject->getCSSURL('ThisFileDoesNotExist.css'));
    }

    public function testGetCSSURLAddsJsPathIfSpecified()
    {
        // check one may not hit cache
        $this->assertMatchesRegularExpression(
            '/style\.css\?/',
            $this->themeObject->getCSSURL('style.css')
        );

        // check two definitely should hit cache
        $this->assertMatchesRegularExpression(
            '/style\.css\?/',
            $this->themeObject->getCSSURL('style.css')
        );

        // check three for the jspath not being added
        $this->assertStringNotContainsString(
            '?',
            $this->themeObject->getCSSURL('style.css', false)
        );
    }

    public function testGetJS()
    {
        $matches = [];
        preg_match_all('/src="([^"]+)"/', $this->themeObject->getJS(), $matches);
        $i = 0;

        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObject->__toString() . '\/js\/style-min.js/',
            $matches[1][$i++]
        );

        $output = file_get_contents(sugar_cached('themes/') . $this->themeObject->__toString() . '/js/style-min.js');
        $this->assertMatchesRegularExpression('/var dog="cat";/', $output);
    }

    public function testGetJSCustom()
    {
        create_custom_directory('themes/' . $this->themeObject->__toString() . '/js/');
        file_put_contents('custom/themes/' . $this->themeObject->__toString() . '/js/style.js', 'var x = 1;');

        $matches = [];
        preg_match_all('/src="([^"]+)"/', $this->themeObject->getJS(), $matches);
        $i = 0;

        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObject->__toString() . '\/js\/style-min.js/',
            $matches[1][$i++]
        );

        $output = file_get_contents(sugar_cached('themes/') . $this->themeObject->__toString() . '/js/style-min.js');
        $this->assertMatchesRegularExpression('/var dog="cat";/', $output);
        $this->assertMatchesRegularExpression('/var x=1;/', $output);
    }

    public function testGetJSWithParentTheme()
    {
        $matches = [];
        preg_match_all('/src="([^"]+)"/', $this->themeObjectChild->getJS(), $matches);
        $i = 0;

        $this->assertMatchesRegularExpression(
            '/themes\/' . $this->themeObjectChild->__toString() . '\/js\/style-min.js/',
            $matches[1][$i++]
        );

        $output = file_get_contents(sugar_cached('themes/') . $this->themeObjectChild->__toString() . '/js/style-min.js');
        $this->assertMatchesRegularExpression('/var dog="cat";var bird="frog";/', $output);
    }

    public function testGetJSURLWithInvalidFileSpecifed()
    {
        $this->assertFalse($this->themeObject->getJSURL('ThisFileDoesNotExist.js'));
    }

    public function testGetJSURLAddsJsPathIfSpecified()
    {
        // check one may not hit cache
        $this->assertMatchesRegularExpression(
            '/style-min\.js\?/',
            $this->themeObject->getJSURL('style.js')
        );

        // check two definitely should hit cache
        $this->assertMatchesRegularExpression(
            '/style-min\.js\?/',
            $this->themeObject->getJSURL('style.js')
        );

        // check three for the jspath not being added
        $this->assertStringNotContainsString(
            '?',
            $this->themeObject->getJSURL('style.js', false)
        );
    }

    public function testGetImageURL()
    {
        $this->assertEquals(
            'themes/' . $this->themeObject->__toString() . '/images/Accounts.gif',
            $this->themeObject->getImageURL('Accounts.gif', false)
        );
    }

    public function testGetImageURLWithInvalidFileSpecifed()
    {
        $this->assertFalse($this->themeObject->getImageURL('ThisFileDoesNotExist.gif'));
    }

    public function testGetImageURLCustom()
    {
        create_custom_directory('themes/' . $this->themeObject->__toString() . '/images/');
        sugar_touch('custom/themes/' . $this->themeObject->__toString() . '/images/Accounts.gif');

        $this->assertEquals(
            'custom/themes/' . $this->themeObject->__toString() . '/images/Accounts.gif',
            $this->themeObject->getImageURL('Accounts.gif', false)
        );
    }

    public function testGetImageURLCustomDifferentExtension()
    {
        create_custom_directory('themes/' . $this->themeObject->__toString() . '/images/');
        sugar_touch('custom/themes/' . $this->themeObject->__toString() . '/images/Accounts.png');
        $this->assertEquals(
            'custom/themes/' . $this->themeObject->__toString() . '/images/Accounts.png',
            $this->themeObject->getImageURL('Accounts.gif', false)
        );
    }

    public function testGetImageURLDefault()
    {
        $this->assertEquals('themes/default/images/Emails.gif', $this->themeObject->getImageURL('Emails.gif', false));
    }

    public function testGetImageURLDefaultCustom()
    {
        create_custom_directory('themes/default/images/');
        sugar_touch('custom/themes/default/images/Emails.gif');

        $this->assertEquals(
            'custom/themes/default/images/Emails.gif',
            $this->themeObject->getImageURL('Emails.gif', false)
        );

        unlink('custom/themes/default/images/Emails.gif');
    }

    public function testGetImageURLNotFound()
    {
        $this->assertEquals('', $this->themeObject->getImageURL('NoImageByThisName.gif', false));
    }

    public function testGetImageURLAddsJsPathIfSpecified()
    {
        // check one may not hit cache
        $this->assertMatchesRegularExpression(
            '/Accounts\.gif\?/',
            $this->themeObject->getImageURL('Accounts.gif')
        );

        // check two definitely should hit cache
        $this->assertMatchesRegularExpression(
            '/Accounts\.gif\?/',
            $this->themeObject->getImageURL('Accounts.gif')
        );

        // check three for the jspath not being added
        $this->assertStringNotContainsString(
            '?',
            $this->themeObject->getImageURL('Accounts.gif', false)
        );
    }

    public function testGetImageURLWithParentTheme()
    {
        $this->assertEquals(
            'themes/' . $this->themeObject->__toString() . '/images/Accounts.gif',
            $this->themeObjectChild->getImageURL('Accounts.gif', false)
        );
    }

    public function testGetTemplate()
    {
        $this->assertEquals(
            'themes/' . $this->themeObject->__toString() . '/tpls/header.tpl',
            $this->themeObject->getTemplate('header.tpl')
        );
    }

    public function testGetTemplateCustom()
    {
        create_custom_directory('themes/' . $this->themeObject->__toString() . '/tpls/');
        sugar_touch('custom/themes/' . $this->themeObject->__toString() . '/tpls/header.tpl');

        $this->assertEquals(
            'custom/themes/' . $this->themeObject->__toString() . '/tpls/header.tpl',
            $this->themeObject->getTemplate('header.tpl')
        );
    }

    public function testGetTemplateDefaultCustom()
    {
        create_custom_directory('themes/default/tpls/');
        sugar_touch('custom/themes/default/tpls/SomeDefaultTemplate.tpl');

        $this->assertEquals(
            'custom/themes/default/tpls/SomeDefaultTemplate.tpl',
            $this->themeObject->getTemplate('SomeDefaultTemplate.tpl')
        );

        unlink('custom/themes/default/tpls/SomeDefaultTemplate.tpl');
    }

    public function testGetTemplateWithParentTheme()
    {
        $this->assertEquals(
            'themes/' . $this->themeObject->__toString() . '/tpls/header.tpl',
            $this->themeObjectChild->getTemplate('header.tpl')
        );
    }

    public function testGetTemplateNotFound()
    {
        $this->assertFalse($this->themeObject->getTemplate('NoTemplateWithThisName.tpl'));
    }

    public function testGetAllImages()
    {
        $images = $this->themeObject->getAllImages();

        $this->assertEquals(
            $this->themeObject->getImageURL('Emails.gif', false),
            $images['Emails.gif']
        );
    }

    public function testGetAllImagesWhenImageIsInParentTheme()
    {
        $images = $this->themeObjectChild->getAllImages();

        $this->assertEquals(
            $this->themeObjectChild->getImageURL('Accounts.gif', false),
            $images['Accounts.gif']
        );

        $this->assertStringContainsString(
            $this->themeObject->getImagePath(),
            $images['Accounts.gif']
        );
    }

    public function testGetImageSpecifyingWidthAndHeightAndOtherAttributes()
    {
        $this->assertEquals(
            $this->themeObject->getImage('Emails', '', 20, 30, '.gif', 'Emails'),
            '<img src="' . $this->themeObject->getImageURL('Emails.gif') . '"  width="20" height="30"  alt="Emails" />'
        );

        // check again to see if caching of the image size works as expected
        $this->assertEquals(
            $this->themeObject->getImage('Emails', '', 30, 40, '.gif', 'Emails'),
            '<img src="' . $this->themeObject->getImageURL('Emails.gif') . '"  width="30" height="40"  alt="Emails" />'
        );
    }

    public function testGetImageWithInvalidImage()
    {
        $this->assertFalse($this->themeObject->getImage('ThisImageDoesNotExist'));
    }
}
