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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SugarMinifyUtilsTest extends TestCase
{
    /**
     * The file that is built by this process
     *
     * @var string
     */
    protected $builtFile = 'include/javascript/unit_test_built.min.js';

    /** {@inheritDoc} */
    protected function setUp(): void
    {
        SugarTestHelper::saveFile(sugar_cached($this->builtFile));
    }

    public function testConcatenateFiles()
    {
        global $sugar_config;
        $sugar_config['minify_resources'] = true;
        $sugar_config['developerMode'] = false;

        /** @var SugarMinifyUtils|MockObject $minifier */
        $minifier = $this->getMockBuilder('SugarMinifyUtils')
            ->setMethods(['getJSGroupings'])
            ->getMock();
        $minifier->expects($this->any())
            ->method('getJSGroupings')
            ->willReturn([
                [
                    'jssource/minify/test/var.js' => $this->builtFile,
                    'jssource/minify/test/if.js' => $this->builtFile,
                ],
            ]);

        $minifier->ConcatenateFiles('tests/{old}');

        // Test the file was created
        $this->assertFileExists(sugar_cached($this->builtFile));

        // Test the contents of the file. Using contains instead of equals so
        // systems without JSMin won't fail hard
        $content = file_get_contents(sugar_cached($this->builtFile));
        $expect1 = file_get_contents('tests/{old}/jssource/minify/expect/var.js');
        $expect2 = file_get_contents('tests/{old}/jssource/minify/expect/if.js');
        $this->assertStringContainsString($expect1, $content);
        $this->assertStringContainsString($expect2, $content);
    }
}
