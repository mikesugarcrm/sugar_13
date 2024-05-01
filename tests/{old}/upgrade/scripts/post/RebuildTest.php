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

require_once 'upgrade/scripts/post/2_Rebuild.php';

class RebuildTest extends UpgradeTestCase
{
    public function testRebuildAudit(): void
    {
        global $db, $beanFiles;

        $module = 'Account';
        $auditTable = 'accounts_audit';

        if ($db->tableExists($auditTable)) {
            $db->dropTableName($auditTable);
        }

        $beanArray = [
            $module => $beanFiles[$module],
        ];

        $rac = new RepairAndClear();
        $rac->execute = true;

        $rebuildObj = new SugarUpgradeRebuild($this->upgrader);
        $rebuildObj->rebuildAudit($rac, $beanArray);

        $this->assertTrue($db->tableExists($auditTable));
    }
}
