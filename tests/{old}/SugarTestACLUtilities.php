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

class SugarTestACLUtilities
{
    private static $createdRoles = [];
    private static $modules = [];
    private static $objects = [];

    private function __construct()
    {
    }

    /**
     * Create a Role for use in a Unit Test.
     *
     * @param string $name Name of the role.
     * @param array $allowedModules Modules you want to give access to.
     * @param array $allowedActions Actions user is allowed to have.
     * @param array $ownerActions Any owner actions [Edit Owner, etc] the user needs.
     * @param string $type Type of role. Defaults to "module".
     * @param bool $applyRestrictions If false, do not apply any restrictions.
     *   Useful if you want to use this Role only for field-level ACL's.
     *   Defaults to true.
     * @return SugarBean The created Role.
     */
    public static function createRole(
        $name,
        $allowedModules,
        $allowedActions,
        $ownerActions = [],
        $type = 'module',
        bool $applyRestrictions = true
    ) {

        self::$modules = array_merge($allowedModules, self::$modules);

        $role = new ACLRole();
        $role->name = $name;
        $role->description = $name;
        $role->save();
        $db = DBManagerFactory::getInstance();
        $db->commit();

        if ($applyRestrictions) {
            $roleActions = $role->getRoleActions($role->id);
            foreach ($roleActions as $moduleName => $actions) {
                // enable allowed modules
                if (isset($actions[$type]['access']['id']) && !in_array($moduleName, $allowedModules)) {
                    $role->setAction($role->id, $actions[$type]['access']['id'], ACL_ALLOW_DISABLED);
                } elseif (isset($actions[$type]['access']['id']) && in_array($moduleName, $allowedModules)) {
                    $role->setAction($role->id, $actions[$type]['access']['id'], ACL_ALLOW_ENABLED);
                } else {
                    foreach ($actions as $action => $actionName) {
                        if (isset($actions[$action]['access']['id'])) {
                            $role->setAction($role->id, $actions[$action]['access']['id'], ACL_ALLOW_DISABLED);
                        }
                    }
                }

                if (in_array($moduleName, $allowedModules)) {
                    foreach ($actions[$type] as $actionName => $action) {
                        if (in_array($actionName, $allowedActions) && in_array($actionName, $ownerActions)) {
                            $aclAllow = ACL_ALLOW_OWNER;
                        } elseif (in_array($actionName, $allowedActions)) {
                            $aclAllow = ACL_ALLOW_ALL;
                        } else {
                            $aclAllow = ACL_ALLOW_NONE;
                        }

                        $role->setAction($role->id, $action['id'], $aclAllow);
                    }
                }
            }
        }

        self::$createdRoles[] = $role;

        return $role;
    }

    /**
     * Restrict access to a field.
     *
     * @param string $role_id The role to add this to.
     * @param string $module The module that has the field.
     * @param string $field_name The field name to apply the access to.
     * @param int $access_level The access level from ACLField/actiondefs.php.
     * @param ?string $objectName Name of the object corresponding to the given
     *   module, if it differs from the module name.
     * @return SugarBean The created field.
     */
    public static function createField(
        $role_id,
        $module,
        $field_name,
        $access_level,
        string $objectName = ''
    ) {

        self::$modules[] = $module;

        if (!empty($objectName)) {
            self::$objects[$module] = $objectName;
        }

        $aclField = new ACLField();
        $aclField->setAccessControl($module, $role_id, $field_name, $access_level);

        return $aclField;
    }

    /**
     * Give a user a role.
     *
     * @param SugarBean $role Role bean.
     * @param User The User to give the role to. Defaults to current_user.
     * @return null
     */
    public static function setupUser($role, \User $user = null, bool $clearACLCache = true)
    {
        global $current_user;

        if (empty($user)) {
            $oldCurrentUser = $current_user;
            $user = $current_user;
        }

        if (!($user->check_role_membership($role->name))) {
            $user->load_relationship('aclroles');
            $user->aclroles->add($role);
            $user->save();
        }

        $id = $user->id;
        $current_user = BeanFactory::getBean('Users', $id);

        if ($clearACLCache) {
            BeanFactory::newBean('ACLFields')->clearACLCache();
        }

        foreach (self::$modules as $module) {
            $object = self::$objects[$module] ?? $module;
            ACLField::loadUserFields($module, $object, $current_user->id, true);
        }

        if (isset($oldCurrentUser)) {
            $current_user = $oldCurrentUser;
        }
    }

    /**
     * TearDown method to remove any roles and fields setup
     * @return null
     */
    public static function tearDown()
    {
        foreach (self::$createdRoles as $role) {
            $role->mark_deleted($role->id);
            $role->mark_relationships_deleted($role->id);
            $GLOBALS['db']->query("DELETE FROM acl_fields WHERE role_id = '{$role->id}'");
        }
        self::$createdRoles = [];
        BeanFactory::newBean('ACLFields')->clearACLCache();
        SugarACL::resetACLs();
    }
}
