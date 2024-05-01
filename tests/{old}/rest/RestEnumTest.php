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


class RestEnumTest extends RestTestBase
{
    /**
     * @var mixed|string[]
     */
    public $contract_types;

    protected function tearDown(): void
    {
        if (isset($this->contract_types)) {
            foreach ($this->contract_types as $id => $name) {
                $GLOBALS['db']->query("DELETE FROM contract_types WHERE id='{$id}'");
            }
        }

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testFunctionBasedDropDown()
    {
        $contract_types = [
            create_guid() => 'Unit Test 1',
            create_guid() => 'Unit Test 2',
        ];
        $this->contract_types = $contract_types;
        foreach ($contract_types as $id => $name) {
            $ct = BeanFactory::newBean('ContractTypes');
            $ct->new_with_id = true;
            $ct->id = $id;
            $ct->name = $name;
            $ct->save();
        }
        $restReply = $this->restCall('/Contracts/enum/type');
        // add the blank one
        $contract_types[''] = '';

        $this->assertEquals($contract_types, $restReply['reply']);
    }

    /**
     * @group rest
     */
    public function testETagHeaders()
    {
        $restReply = $this->restCall('Products/enum/commit_stage');
        $this->assertNotEmpty($restReply['headers']['ETag']);
        $this->assertEquals($restReply['info']['http_code'], 200);
        $restReply = $this->restCall('Products/enum/commit_stage', '', '', [], ['If-None-Match: ' . $restReply['headers']['ETag']]);
        $this->assertNotEmpty($restReply['headers']['ETag']);
        $this->assertEquals($restReply['info']['http_code'], 304);
    }

    /**
     * @group rest
     */
    public function testHtmlDropDown()
    {
        $restReply = $this->restCall('Products/enum/type_id');
        $this->assertEquals('fatal_error', $restReply['reply']['error'], 'Did not return a fatal error');
        $this->assertEquals('html dropdowns are not supported', $restReply['reply']['error_message'], 'Did not return the correct error message');
    }

    /**
     * @group rest
     */
    public function testStandardDropDown()
    {
        $restReply = $this->restCall('Products/enum/commit_stage');
        $this->assertTrue(!empty($restReply['reply']), 'Commit Stage came back empty');
    }

    /**
     * @group rest
     */
    public function testNonExistantDropDown()
    {
        $restReply = $this->restCall('Accounts/enum/UnitTest' . create_guid());
        $this->assertEquals('not_found', $restReply['reply']['error'], 'Incorrect Error Returned');
        $this->assertEquals('field not found', $restReply['reply']['error_message'], 'Incorrect message returned');
    }
}
