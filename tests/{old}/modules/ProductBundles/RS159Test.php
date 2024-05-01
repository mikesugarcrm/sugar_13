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
 * RS-159: Prepare ProductBundles Module
 */
class RS159Test extends TestCase
{
    /** @var ProductBundle */
    protected $productBundle = null;

    /** @var array */
    protected $remove = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->remove = [];
        $this->productBundle = new ProductBundle();
    }

    protected function tearDown(): void
    {
        foreach ($this->remove as $k => $bundleId) {
            $GLOBALS['db']->query('DELETE FROM ' . $this->productBundle->$k . ' WHERE bundle_id=' . $GLOBALS['db']->quoted($bundleId));
        }
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testGetProducts()
    {
        $actual = $this->productBundle->get_products();
        $this->assertIsArray($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testGetQuotes()
    {
        $actual = $this->productBundle->get_quotes();
        $this->assertIsArray($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testGetNotes()
    {
        $actual = $this->productBundle->get_notes();
        $this->assertIsArray($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testClearProductBundleProductRelationship()
    {
        $actual = $this->productBundle->clear_productbundle_product_relationship(create_guid());
        $this->assertTrue($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testClearProductProductBundleRelationship()
    {
        $actual = $this->productBundle->clear_product_productbundle_relationship(create_guid());
        $this->assertTrue($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testRetrieveProductBundleFromProduct()
    {
        $actual = $this->productBundle->retrieve_productbundle_from_product(create_guid());
        $this->assertFalse($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testInProductBundleFromProduct()
    {
        $actual = $this->productBundle->in_productbundle_from_product(create_guid());
        $this->assertFalse($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testSetProductBundleProductRelationship()
    {
        $bundleId = create_guid();
        $this->remove['rel_products'] = $bundleId;
        $actual = $this->productBundle->set_productbundle_product_relationship(create_guid(), 1, $bundleId);
        $this->assertTrue($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testSetProductBundleNoteRelationship()
    {
        $bundleId = create_guid();
        $this->remove['rel_notes'] = $bundleId;
        $actual = $this->productBundle->set_product_bundle_note_relationship(1, create_guid(), $bundleId);
        $this->assertTrue($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testClearRroductBundleNoteRelationship()
    {
        $actual = $this->productBundle->clear_product_bundle_note_relationship();
        $this->assertTrue($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testClearProductBundleQuoteRelationship()
    {
        $actual = $this->productBundle->clear_productbundle_quote_relationship(create_guid());
        $this->assertTrue($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testClearQuoteProductBundleRelationship()
    {
        $actual = $this->productBundle->clear_quote_productbundle_relationship(create_guid());
        $this->assertTrue($actual);
    }

    /**
     * Test asserts that we don't get any db error in the method
     */
    public function testSetProductBundleQuoteRelationship()
    {
        $bundleId = create_guid();
        $this->remove['rel_quotes'] = $bundleId;
        $actual = $this->productBundle->set_productbundle_quote_relationship(create_guid(), $bundleId, 1);
        $this->assertTrue($actual);
    }
}
