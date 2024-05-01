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
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Highlighter;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Highlighter
 */
class HighlighterTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testDefaults()
    {
        $exp = [
            'pre_tags' => ['<strong>'],
            'post_tags' => ['</strong>'],
            'require_field_match' => true,
            'number_of_fragments' => 3,
            'fragment_size' => 255,
            'encoder' => 'html',
            'order' => 'score',
            'fields' => [
                'bar' => [
                    'type' => 'plain',
                    'force_source' => false,
                    'more' => 'beer',
                ],
            ],
        ];

        $h = new Highlighter();
        $h->setFields(['bar' => ['more' => 'beer']]);
        $this->assertSame($exp, $h->build());
    }

    /**
     * @covers ::build
     * @covers ::setPreTags
     * @covers ::setPostTags
     * @covers ::setRequiredFieldMatch
     * @covers ::setNumberOfFrags
     * @covers ::setFragSize
     * @covers ::setFields
     * @covers ::setDefaultFieldArgs
     * @covers ::getPreTags
     * @covers ::getPostTags
     * @dataProvider providerTestBuild
     */
    public function testBuild(array $pre, array $post, $req, $frags, $size, array $fields, array $default, array $exp)
    {
        $h = new Highlighter();
        $h
            ->setPreTags($pre)
            ->setPostTags($post)
            ->setRequiredFieldMatch($req)
            ->setNumberOfFrags($frags)
            ->setFragSize($size)
            ->setFields($fields)
            ->setDefaultFieldArgs($default);

        $this->assertSame($pre, $h->getPreTags());
        $this->assertSame($post, $h->getPostTags());
        $this->assertSame($exp, $h->build());
    }

    public function providerTestBuild()
    {
        return [
            [
                ['<hit>'],
                ['</hit>'],
                false,
                10,
                20,
                ['hello' => ['world' => 'z']],
                ['foo' => 'bar'],
                [
                    'pre_tags' => ['<hit>'],
                    'post_tags' => ['</hit>'],
                    'require_field_match' => false,
                    'number_of_fragments' => 10,
                    'fragment_size' => 20,
                    'encoder' => 'html',
                    'order' => 'score',
                    'fields' => [
                        'hello' => [
                            'foo' => 'bar',
                            'world' => 'z',
                        ],
                    ],
                ],
            ],
        ];
    }
}
