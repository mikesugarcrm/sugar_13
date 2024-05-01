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
declare(strict_types=1);

use Regression\SugarCRMRegression;

class EmployeesReflectedXssRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return "[BR-11260]: Reflected XSS at #bwc/index.php?module=Employees&action=DetailView";
    }

    public function run(): void
    {
        $this->loginAs('admin')
            ->bwcLogin()
            ->get('index.php?module=Employees&action=DetailView&record=1&apos;);x=eval&lpar;x=%60alert\x28document.cookie\x29%60&rpar;//rrrrrr=rrrrrrr')
            ->expectSubstring('\x2Findex.php\x3Fmodule\x3DEmployees\x26action\x3DDetailView\x26record\x3D1\x26apos\x3B\x29\x3Bx\x3Deval\x26lpar\x3Bx\x3D\x2560alert\x255Cx28document.cookie\x255Cx29\x2560\x26rpar\x3B\x2F\x2Frrrrrr');
    }
}
