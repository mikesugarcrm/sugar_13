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

use PHPUnit\Framework\TestCase;

/**
 * Tests for KBContentsUsefulnessApi
 */
class KBContentsUsefulnessApiTest extends TestCase
{
    /**
     * @var RestService
     */
    protected $service = null;

    /**
     * @var KBContentsUsefulnessApi
     */
    protected $api = null;

    /**
     * @var KBContents
     */
    protected $kbcontent;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new KBContentsUsefulnessApi();

        $this->kbcontent = BeanFactory::newBean('KBContents');
        $this->kbcontent->name = 'SugarKBContent' . time();
        $this->kbcontent->save();

        DBManagerFactory::getInstance()->commit();
    }

    protected function tearDown(): void
    {
        DBManagerFactory::getInstance()
            ->query('DELETE FROM kbcontents WHERE id = \'' . $this->kbcontent->id . '\'');

        $this->service = null;
        $this->api = null;

        SugarTestHelper::tearDown();
    }

    /**
     * Test for votes useful.
     */
    public function testVoteUseful()
    {
        $this->assertEquals(0, $this->kbcontent->useful);
        for ($i = 1; $i <= 3; $i++) {
            $result = $this->api->voteUseful(
                $this->service,
                [
                    'module' => 'KBContents',
                    'record' => $this->kbcontent->id,
                ]
            );

            $this->assertNotEmpty($result);

            $this->assertArrayHasKey('id', $result);
            $this->assertArrayHasKey('useful', $result);
            $this->assertArrayHasKey('notuseful', $result);

            $this->assertEquals($this->kbcontent->id, $result['id']);
            $this->assertEquals($i, $result['useful']);
            $this->assertEquals(0, $result['notuseful']);
        }
    }

    /**
     * Test for votes not useful
     */
    public function testVoteNotUseful()
    {
        $this->assertEquals(0, $this->kbcontent->useful);
        for ($i = 1; $i <= 3; $i++) {
            $result = $this->api->voteNotUseful(
                $this->service,
                [
                    'module' => 'KBContents',
                    'record' => $this->kbcontent->id,
                ]
            );

            $this->assertNotEmpty($result);

            $this->assertArrayHasKey('id', $result);
            $this->assertArrayHasKey('useful', $result);
            $this->assertArrayHasKey('notuseful', $result);

            $this->assertEquals($this->kbcontent->id, $result['id']);
            $this->assertEquals($i, $result['notuseful']);
            $this->assertEquals(0, $result['useful']);
        }
    }

    /**
     * Data provider with useful/not useful
     *
     * @return array
     */
    public function dataProviderUsefulAndNotUseful()
    {
        return [
            [true], // useful
            [false], // not useful
        ];
    }

    /**
     * Test for votes when not specified module
     *
     * @dataProvider dataProviderUsefulAndNotUseful
     */
    public function testVoteNotSpecifiedModule($isUseful)
    {
        $args = [
            'record' => '123',
        ];

        $this->expectException(SugarApiExceptionMissingParameter::class);

        if ($isUseful) {
            $this->api->voteUseful($this->service, $args);
        } else {
            $this->api->voteNotUseful($this->service, $args);
        }
    }

    /**
     * Test for votes when not specified module
     *
     * @dataProvider dataProviderUsefulAndNotUseful
     */
    public function testVoteNotSpecifiedRecord($isUseful)
    {
        $args = [
            'module' => 'KBContents',
        ];

        $this->expectException(SugarApiExceptionMissingParameter::class);

        if ($isUseful) {
            $this->api->voteUseful($this->service, $args);
        } else {
            $this->api->voteNotUseful($this->service, $args);
        }
    }

    /**
     * Test for votes when record not found
     *
     * @dataProvider dataProviderUsefulAndNotUseful
     */
    public function testVoteNotFoundRecord($isUseful)
    {
        $args = [
            'module' => 'KBContents',
            'record' => 'some_id_123',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);

        if ($isUseful) {
            $this->api->voteUseful($this->service, $args);
        } else {
            $this->api->voteNotUseful($this->service, $args);
        }
    }

    /**
     * Test for votes when record not authorized
     *
     * @dataProvider dataProviderUsefulAndNotUseful
     */
    public function testVoteNotUsefulNotAuthorized($isUseful)
    {
        $beanMock = $this->getMockBuilder('KBContents')->setMethods(['ACLAccess'])->getMock();
        $beanMock->expects($this->once())
            ->method('ACLAccess')
            ->will($this->returnValue(false));

        $apiMock = $this->getMockBuilder('KBContentsUsefulnessApi')->setMethods(['loadBean'])->getMock();
        $apiMock->expects($this->once())
            ->method('loadBean')
            ->will(
                $this->returnCallback(
                    function () use ($beanMock) {
                        return $beanMock;
                    }
                )
            );

        $args = [
            'module' => 'KBContents',
            'record' => $this->kbcontent->id,
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);

        if ($isUseful) {
            $apiMock->voteUseful($this->service, $args);
        } else {
            $apiMock->voteNotUseful($this->service, $args);
        }
    }
}
