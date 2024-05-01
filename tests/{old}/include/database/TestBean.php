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

class TestSugarBean extends SugarBean
{
    public $object_name = 'TestSugarBean';
    public $table_name = 'test';
    public $module_dir = 'Tests';
    public $disable_row_security = true;

    public function __construct($name, $vardefs)
    {
        global $dictionary;
        $this->object_name = $name;
        $this->table_name = $name;
        $dictionary[$this->object_name] = $vardefs;
        parent::__construct();
    }
}
