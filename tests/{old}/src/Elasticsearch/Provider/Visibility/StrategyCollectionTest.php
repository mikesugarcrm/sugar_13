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

namespace Sugarcrm\SugarcrmTests\Elasticsearch\Provider\Visibility;

use InvalidArgumentException;
use stdClass;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\StrategyCollection;
use PHPUnit\Framework\TestCase;

class StrategyCollectionTest extends TestCase
{
    public function testHashInvalidArgument()
    {
        $strategyCollection = new StrategyCollection();

        $this->expectException(InvalidArgumentException::class);
        $strategyCollection->getHash(new stdClass());
    }

    public function testHash()
    {
        $visibility = $this->getMockForAbstractClass('\SugarVisibility', [], 'TestSugarVisibility', false);
        $strategyCollection = new StrategyCollection();
        $this->assertEquals('TestSugarVisibility', $strategyCollection->getHash($visibility));
    }
}
