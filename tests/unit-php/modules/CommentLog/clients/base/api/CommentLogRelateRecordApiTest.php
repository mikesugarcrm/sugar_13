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

namespace Sugarcrm\SugarcrmTestsUnit\modules\CommentLog\clients\base\api;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CommentLogRelateRecordApi
 */
class CommentLogRelateRecordApiTest extends TestCase
{
    /**
     * @covers ::addComment
     */
    public function testAddComment()
    {
        $caseMock = $this->createMock('\\aCase');
        $apiMock = $this->createPartialMock(
            '\\CommentLogRelateRecordApi',
            ['createRelatedRecord', 'loadBean', 'indexComment']
        );
        $apiMock->method('createRelatedRecord')
            ->willReturn([]);
        $apiMock->method('loadBean')
            ->willReturn($caseMock);
        $apiMock->expects($this->once())
            ->method('indexComment')
            ->with($caseMock);
        $service = $this->createMock('\\RestService');
        $apiMock->addComment($service, []);
    }
}
