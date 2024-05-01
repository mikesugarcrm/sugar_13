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
use Regression\SugarCRMScenario;

class VCardDownloadRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-9991] Semicolon permits downloaded vCard file extension change';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $scenario = $this->login('admin', 'asdf')
            ->bwcLogin();

        $scenario->apiCall(
            '/Contacts',
            'POST',
            [
                'deleted' => false,
                'do_not_call' => false,
                'portal_active' => false,
                'cookie_consent' => false,
                'dp_business_purpose' => [
                ],
                'mkto_sync' => false,
                'entry_source' => 'internal',
                'assigned_user_id' => '1',
                'salutation' => '',
                'lead_source' => '',
                'preferred_language' => 'en_us',
                'team_name' => [
                    0 =>
                        [
                            'id' => '1',
                            'display_name' => 'Global',
                            'name' => 'Global',
                            'name_2' => '',
                            'primary' => true,
                            'selected' => false,
                        ],
                ],
                'first_name' => 'test.bat;',
                'name' => 'foo;bar',
                'last_name' => '& cmd.exe',
            ]
        )
            ->expectStatusCode(200)
            ->extract('contactId', function (ResponseInterface $response) {
                $json = json_decode((string)$response->getBody(), true);
                return $json['id'];
            });
        $contactId = $scenario->getVar('contactId');
        $scenario->apiCall(
            "/Contacts/{$contactId}/vcard?platform=base"
        )->expect(function (ResponseInterface $response) {
            $disposition = $response->getHeaderLine('Content-Disposition');
            return $disposition === "attachment; filename*=utf-8''test.bat%3B_%26_cmd.exe.vcf";
        }, 'Filename is not properly escaped');
    }
}
