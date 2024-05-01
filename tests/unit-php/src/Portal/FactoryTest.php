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

namespace Sugarcrm\SugarcrmTestsUnit\src\Portal;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Portal\Factory as PortalFactory;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Portal\Factory
 */
class FactoryTest extends TestCase
{
    /**
     * @covers \Sugarcrm\Sugarcrm\Portal\Factory::getInstance
     */
    public function testFactory(): void
    {
        $ps = PortalFactory::getInstance('Session');
        $this->assertInstanceOf(\Sugarcrm\Sugarcrm\Portal\Session::class, $ps);
    }
}
