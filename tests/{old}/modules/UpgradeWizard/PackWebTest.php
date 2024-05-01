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

require_once __DIR__ . '/../../../../modules/UpgradeWizard/pack_web.php';

class PackWebTest extends TestCase
{
    protected function setUp(): void
    {
        // if shadow is detected, we need to skip this test as it doesn't play nice with shadow
        if (extension_loaded('shadow')) {
            $this->markTestSkipped('Does not work on Shadow');
        }
    }

    public function packUpgradeWizardWebProvider()
    {
        return [
            [
                [
                    'version' => '1.2.3.4',
                ],
                [
                    'version' => '1.2.3.4',
                    'build' => '998',
                    'from' => ['6.5.17'],
                ],
            ],
            [
                [],
                [
                    'version' => '7.5.0.0',
                    'build' => '998',
                    'from' => ['6.5.17'],
                ],
            ],
            [
                [
                    'from' => ['1.2.3.4', '1.2.3.5'],
                ],
                [
                    'version' => '7.5.0.0',
                    'build' => '998',
                    'from' => ['1.2.3.4', '1.2.3.5'],
                ],
            ],
            [
                [
                    'build' => '1.2.3.4',
                ],
                [
                    'version' => '7.5.0.0',
                    'build' => '1.2.3.4',
                    'from' => ['6.5.17'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider packUpgradeWizardWebProvider
     * @param $params
     * @param $expect
     */
    public function testPackUpgradeWizardWeb($params, $expect)
    {
        $manifest = [];
        $zip = $this->createMock('ZipArchive');
        $versionFile = __DIR__ . '/../../../../modules/UpgradeWizard/version.json';
        $zip->expects($this->exactly(14))->method('addFile');
        $zip->expects($this->exactly(2))->method('addFromString');
        $installdefs = [];
        [$zip, $manifest, $installdefs] = packUpgradeWizardWeb($zip, $manifest, $installdefs, $params);

        $this->assertEquals(json_encode($expect), file_get_contents($versionFile));
        $this->assertArrayHasKey('version', $manifest);
        $this->assertEquals($expect['version'], $manifest['version']);
        $this->assertArrayHasKey('acceptable_sugar_versions', $manifest);
        $this->assertEquals($expect['from'], $manifest['acceptable_sugar_versions']);
        $this->assertArrayHasKey('copy', $installdefs);
        $this->assertArrayHasKey(0, $installdefs['copy']);
        $this->assertEquals('<basepath>/UpgradeWizard.php', $installdefs['copy'][0]['from']);
        $this->assertEquals('UpgradeWizard.php', $installdefs['copy'][0]['to']);
        unlink($versionFile);
    }

    public function testPackWebPhp()
    {
        if (is_windows()) {
            $this->markTestSkipped('Skipping on Windows - PHP_BINDIR bug');
        }
        $result = exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../../modules/UpgradeWizard/pack_web.php');
        $this->assertEquals(
            'Use ' . __DIR__ . '/../../../../modules/UpgradeWizard/pack_web.php name.zip [sugarVersion [buildNumber [from]]]',
            $result
        );
        $zip = tempnam('/tmp', 'zip') . '.zip';
        exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../../modules/UpgradeWizard/pack_web.php ' . $zip);
        $this->assertTrue(file_exists($zip));
        unlink($zip);
    }
}
