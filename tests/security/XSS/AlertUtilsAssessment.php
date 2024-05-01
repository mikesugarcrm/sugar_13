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

class AlertUtilsAssessment extends SugarCRMAssessment
{
    public function getAssessmentDescription(): string
    {
        return 'XSS in Calendar Scheduler';
    }

    public function run(): void
    {
        $this->get('modules/Calendar/clients/base/views/scheduler/scheduler.js')
            ->assume(function (\Psr\Http\Message\ResponseInterface $response) {
                $content = (string)$response->getBody();
                preg_match('~<div class="calendarEventBody">(.*?htmlContent.*?)</div>~is', $content, $m);
                if (empty($m[1])) {
                    return false;
                }
                return (strpos($m[1], '_.escape(') === false)
                    && (strpos($m[1], 'DOMPurify.sanitize(') === false);
            }, $result)
            ->checkAssumptions('User-controlled data is not escaped for HTML context', $result);
    }
}
