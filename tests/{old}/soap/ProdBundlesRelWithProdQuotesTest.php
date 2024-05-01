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
 * Bug #32064
 * Setting a relationship between ProductBundles and Quotes or Products and ProductBundles results in a PHP fatal error
 *
 * @ticket 32064
 */
class ProdBundlesRelWithProdQuotesTest extends SOAPTestCase
{
    protected $prodBundle = null;
    protected $quote = null;
    protected $product = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prodBundle = SugarTestProductBundleUtilities::createProductBundle();
        $this->quote = SugarTestQuoteUtilities::createQuote();
        $this->product = SugarTestProductUtilities::createProduct();

        // Commit setUp records for DB2.
        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        $this->tearDownTestUser();

        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestProductUtilities::removeAllCreatedProducts();

        parent::tearDown();
    }

    /**
     * Setting a relationship between ProductBundles and Quotes or
     * Products and ProductBundles results in a PHP fatal error
     *
     * @group 32064
     */
    public function testProductBundlesRelationsWithProductsAndQuotesSoapV4()
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        $this->login();

        $this->soapClient->set_relationship(
            $this->sessionId,
            'ProductBundles',
            $this->prodBundle->id,
            'products',
            [$this->product->id],
            [],
            0
        );

        $this->soapClient->set_relationship(
            $this->sessionId,
            'ProductBundles',
            $this->prodBundle->id,
            'quotes',
            [$this->quote->id],
            [],
            0
        );

        $assertProductsRelObj = $this->soapClient->get_relationships(
            $this->sessionId,
            'ProductBundles',
            $this->prodBundle->id,
            'products',
            '',
            ['id'],
            [],
            0
        );

        $assertQuoteRelObj = $this->soapClient->get_relationships(
            $this->sessionId,
            'ProductBundles',
            $this->prodBundle->id,
            'quotes',
            '',
            ['id'],
            [],
            0
        );
        $assertProductsRel = get_object_vars($assertProductsRelObj);
        $assertQuoteRel = get_object_vars($assertQuoteRelObj);

        $this->assertEquals($this->product->id, get_object_vars($assertProductsRel['entry_list'][0])['id']);
        $this->assertEquals($this->quote->id, get_object_vars($assertQuoteRel['entry_list'][0])['id']);
    }
}
