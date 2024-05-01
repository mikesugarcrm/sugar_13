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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchField;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchField
 */
class SearchFieldTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::compile
     * @dataProvider providerTestCompileSearchField
     */
    public function testCompileSearchField($module, $field, array $path, $boost, $exp)
    {
        $sf = new SearchField($module, $field, []);
        $sf->setPath($path);
        if ($boost != null) {
            $sf->setBoost($boost);
        }
        $this->assertSame($exp, $sf->compile());
    }

    public function providerTestCompileSearchField()
    {
        return [
            // No boost value
            [
                'Accounts',
                'name',
                [],
                null,
                'Accounts__name',
            ],
            [
                'Accounts',
                'name',
                ['name'],
                null,
                'Accounts__name',
            ],
            [
                'Accounts',
                'email',
                ['email_search', 'primary', 'wildcard'],
                null,
                'Accounts__email_search.primary.wildcard',
            ],
            // No boost value
            [
                'Accounts',
                'name',
                [],
                69,
                'Accounts__name^69',
            ],
            [
                'Accounts',
                'name',
                ['name'],
                6.9,
                'Accounts__name^6.9',
            ],
            [
                'Accounts',
                'email',
                ['email_search', 'primary', 'wildcard'],
                6.99,
                'Accounts__email_search.primary.wildcard^6.99',
            ],
        ];
    }

    /**
     * @covers ::getModule
     * @covers ::getField
     * @covers ::getDefs
     */
    public function testGetters()
    {
        $sf = new SearchField('x', 'y', ['a', 'b']);
        $this->assertSame('x', $sf->getModule());
        $this->assertSame('y', $sf->getField());
        $this->assertSame(['a', 'b'], $sf->getDefs());
    }
}
