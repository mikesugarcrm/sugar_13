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

namespace Sugarcrm\SugarcrmTestsUnit\Marketing;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Marketing\MarketingExtrasHelper
 */
class MarketingExtrasHelperTest extends TestCase
{
    /**
     * @covers ::chooseLanguage()
     */
    public function testChooseLanguage()
    {
        $helper = $this->getMarketingExtrasHelperMock();
        $this->assertEquals('en_us', $helper->chooseLanguage(null));
    }

    protected function getMarketingExtrasHelperMock($methods = [])
    {
        return $this->getMockBuilder('\\' . \Sugarcrm\Sugarcrm\Marketing\MarketingExtrasHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods($methods)
            ->getMock();
    }
}
