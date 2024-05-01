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

require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'upgrade/scripts/pre/RemoveInlineHTMLSpacing.php';

/**
 * Test asserts correct removal of inline html in php files under custom directory
 */
class RemoveInlineHTMLSpacingTest extends TestCase
{
    /** @var UpgradeDriver */
    protected $upgradeDriver = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('files');
        $this->upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $this->upgradeDriver->context = [];
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts correct removal of inline HTML
     *
     * @param string $content
     * @param string $expected
     *
     * @dataProvider getContents
     */
    public function testRun($content, $expected)
    {
        $path = sugar_cached(self::class);
        $file = 'custom/' . random_int(1000, 9999) . '/test.php';
        $this->upgradeDriver->context['source_dir'] = $path;
        SugarAutoLoader::ensureDir($path . '/custom');
        SugarTestHelper::saveFile($file);
        sugar_file_put_contents($path . '/' . $file, $content);

        $script = $this->getMockBuilder('SugarUpgradeRemoveInlineHTMLSpacing')
            ->setMethods(['backupFile'])
            ->setConstructorArgs([$this->upgradeDriver])
            ->getMock();
        if ($content == $expected) {
            $script->expects($this->never())->method('backupFile');
        } else {
            $script->expects($this->once())->method('backupFile')->with($this->equalTo($file));
        }
        $script->run();
        $actual = sugar_file_get_contents($path . '/' . $file);
        $this->assertEquals($expected, $actual, 'File trimmed incorrectly');
    }

    /**
     * Returns data for testRun, content and its expected trimmed version
     *
     * @return array
     */
    public static function getContents()
    {
        return [
            [
                '<?php ?>',
                '<?php ?>',
            ],
            [
                "<?php ?>\n",
                "<?php ?>\n",
            ],
            [
                '<?php ?> ',
                '<?php ?>',
            ],

            [
                "<?php ?> \n\r\t\n\r\n",
                '<?php ?>',
            ],
            [
                "<?php \n\r\t\n\r",
                "<?php \n\r\t\n\r",
            ],
            [
                "\n\n<?php ?> ",
                '<?php ?>',
            ],
            [
                "\r\n\r\n\t\t\t<?php ?>\n",
                "<?php ?>\n",
            ],
            [
                "\r\n\r\n\t\t\t<?php ?>\n\n\n\n\n\r",
                '<?php ?>',
            ],
        ];
    }
}
