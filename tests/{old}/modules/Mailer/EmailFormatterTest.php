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

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

class EmailFormatterTest extends TestCase
{
    /**
     * @var EmbeddedFile bean id
     */
    protected $beanId;

    protected function tearDown(): void
    {
        global $db;
        $db->query('DELETE FROM embedded_files WHERE id = ' . $db->quoted($this->beanId));
    }

    public function testConvertEmbeddedFileToImage()
    {
        $bean = BeanFactory::newBean('EmbeddedFiles');
        $bean->save();
        $this->beanId = $bean->id;
        $mockFormatter = self::createPartialMock('EmailFormatter', []);

        // original email body
        $body = <<<EOT
<img src="rest/v11_13/EmbeddedFiles/{$bean->id}/file/doesntmatter" width="134" height="26"/>
EOT;

        // new email body
        $expected = <<<EOT
<img src="cid:{$bean->id}" width="134" height="26"/>
EOT;

        $converted = TestReflection::callProtectedMethod($mockFormatter, 'convertEmbeddedFileToImage', [$body]);

        $this->assertEquals($expected, $converted['body']);
        $this->assertEquals($bean->id, $converted['images'][0]->getCid());
    }

    /**
     * Formerly HandleBodyInHTMLformatTest::testHandleBodyInHtmlformat. This is really testing that from_html works,
     * but it's best not to lose a test and thus risk a regression.
     *
     * @group email
     * @group bug30591
     * @group mailer
     */
    public function testTranslateCharacters_HtmlEntitiesAreTranslatedToRealCharacters()
    {
        $body = "Check to see if &quot; &lt; &gt; &#039; was translated to \" < > '";

        $mockLocale = self::createPartialMock('Localization', ['translateCharset']);
        $mockLocale->expects(self::any())
            ->method('translateCharset')
            ->will(self::returnValue($body)); // return the exact same string

        $mockFormatter = self::createPartialMock('EmailFormatter', ['retrieveDisclosureSettings']);
        $mockFormatter->expects(self::any())
            ->method('retrieveDisclosureSettings')
            ->will(self::returnValue(false));

        $expected = "Check to see if \" < > ' was translated to \" < > '";
        $actual = $mockFormatter->translateCharacters($body, $mockLocale, 'UTF-8');
        self::assertEquals($expected, $actual, 'The HTML entities were not all translated properly');
    }
}
