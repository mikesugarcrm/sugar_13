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
use Regression\SugarCRMAssessment;
use Regression\SugarSession;

class PortalContactInfoAssessment extends SugarCRMAssessment
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        // Metadata and JS sources consume a lot of memory
        ini_set('memory_limit', '-1');
        // Create a new Contact
        $contactName = 'PortalUser' . time();
        $this->login('admin', 'asdf')
            ->apiCall(
                '/Contacts?erased_fields=true&viewed=1',
                'POST',
                [
                    'deleted' => false,
                    'do_not_call' => false,
                    'portal_active' => true,
                    'cookie_consent' => false,
                    'dp_business_purpose' => [],
                    'market_interest_prediction_score' => '',
                    'mkto_sync' => false,
                    'entry_source' => 'internal',
                    'perform_sugar_action' => false,
                    'assigned_user_id' => '1',
                    'salutation' => 'Mr.',
                    'lead_source' => '',
                    'preferred_language' => 'en_us',
                    'team_name' =>
                        [
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
                    'name' => 'Mr. ' . $contactName,
                    'first_name' => 'Test',
                    'last_name' => 'test',
                    'portal_name' => $contactName,
                    'portal_password' => $contactName,
                ]
            );

        $this
            ->portalLogin($contactName, $contactName)
            ->apiCall('/metadata/public?type_filter=&platform=portal&module_dependencies=1')
            ->extract('jsSource', function (ResponseInterface $response) {
                $json = (string)$response->getBody();
                $metadata = json_decode($json, true);
                return $metadata['jssource'];
            })
            ->get($this->getVar('jsSource'))
            ->assume(
                function (ResponseInterface $response) {
                    $content = (string) $response->getBody();
                    return !preg_match(
                        "~this\.contactInfo\.contactURL\s*=\s*app\.config\.siteUrl~is",
                        $content,
                    );
                },
                $noSanitization
            )->checkAssumptions('contactInfo[contactURL] allows the attacker to inject any protocol leading to XSS attack', $noSanitization);
    }

    public function portalLogin(string $username, string $password): self
    {
        $payload = json_encode([
            'username' => $username,
            'password' => $password,
            'grant_type' => 'password',
            'client_id' => 'support_portal',
            'platform' => 'portal',
            'client_secret' => '',
        ]);

        $tokenRequest = new Request(
            'POST',
            $this->prependBase("/oauth2/token?platform=portal"),
            ['Content-Type' => 'application/json',],
            $payload
        );
        $this->send($tokenRequest);
        if ($this->getLastResponse()->getStatusCode() !== 200 || ($token = (json_decode((string)$this->lastResponse->getBody()))->access_token) === null) {
            throw new \RuntimeException("Login failed");
        }
        $this->session = new SugarSession($token);
        return $this;
    }

    public function getAssessmentDescription(): string
    {
        return 'Stored XSS on Portal via contactInfo[contactURL] parameter';
    }
}
