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

require_once __DIR__ . '/../../../../modules/UpgradeWizard/pack_cli.php';

class PackCliTest extends TestCase
{
    protected function setUp(): void
    {
        // if shadow is detected, we need to skip this test as it doesn't play nice with shadow
        if (extension_loaded('shadow')) {
            $this->markTestSkipped('Does not work on Shadow');
        }
    }

    public function packUpgradeWizardCliProvider()
    {
        return [
            [
                [
                    'version' => '1.2.3.4',
                ],
                [
                    'version' => '1.2.3.4',
                    'build' => '998',
                ],
            ],
            [
                [],
                [
                    'version' => '7.6.1.0',
                    'build' => '998',
                ],
            ],
            [
                [
                    'build' => '1.2.3.4',
                ],
                [
                    'version' => '7.6.1.0',
                    'build' => '1.2.3.4',
                ],
            ],
        ];
    }

    /**
     * @dataProvider packUpgradeWizardCliProvider
     * @param $params
     * @param $expect
     */
    public function testPackUpgradeWizardCli($params, $expect)
    {
        $zip = $this->createMock('ZipArchive');
        $versionFile = __DIR__ . '/../../../../modules/UpgradeWizard/version.json';
        packUpgradeWizardCli($zip, $params);

        $this->assertEquals(json_encode($expect), file_get_contents($versionFile));
        unlink($versionFile);
    }

    public function testPackCliPhp()
    {
        if (is_windows()) {
            $this->markTestSkipped('Skipping on Windows - PHP_BINDIR bug');
        }
        $result = exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../../modules/UpgradeWizard/pack_cli.php');
        $this->assertEquals(
            'Use ' . __DIR__ . '/../../../../modules/UpgradeWizard/pack_cli.php name (no zip or phar extension) [sugarVersion [buildNumber]]',
            $result
        );
        if (ini_get('phar.readonly')) {
            $this->markTestSkipped('Disable phar.readonly to run this test');
        }
        $zip = tempnam('/tmp', 'test');
        exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../../modules/UpgradeWizard/pack_cli.php ' . $zip);
        $this->assertTrue(file_exists($zip . '.zip'));
        unlink($zip);
    }
}
