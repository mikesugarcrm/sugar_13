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

use GuzzleHttp\Psr7\Request;
use Regression\Severity;
use Regression\SugarCRMAssessment;

class StoredXssOnReportsWizardAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::MEDIUM;
    }

    public function getAssessmentDescription(): string
    {
        return 'Stored XSS on Reports Wizard';
    }

    public function run(): void
    {
        $request = new Request('GET', 'cache/include/javascript/sugar_grp1_yui.js');

        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->send($request)
            ->assumeSubstring('_populateListItem=function(b,a,c){b.innerHTML', $xss)
            ->checkAssumptions('_populateListItem function in sugar_grp1_yui.js is vulnerable to XSS', $xss);
    }
}
