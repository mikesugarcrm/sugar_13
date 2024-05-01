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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Adapter;

use PHPUnit\Framework\TestCase;
use Elastica\DeleteDocumentWithType\Action;
use Elastica\Document as BaseDocument;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\DeleteDocumentWithType;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\DeleteDocumentWithType
 */
class DeleteDocumentWithTypeTest extends TestCase
{
    /**
     * @covers ::setDocument
     * @param string $class
     * @param $expected
     *
     * @dataProvider getDeleteDocumentProvider
     */
    public function testSetDocument(string $class, bool $expected)
    {
        $document = new $class('id_123456');
        if (method_exists($document, 'setType')) {
            $document->setType('Accounts');
        }

        $doc = new DeleteDocumentWithType($document);
        $this->assertSame($expected, key_exists('_type', $doc->getMetadata()));
    }

    public function getDeleteDocumentProvider()
    {
        return [
            'delete document with type' => [Document::class, true],
            'delete document with no type' => [BaseDocument::class, false],
        ];
    }
}
