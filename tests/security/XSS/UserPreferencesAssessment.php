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

use Psr\Http\Message\ResponseInterface;
use Regression\SugarCRMAssessment;

class UserPreferencesAssessment extends SugarCRMAssessment
{
    public function getAssessmentDescription(): string
    {
        return 'Stored XSS in User Preferences default_locale_name_format';
    }

    public function run(): void
    {
        $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->apiCall(
                '/me/preferences',
                'PUT',
                [
                    'timepref' => 'H:i',
                    'datepref' => 'm-d-Y',
                    'default_locale_name_format' => '%26gt;%26quot;%26lt;"\';}}></SCRIPT><img src=x onerror=alert(69)>${{7*7}}',
                    'ut' => true,
                ]
            )
            ->assume(function (ResponseInterface $response) {
                return $response->getStatusCode() < 400;
            }, $noValidation);
        $this->checkAssumptions('default_locale_name_format is not validated', $noValidation);
    }
}
