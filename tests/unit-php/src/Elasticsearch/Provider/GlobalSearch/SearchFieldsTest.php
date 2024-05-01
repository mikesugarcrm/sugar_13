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
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Booster;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchField;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields
 */
class SearchFieldsTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::addSearchField
     * @covers ::getIterator
     * @dataProvider providerTestAddSearchField
     */
    public function testAddSearchField(SearchField $sf, $weightId, $expected, Booster $booster = null)
    {
        $sut = new SearchFields($booster);
        $sut->addSearchField($sf, $weightId);
        foreach ($sut as $getSf) {
            $this->assertSame($sf, $getSf);
            $this->assertSame($expected, $getSf->compile());
        }
    }

    public function providerTestAddSearchField()
    {
        $booster = new Booster();
        $booster->setWeighted(['bar' => 0.5]);

        return [
            // no booster
            [
                new SearchField('Accounts', 'name', []),
                'foo',
                'Accounts__name',
                null,
            ],
            // without weighting
            [
                new SearchField('Accounts', 'name', []),
                'foo',
                'Accounts__name^1',
                $booster,
            ],
            [
                new SearchField('Accounts', 'name', ['full_text_search' => ['boost' => 0.5]]),
                'foo',
                'Accounts__name^0.5',
                $booster,
            ],
            // with weighting
            [
                new SearchField('Accounts', 'name', []),
                'bar',
                'Accounts__name^0.5',
                $booster,
            ],
            [
                new SearchField('Accounts', 'name', ['full_text_search' => ['boost' => 0.5]]),
                'bar',
                'Accounts__name^0.25',
                $booster,
            ],
        ];
    }
}
