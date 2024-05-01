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
use Psr\Http\Message\ResponseInterface;
use Regression\Severity;
use Regression\SugarCRMAssessment;

class StoredXssInCalendarViaFamilyNameAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::MEDIUM;
    }

    public function getAssessmentDescription(): string
    {
        return 'Stored XSS via family_name parameter.';
    }

    public function run(): void
    {
        $request = new Request(
            'GET',
            'modules/Calendar/clients/base/views/scheduler/scheduler.js',
        );

        $this
            ->send($request)
            ->assumeSubstring('_.escape(event.module)', $escapedModule)
            ->assumeSubstring('_.escape(event.dbclickRecordId)', $escapedRecordId)
            ->assumeSubstring('_.escape(event.assignedUserName)', $escapedAssignee)
            ->assumeSubstring('_.escape(assignedUserColor)', $escapedAssigneeColor)
            ->assumeSubstring('_.escape(inviteeName)', $escapedInvitee)
            ->assumeSubstring('_.escape(inviteeColor)', $escapedInviteeColor)
            ->checkAssumptions(
                'Stored XSS in modules/Calendar/clients/base/views/scheduler/scheduler.js was found',
                !$escapedModule,
                !$escapedRecordId,
                !$escapedAssignee,
                !$escapedAssigneeColor,
                !$escapedInvitee,
                !$escapedInviteeColor,
            );
    }
}
