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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\UrlValidator;

/**
 * This test adds some additional coverage for the OOTB Symfony URL constraint.
 *
 * @coversDefaultClass \Symfony\Component\Validator\Constraints\UrlValidator
 */
class UrlValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new UrlValidator();
    }

    /**
     * @covers ::validate
     * @dataProvider providerInvalidUrls
     */
    public function testInvalidUrls($url)
    {
        $constraint = new Url([
            'message' => 'myMessage',
        ]);

        $this->validator->validate($url, $constraint);
        $this->buildViolation('myMessage')
            ->setParameter('{{ value }}', '"' . $url . '"')
            ->setCode(Url::INVALID_URL_ERROR)
            ->assertRaised();
    }

    public function providerInvalidUrls()
    {
        return [
            ['/etc/hosts'],
            ['~/.profile'],
            ['public_html/'],
            ['C:\Windows\System32\stuff.ini'],
            ['\\\\share\\\\folder'],
        ];
    }
}
