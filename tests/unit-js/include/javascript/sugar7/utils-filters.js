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
describe('Sugar7 filters utils', function() {

    var app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile('../include/javascript/sugar7', 'utils-filters', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
    });

    describe('FilterOptions', function() {

        it('should format a simple filter options object', function() {
            var filterOptions = new app.utils.FilterOptions()
                .config({
                    initial_filter: 'my_custom',
                    initial_filter_label: 'LBL_MY_CUSTOM',
                    filter_populate: {
                        'module': 'Accounts'
                    }
                })
                .format();

            expect(filterOptions).toEqual({
                initial_filter: 'my_custom',
                initial_filter_label: 'LBL_MY_CUSTOM',
                filter_populate: {
                    'module': 'Accounts'
                },
                stickiness: false
            });
        });

        describe('populate with values from a model', function() {
            var relatedModel;

            beforeEach(function() {
                relatedModel = new Backbone.Model({
                    'acc_id': 'abcd-efgh',
                    'acc_name': 'The related account',
                    'contact_id': '1234-5678',
                    'contact_name': 'The related contact'
                });
                relatedModel.fields = {
                    'acc_id': {
                        name: 'acc_id'
                    },
                    'acc_name': {
                        name: 'acc_name',
                        id_name: 'acc_id'
                    },
                    'contact_id': {
                        name: 'contact_id'
                    },
                    'contact_name': {
                        name: 'contact_name',
                        id_name: 'contact_id'
                    }
                };
            });

            it('should populate values and use the `initial_filter_label`', function() {
                var filterOptions = new app.utils.FilterOptions()
                    .config({
                        initial_filter: 'my_custom',
                        initial_filter_label: 'LBL_FILTER_USE_RELATED_FIELDS',
                        filter_relate: {
                            'acc_id': 'account_id',
                            'contact_id': 'contact_id'
                        },
                        filter_populate: {
                            'account_id': '',
                            'contact_id': ''
                        }
                    })
                    .populateRelate(relatedModel)
                    .format();

                expect(filterOptions).toEqual({
                    initial_filter: 'my_custom',
                    initial_filter_label: 'LBL_FILTER_USE_RELATED_FIELDS',
                    filter_populate: {
                        'account_id': 'abcd-efgh',
                        'contact_id': '1234-5678'
                    },
                    stickiness: false
                });
            });

            it('should populate values and format label from related values', function() {
                var filterOptions = new app.utils.FilterOptions()
                    .config({
                        initial_filter: 'my_custom',
                        filter_relate: {
                            'acc_id': 'account_id',
                            'contact_id': 'contact_id'
                        },
                        filter_populate: {
                            'account_id': '',
                            'contact_id': ''
                        }
                    })
                    .populateRelate(relatedModel)
                    .format();

                expect(filterOptions).toEqual({
                    initial_filter: 'my_custom',
                    initial_filter_label: 'The related account, The related contact',
                    filter_populate: {
                        'account_id': 'abcd-efgh',
                        'contact_id': '1234-5678'
                    },
                    stickiness: false
                });
            });

            describe('missing params', function() {

                it('should not populate values if `filter_relate` is not supplied', function() {
                    var filterOptions = new app.utils.FilterOptions()
                        .config({
                            initial_filter: 'my_custom',
                            filter_populate: {
                                'module': 'Accounts'
                            }
                        })
                        .populateRelate(relatedModel)
                        .format();

                    expect(filterOptions).toEqual({
                        initial_filter: 'my_custom',
                        filter_populate: {
                            'module': 'Accounts'
                        },
                        stickiness: false
                    });
                });

                it('should not populate values if `populateRelate` is not called', function() {
                    var filterOptions = new app.utils.FilterOptions()
                        .config({
                            initial_filter: 'my_custom',
                            filter_relate: {
                                'acc_id': 'account_id',
                                'contact_id': 'contact_id'
                            },
                            filter_populate: {
                                'module': 'Accounts'
                            }
                        })
                        .format();

                    expect(filterOptions).toEqual({
                        initial_filter: 'my_custom',
                        filter_populate: {
                            'module': 'Accounts'
                        },
                        stickiness: false
                    });
                });
            });
        });

        using('invalid filter options', [
            {},
            {
                initial_filter: 'my_custom',
                initial_filter_label: 'LBL_MY_CUSTOM',
                filter_populate: {}
            },
            {
                initial_filter: 'my_custom',
                initial_filter_label: 'LBL_MY_CUSTOM',
                filter_populate: {}
            }
        ], function(options) {

            it('should return undefined', function() {
                var filterOptions = new app.utils.FilterOptions().config(options).format();
                expect(filterOptions).toBeUndefined();
            });
        });

    });
});
