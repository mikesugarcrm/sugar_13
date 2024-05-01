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

namespace Sugarcrm\SugarcrmTests\DataPrivacy\Erasure;

use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Repository;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Repository
 */
class RepositoryTest extends TestCase
{
    /**
     * @var Repository
     */
    private $repo;

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $id;

    protected function setUp(): void
    {
        $container = Container::getInstance();
        $this->repo = $container->get(Repository::class);

        $this->table = 'contacts';
        $this->id = '770a7222-0acd-11e8-a805-6c4008a36d36';
    }

    /**
     * @covers ::addBeanFields()
     * @covers ::getBeanFields()
     * @covers ::insertBeanFields()
     * @covers ::updateBeanFields()
     * @dataProvider providerTestAddGet
     */
    public function testAddGet(array $fieldArr, array $expected)
    {
        $fields = FieldList::fromArray($fieldArr);
        $this->repo->addBeanFields($this->table, $this->id, $fields);

        $res = $this->repo->getBeanFields($this->table, $this->id);
        $this->assertEqualsCanonicalizing($expected, $res);
    }

    public static function providerTestAddGet()
    {
        return [
            'add one field' => [
                ['first_name'],
                ['first_name'],
            ],
            'add another field' => [
                ['description'],
                ['description', 'first_name'],
            ],
            'add no field again' => [
                [],
                ['description', 'first_name'],
            ],
            'add fields (duplicated + new)' => [
                ['first_name', 'last_name'],
                ['description', 'first_name', 'last_name'],
            ],
            'add fields (duplicated only)' => [
                ['description', 'last_name'],
                ['description', 'first_name', 'last_name'],
            ],
        ];
    }


    /**
     * @covers ::removeBeanFields()
     * @covers ::getBeanFields()
     * @covers ::updateBeanFields()
     * @covers ::deleteBeanFields()
     * @dataProvider providerTestRemoveGet
     */
    public function testRemoveGet(array $fieldArr, array $expected)
    {
        $fields = FieldList::fromArray($fieldArr);
        $this->repo->removeBeanFields($this->table, $this->id, $fields);

        $res = $this->repo->getBeanFields($this->table, $this->id);
        $this->assertEqualsCanonicalizing($expected, $res);
    }

    public static function providerTestRemoveGet()
    {
        return [
            'remove no field initially' => [
                [],
                ['description', 'first_name', 'last_name'],
            ],
            'remove non-existing field' => [
                ['last_field'],
                ['description', 'first_name', 'last_name'],
            ],
            'remove one field' => [
                ['first_name'],
                ['description', 'last_name'],
            ],
            'remove fields (duplicated + new]' => [
                ['description', 'first_name'],
                ['last_name'],
            ],
            'remove fields (duplicated]' => [
                ['first_name'],
                ['last_name'],
            ],
        ];
    }

    /**
     * @covers ::addBeanFields()
     * @covers ::removeBeanFields()
     * @covers ::getBeanFields()
     */
    public function testAddRemoveEmptyList()
    {
        $fields = FieldList::fromArray(['last_name']);
        $this->repo->removeBeanFields($this->table, $this->id, $fields);
        $res = $this->repo->getBeanFields($this->table, $this->id);
        $this->assertNull($res);

        $fields = FieldList::fromArray([]);
        $this->repo->addBeanFields($this->table, $this->id, $fields);
        $res = $this->repo->getBeanFields($this->table, $this->id);
        $this->assertNull($res);

        $fields = FieldList::fromArray([]);
        $this->repo->removeBeanFields($this->table, $this->id, $fields);
        $res = $this->repo->getBeanFields($this->table, $this->id);
        $this->assertNull($res);
    }
}
