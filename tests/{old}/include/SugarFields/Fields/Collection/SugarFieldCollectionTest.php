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
 * Class SugarFieldCollectionTest
 *
 * @property User $ownerUser
 * @property User $nonOwnerUser
 * @property Role $role
 * @property SugarBean $bean
 */
class SugarFieldCollectionTest extends TestCase
{
    private $ownerUser;
    private $nonOwnerUser;
    private $role;
    private $bean;

    protected function setUp(): void
    {
        \SugarTestHelper::setUp('dictionary');
        \SugarTestHelper::setUp('current_user');
        $this->ownerUser = \SugarTestUserUtilities::createAnonymousUser();
        $this->nonOwnerUser = \SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        \SugarTestACLUtilities::tearDown();
        \ACLField::clearACLCache();
        \SugarTestCaseUtilities::removeAllCreatedCases();
        unset($this->role);
        unset($this->bean);
        \SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        \SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider providerGetForbiddenLinksRetrievesRestrictedLinks
     * @param int $accessLevel Access level.
     * @param array $ownerExpectedResults List of links that should be
     *   restricted for the record owner.
     * @param array $nonOwnerExpectedResults List of links that should be
     *   restricted for users that do not own the record.
     */
    public function testGetForbiddenLinksRetrievesRestrictedLinks(
        int   $accessLevel,
        array $ownerExpectedResults,
        array $nonOwnerExpectedResults
    ) {

        global $current_user;
        $field = \SugarFieldHandler::getSugarField('collection');

        // note, restricting a collection field propagates that restriction to its constitutient links
        $this->setUpRestrictedField('Cases', 'Case', 'commentlog', $accessLevel);

        // create a Cases bean and assign it
        $this->bean = \SugarTestCaseUtilities::createCase(
            '',
            [
                'name' => 'New Case',
                'assigned_user_id' => $this->ownerUser->id,
            ]
        );

        // check owner first
        $current_user = $this->ownerUser;
        $actual = $field->getForbiddenLinks($this->bean, 'read', ['commentlog_link', 'some_other_link']);
        $this->assertEquals(
            $ownerExpectedResults,
            $actual,
            'getForbiddenLinks did not retrieve the expected links for the record owner'
        );

        // now the non-owner
        $current_user = $this->nonOwnerUser;
        $actual = $field->getForbiddenLinks($this->bean, 'read', ['commentlog_link', 'some_other_link']);
        $this->assertEquals(
            $nonOwnerExpectedResults,
            $actual,
            'getForbiddenLinks did not retrieve the expected links for a non-owner'
        );
    }

    public function providerGetForbiddenLinksRetrievesRestrictedLinks(): array
    {
        return [
            [ACL_READ_ONLY, [], []],
            [ACL_READ_OWNER_WRITE, [], []],
            [ACL_OWNER_READ_WRITE, [], ['commentlog_link']],
            [ACL_READ_WRITE, [], []],
            [ACL_ALLOW_NONE, ['commentlog_link'], ['commentlog_link']],
        ];
    }

    private function setUpRestrictedField(string $module, string $object, string $field, int $accessLevel)
    {
        $this->role = \SugarTestACLUtilities::createRole(
            'Owner_Read_Owner_Write_Comment_Log',
            [],
            [],
            [],
            'module',
            false // we only want field-level ACL's.
        );
        \SugarTestACLUtilities::createField($this->role->id, $module, $field, $accessLevel, $object);

        \SugarTestACLUtilities::setupUser($this->role, $this->ownerUser);
        \SugarTestACLUtilities::setupUser($this->role, $this->nonOwnerUser, false);
    }
}
