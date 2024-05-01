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

require_once 'include/utils.php';

class SortExtensionFilesTest extends TestCase
{
    public static $test_directory = 'custom/sort_extension_files_test/';

    public static function setUpBeforeClass(): void
    {
        create_custom_directory('sort_extension_files_test');

        sugar_touch(self::getTestFilePath('file1.php'));
        sugar_touch(self::getTestFilePath('file2.php'));
        sugar_touch(self::getTestFilePath('file3.php'));
        sugar_touch(self::getTestFilePath('_overridefile3.php'));
    }

    public static function tearDownAfterClass(): void
    {
        if (is_dir(self::$test_directory)) {
            rmdir_recursive(self::$test_directory);
        }
    }

    /**
     * Wraps filename with full test filepath
     * @param string $filename Filename
     * @return string Filepath
     */
    public static function getTestFilePath($filename)
    {
        return self::$test_directory . $filename;
    }

    /**
     * @dataProvider providerSortExtensionFiles
     */
    public function testSortExtensionFiles($files_for_test, $expected_result)
    {
        $timestamp = time();
        $files = [];

        foreach ($files_for_test as $file) {
            sugar_touch(self::getTestFilePath($file['filename']), $timestamp + $file['time_diff']);
            $files[] = self::getTestFilePath($file['filename']);
        }

        foreach ($expected_result as $id => $file) {
            $expected_result[$id] = self::getTestFilePath($file);
        }

        $files_sorted = sortExtensionFiles($files);

        $this->assertEquals($expected_result, $files_sorted);
    }

    public static function providerSortExtensionFiles()
    {
        return [
            [
                'files_for_test' => [
                    [
                        'filename' => 'file1.php',
                        'time_diff' => 2,
                    ],
                    [
                        'filename' => 'file2.php',
                        'time_diff' => 0,
                    ],
                    [
                        'filename' => 'file3.php',
                        'time_diff' => 1,
                    ],
                    [
                        'filename' => '_overridefile3.php',
                        'time_diff' => 0,
                    ],
                ],
                'expected_result' => [
                    'file2.php',
                    'file3.php',
                    'file1.php',
                    '_overridefile3.php',
                ],
            ],
            [
                'files_for_test' => [
                    [
                        'filename' => 'file1.php',
                        'time_diff' => 0,
                    ],
                    [
                        'filename' => 'file2.php',
                        'time_diff' => 0,
                    ],
                    [
                        'filename' => 'file3.php',
                        'time_diff' => 0,
                    ],
                ],
                'expected_result' => [
                    'file1.php',
                    'file2.php',
                    'file3.php',
                ],
            ],
            [
                'files_for_test' => [
                    [
                        'filename' => 'file1.php',
                        'time_diff' => 2,
                    ],
                    [
                        'filename' => 'file2.php',
                        'time_diff' => 2,
                    ],
                    [
                        'filename' => 'file3.php',
                        'time_diff' => 1,
                    ],
                ],
                'expected_result' => [
                    'file3.php',
                    'file1.php',
                    'file2.php',
                ],
            ],
            [
                'files_for_test' => [
                    [
                        'filename' => 'file1.php',
                        'time_diff' => 2,
                    ],
                    [
                        'filename' => 'file2.php',
                        'time_diff' => 0,
                    ],
                    [
                        'filename' => 'file3.php',
                        'time_diff' => 1,
                    ],
                ],
                'expected_result' => [
                    'file2.php',
                    'file3.php',
                    'file1.php',
                ],
            ],
        ];
    }
}
