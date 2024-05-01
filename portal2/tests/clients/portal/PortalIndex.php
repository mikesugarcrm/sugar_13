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

// php phpunit.php --group=portal2 portal2/PortalIndex.php

class PortalIndexTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $rootDir;
    public $pathToSidecar;
    public $sugarsidecar;

    public function setUp()
    {
        parent::setUp();
        $this->rootDir = getcwd() . '/tests/tmp';
        $this->pathToSidecar = $this->rootDir . '/cache/include/javascript/';
        $this->sugarsidecar = $this->pathToSidecar . 'sugar_sidecar.min.js';
        sugar_mkdir($this->pathToSidecar, null, true);
    }

    public function tearDown()
    {
        parent::tearDown();
        if (is_file($this->sugarsidecar)) {
            unlink($this->sugarsidecar);
        }
        if (is_dir($this->pathToSidecar)) {
            rmdir($this->pathToSidecar);
        }
    }

    /**
     * @group portal2
     */
    public function testEnsureCacheWhenNoFiles()
    {
        $minifyUtilsMock = $this->createPartialMock('SugarMinifyUtils', ['ConcatenateFiles']);
        $minifyUtilsMock->expects($this->once())
            ->method('ConcatenateFiles')
            ->with($this->rootDir);
        $actual = ensureCache($minifyUtilsMock, $this->rootDir);
        $this->assertEquals($this->sugarsidecar, $actual, 'Should return the path to sidecar min');
    }

    /**
     * @group portal2
     */
    public function testEnsureCacheAlreadySidecarMin()
    {
        sugar_touch($this->sugarsidecar);
        sugar_file_put_contents($this->sugarsidecar, 'adfs');
        $minifyUtilsMock = $this->createPartialMock('SugarMinifyUtils', ['ConcatenateFiles']);
        // We don't call ConcatenateFiles if min file already there
        $minifyUtilsMock->expects($this->never())
            ->method('ConcatenateFiles');
        $actual = ensureCache($minifyUtilsMock, $this->rootDir);
        $this->assertEquals($this->sugarsidecar, $actual, 'Should still return the path to sidecar min');
    }
}
