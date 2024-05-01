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

class KBContentsTest extends TestCase
{
    /**
     * @var KBContentMock
     */
    protected $bean;

    /**
     * Category root node
     *
     * @var CategoryMock $categoryRoot
     */
    protected $categoryRoot;

    /**
     * Backup of current config for module.
     * @var array $config
     */
    protected $config;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user', [true, true]);
        $this->bean = SugarTestKBContentUtilities::createBean();

        $apiClass = new ConfigModuleApi();
        $this->config = $apiClass->config(SugarTestRestUtilities::getRestServiceMock(), ['module' => 'KBContents']);

        $apiClass->configSave(
            SugarTestRestUtilities::getRestServiceMock(),
            [
                'languages' => [
                    [
                        'en' => 'English',
                        'primary' => true,
                    ],
                    [
                        'ww' => 'Test Language',
                        'primary' => false,
                    ],
                ],
                'module' => 'KBContents',
            ]
        );
        $this->categoryRoot = SugarTestCategoryUtilities::createRootBean();
    }

    protected function tearDown(): void
    {
        SugarTestCategoryUtilities::removeAllCreatedBeans();
        SugarTestKBContentUtilities::removeAllCreatedBeans();

        $apiClass = new ConfigModuleApi();
        $apiClass->configSave(
            SugarTestRestUtilities::getRestServiceMock(),
            array_merge($this->config, ['module' => 'KBContents'])
        );

        SugarTestHelper::tearDown();
    }

    public function testFormatForApi()
    {
        $helper = new KBContentsApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $data = $helper->formatForApi($this->bean);
        $lang = $this->bean->getPrimaryLanguage();

        $this->assertEquals($data['name'], $this->bean->name);
        $this->assertEquals($data['language'], $lang['key']);
    }

    public function testKBContentsDefaults()
    {
        $this->assertEquals($this->bean->language, 'en');
        $this->assertEquals($this->bean->status, KBContent::DEFAULT_STATUS);
        $this->assertEquals($this->bean->revision, 1);
    }

    public function testPrimaryLanguage()
    {
        $this->assertEquals([
            'label' => 'English',
            'key' => 'en',
        ], $this->bean->getPrimaryLanguage());
    }

    public function testDocumentRelationship()
    {
        $doc = BeanFactory::newBean('KBDocuments');
        $doc->fetch($this->bean->kbdocument_id);
        $this->assertEquals($this->bean->name, $doc->name);
    }

    public function testArticleRelationship()
    {
        $article = BeanFactory::newBean('KBArticles');
        $article->fetch($this->bean->kbarticle_id);
        $this->assertEquals($this->bean->name, $article->name);
    }

    public function testLocalizationsLink()
    {
        $this->bean->load_relationship('localizations');
        $this->assertInstanceOf('LocalizationsLink', $this->bean->localizations);

        $query = new SugarQuery();
        $query->from($this->bean);

        $joinSugarQuery = $this->bean->localizations->buildJoinSugarQuery($query);
        $this->assertIsObject($joinSugarQuery);
    }

    public function testRevisionsLink()
    {
        $this->bean->load_relationship('revisions');
        $this->assertInstanceOf('RevisionsLink', $this->bean->revisions);

        $query = new SugarQuery();
        $query->from($this->bean);

        $joinSugarQuery = $this->bean->revisions->buildJoinSugarQuery($query);
        $this->assertIsObject($joinSugarQuery);
    }

    /**
     * New non-published revision should be active.
     */
    public function testActiveRevision()
    {
        $revisionData = [
            'kbarticle_id' => $this->bean->kbarticle_id,
            'kbdocument_id' => $this->bean->kbdocument_id,
            'language' => $this->bean->language,
        ];

        $revision1 = SugarTestKBContentUtilities::createBean($revisionData);
        $this->assertEquals($this->bean->active_rev, 0);
        $this->assertEquals($revision1->active_rev, 1);

        $revision2 = SugarTestKBContentUtilities::createBean($revisionData);
        $this->assertEquals($this->bean->active_rev, 0);
        $this->assertEquals($revision1->active_rev, 0);
        $this->assertEquals($revision2->active_rev, 1);
    }

    /**
     * Published revision becomes active.
     */
    public function testPublishedRevisionIsActive()
    {
        $publishStatuses = $this->bean->getPublishedStatuses();
        $revisionData = [
            'kbarticle_id' => $this->bean->kbarticle_id,
            'kbdocument_id' => $this->bean->kbdocument_id,
            'language' => $this->bean->language,
        ];

        $this->bean->active_rev = 1;
        $this->bean->status = $publishStatuses[0];
        $this->bean->save();

        $draftRevision = SugarTestKBContentUtilities::createBean($revisionData);
        $this->assertEquals($this->bean->active_rev, 1);
        $this->assertEquals($draftRevision->active_rev, 0);

        $publishedRevision = SugarTestKBContentUtilities::createBean(
            $revisionData + [
                'status' => $publishStatuses[0],
            ]
        );
        $this->assertEquals($this->bean->active_rev, 0);
        $this->assertEquals($draftRevision->active_rev, 0);
        $this->assertEquals($publishedRevision->active_rev, 1);
    }

    /**
     * Published article becomes active.
     */
    public function testPublishedBeanIsActive()
    {
        $publishStatuses = $this->bean->getPublishedStatuses();
        $revisionData = [
            'kbarticle_id' => $this->bean->kbarticle_id,
            'kbdocument_id' => $this->bean->kbdocument_id,
            'language' => $this->bean->language,
        ];

        $this->bean->active_rev = 1;
        $this->bean->save();

        $revision = SugarTestKBContentUtilities::createBean($revisionData);
        $this->assertEquals($this->bean->active_rev, 0);
        $this->assertEquals($revision->active_rev, 1);

        $this->bean->status = $publishStatuses[0];
        $this->bean->save();

        $this->assertEquals($this->bean->active_rev, 1);
        $this->assertEquals($revision->active_rev, 0);
    }

    public function testUpdateCategoryExternalVisibility()
    {
        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . random_int(0, mt_getrandmax());
        $this->categoryRoot->addNodeMock($subnode, 2, 1);
        SugarTestCategoryUtilities::addCreatedBean($subnode->save());

        // Scenario 1: Save not external document, status is default (draft). Expected result: category is not external
        $this->bean->category_id = $subnode->id;
        $this->bean->save();

        $categoryBean = BeanFactory::retrieveBean('Categories', $subnode->id, [
            'use_cache' => false,
        ]);

        $this->assertEquals(0, $categoryBean->is_external);

        // Scenario 2: Save external document, status is default (draft). Expected result: category is not external
        $this->bean->is_external = 1;
        $this->bean->save();

        $categoryBean = BeanFactory::retrieveBean('Categories', $subnode->id, [
            'use_cache' => false,
        ]);

        $this->assertEquals(0, $categoryBean->is_external);

        // Scenario 2: Save external document, status is published. Expected result: category is external
        $this->bean->status = KBContent::ST_PUBLISHED;
        $this->bean->save();

        $categoryBean = BeanFactory::retrieveBean('Categories', $subnode->id, [
            'use_cache' => false,
        ]);

        $this->assertEquals(1, $categoryBean->is_external);

        // Scenario 2: Delete document. Expected result: category is not external
        $this->bean->mark_deleted($this->bean->id);
        $categoryBean = BeanFactory::retrieveBean('Categories', $subnode->id, [
            'use_cache' => false,
        ]);

        $this->assertEquals(0, $categoryBean->is_external);
    }

    /**
     * After Content is approved set Approved By to current user
     */
    public function testApprovedbyAutoset()
    {
        $bean = SugarTestKBContentUtilities::createBean();
        /**
         * this line is needed for getting a valid data
         * from $this->db->getDataChanges in the KBContent::save method
         */
        $bean->loadFromRow($bean->toArray());

        $this->assertNotEquals(KBContent::ST_APPROVED, $bean->status);

        //$user = SugarTestUserUtilities::createAnonymousUser();
        $user = $GLOBALS['current_user'];

        $bean->status = KBContent::ST_APPROVED;
        $bean->save();

        $this->assertEquals(KBContent::ST_APPROVED, $bean->status);
        // approver id should be equal to the current user id
        $this->assertEquals($bean->kbsapprover_id, $user->id);
    }

    /**
     * Test save usefulness on empty bean.
     */
    public function testSaveUsefulnessWithBeanWithoutId()
    {
        $this->bean->id = false;

        $this->expectException(SugarApiException::class);
        $this->bean->saveUsefulness();
    }

    /**
     * Test save usefulness on new bean.
     */
    public function testSaveUsefulnessWithNewBean()
    {
        $this->bean->new_with_id = true;

        $this->expectException(SugarApiException::class);
        $this->bean->saveUsefulness();
    }

    /**
     * Test of saveUsefulness().
     */
    public function testSaveUsefulness()
    {
        $beanDateModified = $this->bean->date_modified;
        $beanModifiedBy = $this->bean->modified_by;

        $this->bean->useful = 1;
        $this->bean->notuseful = 0;

        $this->assertTrue($this->bean->update_date_modified);
        $this->assertTrue($this->bean->update_modified_by);

        $this->bean->saveUsefulness();
        $this->assertFalse($this->bean->update_date_modified);
        $this->assertFalse($this->bean->update_modified_by);

        $this->bean->retrieve();
        $this->assertEquals('1', $this->bean->useful);
        $this->assertEquals('0', $this->bean->notuseful);
        $this->assertEquals($beanDateModified, $this->bean->date_modified);
        $this->assertEquals($beanModifiedBy, $this->bean->modified_by);
    }

    /**
     * Test that in saving of new kbcontent we omit usefulness.
     */
    public function testSavingNewBeanOmitUsefulness()
    {
        $bean = SugarTestKBContentUtilities::createBean(['useful' => 1], false);
        $bean->new_with_id = true;
        SugarTestKBContentUtilities::saveBean($bean);
        $this->assertEquals('0', $bean->useful);
    }

    /**
     * Test that in saving of new kbcontent we omit usefulness.
     */
    public function testSavingExistingBeanOmitUsefulness()
    {
        $this->bean->retrieve(); // to fill bean->fetched_row
        $this->bean->useful = 1;
        $this->bean->save();
        $this->assertEquals('0', $this->bean->useful);
    }

    /**
     * Test empty active date after status changed from KBContent::ST_PUBLISHED
     */
    public function testClearActiveDate()
    {
        //Prepare bean
        $this->bean->save();
        $this->bean->retrieve();
        //Set status and check active date
        $this->bean->status = KBContent::ST_PUBLISHED;
        $this->bean->save();
        $this->bean->retrieve();
        $this->assertNotEmpty($this->bean->active_date);
        //Another save with no status changed and check active date
        $this->bean->status = KBContent::ST_PUBLISHED;
        $this->bean->save();
        $this->bean->retrieve();
        $this->assertNotEmpty($this->bean->active_date);
        //Change status and check active date
        $this->bean->status = KBContent::ST_DRAFT;
        $this->bean->save();
        $this->bean->retrieve();
        $this->assertEmpty($this->bean->active_date);
        //Check that active date isn't cleared for approved status change
        $this->bean->status = KBContent::ST_PUBLISHED;
        $this->bean->save();
        $this->bean->retrieve();
        $this->bean->status = KBContent::ST_APPROVED;
        $this->bean->save();
        $this->bean->retrieve();
        $this->assertNotEmpty($this->bean->active_date);
    }

    /**
     * Test that we don't try to call updateCategoryExternalVisibility for empty category_id
     */
    public function testUpdateCategoryExternalVisibilityCallsOnSave()
    {
        //Prepare bean
        $this->bean->category_id = '';
        $this->bean->save();
        $this->bean->retrieve();

        // Update bean by random category info
        $this->bean->category_name = 'SugarCategory_' . Uuid::uuid1();
        $this->bean->category_id = Uuid::uuid1();

        // Save updated bean
        $this->bean->save();

        $this->assertNotContains('', $this->bean->updatedCategories);
    }

    public function testNextAvailableLanguageTakenWhenSavingNewLocalization()
    {
        $bean = SugarTestKBContentUtilities::createBean([], false);

        $bean->language = '';
        SugarTestKBContentUtilities::saveBean($bean);

        $primaryLanguage = $bean->getPrimaryLanguage();
        $this->assertEquals($primaryLanguage['key'], $bean->language);

        $filtered = array_filter($bean->getLanguages(), function ($language) {
            return $language['primary'] !== true;
        });
        $nonPrimaryLanguages = array_map(function ($lang) {
            unset($lang['primary']);

            return $lang;
        }, $filtered);

        foreach ($nonPrimaryLanguages as $language) {
            $newLocalization = SugarTestKBContentUtilities::createBean([], false);
            $newLocalization->kbdocument_id = $bean->kbdocument_id;
            $newLocalization->language = '';
            SugarTestKBContentUtilities::saveBean($newLocalization);

            $keys = array_keys($language);
            $this->assertEquals(reset($keys), $newLocalization->language);
        }
    }

    public function testExceptionGeneratedIfNoAvailableLocalizationLanguage()
    {
        $bean = SugarTestKBContentUtilities::createBean([], false);
        $bean->language = '';
        $bean->save();
        SugarTestKBContentUtilities::saveBean($bean);

        $filtered = array_filter($bean->getLanguages(), function ($language) {
            return $language['primary'] !== true;
        });
        $nonPrimaryLanguages = array_map(function ($lang) {
            unset($lang['primary']);

            return $lang;
        }, $filtered);

        foreach ($nonPrimaryLanguages as $language) {
            $keys = array_keys($language);
            $languageKey = reset($keys);
            $newLocalization = SugarTestKBContentUtilities::createBean([], false);
            $newLocalization->kbdocument_id = $bean->kbdocument_id;
            $newLocalization->language = $languageKey;
            SugarTestKBContentUtilities::saveBean($newLocalization);
        }

        $newLocalization = SugarTestKBContentUtilities::createBean([], false);
        $newLocalization->kbdocument_id = $bean->kbdocument_id;
        $newLocalization->language = '';

        $this->expectException(SugarApiException::class);
        SugarTestKBContentUtilities::saveBean($newLocalization);
    }

    /**
     * Test related API works correctly with localization/revision subpanels.
     */
    public function testRelatedApi()
    {
        //create localization
        $languages = array_filter(
            $this->bean->getLanguages(),
            function ($language) {
                return $language['primary'] !== true;
            }
        );
        $lang = array_pop($languages);
        unset($lang['primary']);
        $keys = array_keys($lang);
        $languageKey = reset($keys);
        $localizationData = [
            'kbdocument_id' => $this->bean->kbdocument_id,
            'language' => $languageKey,
        ];
        SugarTestKBContentUtilities::createBean($localizationData);

        //create revision
        $revisionData = [
            'kbarticle_id' => $this->bean->kbarticle_id,
            'kbdocument_id' => $this->bean->kbdocument_id,
            'language' => $this->bean->language,
        ];
        SugarTestKBContentUtilities::createBean($revisionData);

        $relateApi = new RelateApi();
        $rest = SugarTestRestUtilities::getRestServiceMock();
        $args = [
            'module' => 'KBContents',
            'fields' => ['name'],
            'record' => $this->bean->id,
            'oreder_by' => 'date_modified:desc',
            'max_num' => 3,
            'link_name' => 'localizations',
        ];
        $result = $relateApi->filterRelated($rest, $args);
        $this->assertCount(1, $result['records'], 'RelateAPI should return one record for localization');

        $args['link_name'] = 'revisions';
        $result = $relateApi->filterRelated($rest, $args);
        $this->assertCount(1, $result['records'], 'RelateAPI should return one record for revision');
    }
}
