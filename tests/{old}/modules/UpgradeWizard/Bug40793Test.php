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

require_once 'modules/UpgradeWizard/uw_utils.php';

/**
 * @ticket 40793
 */
class Bug40793Test extends TestCase
{
    public const WEBALIZER_DIR_NAME = 'bug40793';
    private $notIncludeDir;
    private $includeDir;

    protected function setUp(): void
    {
        $this->notIncludeDir = self::WEBALIZER_DIR_NAME . '/this_dir_should_not_include';
        $this->includeDir = self::WEBALIZER_DIR_NAME . '/1';
        mkdir(self::WEBALIZER_DIR_NAME, 0755);
        mkdir($this->notIncludeDir, 0755);
        mkdir($this->includeDir, 0755);
    }

    protected function tearDown(): void
    {
        rmdir($this->notIncludeDir);
        rmdir($this->includeDir);
        rmdir(self::WEBALIZER_DIR_NAME);
    }

    public function testIfDirIsNotIncluded()
    {
        $skipDirs = [$this->notIncludeDir];
        $files = uwFindAllFiles(self::WEBALIZER_DIR_NAME, [], true, $skipDirs);
        $this->assertNotContains($this->notIncludeDir, $files, "Directory {$this->notIncludeDir} shouldn't been included in this list");
        $this->assertContains($this->includeDir, $files, "Directory {$this->includeDir} should been included in this list");
    }
}
