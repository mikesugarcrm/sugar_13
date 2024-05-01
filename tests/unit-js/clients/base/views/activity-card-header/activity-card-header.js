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
describe('View.Views.Base.ActivityCardHeaderView', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'activity-card');

        view = SugarTest.createView('base', '', 'activity-card-header');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        app = null;
    });

    describe('setInvitees', function() {
        beforeEach(function() {
            view.activity = app.data.createBean('');
            sinon.stub(view, 'getUsersPanel').returns({
                defaultFields: [
                    {
                        label: 'LBL_NAME',
                        name: 'name',
                        type: 'relate',
                        link: true
                    }
                ]
            });
        });

        it('should not add to user list when invitees is empty', function() {
            var actual = view.getInvitees();

            expect(actual).toEqual([]);
        });

        it('should add invitee models to user list', function() {
            var def = {
                label: 'LBL_NAME',
                name: 'name',
                type: 'relate',
                link: true
            };

            var modelOne = app.data.createBean('', {
                name: 'First Last'
            });
            var modelTwo = app.data.createBean('', {
                email: [
                    {
                        email_address: 'a@a.com'
                    }
                ]
            });

            view.activity.set('invitees', {
                models: [
                    modelOne,
                    modelTwo
                ]
            });

            var actual = view.getInvitees();

            expect(actual).toEqual([
                {
                    userField: def,
                    userModel: modelOne
                },
                {
                    userValue: 'a@a.com'
                }
            ]);
        });
    });

    describe('hasMoreInvitees', function() {
        using('different invitee data', [
            {
                invitees: {},
                expected: false
            },
            {
                invitees: {
                    offsets: {
                        contacts: -1
                    }
                },
                expected: false
            },
            {
                invitees: {
                    offsets: {
                        contacts: -1,
                        users: 0
                    }
                },
                expected: true
            }
        ], function(values) {
            it('should determine if the model has more invitees', function() {
                view.activity = app.data.createBean('', {
                    invitees: values.invitees
                });

                var actual = view.hasMoreInvitees();

                expect(actual).toEqual(values.expected);
            });
        });
    });

    describe('setUsersTemplate', function() {
        using('different metadata', [
            {
                panel: {},
                expected: 'user-single'
            },
            {
                panel: {
                    template: 'user-list'
                },
                expected: 'user-list'
            }
        ], function(values) {
            it('should get the specified panel from metadata', function() {
                sinon.stub(view, 'getUsersPanel').returns(values.panel);

                view.setUsersTemplate();

                expect(view.usersTemplate).toEqual(values.expected);
            });
        });
    });
});
