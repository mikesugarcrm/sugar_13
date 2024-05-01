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
 * Bug50677Test
 *
 * This test is to make sure that you can add a relationship between Product Bundles and Products via the standard
 * set_relationship method and include in the extra field.
 */
class Bug50677Test extends SOAPTestCase
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductBundle
     */
    private $productBundle;

    /**
     * setUp
     * Override the setup from SOAPTestCase to also create the seed search data for Accounts and Contacts.
     */
    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v3_1/soap.php';
        parent::setUp();
        $this->login(); // Logging in just before the SOAP call as this will also commit any pending DB changes

        $this->product = SugarTestProductUtilities::createProduct();
        $this->productBundle = SugarTestProductBundleUtilities::createProductBundle();
        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM product_bundle_product WHERE bundle_id = '{$this->productBundle->id}'");

        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        parent::tearDown();
    }

    public function testSetRelationshipProductBundleProduct()
    {
        $result = $this->soapClient->set_relationship(
            $this->sessionId,
            'ProductBundles',
            $this->productBundle->id,
            'products',
            $this->product->id,
            [
                ['name' => 'product_index', 'value' => 1],
            ],
            0
        );
        $result = object_to_array_deep($result);
        $this->assertEquals(1, $result['created'], 'Failed To Create Product Bundle -> Product Relationship');

        // lets make sure the row is correct since it was created
        // it should have a product_index of 1.
        $db = $GLOBALS['db'];
        $sql = "SELECT id, product_index FROM product_bundle_product WHERE bundle_id = '" . $db->quote($this->productBundle->id) . "'
                AND product_id = '" . $db->quote($this->product->id) . "'";
        $result = $db->query($sql);
        $row = $db->fetchByAssoc($result);

        $this->assertTrue(is_guid($row['id']));
        $this->assertEquals(1, $row['product_index']);
    }
}
