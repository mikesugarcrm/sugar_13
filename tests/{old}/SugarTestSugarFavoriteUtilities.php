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

class SugarTestSugarFavoriteUtilities
{
    private static $createdFavorites = [];

    /**
     * Mark a bean as favorited
     *
     * @param SugarBean $beanToFavorite the bean to favorite
     * @param SugarBean $user optional: The User who is favoriting the
     *                  bean. If not provided, will default to the current user
     * @param string $favoriteId the optional ID to use for the favorite record
     * @return SugarBean the created SugarFavorite bean
     */
    public static function favoriteBean(SugarBean $beanToFavorite, SugarBean $user = null, $favoriteId = null)
    {
        if (empty($user)) {
            $user = $GLOBALS['current_user'];
        }
        $favorite = BeanFactory::newBean('SugarFavorites');
        if (!empty($favoriteId)) {
            $favorite->new_with_id = true;
            $favorite->id = $favoriteId;
        }
        $favorite->module = $beanToFavorite->getModuleName();
        $favorite->record_id = $beanToFavorite->id;
        $favorite->created_by = $user->id;
        $favorite->assigned_user_id = $user->id;
        $favorite->save();
        self::$createdFavorites[] = $favorite;

        return $favorite;
    }

    /**
     * Remove all favorites created
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public static function removeAllCreatedFavorites()
    {
        $ids = [];
        foreach (self::$createdFavorites as $favorite) {
            $ids[] = $favorite->id;
        }
        $qb = DBManagerFactory::getInstance()->getConnection()->createQueryBuilder();
        $qb->delete('sugarfavorites')
            ->where($qb->expr()->in(
                'id',
                $qb->createPositionalParameter($ids, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
            ))
            ->execute();
        self::$createdFavorites = [];
    }

    /**
     * Removes favorites for a user and a module
     *
     * @param string $userId
     * @param string $module
     * @return void
     */
    public static function removeFavoritesFor(string $userId, string $module)
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        if (!$userId || !$module) {
            return;
        }
        $query = 'DELETE FROM sugarfavorites WHERE assigned_user_id = ? AND module =  ?';
        $conn->executeStatement($query, [$userId, $module]);
    }

    /**
     * Remove from sugarfavorites by record id and record module
     *
     * @param string $module
     * @param string $recordId
     * @return void
     */
    public static function removeFavoritesByRecord(string $module, string $recordId): void
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        if (!$module || !$recordId) {
            return;
        }
        $query = 'DELETE FROM sugarfavorites WHERE module = ? AND record_id= ?';
        $conn->executeStatement($query, [$module, $recordId]);
    }
}
