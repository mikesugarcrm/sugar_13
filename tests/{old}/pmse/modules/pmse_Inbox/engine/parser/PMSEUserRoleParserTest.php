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

class PMSEUserRoleParserTest extends TestCase
{
    protected $dataParser;

    protected function setUp(): void
    {
        $this->dataParser = $this->getMockBuilder('PMSEUserRoleParser')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    public function testParseCriteriaTokenCurrentUserIsAdmin()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $userBeanMock->is_admin = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $currentUserMock->is_admin = 1;

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": null,
            "expType": "USER_ADMIN",
            "expLabel": "Current user is admin"
        }');

        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": "is_admin",
            "expType": "USER_ADMIN",
            "expLabel": "Current user is admin",
            "expToken": "{::future::Users::id::}",
            "currentValue": ["is_admin"]
        }');

        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals($expectedCriteriaToken, $resultCriteriaObject);
    }

    public function testParseCriteriaTokenOwnerIsAdmin()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $userBeanMock->expects($this->exactly(1))
            ->method('retrieve')
            ->with($this->isType('string'));
        $userBeanMock->is_admin = 1;

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "owner",
            "expOperator": "equals",
            "expValue": null,
            "expType": "USER_ADMIN",
            "expLabel": "Record owner is admin"
        }');

        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "owner",
            "expOperator": "equals",
            "expValue": "is_admin",
            "expType": "USER_ADMIN",
            "expLabel": "Record owner is admin",
            "expToken": "{::future::Users::id::}",
            "currentValue": ["is_admin"]
        }');

        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals($expectedCriteriaToken, $resultCriteriaObject);
    }

    public function testParseCriteriaTokenSupervisorIsAdmin()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->disableOriginalConstructor()
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $userBeanMock->expects($this->exactly(1))
            ->method('retrieve')
            ->with($this->isType('string'));

        $userBeanMock->is_admin = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "supervisor",
            "expOperator": "equals",
            "expValue": null,
            "expType": "USER_ADMIN",
            "expLabel": "Supervisor is admin"
        }');

        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "supervisor",
            "expOperator": "equals",
            "expValue": "is_admin",
            "expType": "USER_ADMIN",
            "expLabel": "Supervisor is admin",
            "expToken": "{::future::Users::id::}",
            "currentValue": ["is_admin"]
        }');

        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals($expectedCriteriaToken, $resultCriteriaObject);
    }

    public function testParseCriteriaTokenSupervisorNull()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $userBeanMock->expects($this->exactly(1))
            ->method('retrieve')
            ->with($this->isType('string'))
            ->willReturn(null);

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "supervisor",
            "expOperator": "equals",
            "expValue": null,
            "expType": "USER_ADMIN",
            "expLabel": "Supervisor is admin"
        }');
        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals([''], $resultCriteriaObject->currentValue);
    }

    public function testParseCriteriaTokenCurrentUserHasRoleAdmin()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $supervisorUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $supervisorUserMock->is_admin = 1;

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $userBeanMock->is_admin = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;

        $dbHandlerMock = $this->getMockBuilder('db')
            ->setMethods(['query'])
            ->getMock();

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": "is_admin",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin"
        }');

        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": "is_admin",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin",
            "expToken": "{::future::Users::id::}",
            "currentValue": ["is_admin"]
        }');

        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setDbHandler($dbHandlerMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals($expectedCriteriaToken, $resultCriteriaObject);
    }

    public function testParseCriteriaTokenCurrentUserHasRole()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $supervisorUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $supervisorUserMock->is_admin = 1;

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $userBeanMock->is_admin = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $currentUserMock->id = '1';
        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;

        $dbHandlerMock = $this->getMockBuilder('db')
            ->setMethods(['query', 'fetchByAssoc'])
            ->getMock();
        $dbHandlerMock->expects($this->exactly(1))
            ->method('query')
            ->with($this->isType('string'));
        $dbHandlerMock->expects($this->any())
            ->method('fetchByAssoc')
            ->willReturn(['id' => 'abc']);

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin"
        }');

        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin",
            "expToken": "{::future::Users::id::}",
            "currentValue": ["1"]
        }');

        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setDbHandler($dbHandlerMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals($expectedCriteriaToken, $resultCriteriaObject);
    }

    public function testParseCriteriaTokenOwnerHasRole()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $userBeanMock->is_admin = 1;
        $userBeanMock->id = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;

        $dbHandlerMock = $this->getMockBuilder('db')
            ->setMethods(['query', 'fetchByAssoc'])
            ->getMock();
        $dbHandlerMock->expects($this->exactly(1))
            ->method('query')
            ->with($this->isType('string'));

        $dbHandlerMock->expects($this->any())
            ->method('fetchByAssoc')
            ->willReturn(['id' => 'abc']);

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "owner",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin"
        }');

        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "owner",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin",
            "expToken": "{::future::Users::id::}",
            "currentValue": ["1"]
        }');

        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setDbHandler($dbHandlerMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals($expectedCriteriaToken, $resultCriteriaObject);
    }

    public function testParseCriteriaTokenOwnerHasRoleIsAdmin()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $supervisorUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $supervisorUserMock->is_admin = 1;

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $userBeanMock->is_admin = 1;
        $userBeanMock->id = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "owner",
            "expOperator": "equals",
            "expValue": "is_admin",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin"
        }');
        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals(['is_admin'], $resultCriteriaObject->currentValue);
    }

    public function testParseCriteriaTokenOwnerHasRoleAdmin()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $supervisorUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $supervisorUserMock->is_admin = 1;

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $userBeanMock->is_admin = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;
        $currentUserMock->id = '1';

        $dbHandlerMock = $this->getMockBuilder('db')
            ->setMethods(['query'])
            ->getMock();

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": "is_admin",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin"
        }');

        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": "is_admin",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin",
            "expToken": "{::future::Users::id::}",
            "currentValue": ["is_admin"]
        }');

        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setDbHandler($dbHandlerMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals($expectedCriteriaToken, $resultCriteriaObject);
    }

    public function testParseCriteriaTokenSupervisorHasRole()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $userBeanMock->expects($this->exactly(1))
            ->method('retrieve')
            ->with($this->isType('string'));

        $userBeanMock->is_admin = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;

        $dbHandlerMock = $this->getMockBuilder('db')
            ->setMethods(['query', 'fetchByAssoc'])
            ->getMock();

        $dbHandlerMock->expects($this->exactly(1))
            ->method('query')
            ->with($this->isType('string'));

        $dbHandlerMock->expects($this->any())
            ->method('fetchByAssoc')
            ->willReturn(['id' => 'abc']);

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "supervisor",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin"
        }');

        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "supervisor",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin",
            "expToken": "{::future::Users::id::}",
            "currentValue": ["1"]
        }');

        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setDbHandler($dbHandlerMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals($expectedCriteriaToken, $resultCriteriaObject);
    }

    public function testParseCriteriaTokenSupervisor()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $userBeanMock->expects($this->any())
            ->method('retrieve')
            ->with($this->isType('string'));

        $userBeanMock->is_admin = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "supervisor",
            "expOperator": "equals",
            "expValue": "is_admin",
            "expType": "USER_ROLE",
            "expLabel": "Supervisor is admin"
        }');
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals(['is_admin'], $resultCriteriaObject->currentValue);
    }

    public function testParseCriteriaTokenCurrentUserHasIdentity()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $supervisorUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $supervisorUserMock->is_admin = 1;
        $supervisorUserMock->id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $userBeanMock->is_admin = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;
        $currentUserMock->id = 1;

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_IDENTITY",
            "expLabel": "Supervisor is admin"
        }');

        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_IDENTITY",
            "expLabel": "Supervisor is admin",
            "expToken": "{::future::Users::id::}",
            "currentValue": ["1"]
        }');

        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals($expectedCriteriaToken, $resultCriteriaObject);
    }

    public function testParseCriteriaTokenOwnerHasIdentity()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $supervisorUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $supervisorUserMock->is_admin = 1;
        $supervisorUserMock->id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $userBeanMock->is_admin = 1;
        $userBeanMock->id = '1';

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "owner",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_IDENTITY",
            "expLabel": "Supervisor is admin"
        }');

        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "owner",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_IDENTITY",
            "expLabel": "Supervisor is admin",
            "expToken": "{::future::Users::id::}",
            "currentValue": ["1"]
        }');

        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals($expectedCriteriaToken, $resultCriteriaObject);
    }

    public function testParseCriteriaTokenSupervisorHasIdentity()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $supervisorUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $supervisorUserMock->is_admin = 1;
        $supervisorUserMock->id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $userBeanMock->is_admin = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;
        $currentUserMock->id = '1';

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "supervisor",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_IDENTITY",
            "expLabel": "Supervisor = \"1\""
        }');

        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "supervisor",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_IDENTITY",
            "expLabel": "Supervisor = \"1\"",
            "expToken": "{::future::Users::id::}",
            "currentValue": ["1"]
        }');

        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals($expectedCriteriaToken, $resultCriteriaObject);
    }

    public function testParseCriteriaTokenOwnerHasIdentityNull()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = null;

        $supervisorUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $supervisorUserMock->is_admin = 1;
        $supervisorUserMock->id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $userBeanMock->is_admin = 1;
        $userBeanMock->id = '1';

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $currentUserMock->reports_to_id = '1';
        $currentUserMock->is_admin = 1;

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "owner",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_IDENTITY",
            "expLabel": "Supervisor is admin"
        }');
        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals(['false'], $resultCriteriaObject->currentValue);
    }

    public function testParseCriteriaTokenSupervisorHasIdentityNull()
    {
        $evaluatedBeanMock = $this->getMockBuilder('leadMock')
            ->setMockClassName('leads')
            ->getMock();

        $evaluatedBeanMock->assigned_user_id = '1';

        $supervisorUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $supervisorUserMock->is_admin = 1;
        $supervisorUserMock->id = '1';

        $userBeanMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $userBeanMock->is_admin = 1;

        $currentUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $currentUserMock->reports_to_id = null;
        $currentUserMock->is_admin = 1;
        $currentUserMock->id = '1';

        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "supervisor",
            "expOperator": "equals",
            "expValue": "1",
            "expType": "USER_IDENTITY",
            "expLabel": "Supervisor = \"1\""
        }');
        $this->dataParser->setCurrentUser($currentUserMock);
        $this->dataParser->setUserBean($userBeanMock);
        $this->dataParser->setEvaluatedBean($evaluatedBeanMock);
        $resultCriteriaObject = $this->dataParser->parseCriteriaToken($criteriaToken);
        $this->assertEquals(['false'], $resultCriteriaObject->currentValue);
    }

    public function testDecomposeToken()
    {
        $resultCriteriaObject = $this->dataParser->decomposeToken('{::future::Users::id::}');
        $this->assertEquals('future', $resultCriteriaObject[0]);
        $this->assertEquals('Users', $resultCriteriaObject[1]);
        $this->assertEquals('id', $resultCriteriaObject[2]);
    }
}
