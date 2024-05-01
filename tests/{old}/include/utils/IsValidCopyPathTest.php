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

/**
 * Can't use 'covers' on functions.
 * covers isValidCopyPath
 */
class IsValidCopyPathTest extends TestCase
{
    /**
     * @dataProvider isValidCopyPathSuccessProvider
     */
    public function testIsValidCopyPathSuccess($path)
    {
        $actual = isValidCopyPath($path);
        $this->assertTrue($actual);
    }

    public static function isValidCopyPathSuccessProvider()
    {
        return [
            'unix-path' => ['a/b/c'],
            'windows-path' => ['a\\b\\c'],
            'directory-name-contains-dots' => ['a/b..c/d'],
        ];
    }

    /**
     * @dataProvider isValidCopyPathFailureProvider
     */
    public function testIsValidCopyPathFailure($path)
    {
        $actual = isValidCopyPath($path);
        $this->assertFalse($actual);
    }

    public static function isValidCopyPathFailureProvider()
    {
        return [
            'absolute-unix' => ['/etc/passwd'],
            'absolute-windows' => ['\\Windows\\Whatever'],
            'parent-directory-beginning' => ['../some/other/instance'],
            'parent-directory-middle' => ['some/../other/instance'],
            'parent-directory-end' => ['some/other/instance/..'],
            'empty' => [''],
        ];
    }

    /**
     * @dataProvider isValidCopyPathFailureOnWindowsProvider
     *
     * @requires OSFAMILY Windows
     */
    public function testIsValidCopyPathFailureOnWindows($path)
    {
        $actual = isValidCopyPath($path);
        $this->assertFalse($actual);
    }

    public static function isValidCopyPathFailureOnWindowsProvider()
    {
        return [
            'drive-letter' => ['C:\\Windows\\Whatever'],
        ];
    }
}
