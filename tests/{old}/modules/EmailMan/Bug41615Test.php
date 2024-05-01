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

class Bug41615Test extends TestCase
{
    public function testCreateNewListQuery()
    {
        $emailMan = new EmailMan();
        $filter = [];
        $filter['campaign_name'] = 1;
        $filter['recipient_name'] = 1;
        $filter['recipient_email'] = 1;
        $filter['message_name'] = 1;
        $filter['send_date_time'] = 1;
        $filter['send_attempts'] = 1;
        $filter['in_queue'] = 1;

        $params = [];
        $params['massupdate'] = 1;

        $query = $emailMan->create_new_list_query('date_entered DESC', '', $filter, $params);
        $this->assertStringContainsString('ORDER BY emailman.date_entered', $query);
    }
}
