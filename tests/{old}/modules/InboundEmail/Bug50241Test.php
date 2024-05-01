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

/**
 * @ticket 50241
 */
class Bug50241Test extends TestCase
{
    public function testEmailCleanup()
    {
        if (is_windows()) {
            $this->markTestSkipped('On Windows, DOMDocument handles whitespaces differently than the test expects');
        }

        $inStr = <<<EOS
<head>
<style><!--
.hmmessage P
{
margin:0px;
padding:0px
}
body.hmmessage
{
font-size: 10pt;
font-family:Tahoma
}
--></style></head>
<body class='hmmessage'><div dir='ltr'>
<SPAN style="FONT-FAMILY: 'Tahoma','sans-serif'; FONT-SIZE: 10pt">hello, <o:p></o:p></SPAN><BR><SPAN style="FONT-FAMILY: 'Tahoma','sans-serif'; FONT-SIZE: 10pt">i recently got Batman Arkham City and tried to get catwoman as an add-on character but when i put the code in it said that my code had already been used. <o:p></o:p></SPAN><BR><SPAN style="FONT-FAMILY: 'Tahoma','sans-serif'; FONT-SIZE: 10pt">what can i do, so that i can play catwoman?<o:p></o:p></SPAN><BR><BR></div></body>
</html>
EOS;

        $outStr = <<<EOS
<div dir="ltr">
<span style="font-family:Tahoma, 'sans-serif';font-size:10pt;">hello, </span><p></p><br /><span style="font-family:Tahoma, 'sans-serif';font-size:10pt;">i recently got Batman Arkham City and tried to get catwoman as an add-on character but when i put the code in it said that my code had already been used. </span><p></p><br /><span style="font-family:Tahoma, 'sans-serif';font-size:10pt;">what can i do, so that i can play catwoman?</span><p></p><br /><br /></div>
EOS;

        $actual = SugarCleaner::cleanHtml($inStr);

        // Normalize the line endings - Bug #51227
        $outStr = str_replace("\r\n", "\n", $outStr);
        $actual = str_replace("\r\n", "\n", $actual);
        $this->assertEquals(trim($outStr), trim($actual));
    }
}
