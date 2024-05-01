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
 * Class PurchaseTest
 * @coversDefaultClass Purchase
 */
class PurchaseTest extends TestCase
{
    public function tearDown(): void
    {
        SugarTestPurchaseUtilities::removeAllCreatedPurchases();
        SugarTestPurchasedLineItemUtilities::removeAllCreatedPurchasedLineItems();
    }

    /**
     * @covers ::mapFieldsToPli
     * @dataProvider providerMapFieldsToPli
     */
    public function testMapFieldsToPli($service, $typeName, $categoryName): void
    {
        $purchase = SugarTestPurchaseUtilities::createPurchase();
        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        $purchase->service = $service;
        $purchase->type_id = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $purchase->type_name = $typeName;
        $purchase->category_id = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $purchase->category_name = $categoryName;

        $purchase->mapFieldsToPli($pli);

        foreach ($purchase->pliCopyFields as $purchField => $pliField) {
            $this->assertEquals($purchase->$purchField, $pli->$pliField);
        }
    }

    public function providerMapFieldsToPli(): array
    {
        return [
            [true, 'Product Type Name 1', 'Product Category Name 1',],
            [false, 'Product Type Name 2', 'Product Category Name 2',],
        ];
    }

    /**
     * @covers ::save_relationship_changes
     */
    public function testSaveRelationshipChanges()
    {
        $purchase = $this->getMockBuilder('Purchase')
            ->setMethods(
                [
                    'handle_remaining_relate_fields',
                    'update_parent_relationships',
                    'handle_request_relate',
                    'load_relationship',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $purchase->id = 'test_purchase_id';
        $purchase->account_id = 'test_account_id1';
        $purchase->rel_fields_before_value['account_id'] = 'test_account_id2';

        $linkPLIs = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['getBeans'])
            ->getMock();

        $mockPLI = $this->getMockBuilder('PurchasedLineItem')
            ->setMethods(['save'])
            ->getMock();

        $mockPLI->expects($this->once())
            ->method('save');

        $linkPLIs->expects($this->once())
            ->method('getBeans')
            ->willReturn([$mockPLI]);

        $purchase->purchasedlineitems = $linkPLIs;
        $purchase->save_relationship_changes(true);

        $this->assertEquals('test_account_id1', $mockPLI->account_id);
    }
}
