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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Administration\clients\base\api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SugarApiExceptionNotAuthorized;
use SugarApiExceptionNotFound;

/**
 * @coversDefaultClass \IdmUserFilterApi
 */
class IdmUserFilterApiTest extends TestCase
{
    /**
     * @var \User|MockObject
     */
    private $currentUser;

    /**
     * @var \ServiceBase|MockObject
     */
    private $apiService;

    /**
     * @var \IdmUserFilterApi|MockObject
     */
    private $api;

    /**
     * @var array
     */
    private $sugarConfig;


    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->api = $this->createPartialMock(\IdmUserFilterApi::class, [
            'filterListSetup',
            'getRelatedCollectionOptions',
            'runRelateCollectionQuery',
            'populateRelatedFields',
        ]);
        $this->api->method('getRelatedCollectionOptions')->willReturn([]);
        $this->api->method('runRelateCollectionQuery')->willReturn([]);

        $this->currentUser = $this->createPartialMock(\User::class, ['isAdmin']);

        $this->apiService = $this->createMock(\ServiceBase::class);
        $this->apiService->user = $this->currentUser;

        $GLOBALS['current_user'] = $this->currentUser;
        $GLOBALS['app_strings'] = ['EXCEPTION_NOT_AUTHORIZED' => 'EXCEPTION_NOT_AUTHORIZED'];

        $this->sugarConfig = $GLOBALS['sugar_config'] ?? null;
        $GLOBALS['sugar_config']['idmMigration'] = true;
    }

    /**
     * A few test cases require a current user. Unsets the current user from $GLOBALS.
     */
    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
        $GLOBALS['sugar_config'] = $this->sugarConfig;

        parent::tearDown();
    }

    /**
     * @covers ::getIdmUsers
     */
    public function testGetIdmUserApiParameterNotPresentInConfig(): void
    {
        unset($GLOBALS['sugar_config']['idmMigration']);

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->api->getIdmUsers($this->apiService, []);
    }

    /**
     * @covers ::getIdmUsers
     */
    public function testGetIdmUserApiParameterIsFalseInConfig(): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = false;

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->api->getIdmUsers($this->apiService, []);
    }

    /**
     *
     * @covers ::getIdmUsers
     */
    public function testGetIdmUsersNotAdmin()
    {
        $GLOBALS['current_user']->method('isAdmin')->willReturn(false);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->getIdmUsers($this->apiService, []);
    }

    /**
     * @covers ::getIdmUsers
     */
    public function testGetIdmUsersNotAuthorized()
    {
        unset($GLOBALS['current_user']);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->getIdmUsers($this->apiService, []);
    }

    /**
     * @covers ::getIdmUsers
     */
    public function testGetIdmUsersRawUserHashes()
    {
        // is admin
        $GLOBALS['current_user']->method('isAdmin')->willReturn(true);

        // some fetched user
        $someUser = $this->createMock(\User::class);
        $someUser->id = 'some_user_id';
        $someUser->user_hash = $userHash = '$2y$10$PXY1UPZHjcm.t6ZArbia0uVzjCNEDm0XcGu/whGGk2xaPzAEKrKLa';

        // seed
        $seed = $this->createPartialMock(\User::class, ['fetchFromQuery']);
        $seed->id = 'some_seed_id';
        $seed->method('fetchFromQuery')->willReturn([
            $someUser->id => $someUser,
            '_rows' => [
                $someUser->id => [
                    'id' => $someUser->id,
                    'user_hash' => $someUser->user_hash,
                ],
            ],
            '_distinctCompensation' => 0,
        ]);

        $this->api->method('filterListSetup')->willReturn([
            [],
            $this->createMock(\SugarQuery::class),
            ['limit' => 1],
            $seed,
        ]);

        $result = $this->api->getIdmUsers($this->apiService, []);

        $this->assertEquals($userHash, $result['records'][0]['user_hash'], 'User hash was modified');
    }
}
