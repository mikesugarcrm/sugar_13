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

class StoredXssInCalendarNameAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::MEDIUM;
    }

    public function getAssessmentDescription(): string
    {
        return 'Stored XSS via calendar name parameter.';
    }

    public function run(): void
    {
        $request = new Request(
            'GET',
            'modules/Calendar/clients/base/views/scheduler/scheduler.js',
        );

        $this
            ->send($request)
            ->assumeSubstring('text: DOMPurify.sanitize(calendarDef.name)', $escapedCalendarName)
            ->assumeSubstring('value: DOMPurify.sanitize(calendarDef.id)', $escapedCalendarId)
            ->assumeSubstring('color: DOMPurify.sanitize(calendarDef.color)', $escapedCalendarColor)
            ->checkAssumptions(
                'Calendar is vulnerable to XSS attack through calendarName or calendarId or calendarColor',
                !$escapedCalendarName,
                !$escapedCalendarId,
                !$escapedCalendarColor,
            );
    }
}
