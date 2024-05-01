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

use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Regression\SugarCRMScenario;

class EmailTemplateAttachmentsRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10139] Create security regression test for SugarCRM 0-day RCE';
    }

    public function run(): void
    {
        $multiPartStream = new MultipartStream([
            [
                'name' => 'test-email-attachement.txt',
                'contents' => Utils::tryFopen(__DIR__ . '/test-email-attachement.txt', 'r'),
            ],
        ]);
        $emailTemplateRequest = new Request(
            'POST',
            'index.php?to_pdf=1&module=EmailTemplates&action=AttachFiles',
            [],
            $multiPartStream
        );

        $loggedInSession = $this->login('sarah', 'sarah')->bwcLogin();

        $loggedInSession
            ->send($emailTemplateRequest)
            ->expectStatusCode(500);

        $getRequest = new Request(
            'GET',
            'index.php?to_pdf=1&module=EmailTemplates&action=AttachFiles',
        );

        $loggedInSession
            ->send($getRequest)
            ->expectStatusCode(500);
    }
}
