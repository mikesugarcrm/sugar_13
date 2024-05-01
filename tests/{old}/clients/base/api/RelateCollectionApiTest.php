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

include_once 'clients/base/api/CollectionApi/CollectionDefinition/CollectionDefinitionInterface.php';

class RelateCollectionApiTest extends TestCase
{
    /**
     * @var string
     */
    private $module = 'Meetings';

    /**
     * @var string
     */
    private $collectionName = 'invitees';

    /**
     * @var SugarBean
     */
    private $bean;

    /**
     * @var User
     */
    private $user;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true]);
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->bean = SugarTestMeetingUtilities::createMeeting('', $this->user);
    }

    protected function tearDown(): void
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * Test case to check issue TR-14198.
     */
    public function testGetCollectionWithOnlyViewOwnerPermission()
    {
        $aclData = [];
        $aclData['module']['list']['aclaccess'] = ACL_ALLOW_ALL;
        $aclData['module']['view']['aclaccess'] = ACL_ALLOW_OWNER;
        ACLAction::setACLData($GLOBALS['current_user']->id, $this->module, $aclData);

        $this->bean->assigned_user_id = $this->user->id;
        $this->bean->save();
        BeanFactory::unregisterBean($this->module, $this->bean->id);

        $serviceBaseMock = $this->createMock('ServiceBase');
        $args = [
            'collection_name' => $this->collectionName,
            'module' => $this->module,
            'record' => $this->bean->id,
            'order_by' => [],
            'offset' => [],
            'max_num' => 20,
        ];

        $relateCollectionApi = $this->createPartialMock('RelateCollectionApi', [
            'normalizeArguments',
            'getSortSpec',
            'getAdditionalSortFields',
            'getData',
            'cleanData',
            'extractErrors',
            'buildResponse',
        ]);

        $relateCollectionApi->expects($this->once())->method('getSortSpec')->willReturn([]);
        $relateCollectionApi->expects($this->once())->method('getAdditionalSortFields')->willReturn([]);
        $relateCollectionApi->expects($this->once())->method('getData')->willReturn([
            [
                'records' => [],
                'next_offset' => 0,
            ],
        ]);

        $relateCollectionApi->expects($this->once())->method('cleanData')->willReturn([]);
        $relateCollectionApi->expects($this->once())->method('extractErrors')->willReturn([]);

        $relateCollectionApi->expects($this->once())
            ->method('normalizeArguments')
            ->with($args, $this->isInstanceOf('RelateCollectionDefinition'))
            ->willReturn($args);

        $relateCollectionApi->getCollection($serviceBaseMock, $args);
    }
}
