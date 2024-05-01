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


/**
 * @ticket 42683
 */
class Bug42683Test extends SOAPTestCase
{
    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v2/soap.php';
        parent::setUp();
    }

    protected function tearDown(): void
    {
        SugarTestLeadUtilities::removeAllCreatedLeads();
        parent::tearDown();
    }

    public function testBadQuery()
    {
        $lead = SugarTestLeadUtilities::createLead();

        $this->login();
        $result = $this->soapClient->get_entry_list(
            $this->sessionId,
            'Leads',
            "leads.id = '{$lead->id}'",
            '',
            0,
            [
                'name',
            ],
            [
                [
                    'name' => 'email_addresses',
                    'value' => [
                        'id',
                        'email_address',
                        'opt_out',
                        'primary_address',
                    ],
                ],
            ],
            1,
            0
        );
        $result = object_to_array_deep($result);

        $this->assertEquals('primary_address', $result['relationship_list'][0][0]['records'][0][3]['name']);
    }
}
