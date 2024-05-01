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

$viewdefs['base']['filter']['operators'] = [
    'multienum' => [
        '$contains' => 'LBL_OPERATOR_ONE_OF',
        '$not_contains' => 'LBL_OPERATOR_NOT_ONE_OF',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'enum' => [
        '$in' => 'LBL_OPERATOR_CONTAINS',
        '$not_in' => 'LBL_OPERATOR_NOT_CONTAINS',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'varchar' => [
        '$equals' => 'LBL_OPERATOR_MATCHES',
        '$starts' => 'LBL_OPERATOR_STARTS_WITH',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'name' => [
        '$equals' => 'LBL_OPERATOR_MATCHES',
        '$starts' => 'LBL_OPERATOR_STARTS_WITH',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
        '$contains' => 'LBL_OPERATOR_CONTAINS_WORD',
    ],
    'email' => [
        '$equals' => 'LBL_OPERATOR_MATCHES',
        '$starts' => 'LBL_OPERATOR_STARTS_WITH',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
        '$contains' => 'LBL_OPERATOR_CONTAINS_WORD',
    ],
    'text' => [
        '$equals' => 'LBL_OPERATOR_MATCHES',
        '$starts' => 'LBL_OPERATOR_STARTS_WITH',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
        '$contains' => 'LBL_OPERATOR_CONTAINS_WORD',
    ],
    'textarea' => [
        '$equals' => 'LBL_OPERATOR_MATCHES',
        '$starts' => 'LBL_OPERATOR_STARTS_WITH',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'currency' => [
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$not_equals' => 'LBL_OPERATOR_NOT_EQUALS',
        '$gt' => 'LBL_OPERATOR_GREATER_THAN',
        '$lt' => 'LBL_OPERATOR_LESS_THAN',
        '$gte' => 'LBL_OPERATOR_GREATER_THAN_OR_EQUALS',
        '$lte' => 'LBL_OPERATOR_LESS_THAN_OR_EQUALS',
        '$between' => 'LBL_OPERATOR_BETWEEN',
    ],
    'int' => [
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$not_equals' => 'LBL_OPERATOR_NOT_EQUALS',
        '$in' => 'LBL_OPERATOR_CONTAINS',
        '$gt' => 'LBL_OPERATOR_GREATER_THAN',
        '$lt' => 'LBL_OPERATOR_LESS_THAN',
        '$gte' => 'LBL_OPERATOR_GREATER_THAN_OR_EQUALS',
        '$lte' => 'LBL_OPERATOR_LESS_THAN_OR_EQUALS',
        '$between' => 'LBL_OPERATOR_BETWEEN',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'autoincrement' => [
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$not_equals' => 'LBL_OPERATOR_NOT_EQUALS',
        '$in' => 'LBL_OPERATOR_CONTAINS',
        '$gt' => 'LBL_OPERATOR_GREATER_THAN',
        '$lt' => 'LBL_OPERATOR_LESS_THAN',
        '$gte' => 'LBL_OPERATOR_GREATER_THAN_OR_EQUALS',
        '$lte' => 'LBL_OPERATOR_LESS_THAN_OR_EQUALS',
        '$between' => 'LBL_OPERATOR_BETWEEN',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'double' => [
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$not_equals' => 'LBL_OPERATOR_NOT_EQUALS',
        '$gt' => 'LBL_OPERATOR_GREATER_THAN',
        '$lt' => 'LBL_OPERATOR_LESS_THAN',
        '$gte' => 'LBL_OPERATOR_GREATER_THAN_OR_EQUALS',
        '$lte' => 'LBL_OPERATOR_LESS_THAN_OR_EQUALS',
        '$between' => 'LBL_OPERATOR_BETWEEN',
    ],
    'float' => [
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$not_equals' => 'LBL_OPERATOR_NOT_EQUALS',
        '$gt' => 'LBL_OPERATOR_GREATER_THAN',
        '$lt' => 'LBL_OPERATOR_LESS_THAN',
        '$gte' => 'LBL_OPERATOR_GREATER_THAN_OR_EQUALS',
        '$lte' => 'LBL_OPERATOR_LESS_THAN_OR_EQUALS',
        '$between' => 'LBL_OPERATOR_BETWEEN',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'decimal' => [
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$not_equals' => 'LBL_OPERATOR_NOT_EQUALS',
        '$gt' => 'LBL_OPERATOR_GREATER_THAN',
        '$lt' => 'LBL_OPERATOR_LESS_THAN',
        '$gte' => 'LBL_OPERATOR_GREATER_THAN_OR_EQUALS',
        '$lte' => 'LBL_OPERATOR_LESS_THAN_OR_EQUALS',
        '$between' => 'LBL_OPERATOR_BETWEEN',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'date' => [
        '$more_x_days_ago' => 'LBL_OPERATOR_MORE_X_DAYS_AGO',
        '$last_x_days' => 'LBL_OPERATOR_LAST_X_DAYS',
        '$next_x_days' => 'LBL_OPERATOR_NEXT_X_DAYS',
        '$more_x_days_ahead' => 'LBL_OPERATOR_MORE_X_DAYS_AHEAD',
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$lt' => 'LBL_OPERATOR_BEFORE',
        '$gt' => 'LBL_OPERATOR_AFTER',
        'yesterday' => 'LBL_OPERATOR_YESTERDAY',
        'today' => 'LBL_OPERATOR_TODAY',
        'tomorrow' => 'LBL_OPERATOR_TOMORROW',
        'last_7_days' => 'LBL_OPERATOR_LAST_7_DAYS',
        'next_7_days' => 'LBL_OPERATOR_NEXT_7_DAYS',
        'last_30_days' => 'LBL_OPERATOR_LAST_30_DAYS',
        'next_30_days' => 'LBL_OPERATOR_NEXT_30_DAYS',
        'last_month' => 'LBL_OPERATOR_LAST_MONTH',
        'this_month' => 'LBL_OPERATOR_THIS_MONTH',
        'next_month' => 'LBL_OPERATOR_NEXT_MONTH',
        'last_year' => 'LBL_OPERATOR_LAST_YEAR',
        'this_year' => 'LBL_OPERATOR_THIS_YEAR',
        'next_year' => 'LBL_OPERATOR_NEXT_YEAR',
        '$dateBetween' => 'LBL_OPERATOR_BETWEEN',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'datetime' => [
        '$more_x_days_ago' => 'LBL_OPERATOR_MORE_X_DAYS_AGO',
        '$last_x_days' => 'LBL_OPERATOR_LAST_X_DAYS',
        '$next_x_days' => 'LBL_OPERATOR_NEXT_X_DAYS',
        '$more_x_days_ahead' => 'LBL_OPERATOR_MORE_X_DAYS_AHEAD',
        '$starts' => 'LBL_OPERATOR_EQUALS',
        '$lte' => 'LBL_OPERATOR_BEFORE',
        '$gte' => 'LBL_OPERATOR_AFTER',
        'yesterday' => 'LBL_OPERATOR_YESTERDAY',
        'today' => 'LBL_OPERATOR_TODAY',
        'tomorrow' => 'LBL_OPERATOR_TOMORROW',
        'last_7_days' => 'LBL_OPERATOR_LAST_7_DAYS',
        'next_7_days' => 'LBL_OPERATOR_NEXT_7_DAYS',
        'last_30_days' => 'LBL_OPERATOR_LAST_30_DAYS',
        'next_30_days' => 'LBL_OPERATOR_NEXT_30_DAYS',
        'last_month' => 'LBL_OPERATOR_LAST_MONTH',
        'this_month' => 'LBL_OPERATOR_THIS_MONTH',
        'next_month' => 'LBL_OPERATOR_NEXT_MONTH',
        'last_year' => 'LBL_OPERATOR_LAST_YEAR',
        'this_year' => 'LBL_OPERATOR_THIS_YEAR',
        'next_year' => 'LBL_OPERATOR_NEXT_YEAR',
        '$dateBetween' => 'LBL_OPERATOR_BETWEEN',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'datetimecombo' => [
        '$starts' => 'LBL_OPERATOR_EQUALS',
        '$lte' => 'LBL_OPERATOR_BEFORE',
        '$gte' => 'LBL_OPERATOR_AFTER',
        'yesterday' => 'LBL_OPERATOR_YESTERDAY',
        'today' => 'LBL_OPERATOR_TODAY',
        'tomorrow' => 'LBL_OPERATOR_TOMORROW',
        'last_7_days' => 'LBL_OPERATOR_LAST_7_DAYS',
        'next_7_days' => 'LBL_OPERATOR_NEXT_7_DAYS',
        'last_30_days' => 'LBL_OPERATOR_LAST_30_DAYS',
        'next_30_days' => 'LBL_OPERATOR_NEXT_30_DAYS',
        'last_month' => 'LBL_OPERATOR_LAST_MONTH',
        'this_month' => 'LBL_OPERATOR_THIS_MONTH',
        'next_month' => 'LBL_OPERATOR_NEXT_MONTH',
        'last_year' => 'LBL_OPERATOR_LAST_YEAR',
        'this_year' => 'LBL_OPERATOR_THIS_YEAR',
        'next_year' => 'LBL_OPERATOR_NEXT_YEAR',
        '$dateBetween' => 'LBL_OPERATOR_BETWEEN',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'bool' => [
        '$equals' => 'LBL_OPERATOR_IS',
    ],
    'relate' => [
        '$in' => 'LBL_OPERATOR_CONTAINS',
        '$not_in' => 'LBL_OPERATOR_NOT_CONTAINS',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
        '$contains' => 'LBL_OPERATOR_CONTAINS_WORD',
    ],
    'teamset' => [
        '$in' => 'LBL_OPERATOR_CONTAINS',
        '$not_in' => 'LBL_OPERATOR_NOT_CONTAINS',
    ],
    'phone' => [
        '$starts' => 'LBL_OPERATOR_STARTS_WITH',
        '$equals' => 'LBL_OPERATOR_IS',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
        '$contains' => 'LBL_OPERATOR_CONTAINS_WORD',
    ],
    'radioenum' => [
        '$equals' => 'LBL_OPERATOR_IS',
        '$not_equals' => 'LBL_OPERATOR_IS_NOT',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'parent' => [
        '$equals' => 'LBL_OPERATOR_IS',
    ],
    'tag' => [
        '$in' => 'LBL_OPERATOR_CONTAINS',
        '$not_in' => 'LBL_OPERATOR_NOT_CONTAINS',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'calendar-modules' => [
        '$in' => 'LBL_OPERATOR_CONTAINS',
        '$not_in' => 'LBL_OPERATOR_NOT_CONTAINS',
        '$empty' => 'LBL_OPERATOR_EMPTY',
        '$not_empty' => 'LBL_OPERATOR_NOT_EMPTY',
    ],
    'maps-distance' => [
        '$in_radius_from_zip' => 'LBL_MAPS_IN_RADIUS_FROM_ZIP',
    ],
];
