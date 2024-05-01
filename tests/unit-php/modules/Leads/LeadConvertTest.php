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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Leads;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \LeadConvert
 */
class LeadConvertTest extends TestCase
{
    /**
     * @covers ::performDataPrivacyTransfer
     */
    public function testPerformDataPrivacyTransfer()
    {
        $leadConvert = $this->getMockBuilder('\LeadConvert')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $contact = $this->createPartialMock('\Contact', [
            'load_relationship',
            'save',
        ]);
        $contact->expects($this->once())
            ->method('load_relationship')
            ->will($this->returnValue(true));
        $contact->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));
        $dp = $this->createPartialMock('\Link2', [
            'add',
        ]);
        $dp->expects($this->once())
            ->method('add');
        $contact->dataprivacy = $dp;
        $dpr = $this->createMock('\DataPrivacy');
        $lead = $this->createPartialMock('\Lead', [
            'get_linked_beans',
        ]);
        $lead->dp_business_purpose = 'Business Communications';
        $lead->dp_consent_last_updated = '2018-01-01';
        $lead->expects($this->once())
            ->method('get_linked_beans')
            ->will($this->returnValue([$dpr]));
        TestReflection::setProtectedValue($leadConvert, 'lead', $lead);
        TestReflection::setProtectedValue($leadConvert, 'contact', $contact);
        TestReflection::setProtectedValue($leadConvert, 'modules', ['Contacts' => $contact]);
        $leadConvert->performDataPrivacyTransfer();
    }
}
