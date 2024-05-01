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
 * @coversDefaultClass DocuSignEnvelopesFilterApi
 */
class DocuSignEnvelopesFilterApiTest extends TestCase
{
    /**
     * @var DocuSignApi
     */
    protected $api;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->api = new DocuSignEnvelopesFilterApi();
    }

    /**
     * @covers ::filterListSetup
     */
    public function testFilterListSetup()
    {
        $serviceBase = SugarTestRestUtilities::getRestServiceMock();
        $res = $this->api->filterListSetup($serviceBase, [
            'module' => 'DocuSignEnvelopes',
            'layout' => 'record',
            'recordModule' => 'Accounts',
            'record' => '123',
            'status' => 'created',
        ]);

        $query = $res[1];
        $compiled = $query->compile();
        $sql = $compiled->getSql();
        $parameters = $compiled->getParameters();
        $completeSql = $sql;
        foreach ($parameters as $param) {
            $pos = strpos($completeSql, '?');
            $completeSql = substr_replace($completeSql, $param, $pos, 1);
        }

        $parentTypeFilterFound = strpos($completeSql, 'parent_type = Accounts') !== false;
        $parentIdFilterFound = strpos($completeSql, 'parent_id = 123') !== false;
        $statusFilterFound = strpos($completeSql, 'status = created') !== false;
        $this->assertEquals(true, $parentTypeFilterFound);
        $this->assertEquals(true, $parentIdFilterFound);
        $this->assertEquals(true, $statusFilterFound);
    }
}
