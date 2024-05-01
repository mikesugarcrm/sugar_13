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


use Sugarcrm\Sugarcrm\Portal\Factory as PortalFactory;

class SugarTestPortalUtilities
{
    protected static $originalPortalState = false;
    public static $modulesCreationAllowed = [
        'Notes',
        'Cases',
        'Bugs',
    ];

    public static $modulesListviewsAllowed = [
        'Notes',
        'Cases',
        'Bugs',
        'KBContents',
        'Categories',
        'Dashboards',
    ];

    public static $modulesToIgnore = [
        'Manufacturers',
    ];

    protected static $manuallyEnabledRolesModules = [];

    protected static $requiredEnabledRolesModules = [
        'Bugs',
        'Cases',
        'Notes',
        'KBContents',
        'Contacts',
    ];

    protected static $originalUser;

    protected static function retrieveCurrentPortalState()
    {
        sugar_cache_clear('admin_settings_cache');
        $admin = new Administration();
        $systemConfig = $admin->retrieveSettings();
        return !empty($systemConfig->settings['portal_on']);
    }

    public static function enablePortal()
    {
        // save previous portal enabled state
        self::$originalPortalState = self::retrieveCurrentPortalState();

        if (!self::$originalPortalState) {
            $portal = new ParserModifyPortalConfig();
            $portal->setUpPortal();
        }

        // manipulate the role, to make sure the 5 modules required are set (required after the introduction of CS-296)
        self::enableRequiredModulesForPortalRole();
    }

    protected static function getPortalRole()
    {
        $q = new SugarQuery();
        $q->select('id');
        $q->from(BeanFactory::newBean('ACLRoles'));
        $q->where()->equals('name', 'Customer Self-Service Portal Role');
        $result = $q->execute();

        if (!empty($result['0']['id'])) {
            $role = BeanFactory::retrieveBean('ACLRoles', $result['0']['id']);
        }

        if (!empty($role->id)) {
            return $role;
        } else {
            return null;
        }
    }

    protected static function enableRequiredModulesForPortalRole()
    {
        // store the original settings, if not already done
        $role = self::getPortalRole();
        if (empty(self::$manuallyEnabledRolesModules) && !empty($role->id)) {
            $actions = $role->getRoleActions($role->id);
            if (!empty($actions)) {
                foreach (self::$requiredEnabledRolesModules as $module) {
                    if (!empty($actions[$module]['module']['access']['aclaccess']) &&
                        !empty($actions[$module]['module']['access']['id']) &&
                        $actions[$module]['module']['access']['aclaccess'] === ACL_ALLOW_DISABLED) {
                        // enable
                        self::$manuallyEnabledRolesModules[] = $module;
                        $role->setAction($role->id, $actions[$module]['module']['access']['id'], ACL_ALLOW_ENABLED);
                    }
                }
            }
        }
    }

    protected static function restoreModulesForPortalRole()
    {
        $role = self::getPortalRole();
        if (!empty(self::$manuallyEnabledRolesModules) && !empty($role)) {
            $actions = $role->getRoleActions($role->id);
            foreach (self::$manuallyEnabledRolesModules as $key => $module) {
                $id = $actions[$module]['module']['access']['id'] ?? '';
                if (!empty($id)) {
                    $role->setAction($role->id, $id, ACL_ALLOW_DISABLED);
                }
                unset(self::$manuallyEnabledRolesModules[$key]);
            }
        }
    }

    public static function getPortalUser()
    {
        $portal = new ParserModifyPortalConfig();
        return $portal->getPortalUser();
    }

    public static function storeOriginalUser()
    {
        // the first time we call this method, store the original user to be able to restore it at the end
        if (empty(self::$originalUser) && !empty($GLOBALS['current_user'])) {
            self::$originalUser = clone($GLOBALS['current_user']);
        }
    }

    public static function loginAsPortalUser(string $contactId)
    {
        self::storeOriginalUser();
        self::clearLoggedInUser();
        $GLOBALS['current_user'] = self::getPortalUser();
        $_SESSION['portal_user_id'] = $GLOBALS['current_user']->id;
        $_SESSION['contact_id'] = $contactId;
        return SugarTestRestUtilities::getRestServiceMock($GLOBALS['current_user'], 'portal', 'support_portal');
    }

    public static function restoreOriginalUser()
    {
        if (!empty(self::$originalUser)) {
            self::clearLoggedInUser();
            $GLOBALS['current_user'] = self::$originalUser;
        }
    }

    public static function removeSessionSpecificVariables()
    {
        unset($_SESSION['portal_user_id']);
        unset($_SESSION['contact_id']);
        SugarTestRestUtilities::cleanupRestServiceMock('portal');
    }

    public static function removeCache()
    {
        BeanFactory::clearCache();
        SugarACL::resetACLs();
        sugar_cache_clear('admin_settings_cache');
    }

    public static function clearLoggedInUser()
    {
        PortalFactory::getInstance('Session')->unsetCache();
        self::removeCache();
        self::removeSessionSpecificVariables();
        unset($GLOBALS['current_user']);
    }

    public static function restoreNormalUser(bool $isAdmin = false)
    {
        self::clearLoggedInUser();
        SugarTestHelper::setUp('current_user', [true, $isAdmin]);
    }

    public static function getPortalCurrentUser(string $contactId)
    {
        return (new CurrentUserPortalApi())->retrieveCurrentUser(self::loginAsPortalUser($contactId), []);
    }

    public static function createBasicObject(string $module, string $id)
    {
        // retrieve also deleted, just in case the same id exists as soft deleted record
        $b = BeanFactory::getBean($module, $id, ['deleted' => false]);
        if (empty($b->id)) {
            $b->new_with_id = true;
            $b->id = $id;
        }
        if ($b->deleted) {
            $b->mark_undeleted($id);
        }

        return $b;
    }

    public static function deleteSingleRecord($module, $id)
    {
        $b = BeanFactory::retrieveBean($module, $id);
        if (!empty($b)) {
            // soft delete just in case some additional related records are left behind
            $b->mark_deleted($id);
            // hard delete the record as well
            $qb = \DBManagerFactory::getInstance()->getConnection()->createQueryBuilder();
            $qb->delete($b->table_name)->where($qb->expr()->eq('id', $qb->createPositionalParameter($id)))->execute();
        }
    }

    public static function disablePortal()
    {
        // if any of the roles settings have been changed, revert them
        self::restoreModulesForPortalRole();

        // only disable the portal if it was disabled when attempting to enable it
        if (!self::$originalPortalState) {
            $portal = new ParserModifyPortalConfig();
            $portal->unsetPortal();
            sugar_cache_clear('admin_settings_cache');
        }
    }
}
