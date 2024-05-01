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
use Regression\SugarCRMRegression;

class StoredXssInCalendarViaFamilyNameRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return '[BR-10661]: Stored XSS on https://bugbounty-1.managed.ms.sugarcrm.com/#Calendar/center via family_name parameter.';
    }

    public function run(): void
    {
        $request = new Request(
            'GET',
            'modules/Calendar/clients/base/views/scheduler/scheduler.js',
        );

        $this
            ->send($request)
            ->expectStatusCode(200)
            ->expectSubstring('_.escape(event.module)')
            ->expectSubstring('_.escape(event.dbclickRecordId)')
            ->expectSubstring('_.escape(event.assignedUserName)')
            ->expectSubstring('_.escape(assignedUserColor)')
            ->expectSubstring('_.escape(inviteeName)')
            ->expectSubstring('_.escape(inviteeColor)');
    }
}
