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

class GetControlTemplateInjectionAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::HIGH;
    }

    public function getAssessmentDescription(): string
    {
        return 'Server-Side Template Injection through GetControl action';
    }

    public function run(): void
    {
        $request = new Request(
            'GET',
            'index.php?module=Import&action=GetControl&import_module=Bugs&field_name=/../../../../upload/test',
        );

        $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->send($request)
            ->assumeSubstring('Invalid filename', $escaped)
            ->checkAssumptions('Template injection through GetControl action was found', !$escaped);
    }
}
