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
require_once 'upgrade/scripts/post/3_UpgradeAccess.php';
require_once 'install/install_utils.php';


class PostUpgradeAccessTest extends UpgradeTestCase
{
    protected $testHtacessPath = 'tests/{old}/upgrade/scripts/post/.htaccess';
    protected $test_site_url = '/foo123';
    protected $original_site_url;

    /**
     * @var SugarUpgradeUpgradeAccessTest
     */
    private $upgradeAccess;

    protected function setUp(): void
    {
        global $sugar_config;
        parent::setUp();
        $this->original_site_url = $sugar_config['site_url'];
        $sugar_config['site_url'] = $this->test_site_url;
        if (empty($this->upgrader->config)) {
            $this->upgrader->config = [];
        }
        $this->upgrader->config['site_url'] = $this->test_site_url;
        $this->upgrader->context['source_dir'] = dirname($this->testHtacessPath);
        $this->upgradeAccess = new SugarUpgradeUpgradeAccessTest($this->upgrader);
        $htaccessContent = <<<EOQ
# old mod headers
<FilesMatch "\.(jpg|png|gif|js|css|ico)$">
        <IfModule mod_headers.c>
                Header set ETag ""
                Header set Cache-Control "max-age=2592000"
                Header set Expires "01 Jan 2112 00:00:00 GMT"
        </IfModule>
</FilesMatch>
<IfModule mod_expires.c>
        ExpiresByType text/css "access plus 1 month"
        ExpiresByType text/javascript "access plus 1 month"
        ExpiresByType application/x-javascript "access plus 1 month"
        ExpiresByType image/gif "access plus 1 month"
        ExpiresByType image/jpg "access plus 1 month"
        ExpiresByType image/png "access plus 1 month"
</IfModule>

# Customization above restrictions

# BEGIN SUGARCRM RESTRICTIONS
# Fix mimetype for logo.svg (SP-1395)
AddType     image/svg+xml     .svg
AddType     application/json  .json
AddType     application/javascript  .js

# Customization inside restrictions

# END SUGARCRM RESTRICTIONS

# Customization below restrictions
# 6.x mod_rewrite we need to remove
<IfModule mod_rewrite.c>
    Options +FollowSymLinks
    RewriteEngine On
    RewriteBase {$this->test_site_url}
    RewriteRule ^cache/jsLanguage/(.._..).js$ index.php?entryPoint=jslang&module=app_strings&lang=$1 [L,QSA]
    RewriteRule ^cache/jsLanguage/(\w*)/(.._..).js$ index.php?entryPoint=jslang&module=$1&lang=$2 [L,QSA]
</IfModule>
<FilesMatch "\.(jpg|png|gif|js|css|ico)$">
        <IfModule mod_headers.c>
                Header set ETag ""
                Header set Cache-Control "max-age=2592000"
                Header set Expires "01 Jan 2112 00:00:00 GMT"
        </IfModule>
</FilesMatch>
<IfModule mod_expires.c>
        ExpiresByType text/css "access plus 1 month"
        ExpiresByType text/javascript "access plus 1 month"
        ExpiresByType application/x-javascript "access plus 1 month"
        ExpiresByType image/gif "access plus 1 month"
        ExpiresByType image/jpg "access plus 1 month"
        ExpiresByType image/png "access plus 1 month"
</IfModule>

EOQ;
        file_put_contents($this->testHtacessPath, $htaccessContent);
    }

    protected function tearDown(): void
    {
        global $sugar_config;
        parent::tearDown();
        unlink($this->testHtacessPath);
        $sugar_config['site_url'] = $this->original_site_url;
    }

    /**
     * Verify that customizations to the htaccess file outside of the "sugarcrm zone" are preserved after upgrade.
     */
    public function testUpdateHtacessLeavesCustomizations()
    {
        $this->upgradeAccess->testhandleHtaccess();
        $newContent = file_get_contents($this->testHtacessPath);
        $this->assertStringContainsString('# Customization above restrictions', $newContent);
        $this->assertStringContainsString('# Customization below restrictions', $newContent);
        $this->assertStringNotContainsString('# Customization inside restrictions', $newContent);

        //Verify that the 6.x sugar directives outside of the sugar block are removed
        $contentsWithoutBlock = $this->getContentsWithoutSugarBlock($this->testHtacessPath);
        $this->assertStringNotContainsString('RewriteEngine On', $contentsWithoutBlock);
        $this->assertStringNotContainsString('Header set Cache-Control "max-age=2592000"', $contentsWithoutBlock);
    }

    protected function getContentsWithoutSugarBlock($htaccess_file)
    {
        $contents = '';
        if (file_exists($htaccess_file)) {
            $fp = fopen($htaccess_file, 'r');
            $skip = false;
            while ($line = fgets($fp)) {
                if (preg_match('/\s*#\s*BEGIN\s*SUGARCRM\s*RESTRICTIONS/i', $line)) {
                    $skip = true;
                }
                if (!$skip) {
                    $contents .= $line;
                }
                if (preg_match('/\s*#\s*END\s*SUGARCRM\s*RESTRICTIONS/i', $line)) {
                    $skip = false;
                }
            }
        }
        return $contents;
    }
}

class SugarUpgradeUpgradeAccessTest extends SugarUpgradeUpgradeAccess
{
    public function testhandleHtaccess()
    {
        $this->handleHtaccess();
    }
}
