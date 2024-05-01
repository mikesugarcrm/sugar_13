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
 * Bug49698Test.php
 * This class tests to ensure that label changes made on the Rename Modules link from the Admin section
 * are accurately reflected.
 *
 * @author Collin Lee
 */
class Bug49698Test extends TestCase
{
    public function testModuleRenameForReportsTree()
    {
        $view = new ReportsViewBuildreportmoduletree();
        $linked_field = [
            'name' => 'accounts',
            'type' => 'link',
            'relationship' => 'accounts_opportunities',
            'source' => 'non-db',
            'link_type' => 'one',
            'module' => 'Accounts',
            'bean_name' => 'Account',
            'vname' => 'LBL_ACCOUNTS',
            'label' => 'Prospects', //Assume here that Accounts module label was renamed to Prospects
        ];
        $node = SugarTestReflection::callProtectedMethod(
            $view,
            '_populateNodeItem',
            ['Opportunity', 'Accounts', $linked_field]
        );
        $this->assertMatchesRegularExpression('/\\\'Prospects\\\'/', $node['href']);
    }
}
