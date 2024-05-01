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

/**
 * Bug #49109
 * Cannot select Users in View Relationships in Email modules.
 *
 * @author mgusev@sugarcrm.com
 * @ticket 49109
 */
class Bug49109Test extends TestCase
{
    protected function setUp(): void
    {
        $beanList = null;
        $beanFiles = null;
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }

    /**
     * @group 49109
     */
    public function testRelations()
    {
        global $beanFiles;
        $bean = new Email();
        $bean->load_relationship('users');
        $relation = $bean->users->getRelationshipObject();
        $this->assertNotEmpty($relation->lhsLink, 'lhsLink is undefined');
        $this->assertNotEmpty($relation->rhsLink, 'rhsLink is undefined');
    }
}
