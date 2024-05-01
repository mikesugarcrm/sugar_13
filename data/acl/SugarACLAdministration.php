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

/**
 * This class is used to enforce ACLs on modules that are restricted to admins only.
 */
class SugarACLAdministration extends SugarACLStrategy
{
    /**
     * Only allow access to users with the user admin setting
     * @param string $module
     * @param string $view
     * @param array $context
     * @return bool|void
     */
    public function checkAccess($module, $view, $context)
    {
        $current_user = $this->getCurrentUser($context);
        if (!$current_user) {
            return false;
        }

        if ($current_user->isAdmin()) {
            return true;
        }

        // if they are admin for ANY modules

        $adminForAny = $current_user->getAdminModules();

        if (!empty($adminForAny)) {
            return true;
        }

        return false;
    }
}