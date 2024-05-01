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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Logger;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Logger
 */
class LoggerTest extends TestCase
{
    /**
     * @covers ::encodeData
     * @dataProvider providerEncodeData
     *
     * @param string $inputData
     * @param string $outputData
     */
    public function testEncodeData($inputData, $outputData)
    {
        $logger = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $encoded = TestReflection::callProtectedMethod($logger, 'encodeData', [$inputData]);
        $this->assertEquals($outputData, $encoded);
    }

    public function providerEncodeData()
    {
        return [
            [
                'Hello world',
                'Hello world',
            ],
            [
                ['1' => 'Hello', '2' => 'world'],
                '{"1":"Hello","2":"world"}',
            ],
        ];
    }

    /**
     * @covers ::isDeleteMissingIndexRequest
     * @dataProvider providerIsDeleteMissingIndexRequest
     *
     * @param array $expMsg : message for the exception
     * @param string $path : path of the request
     * @param string $method : method of the request
     * @param string $output : the expected return value of the method
     */
    public function testIsDeleteMissingIndexRequest($expMsg, $path, $method, $output)
    {
        $logger = new Logger(\LoggerManager::getLogger());

        $request = new \Elastica\Request($path, $method);
        $response = new \Elastica\Response($expMsg);
        $e = new \Elastica\Exception\ResponseException($request, $response);

        $result = $logger->isDeleteMissingIndexRequest($e);
        $this->assertEquals($output, $result);
    }

    public function providerIsDeleteMissingIndexRequest()
    {
        return [
            [
                '{"error":{"reason":"no such index","index":"0e787f44_shared"},"status":404}',
                '0e787f44_shared/',
                'DELETE',
                true,
            ],
            // not 'DELETE' method
            [
                '{"error":{"reason":"no such index","index":"0e787f44_shared"},"status":404}',
                '0e787f44_shared/',
                'GET',
                false,
            ],
            // not 'no such index' error
            [
                '{"error":{"reason":"unknown reason","index":"0e787f44_shared"},"status":404}',
                '0e787f44_shared/',
                'DELETE',
                false,
            ],
        ];
    }
}
