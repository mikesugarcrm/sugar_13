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
require_once 'include/dir_inc.php';

class CreateCacheDirectoryTest extends TestCase
{
    private $original_cwd = '';

    protected function setUp(): void
    {
        global $sugar_config;
        $this->original_cwd = getcwd();
        $sugar_config['cache_dir'] = 'cache/';
        chdir(__DIR__);
        rmdir_recursive('./cache');
    }

    protected function tearDown(): void
    {
        $sugar_config = [];
        rmdir_recursive('./cache');
        chdir($this->original_cwd);
        $sugar_config['cache_dir'] = $this->original_cwd;
    }

    public function testCreatesCacheDirectoryIfDoesnotExist()
    {
        $this->assertFalse(file_exists('./cache'), 'check that the cache directory does not exist');
        create_cache_directory('foobar');
        $this->assertTrue(file_exists('./cache'), 'creates a cache directory');
    }

    public function testCreatesDirectoryInCacheDirectoryProvidedItIsGivenAFile()
    {
        $this->assertFalse(file_exists('./cache/foobar-test'));
        create_cache_directory('foobar-test/cache-file.php');
        $this->assertTrue(file_exists('./cache/foobar-test'));
    }

    public function testReturnsDirectoryCreated()
    {
        $created = create_cache_directory('foobar/cache-file.php');
        $this->assertEquals(
            'cache/foobar/cache-file.php',
            $created
        );
    }
}
