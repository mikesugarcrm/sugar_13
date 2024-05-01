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

use Regression\SugarCRMAssessment;
use Regression\Severity;

class PackageReadmeAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::HIGH;
    }

    /**
     * @return string
     */
    public function getAssessmentDescription(): string
    {
        return 'Stored XSS via Readme field at package.';
    }

    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                'index.php?to_pdf=1&sugar_body_only=1&module=ModuleBuilder&action=package&new=1',
                [],
                'index.php?module=ModuleBuilder&action=index&type=mb'
            )
            ->submitForm(
                'index.php?to_pdf=1&sugar_body_only=1',
                [
                    'module' => 'ModuleBuilder',
                    'action' => 'SavePackage',
                    'duplicate' => 0,
                    'to_pdf' => 1,
                    'original_name' => '',
                    'name' => 'xss_' . time(),
                    'author' => '',
                    'key' => 'xss_' . time(),
                    'description' => '',
                    'readme' => '</textarea><img src=x onerror=alert()>',
                ],
            )
            ->assumeSubstring('\u003Cimg src=x onerror=alert()', $xss)
            ->checkAssumptions('[readme] field is not escaped for HTML context', $xss);
    }
}
