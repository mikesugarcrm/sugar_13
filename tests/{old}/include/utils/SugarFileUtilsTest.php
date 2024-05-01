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

require_once 'include/utils/file_utils.php';

class SugarFileUtilsTest extends TestCase
{
    private $filename;
    private $oldDefaultPermissions;
    private $testDirectory;

    protected function setUp(): void
    {
        if (is_windows()) {
            $this->markTestSkipped('Skipping on Windows');
        }

        $this->filename = realpath(__DIR__ . '/../../../cache/') . 'file_utils_override' . random_int(0, mt_getrandmax()) . '.txt';
        touch($this->filename);
        $this->oldDefaultPermissions = $GLOBALS['sugar_config']['default_permissions'];
        $GLOBALS['sugar_config']['default_permissions'] = [
            'dir_mode' => 0777,
            'file_mode' => 0660,
            'user' => $this->getCurrentUser(),
            'group' => $this->getCurrentGroup(),
        ];

        $this->testDirectory = $GLOBALS['sugar_config']['cache_dir'] . md5($GLOBALS['sugar_config']['cache_dir']) . '/';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }

        $this->recursiveRmdir($this->testDirectory);

        $GLOBALS['sugar_config']['default_permissions'] = $this->oldDefaultPermissions;
        SugarConfig::getInstance()->clearCache();
    }

    private function getCurrentUser()
    {
        if (function_exists('posix_getuid')) {
            return posix_getuid();
        }
        return '';
    }

    private function getCurrentGroup()
    {
        if (function_exists('posix_getgid')) {
            return posix_getgid();
        }
        return '';
    }

    public function testSugarTouch()
    {
        $this->assertTrue(sugar_touch($this->filename));
    }

    public function testSugarTouchWithTime()
    {
        $time = filemtime($this->filename);

        $this->assertTrue(sugar_touch($this->filename, $time));

        $this->assertEquals($time, filemtime($this->filename));
    }

    public function testSugarTouchWithAccessTime()
    {
        $time = filemtime($this->filename);
        $atime = time();

        $this->assertTrue(sugar_touch($this->filename, $time, $atime));

        $this->assertEquals($time, filemtime($this->filename));
        $this->assertEquals($atime, fileatime($this->filename));
    }

    public function testSugarChmodDefaultModeNotAnInteger()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = '';
        $this->assertFalse(sugar_chmod($this->filename));
    }

    public function testSugarChmodDefaultModeIsZero()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = 0;
        $this->assertFalse(sugar_chmod($this->filename));
    }

    public function testSugarChown()
    {
        if ($GLOBALS['sugar_config']['default_permissions']['user'] == '') {
            $this->markTestSkipped('Can not get UID. Posix extension is required.');
        }
        $this->assertTrue(sugar_chown($this->filename));
        $this->assertEquals(fileowner($this->filename), $this->getCurrentUser());
    }

    /**
     * @requires function posix_getuid
     */
    public function testSugarChownWithUser()
    {
        $this->assertTrue(sugar_chown($this->filename, $this->getCurrentUser()));
        $this->assertEquals(fileowner($this->filename), $this->getCurrentUser());
    }

    public function testSugarChownNoDefaultUser()
    {
        $GLOBALS['sugar_config']['default_permissions']['user'] = '';

        $this->assertFalse(sugar_chown($this->filename));
    }

    /**
     * @requires function posix_getuid
     */
    public function testSugarChownWithUserNoDefaultUser()
    {
        $GLOBALS['sugar_config']['default_permissions']['user'] = '';

        $this->assertTrue(sugar_chown($this->filename, $this->getCurrentUser()));

        $this->assertEquals(fileowner($this->filename), $this->getCurrentUser());
    }

    public function testSugarTouchDirectoryCreation()
    {
        $this->recursiveRmdir($this->testDirectory);

        $this->assertEquals(false, is_dir($this->testDirectory), 'Directory exists, though we removed it');

        $file = $this->testDirectory . md5($this->testDirectory);
        sugar_touch($file);

        $this->assertFileExists($file, 'File should be created together with directory');
    }

    private function recursiveRmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir . '/' . $object) == 'dir') {
                        $this->recursiveRmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
