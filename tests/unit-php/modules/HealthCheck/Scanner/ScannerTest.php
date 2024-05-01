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

namespace Sugarcrm\SugarcrmTestsUnit\modules\HealthCheck\Scanner;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

require_once 'modules/HealthCheck/Scanner/Scanner.php';

/**
 * @coversDefaultClass HealthCheckScanner
 */
class ScannerTest extends TestCase
{
    public function scanFileForIncompatibleInheritanceProvider()
    {
        return [
            'no classes' => [
                <<<'EOT'
                <?php
                echo 123;
                EOT,
                0,
            ],
            'class without inheritance' => [
                <<<'EOT'
                <?php
                class qwe {}
                EOT,
                0,
            ],
            'class with no rules for parent' => [
                <<<'EOT'
                <?php
                class qwe extends SugarBean {}
                EOT,
                0,
            ],
            'class with rule but no methods' => [
                <<<'EOT'
                <?php
                use Monolog\Formatter\LineFormatter;
                class qwe extends LineFormatter {}
                EOT,
                0,
            ],
            'class with rule and valid method' => [
                <<<'EOT'
                <?php
                use Monolog\Formatter\LineFormatter;
                class qwe extends LineFormatter {
                    public function format(array $record): string {}
                }
                EOT,
                0,
            ],
            'class with rule and invalid method 1' => [
                <<<'EOT'
                <?php
                use Monolog\Formatter\LineFormatter;
                class qwe extends LineFormatter {
                    public function format(array $record) {}
                }
                EOT,
                1,
            ],
            'class with rule and invalid method 2' => [
                <<<'EOT'
                <?php
                use Monolog\Formatter\LineFormatter;
                class qwe extends LineFormatter {
                    public function format(array $record): int {}
                    public function formatBatch(array $record): int {}
                }
                EOT,
                1,
            ],
        ];
    }

    /**
     * @dataProvider scanFileForIncompatibleInheritanceProvider
     * @param $code
     */
    public function testScanFileForIncompatibleInheritance(string $code, int $expected): void
    {
        $hc = $this->getMockBuilder(\HealthCheckScanner::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getVersionAndFlavor'])
            ->getMock();
        $hc->method('getVersionAndFlavor')->willReturn(['12.2.0', '']);

        $hc->scanFileForIncompatibleInheritance('nonexistentpath', $code);

        $files = TestReflection::getProtectedValue($hc, 'filesWithIncompatibleInheritance');
        $this->assertEquals($expected, safeCount($files));
    }

    public function testScanFileForIncompatibleInheritance_ParseError()
    {
        $hc = $this->getMockBuilder(\HealthCheckScanner::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateStatus', 'getVersionAndFlavor'])
            ->getMock();
        $hc->method('getVersionAndFlavor')->willReturn(['12.2.0', '']);
        $hc->expects($this->once())->method('updateStatus')->with($this->equalTo('phpError'));

        $hc->scanFileForIncompatibleInheritance('nonexistentpath', '<?php $$$$');
    }
}
