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
describe('View.Views.Base.ActivityCardContentView', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'activity-card-content');
        view = SugarTest.createView(
            'base',
            'Notes',
            'activity-card-content',
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

    describe('formatDescriptionField', function() {
        var getStub;
        beforeEach(function() {
            sinon.stub(view, 'formatContent').returns('format test');
            getStub = sinon.stub().returns('test');
        });

        afterEach(function() {
            getStub = null;
        });

        it('should not call formatContent method if activity is not defined', function() {
            view.activity = undefined;

            view.formatDescriptionField();
            expect(view.formatContent).not.toHaveBeenCalled();
        });

        it('should call set descriptionField method', function() {
            view.activity = {
                'get': getStub
            };

            view.formatDescriptionField();
            expect(view.activity.get).toHaveBeenCalledWith('description');
            expect(view.formatContent).toHaveBeenCalledWith('test');
            expect(view.descriptionField).toEqual('format test');
        });
    });
});
