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

class Bug41493Test extends TestCase
{
    public function testGetListViewSmarty()
    {
        $teamset = new SugarFieldTeamset('teamset');
        $result_template = $teamset->getListViewSmarty(['TEAM_NAME' => 'Team name'], ['name' => 'team_name'], [], '');
        $this->assertMatchesRegularExpression('/Team name/', $result_template, 'lowercase name');
        $result_template = $teamset->getListViewSmarty(['TEAM_NAME' => 'Team name'], ['name' => 'TEAM_NAME'], [], '');
        $this->assertMatchesRegularExpression('/Team name/', $result_template, 'uppercase name');
    }
}
