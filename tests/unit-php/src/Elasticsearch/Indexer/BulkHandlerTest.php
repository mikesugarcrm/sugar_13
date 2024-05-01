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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Indexer;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Bulk;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;
use Sugarcrm\Sugarcrm\Elasticsearch\Indexer\BulkHandler;
use Elastica\Bulk\Action;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Indexer\BulkHandler
 */
class BulkHandlerTest extends TestCase
{
    /**
     * Trace log messages
     * @var array
     */
    public $logMessages = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->logMessages = [];
    }

    /**
     * @covers ::batchDocuments
     * @covers ::batchDocument
     * @covers ::getBatchedDocuments
     * @covers ::removeIndexFromDocument
     * @covers ::setMaxBulkThreshold
     */
    public function testBatchDocuments()
    {
        $bulk = $this->getBulkMock(['__destruct', 'sendBulk']);
        $bulk->setMaxBulkThreshold(3);

        // 4 document batch (within threshold of 3 per index)
        $doc1 = new Document('11', ['name' => 'doc1'], 'index1');
        $doc2 = new Document('12', ['name' => 'doc2'], 'index1');
        $doc3 = new Document('23', ['name' => 'doc3'], 'index2');
        $doc4 = new Document('24', ['name' => 'doc4'], 'index2');
        $docs = [$doc1, $doc2, $doc3, $doc4];

        // ensure index is set
        foreach ($docs as $doc) {
            $this->assertTrue($doc->hasParam('_index'));
        }

        // expect 2 indices in batch, verify docs
        $bulk->batchDocuments($docs);
        $this->assertCount(2, $bulk->getBatchedDocuments());

        $this->assertSame([
            'index1' => [$doc1, $doc2],
            'index2' => [$doc3, $doc4],
        ], $bulk->getBatchedDocuments());

        // indices are expected to be stripped from the docs
        foreach ($docs as $doc) {
            $this->assertFalse($doc->hasParam('_index'));
        }

        // testing trigger of sendBulk
        $bulk->expects($this->exactly(2))
            ->method('sendBulk');

        // 3th document should trigger a send to index1
        $doc5 = new Document('15', ['name' => 'doc5'], 'index1');
        $bulk->batchDocument($doc5);
        $batched = $bulk->getBatchedDocuments();
        $this->assertCount(0, $batched['index1']);

        // 3th document should trigger a send to index2
        $doc6 = new Document('26', ['name' => 'doc6'], 'index2');
        $bulk->batchDocument($doc6);
        $batched = $bulk->getBatchedDocuments();
        $this->assertCount(0, $batched['index2']);
    }

    /**
     * @covers ::finishBatch
     */
    public function testFinishBatch()
    {
        $bulk = $this->getBulkMock(['__destruct', 'sendBulk']);
        $bulk->setMaxBulkThreshold(10);

        $docs = [
            new Document('11', ['name' => 'doc1'], 'index1'),
            new Document('12', ['name' => 'doc2'], 'index1'),
            new Document('23', ['name' => 'doc3'], 'index2'),
            new Document('24', ['name' => 'doc4'], 'index2'),
        ];

        $bulk->batchDocuments($docs);

        // expect 2 batches, one for every index
        $bulk->expects($this->exactly(2))
            ->method('sendBulk');

        $bulk->finishBatch();

        $batched = $bulk->getBatchedDocuments();
        $this->assertCount(0, $batched['index1']);
        $this->assertCount(0, $batched['index2']);
    }

    /**
     * @covers ::sendBulk
     *
     * @dataProvider sendBulkProvider
     */
    public function testSendBulk(string $version)
    {
        $bulk = $this->getBulkMock(['__destruct', 'newBulkObject']);

        // set threshold to amount of docs we are testing with
        $bulk->setMaxBulkThreshold(2);

        // index document
        $doc1 = new Document('11', ['name' => 'doc1'], 'index1');
        $doc1->setOpType(Action::OP_TYPE_INDEX);

        // delete document
        $doc2 = new Document('12', [], 'index1');
        $doc2->setOpType(Action::OP_TYPE_DELETE);

        $docs = [$doc1, $doc2];

        // mock Adaptor/Bulk object
        $elasticaBulk = $this->getAdaptorBulkMock(['send', 'getServerVersion']);
        $elasticaBulk->expects($this->any())
            ->method('getServerVersion')
            ->willReturn($version);

        $bulk->expects($this->once())
            ->method('newBulkObject')
            ->will($this->returnValue($elasticaBulk));

        // batch our documents, this will invoke sendBulk
        $bulk->batchDocuments($docs);

        // both documents end up in same index
        $this->assertSame('index1', $elasticaBulk->getIndex());

        // verify documents are properly added
        $this->assertCount(2, $elasticaBulk->getActions());
        foreach ($elasticaBulk->getActions() as $i => $action) {
            $this->assertSame($docs[$i], $action->getDocument());
        }

        // batch queue should be empty
        $batched = $bulk->getBatchedDocuments();
        $this->assertCount(0, $batched['index1']);
    }

    public function sendBulkProvider()
    {
        return [
            ['5.6.0'],
            ['6.2.0'],
            ['7.9.0'],
        ];
    }

    /**
     * Test error handling using raw example failure requests
     * @covers ::sendBulk
     * @covers ::handleBulkException
     * @dataProvider providerTestHandleBulkException
     */
    public function testHandleBulkException($docCount, $responseString, $status, $expectedLog)
    {
        $bulk = $this->getBulkMock(['__destruct', 'newBulkObject', 'log']);
        $bulk->setMaxBulkThreshold($docCount);

        // mock Elastica bulk/client
        $elasticaBulk = $this->getElasticaBulkClientMock($responseString, $status);

        $bulk->expects($this->once())
            ->method('newBulkObject')
            ->will($this->returnValue($elasticaBulk));

        $that = $this;
        $bulk->expects($this->exactly(safeCount($expectedLog)))
            ->method('log')
            ->will($this->returnCallback(function ($level, $message) use ($that) {
                $that->logMessages[] = $message;
            }));

        // build documents to send
        $documents = [];
        for ($i = 1; $i <= $docCount; $i++) {
            $documents[] = new Document($i, ['name' => 'foo'], 'foobar');
        }

        $bulk->batchDocuments($documents);
        $this->assertSame($expectedLog, $this->logMessages);
    }

    public function providerTestHandleBulkException()
    {
        return [
            // one document, one failure, HTTP 200 return (actual log message)
            [
                1,
                '{"took":4,"errors":true,"items":[{"index":{"_index":"foobar","_type":"Accounts","_id":"633aca08-594e-1ba3-2ec4-5727c4e231e0","status":500,"error":"IllegalArgumentException[Document contains at least one immense term in field=\"Accounts__description\" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms, original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003];"}}]}',
                200,
                [
                    'Unrecoverable indexing failure [500]: foobar -> Accounts -> 633aca08-594e-1ba3-2ec4-5727c4e231e0 -> IllegalArgumentException[Document contains at least one immense term in field="Accounts__description" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms, original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003];',
                ],
            ],
            // 3 documents, two failures, HTTP 200 return (actual log message)
            [
                3,
                '{"took":10,"errors":true,"items":[{"index":{"_index":"autobr4142_accountsonly","_type":"Accounts","_id":"5d3f72e7-4dee-4208-185f-571fc5d95a9d","_version":1,"status":201}},{"index":{"_index":"autobr4142_accountsonly","_type":"Accounts","_id":"633aca08-594e-1ba3-2ec4-5727c4e231e0","status":500,"error":"IllegalArgumentException[Document contains at least one immense term in field=\"Accounts__description\" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms., original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003]; "}},{"index":{"_index":"autobr4142_accountsonly","_type":"Accounts","_id":"a88802d5-909e-3bc1-c025-5727fd4cb5e8","status":500,"error":"IllegalArgumentException[Document contains at least one immense term in field=\"Accounts__description\" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms., original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003]; "}}]}',
                200,
                [
                    'Unrecoverable indexing failure [500]: autobr4142_accountsonly -> Accounts -> 633aca08-594e-1ba3-2ec4-5727c4e231e0 -> IllegalArgumentException[Document contains at least one immense term in field="Accounts__description" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms., original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003]; ',
                    'Unrecoverable indexing failure [500]: autobr4142_accountsonly -> Accounts -> a88802d5-909e-3bc1-c025-5727fd4cb5e8 -> IllegalArgumentException[Document contains at least one immense term in field="Accounts__description" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms., original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003]; ',
                ],
            ],
            // 2 documents, one failure
            [
                2,
                '{"took":10,"errors":true,"items":[{"index":{"_index":"autobr5598_accountsonly","_type":"Leads","_id":"5d3f72e7-4dee-4208-185f-571fc5d95a9d","_version":1,"status":201}},{"index":{"_index":"autobr4142_accountsonly","_type":"Leads","_id":"633aca08-594e-1ba3-2ec4-5727c4e231e0","status":404,"error":"TypeMissingException[[automaster_shared] type[[Leads, trying to auto create mapping, but dynamic mapping is disabled]] missing]"}}]}',
                200,
                [
                    'Unrecoverable indexing failure [404]: autobr4142_accountsonly -> Leads -> 633aca08-594e-1ba3-2ec4-5727c4e231e0 -> TypeMissingException[[automaster_shared] type[[Leads, trying to auto create mapping, but dynamic mapping is disabled]] missing]',
                ],
            ],
            // Same as above with 2 documents, but now with an array reported error
            [
                2,
                '{"took":10,"errors":true,"items":[{"index":{"_index":"autobr5598_accountsonly","_type":"Leads","_id":"5d3f72e7-4dee-4208-185f-571fc5d95a9d","_version":1,"status":201}},{"index":{"_index":"autobr4142_accountsonly","_type":"Leads","_id":"633aca08-594e-1ba3-2ec4-5727c4e231e0","status":404,"error":{"type":"index_closed_exception","reason":"closed","index_uuid":"Km0N3lMIRfGgrs9tPGYIBA","index":"autobr5598_master"}}}]}',
                200,
                [
                    "Unrecoverable indexing failure [404]: autobr4142_accountsonly -> Leads -> 633aca08-594e-1ba3-2ec4-5727c4e231e0 -> array (   'type' => 'index_closed_exception',   'reason' => 'closed',   'index_uuid' => 'Km0N3lMIRfGgrs9tPGYIBA',   'index' => 'autobr5598_master', )",
                ],
            ],
        ];
    }

    /**
     * @covers ::newBulkObject
     */
    public function testNewBulkObject()
    {
        $bulkHandlerMock = $this->getBulkMock([]);
        $result = TestReflection::callProtectedMethod($bulkHandlerMock, 'newBulkObject', []);
        $this->assertTrue($result instanceof Bulk);
    }

    /**
     * @covers ::__wakeup
     */
    public function testWakeup()
    {
        $bulk = $this->getBulkMock(['__destruct']);

        // add one document
        $bulk->batchDocument(new Document('x', [], 'z'));
        $this->assertCount(1, $bulk->getBatchedDocuments());

        // ensure document count is empty on unserialize
        $new = unserialize(serialize($bulk));
        $this->assertCount(0, $new->getBatchedDocuments());
    }

    /**
     * Elastica bulk mock
     * @param array $methods
     * @return \Elastica\Bulk
     */
    protected function getAdaptorBulkMock(array $methods = null)
    {
        return $this->getMockBuilder(Bulk::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Elastica bulk mock based on mocked client raw response
     * @param string $responseString
     * @param integer $status
     * @param array $methods
     * @return \Elastica\Bulk
     */
    protected function getElasticaBulkClientMock($responseString, $status, array $methods = null)
    {
        $client = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['request', 'getConfigValue'])
            ->getMock();

        $client->expects($this->any())
            ->method('request')
            ->will($this->returnValue(new \Elastica\Response($responseString, $status)));

        $client->expects($this->any())
            ->method('getConfigValue')
            ->will($this->returnValue(false));

        return $this->getMockBuilder(\Elastica\Bulk::class)
            ->setConstructorArgs([$client])
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Bulk handler mock
     * @param array $methods
     * @return BulkHandler
     */
    protected function getBulkMock(array $methods = null)
    {
        $container = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Container::class)
            ->setMethods(null)
            ->getMock();

        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Indexer\BulkHandler::class)
            ->setMethods($methods)
            ->setConstructorArgs([$container])
            ->getMock();
    }
}
