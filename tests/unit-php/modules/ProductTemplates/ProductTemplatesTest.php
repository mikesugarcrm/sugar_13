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

namespace Sugarcrm\SugarcrmTestsUnit\modules\ProductTemplates;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class ProductTemplatesTest
 * @coversDefaultClass \ProductTemplate
 */
class ProductTemplatesTest extends TestCase
{
    /**
     * @covers ::calculateDiscountPrice
     */
    public function testCalculateDiscountPrice()
    {
        $productTemplate = $this->createPartialMock('Product', ['save', 'getPriceFormula']);
        $productTemplate->pricing_formula = 'isList';
        $productTemplate->cost_price = '100.000000';
        $productTemplate->list_price = '150.000000';
        $productTemplate->discount_price = '25.000000';
        $productTemplate->pricing_factor = '12.00';


        $formula = $this->getMockBuilder('PercentageDiscount')
            ->setMethods(['calculate_price'])
            ->getMock();

        $formula->expects($this->once())
            ->method('calculate_price')
            ->with(
                $productTemplate->cost_price,
                $productTemplate->list_price,
                $productTemplate->discount_price,
                $productTemplate->pricing_factor
            );

        $productTemplate->expects($this->once())
            ->method('getPriceFormula')
            ->willReturn($formula);

        TestReflection::callProtectedMethod($productTemplate, 'calculateDiscountPrice');
    }
}
