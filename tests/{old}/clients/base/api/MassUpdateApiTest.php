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

namespace Sugarcrm\SugarcrmTestsUnit\clients\base\api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

require_once 'include/utils.php';

/**
 * @coversDefaultClass \MassUpdateApi
 */
class MassUpdateApiTest extends TestCase
{
    /**
     * @var \MassUpdateApi|MockObject
     */
    protected $massUpdateApi;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->massUpdateApi = new \MassUpdateApi();
    }

    /**
     * @dataProvider dataProviderRemoveRelateFields
     * @covers ::removeRelateFieldsFromRelationships
     * @param $params
     * @param $fieldsToExpect
     * @param $fieldsNotToExpect
     */
    public function testRemoveRelateFields($params, $fieldsToExpect, $fieldsNotToExpect)
    {
        $result = $this->massUpdateApi->removeRelateFields($params);

        foreach ($fieldsToExpect as $fieldToExpect) {
            $this->assertTrue(array_key_exists($fieldToExpect, $result));
        }
        foreach ($fieldsNotToExpect as $fieldNotToExpect) {
            $this->assertFalse(array_key_exists($fieldNotToExpect, $result));
        }
    }

    public function dataProviderRemoveRelateFields()
    {
        return [
            [
                // params
                [
                    'module' => 'Opportunities',
                    'account_id' => 'my_account_id',
                    'account_name' => 'my_account_name',
                    'campaign_id' => 'my_campaign_id',
                    'campaign_name' => 'my_campaign_name',
                ],
                // fields that should remain
                [
                    'account_id', 'campaign_id',
                ],
                // fields that should be removed
                [
                    'account_name', 'campaign_name',
                ],
            ],
            [
                // params
                [
                    'module' => 'Quotes',
                    'account_id' => 'my_account_id',
                    'account_name' => 'my_account_name',
                    'opportunity_id' => 'my_opportunity_id',
                    'opportunity_name' => 'my_opportunity_name',
                    'shipping_contact_id' => 'my_shipping_contact_id',
                    'shipping_contact_name' => 'my_shipping_contact_name',
                ],
                // fields that should remain
                [
                    'account_id', 'opportunity_id', 'shipping_contact_id',
                ],
                // fields that should be removed
                [
                    'account_name', 'opportunity_name', 'shipping_contact_name',
                ],
            ],
        ];
    }
}
