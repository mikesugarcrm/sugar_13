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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

class WebToLeadTest extends TestCase
{
    private $campaignId;
    private $configOptoutBackUp;
    private $configFileOverride;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        if (isset($GLOBALS['sugar_config']['new_email_addresses_opted_out'])) {
            $this->configOptoutBackUp = $GLOBALS['sugar_config']['new_email_addresses_opted_out'];
        }

        $campaign = SugarTestCampaignUtilities::createCampaign();
        $this->campaignId = $campaign->id;

        if (file_exists('config_override.php')) {
            $this->configFileOverride = file_get_contents('config_override.php');
        } else {
            $this->configFileOverride = "<?php\r\n";
        }
    }

    protected function tearDown(): void
    {
        SugarAutoLoader::put('config_override.php', $this->configFileOverride);

        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();

        if (isset($this->configOptoutBackUp)) {
            $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $this->configOptoutBackUp;
        } else {
            unset($GLOBALS['sugar_config']['new_email_addresses_opted_out']);
        }
    }

    public function optoutDataProvider()
    {
        return [
            [
                false,
                [],
                0,
            ],
            [
                false,
                ['email_opt_out' => true],
                1,
            ],
            [
                false,
                ['email_opt_in' => 'on'],
                0,
            ],
            [
                false,
                ['email_opt_out' => 'off'],
                1,
            ],
            [
                false,
                [
                    'email_opt_in' => 'on',
                    'email_opt_out' => true,
                ],
                0,
            ],
            [
                false,
                [
                    'email_opt_in' => 'off',
                    'email_opt_out' => true,
                ],
                1,
            ],
            [
                true,
                [],
                1,
            ],
            [
                true,
                ['email_opt_out' => true],
                1,
            ],
            [
                true,
                ['email_opt_in' => 'on'],
                0,
            ],
            [
                true,
                ['email_opt_out' => 'off'],
                1,
            ],
            [
                true,
                [
                    'email_opt_in' => 'on',
                    'email_opt_out' => true,
                ],
                0,
            ],
            [
                true,
                [
                    'email_opt_in' => 'off',
                    'email_opt_out' => true,
                ],
                1,
            ],
        ];
    }

    /**
     * Create Web Lead with Email Address and test the different scenarios that can determine the Email Optout setting.
     *
     * @dataProvider optoutDataProvider
     */
    public function testWebToLeadRequest($configDefaultValue, $formVars, $expectedResult)
    {
        $this->setConfigOptout($configDefaultValue);

        $emails = [];
        for ($i = 0; $i <= 1; $i++) {
            $emails[] = "test{$i}_" . Uuid::uuid1() . '@testonly.app';
        }

        $requestId = Uuid::uuid1();
        $_POST = [
            'first_name' => 'TestFirstName',
            'last_name' => 'TestLastName',
            'campaign_id' => $this->campaignId,
            'redirect_url' => 'http://www.sugarcrm.com/index.php',
            'assigned_user_id' => '1',
            'team_id' => '1',
            'team_set_id' => 'Global',
            'email' => $emails[0],
            'email2' => $emails[1],
            'req_id' => $requestId,
        ];
        foreach ($formVars as $key => $value) {
            $_POST[$key] = $value;
        }

        $postString = '';
        foreach ($_POST as $k => $v) {
            $postString .= "{$k}=" . urlencode($v) . '&';
        }
        $postString = rtrim($postString, '&');

        $ch = curl_init("{$GLOBALS['sugar_config']['site_url']}/index.php?entryPoint=WebToLeadCapture");
        curl_setopt($ch, CURLOPT_POST, count($_POST) + 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        ob_start();
        curl_exec($ch);
        $output = ob_get_clean();

        // First, save ids of created leads so they can be deleted at tearDown.
        $ea = BeanFactory::newBean('EmailAddresses');

        foreach ($emails as $email) {
            // Each email address is associated with a Leads record.
            $beans = $ea->getBeansByEmailAddress($email);
            $leadIds = array_map(function ($bean) {
                return $bean->id;
            }, $beans);
            SugarTestLeadUtilities::setCreatedLead($leadIds);
        }

        // Lastly, verify email addresses were successfully created with the expected address properties.
        for ($i = 0; $i < count($emails); $i++) {
            $ea = $this->fetchEmailAddress($emails[$i]);
            $this->assertNotEmpty($ea, 'Expected Email Address was not found: ' . $emails[$i]);
            $this->assertEquals(0, $ea['invalid_email'], 'Expected Email Address to be Valid');
            $this->assertEquals(
                $expectedResult,
                $ea['opt_out'],
                'Email Address [' . $i . '] opt_out value incorrect'
            );
        }
    }

    private function fetchEmailAddress($emailAddress = '')
    {
        $sea = BeanFactory::newBean('EmailAddresses');
        $q = new SugarQuery();
        $q->select(['*']);
        $q->from($sea);
        $q->where()->queryAnd()
            ->equals('email_address_caps', sugarStrToUpper($emailAddress))
            ->equals('deleted', 0);
        $q->limit(1);
        $rows = $q->execute();
        if (empty($rows)) {
            return [];
        }
        return $rows[0];
    }

    private function setConfigOptout(bool $optOut)
    {
        $config = '$sugar_config[\'new_email_addresses_opted_out\'] = ' . ($optOut ? 'true' : 'false') . ';';
        SugarAutoLoader::put('config_override.php', $this->configFileOverride . "\r\n" . $config, true);
    }
}
