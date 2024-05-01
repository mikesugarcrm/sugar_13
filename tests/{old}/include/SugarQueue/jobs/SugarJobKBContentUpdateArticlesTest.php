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

class SugarJobKBContentUpdateArticlesTest extends TestCase
{
    /**
     * @var SugarJobKBContentUpdateArticles
     */
    protected $job;

    /**
     * @var KBContent
     */
    protected $expArticle;

    /**
     * @var KBContent
     */
    protected $approvedArticle;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, 1]);
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $td = new TimeDate();
        $this->expArticle = SugarTestKBContentUtilities::createBean();
        $this->expArticle->exp_date = $td->nowDate();
        $this->expArticle->save();

        $this->approvedArticle = SugarTestKBContentUtilities::createBean();
        $this->approvedArticle->active_date = $td->nowDate();
        $this->approvedArticle->is_external = false;
        $this->approvedArticle->save();

        $schedulersJob = $this->createMock('SchedulersJob');
        $schedulersJob->expects($this->any())->method('succeedJob')->will($this->returnValue(true));

        $this->job = new SugarJobKBContentUpdateArticles();
        $this->job->setJob($schedulersJob);
    }

    protected function tearDown(): void
    {
        SugarTestKBContentUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
    }

    /**
     * Only published articles can be expired.
     */
    public function testUnpublishedArticleCannotBeExpired()
    {
        $this->expArticle->status = KBContent::ST_DRAFT;
        $this->job->run(null);

        $this->expArticle->retrieve();
        $this->assertEquals(KBContent::ST_DRAFT, $this->expArticle->status);
    }

    /**
     * Only approved articles can be published.
     */
    public function testUnapprovedArticleCannotBePublished()
    {
        $this->approvedArticle->status = KBContent::ST_DRAFT;
        $this->job->run(null);

        $this->approvedArticle->retrieve();
        $this->assertEquals('draft', $this->approvedArticle->status);
    }

    /**
     * If an approved article has expiration date - publish and then expire.
     */
    public function testApproveWhenAlreadyExpired()
    {
        $td = new TimeDate();
        $this->approvedArticle->status = KBContent::ST_APPROVED;
        $this->approvedArticle->exp_date = $td->nowDate();
        $this->approvedArticle->active_date = $td->nowDate();
        $this->approvedArticle->save();

        $this->job->run(null);

        $this->approvedArticle->retrieve();
        $this->assertEquals(KBContent::ST_EXPIRED, $this->approvedArticle->status);
        $this->assertEquals($td->nowDbDate(), $this->approvedArticle->exp_date);
        $this->assertEquals($td->nowDbDate(), $this->approvedArticle->active_date);
    }

    /**
     * The job should handle previous dates for expiring.
     */
    public function testExpirationDateLessThanNow()
    {
        $td = new TimeDate();
        $newDate = $td->getNow()->modify('-10 days');

        $this->expArticle->status = KBContent::ST_PUBLISHED;
        $this->expArticle->exp_date = $td->asUserDate($newDate);
        $this->expArticle->save();

        $this->job->run(null);

        $this->expArticle->retrieve();
        $this->assertEquals(KBContent::ST_EXPIRED, $this->expArticle->status);
    }

    /**
     * The job should handle previous dates for approving.
     */
    public function testApprovedDateLessThanNow()
    {
        $td = new TimeDate();
        $newDate = $td->getNow()->modify('-10 days');

        $this->approvedArticle->status = KBContent::ST_APPROVED;
        $this->approvedArticle->active_date = $td->asUserDate($newDate);
        $this->approvedArticle->save();
        $this->job->run(null);

        $this->approvedArticle->retrieve();
        $this->assertEquals(KBContent::ST_PUBLISHED, $this->approvedArticle->status);
    }

    /**
     * Approving with today's date.
     */
    public function testPublishing()
    {
        $this->approvedArticle->status = KBContent::ST_APPROVED;
        $this->approvedArticle->is_external = true;
        $this->approvedArticle->save();

        $this->job->run(null);

        $this->approvedArticle->retrieve();
        $this->assertEquals(KBContent::ST_PUBLISHED, $this->approvedArticle->status);
    }

    /**
     * Internal article should be published respectively.
     */
    public function testPublishingAsInternal()
    {
        $this->approvedArticle->status = KBContent::ST_APPROVED;
        $this->approvedArticle->is_external = true;
        $this->approvedArticle->save();

        $this->job->run(null);

        $this->approvedArticle->retrieve();
        $this->assertEquals(KBContent::ST_PUBLISHED, $this->approvedArticle->status);
    }

    /**
     * Expiring with today's date.
     * @dataProvider providerPublishingStatuses
     */
    public function testExpiration($status)
    {
        $this->expArticle->status = $status;
        $this->expArticle->save();

        $this->job->run(null);

        $this->expArticle->retrieve();
        $this->assertEquals(KBContent::ST_EXPIRED, $this->expArticle->status);
    }

    public function providerPublishingStatuses()
    {
        return [
            [
                KBContent::ST_PUBLISHED,
            ],
        ];
    }
}
