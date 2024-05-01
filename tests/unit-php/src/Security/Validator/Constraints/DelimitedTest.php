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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Delimited;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\Delimited
 */
class DelimitedTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testRejectNonStringDelimiter()
    {
        $this->expectException(ConstraintDefinitionException::class);
        new Delimited([
            'constraints' => [],
            'delimiter' => true,
        ]);
    }

    /**
     * @covers ::__construct
     */
    public function testRejectEmptyStringDelimiter()
    {
        $this->expectException(ConstraintDefinitionException::class);
        new Delimited([
            'constraints' => [],
            'delimiter' => '',
        ]);
    }
}
