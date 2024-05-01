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

namespace Sugarcrm\SugarcrmTests\Denormalization\Relate\FunctionalityCases\One2Many;

use SugarBean;
use SugarTestContactUtilities;
use SugarTestTaskUtilities;

class TasksContactFunctionalityTest extends AbstractFunctionalityTest
{
    protected static $options = [
        'primary_module' => 'Tasks',
        'field_id' => 'contact_id',
        'field_name' => 'contact_name',
        'relate_field_name' => 'last_name',
        'primary_link_name' => 'contacts',
    ];

    protected function createPrimaryBean(?SugarBean $linkedBean): SugarBean
    {
        $task = SugarTestTaskUtilities::createTask();

        if ($linkedBean) {
            $task->contact_id = $linkedBean->id;
            $task->contact_name = $linkedBean->last_name;
            $task->save();
        }

        return $task;
    }

    protected function createLinkedBean(): SugarBean
    {
        return SugarTestContactUtilities::createContact();
    }

    protected static function removeCreatedBeans(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestTaskUtilities::removeAllCreatedTasks();
    }
}
