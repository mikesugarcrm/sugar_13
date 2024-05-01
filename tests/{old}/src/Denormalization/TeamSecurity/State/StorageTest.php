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

namespace Sugarcrm\SugarcrmTests\Denormalization\TeamSecurity\State;

use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State\Storage;
use PHPUnit\Framework\TestCase;

abstract class StorageTest extends TestCase
{
    /**
     * @var Storage
     */
    protected $storage;

    protected function setUp(): void
    {
        $this->storage = $this->createStorage();
    }

    /**
     * @return Storage
     */
    abstract protected function createStorage();

    /**
     * @test
     */
    public function defaultValue()
    {
        $this->assertNull($this->storage->get('unknown_variable'));
    }

    /**
     * @test
     * @dataProvider updateAndGetProvider
     */
    public function updateAndGet($value)
    {
        $this->storage->update('test', $value);
        $stored = $this->storage->get('test');

        $this->assertSame($value, $stored);
    }

    public static function updateAndGetProvider()
    {
        return [
            'true' => [
                true,
            ],
            'false' => [
                false,
            ],
            'null' => [
                null,
            ],
            'string' => [
                'Hello, World!',
            ],
        ];
    }
}
