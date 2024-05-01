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


class SugarTestCommentUtilities
{
    private static $createdComments = [];

    public static function createUnsavedComment(Activity $a = null, $new_id = '')
    {
        $time = random_int(0, mt_getrandmax());
        $data = ['value' => 'SugarComment' . $time];
        $comment = new Comment();
        $comment->data = $data;
        if ($a && $a->id) {
            $comment->parent_id = $a->id;
        }
        if (!empty($new_id)) {
            $comment->new_with_id = true;
            $comment->id = $new_id;
        }
        return $comment;
    }

    public static function createComment(Activity $a = null, $new_id = '')
    {
        $comment = self::createUnsavedComment($a, $new_id);
        if ($comment) {
            Activity::enable();
            $comment->save();
            Activity::restoreToPreviousState();
            $GLOBALS['db']->commit();
            self::$createdComments[] = $comment;
        }
        return $comment;
    }

    public static function setCreatedComment($comment_ids)
    {
        foreach ($comment_ids as $comment_id) {
            $comment = new Comment();
            $comment->id = $comment_id;
            self::$createdComments[] = $comment;
        }
    }

    public static function removeAllCreatedComments()
    {
        $comment_ids = self::getCreatedCommentIds();
        $GLOBALS['db']->query('DELETE FROM comments WHERE id IN (\'' . implode("', '", $comment_ids) . '\')');
    }

    public static function getCreatedCommentIds()
    {
        $comment_ids = [];
        foreach (self::$createdComments as $comment) {
            $comment_ids[] = $comment->id;
        }
        return $comment_ids;
    }
}
