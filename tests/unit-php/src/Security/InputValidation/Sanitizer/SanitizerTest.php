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

namespace Sugarcrm\SugarcrmTestsUnit\Security\InputValidation\Sanitizer;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\InputValidation\Sanitizer\Sanitizer;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\InputValidation\Sanitizer\Sanitizer
 */
class SanitizerTest extends TestCase
{
    /**
     * @covers ::sanitize
     * @dataProvider providerTestSanitize
     */
    public function testSanitize($value, $expected)
    {
        $sanitizer = new Sanitizer();
        $this->assertEquals($expected, $sanitizer->sanitize($value));
    }

    public function providerTestSanitize()
    {
        return [
            [
                'test',
                'test',
            ],
        ];
    }
}
