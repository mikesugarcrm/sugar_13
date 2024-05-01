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

namespace Sugarcrm\SugarcrmTestsUnit\ProductDefinition\Config\Source;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\ProductDefinition\Config\Source\FileSource;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\ProductDefinition\Config\Source\FileSource
 */
class FileSourceTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructSourceMissing()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new FileSource([]));
    }
}
