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

class Bug48369Test extends TestCase
{
    public $backupContents;

    protected function setUp(): void
    {
        if (!file_exists('custom/include/generic/SugarWidgets/SugarWidgetFieldcustomname.php')) {
            mkdir_recursive('custom/include/generic/SugarWidgets');
        } else {
            $this->backupContents = file_get_contents('custom/include/generic/SugarWidgets/SugarWidgetFieldcustomname.php');
        }

        $contents = <<<EOQ
<?php
class SugarWidgetFieldCustomName extends SugarWidgetFieldName
{
	function queryFilterIs(\$layout_def)
	{
        return "Bug48369Test";
	}
}
EOQ;

        file_put_contents('custom/include/generic/SugarWidgets/SugarWidgetFieldcustomname.php', $contents);
    }

    protected function tearDown(): void
    {
        if (!empty($this->backupContents)) {
            file_put_contents('custom/include/generic/SugarWidgets/SugarWidgetFieldcustomname.php', $this->backupContents);
        } else {
            unlink('custom/include/generic/SugarWidgets/SugarWidgetFieldcustomname.php');
        }
    }

    /**
     * @outputBuffering disabled
     */
    public function testCustomSugarWidgetFilesLoaded()
    {
        $layoutManager = $this->createMock(LayoutManager::class);
        $customWidget = new SugarWidgetFieldCustomName($layoutManager);
        $this->assertEquals('Bug48369Test', $customWidget->queryFilterIs([]));
    }
}
