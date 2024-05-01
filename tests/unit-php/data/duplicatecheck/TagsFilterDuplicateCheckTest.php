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

namespace Sugarcrm\SugarcrmTestsUnit\data\duplicatecheck;

use PHPUnit\Framework\TestCase;
use TagsFilterDuplicateCheck;

/**
 * @coversDefaultClass \TagsFilterDuplicateCheck
 */
class TagsFilterDuplicateCheckTest extends TestCase
{
    /**
     * @covers ::getValueFromField
     */
    public function testGetValueFromField()
    {
        $tag = $this->getMockBuilder('Tag');
        $tag->name = 'Hello';

        $mock = new TagsFilterDuplicateCheck($tag, []);
        $result = $mock->getValueFromField('name');
        $this->assertEquals('hello', $result);
    }
}
