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

namespace Sugarcrm\SugarcrmTestsUnit\data\visibility;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \KBVisibility
 */
class KBVisibilityTest extends TestCase
{
    /**
     * @covers ::elasticBuildMapping
     */
    public function testElasticBuildMapping()
    {
        $kb = $this->createMock('\\KBContent');
        $provider = new \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility();
        $mapping = new \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping('KBContents');

        $strategy = new \KBVisibility($kb);
        $strategy->elasticBuildMapping($mapping, $provider);

        $properties = $mapping->compile();

        $this->assertArrayHasKey('KBContents__status', $properties, 'Should have KBContents__status');
        $this->assertArrayHasKey('KBContents__active_rev', $properties, 'Should have KBContents__active_rev');
        $this->assertArrayHasKey('KBContents__is_external', $properties, 'Should have KBContents__is_external');
        $this->assertArrayHasKey('KBContents__exp_date', $properties, 'Should have KBContents__exp_date');
    }
}
