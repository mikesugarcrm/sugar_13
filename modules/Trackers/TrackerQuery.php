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

class TrackerQuery extends SugarBean
{
    public $module_dir = 'Trackers';
    public $module_name = 'TrackerQueries';
    public $object_name = 'tracker_queries';
    public $table_name = 'tracker_queries';
    public $acltype = 'TrackerQuery';
    public $acl_category = 'TrackerQueries';
    public $disable_custom_fields = true;

    public $disable_row_level_security = true;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }
}
