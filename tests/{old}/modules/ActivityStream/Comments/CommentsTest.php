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
 * @group ActivityStream
 */
class CommentsTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestActivityUtilities::removeAllCreatedActivities();
        SugarTestCommentUtilities::removeAllCreatedComments();
        SugarTestHelper::tearDown();
    }

    /**
     * Tests that the magic method __toString() on a Comment bean is valid.
     * @covers Comment::toJson
     */
    public function testToString()
    {
        $activity = SugarTestActivityUtilities::createActivity();
        $comment = SugarTestCommentUtilities::createComment($activity);
        $json = $comment->toJson();
        $this->assertIsString($json);
        $this->assertNotEquals(false, json_decode($json, true));
    }

    /**
     * Tests that saving a comment that the post has already counted does not
     * increment the cached count again.
     * @covers Comment::save
     */
    public function testDoubleSaveDoesntUpdateCommentCount()
    {
        $activity = SugarTestActivityUtilities::createActivity();
        $comment = SugarTestCommentUtilities::createComment($activity);
        $this->assertEquals(1, $activity->comment_count);
        $comment->save();
        $this->assertEquals(1, $activity->comment_count);
    }

    /**
     * Tests that saving a comment without a parent post returns false.
     * @covers Comment::save
     */
    public function testSave_WithoutParentPost_ReturnsFalse()
    {
        Activity::enable();
        $comment = BeanFactory::newBean('Comments');
        $id = $comment->save();
        Activity::restoreToPreviousState();
        $this->assertFalse($id);
    }

    /**
     * @covers Comment::processCommentTags
     */
    public function testProcessCommentTags_NoTagsOnComment_ProcessTagsNotCalled()
    {
        $comment = BeanFactory::newBean('Comments');
        $comment->data = '{}';

        $activity = $this->createPartialMock('Activity', ['processTags']);
        $activity->expects($this->never())->method('processTags');

        SugarTestReflection::callProtectedMethod($comment, 'processCommentTags', [$activity]);
    }

    /**
     * @covers Comment::processCommentTags
     */
    public function testProcessCommentTags_TagsOnComment_ProcessTagsCalled()
    {
        $comment = BeanFactory::newBean('Comments');
        $comment->data = '{"tags":[{"module":"Foo","id":"123"}]}';

        $activity = $this->createPartialMock('Activity', ['processTags']);
        $activity->expects($this->once())->method('processTags');

        SugarTestReflection::callProtectedMethod($comment, 'processCommentTags', [$activity]);
    }
}
