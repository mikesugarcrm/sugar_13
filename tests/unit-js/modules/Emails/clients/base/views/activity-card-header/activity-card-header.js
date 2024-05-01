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
describe('View.Views.Base.Emails.ActivityCardHeaderView', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'activity-card');
        SugarTest.loadComponent('base', 'view', 'activity-card-header');
        view = SugarTest.createView(
            'base',
            'Emails',
            'activity-card-header',
            null,
            null,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        app = null;
    });

    describe('setUsersFields', function() {
        using('different fields', [
            {
                fields: [],
                expected: [undefined, undefined],
            },
            {
                fields: [{name: 'created_by_name'}],
                expected: [undefined, undefined],
            },
            {
                fields: [{name: 'to_collection', type: 'test'}, {name: 'test'}],
                expected: [undefined, {name: 'to_collection', type: 'test'}],
            },
            {
                fields: [{name: 'from_collection', type: 'test'}, {name: 'to_collection', type: 'test'}],
                expected: [{name: 'from_collection', type: 'test'}, {name: 'to_collection', type: 'test'}],
            },
        ], function(values) {
            it('should set fromField and toField correctly', function() {
                sinon.stub(view, 'getUsersPanel').returns({fields: values.fields});

                view.setUsersFields();
                expect(view.fromField).toEqual(values.expected[0]);
                expect(view.toField).toEqual(values.expected[1]);
            });
        });
    });
});
