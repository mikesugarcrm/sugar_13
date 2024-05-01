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
use Sugarcrm\Sugarcrm\Util\Uuid;

require_once 'include/utils/file_utils.php';

class FilePutContentsAtomicTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUpFiles();
    }

    /**
     * @test
     */
    public function fileIsReadableByWebServer()
    {
        global $sugar_config;

        $uuid = Uuid::uuid4();

        $path = sprintf('%s/%s.php', SUGAR_BASE_DIR, $uuid);
        SugarTestHelper::saveFile($path);

        sugar_file_put_contents_atomic(
            $path,
            <<<'PHP'
<?php

echo 'Test';
PHP
        );

        $url = sprintf('%s/%s.php', $sugar_config['site_url'], $uuid);
        $this->assertEquals('Test', file_get_contents($url));
    }
}
