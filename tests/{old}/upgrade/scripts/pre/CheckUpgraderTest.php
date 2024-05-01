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

require_once 'upgrade/scripts/pre/CheckUpgrader.php';

/**
 * Test asserts different version of upgrade driver
 */
class CheckUpgraderTest extends UpgradeTestCase
{
    protected $script = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->script = $this->upgrader->getScript('pre', 'CheckUpgrader');
    }

    /**
     * Test asserts for upgrader versions
     *
     * @param string $content
     * @param bool $expected
     *
     * @dataProvider getVersions
     */
    public function testRun($content, $expected)
    {
        $this->upgrader->context['versionInfo'] = [$content, '1000'];
        $result = $this->script->run();
        $this->assertEquals($result, $expected);
    }

    /**
     * Returns data for testRun, content and its expected trimmed version
     *
     * @return array
     */
    public static function getVersions()
    {
        return [
            [
                '',
                false,
            ],
            [
                '6.5.20',
                false,
            ],
            [
                '7.6.0.0',
                false,
            ],
            [
                '7.7.0.0',
                true,
            ],
            [
                '7.8.0.0',
                true,
            ],
        ];
    }
}
