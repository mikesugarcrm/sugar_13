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


class RestTestMetadataInvalidRelationship extends RestTestBase
{
    protected function setUp(): void
    {
        //Create an anonymous user for login purposes/
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->user;
        $this->restLogin($this->user->user_name, $this->user->user_name);
        $invalidRelationship = [
            'lhs_key' => 'id',
            'lhs_module' => 'badModule',
            'lhs_tabls' => 'badTable',
            'relationship_type' => 'one-to-many',
            'rhs_key' => 'badModule_id',
            'rhs_module' => 'Opportunities',
            'rhs_table' => 'opportunities',
        ];
        $GLOBALS['dictionary']['Opportunity']['relationships']['opportunities_badModule'] = $invalidRelationship;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['dictionary']['Opportunity']['relationships']['opportunities_badModule']);
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testFullMetadataWithInvalidRelationship()
    {
        global $dictionary;
        $restReply = $this->restCall('metadata');

        $this->assertTrue(isset($restReply['reply']['_hash']), 'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['modules']), 'Modules are missing.');

        $this->assertTrue(isset($restReply['reply']['fields']), 'SugarFields are missing.');
        $this->assertTrue(isset($restReply['reply']['views']), 'ViewTemplates are missing.');
    }
}
