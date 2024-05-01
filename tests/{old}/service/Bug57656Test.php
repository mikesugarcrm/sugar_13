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
 * @ticket 57656
 */
class Bug57656Test extends SOAPTestCase
{
    /**
     * @var \TabController|mixed
     */
    public $tabs;
    /**
     * @var mixed[]|mixed
     */
    public $orig_tabs;
    /**
     * @var mixed|string
     */
    public $bugid;

    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';

        parent::setUp();
        $this->tabs = new TabController();
        $tabs = $this->orig_tabs = $this->tabs->get_system_tabs();
        if (in_array('Bugs', $tabs)) {
            unset($tabs[array_search('Bugs', $tabs)]);
        }
        $this->tabs->set_system_tabs($tabs);
    }

    protected function tearDown(): void
    {
        if (!empty($this->bugid)) {
            $GLOBALS['db']->query("DELETE FROM bugs WHERE id='{$this->bugid}'");
        }
        $this->tabs->set_system_tabs($this->orig_tabs);
    }

    public function soapClients()
    {
        return [
            [$GLOBALS['sugar_config']['site_url'] . '/soap.php'],
            [$GLOBALS['sugar_config']['site_url'] . '/service/v3_1/soap.php'],
        ];
    }

    /**
     * Test creates new bug report
     * @dataProvider soapClients
     * @group 57656
     */
    public function testCreateBug($url)
    {
        $soapParams = [
            'soap_version' => SOAP_1_1,
            'trace' => 1,
            'exceptions' => 1,
        ];
        $this->soapClient = new \SoapClient($url . '?wsdl', $soapParams);
        $this->login();
        $params = [
            ['name' => 'name', 'value' => 'TEST'],
            ['name' => 'parent_id', 'value' => '5a770071-66ca-6127-5a1a-4cb3a2c46e40'],
            ['name' => 'parent_type', 'value' => 'Accounts'],
            ['name' => 'from_addr', 'value' => 'test@test.com'],
            ['name' => 'to_addrs', 'value' => 'test@test.com'],
        ];
        $res = $this->soapClient->set_entry($this->sessionId, $params);
        $res = object_to_array_deep($res);
        $this->assertNotEquals('-1', $res['id'], 'Bad bug ID');

        $b = new Bug();
        $b->retrieve($res['id']);
        $this->assertNotEmpty($b->id);

        $this->bugid = $b->id;
    }
}
