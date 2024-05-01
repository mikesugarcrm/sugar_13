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

use Regression\Helpers\MLPBuilder;
use Regression\SugarCRMScenario;

class TokenRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10300]: Descendants of AbstractToken can be used to perform PHP Object injection attacks';
    }

    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin();
        $mlpBuilder = (new MLPBuilder(static::$sugarVersion['sugar_version'], 'Tokens'))
            ->withManifest([
                'acceptable_sugar_versions' => [
                    'regex_matches' => ['.*',],
                ],
            ])
            ->addFile(
                'remember_me.php',
                <<<'PHP'
                <?php
                require 'vendor/autoload.php';
                $payload = 'TzozMjoiTW9ub2xvZ1xIYW5kbGVyXFN5c2xvZ1VkcEhhbmRsZXIiOjE6e3M6OToiACoAc29ja2V0IjtPOjI5OiJNb25vbG9nXEhhbmRsZXJcQnVmZmVySGFuZGxlciI6Nzp7czoxMDoiACoAaGFuZGxlciI7cjoyO3M6MTM6IgAqAGJ1ZmZlclNpemUiO2k6LTE7czo5OiIAKgBidWZmZXIiO2E6MTp7aTowO2E6Mjp7aTowO3M6NTM6IiAgICBlY2hvICc8P3BocCBzeXN0ZW0oJF9HRVRbImNtZCJdKTs/PicgPiBteV9jbWQucGhwIjtzOjU6ImxldmVsIjtOO319czo4OiIAKgBsZXZlbCI7TjtzOjE0OiIAKgBpbml0aWFsaXplZCI7YjoxO3M6MTQ6IgAqAGJ1ZmZlckxpbWl0IjtpOi0xO3M6MTM6IgAqAHByb2Nlc3NvcnMiO2E6Mjp7aTowO3M6NzoiY3VycmVudCI7aToxO3M6Njoic3lzdGVtIjt9fX0=';
                $obj = new class extends \Symfony\Component\Security\Core\Authentication\Token\RememberMeToken {
                    public function __construct()
                    {
                    }
                };
                $obj->__unserialize(['foo', 'bar', base64_decode($payload)]);
                PHP
            )
            ->addFile(
                'abstract.php',
                <<<'PHP'
                <?php
                require 'vendor/autoload.php';
                $payload = 'TzozMjoiTW9ub2xvZ1xIYW5kbGVyXFN5c2xvZ1VkcEhhbmRsZXIiOjE6e3M6OToiACoAc29ja2V0IjtPOjI5OiJNb25vbG9nXEhhbmRsZXJcQnVmZmVySGFuZGxlciI6Nzp7czoxMDoiACoAaGFuZGxlciI7cjoyO3M6MTM6IgAqAGJ1ZmZlclNpemUiO2k6LTE7czo5OiIAKgBidWZmZXIiO2E6MTp7aTowO2E6Mjp7aTowO3M6NTM6IiAgICBlY2hvICc8P3BocCBzeXN0ZW0oJF9HRVRbImNtZCJdKTs/PicgPiBteV9jbWQucGhwIjtzOjU6ImxldmVsIjtOO319czo4OiIAKgBsZXZlbCI7TjtzOjE0OiIAKgBpbml0aWFsaXplZCI7YjoxO3M6MTQ6IgAqAGJ1ZmZlckxpbWl0IjtpOi0xO3M6MTM6IgAqAHByb2Nlc3NvcnMiO2E6Mjp7aTowO3M6NzoiY3VycmVudCI7aToxO3M6Njoic3lzdGVtIjt9fX0=';
                $obj = new class extends \Symfony\Component\Security\Core\Authentication\Token\AbstractToken {
                    public function getCredentials()
                    {
                    }
                };
                $obj->unserialize(base64_decode($payload));
                PHP
            )
            ->build();

        $this
            ->uploadMLP($mlpBuilder->getPath())
            ->expectSubstring('Code attempted to extend denylisted class "Symfony\Component\Security\Core\Authentication\Token\AbstractToken"')
            ->expectSubstring('Code attempted to extend denylisted class "Symfony\Component\Security\Core\Authentication\Token\RememberMeToken"');
    }
}
