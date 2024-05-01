<?php

declare(strict_types=1);
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

namespace Sugarcrm\SugarcrmTestsUnit\Security\ValueObjects;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\ValueObjects\PlatformName;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\ValueObjects\PlatformName
 */
class PlatformNameTest extends TestCase
{
    public function providerValidNames(): array
    {
        return [
            ['base'],
            ['123'],
            ['-custom_platform-'],
        ];
    }

    /**
     * @dataProvider providerValidNames
     * @covers ::fromString
     */
    public function testValidNames(string $name): void
    {
        $directory = PlatformName::fromString($name);
        $this->assertEquals($name, $directory->value());
    }

    public function providerInvalidNames(): array
    {
        return [
            [''],
            ["foo\0"],
            ['foo/bar'],
            ['custom\\client'],
            ['custom:client'],
            ['{client}'],
            [str_repeat('super_very_long_platform_name', 5)],
        ];
    }

    /**
     * @dataProvider providerInvalidNames
     * @covers ::fromString
     */
    public function testInvalidNames(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        PlatformName::fromString($name);
    }
}
