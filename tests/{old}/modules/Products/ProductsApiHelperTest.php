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

class ProductsApiHelperTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        unset($this->helper);
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestHelper::tearDown();
    }

    public function testAvoidPositionAsEmptyString()
    {
        $mockService = new ProductsServiceMock();
        $mockService->user = SugarTestHelper::setUp('current_user');

        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['ACLAccess', 'ACLFieldAccess'])
            ->setConstructorArgs([$mockService])
            ->getMock();
        $product->expects($this->any())->method('ACLAccess')->willReturn(true);
        $product->expects($this->any())->method('ACLFieldAccess')->willReturn(true);
        $product->position = '';

        $originalHelper = new SugarBeanApiHelper($mockService);

        $data = $originalHelper->formatForApi($product);
        $this->assertArrayHasKey('position', $data);
        $this->assertEquals('', $data['position']);

        $modifiedHelper = new ProductsApiHelper($mockService);
        $data = $modifiedHelper->formatForApi($product);

        $this->assertArrayNotHasKey('position', $data);
    }
}

class ProductsServiceMock extends ServiceBase
{
    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}
