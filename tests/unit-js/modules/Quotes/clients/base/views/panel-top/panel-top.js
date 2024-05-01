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
describe('Quotes.Base.Views.PanelTop', function() {
    var app;
    var view;
    var viewMeta;
    var viewLayoutModel;
    var layout;
    var layoutDefs;
    var context;
    beforeEach(function() {
        app = SugarTest.app;
        viewLayoutModel = new Backbone.Model();
        layoutDefs = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        layout = SugarTest.createLayout('base', 'Quotes', 'default', layoutDefs);

        SugarTest.loadComponent('base', 'view', 'panel-top');

        var parentContext = app.context.getContext();

        parentContext.set('module', 'Accounts');
        context = app.context.getContext();
        context.parent = parentContext;

        viewMeta = {
            panels: [{
                fields: ['field1', 'field2']
            }]
        };
        view = SugarTest.createView('base', 'Quotes', 'panel-top', viewMeta, context, true, layout);
        sinon.stub(view, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should add MassQuote to this.plugins', function() {
            expect(view.plugins).toContain('MassQuote');
        });
    });

    describe('createRelatedClicked()', function() {
        var contextCollection;

        beforeEach(function() {
            contextCollection = new Backbone.Collection();
            view.context.set('collection', contextCollection);
            sinon.stub(view.layout, 'trigger').callsFake(function() {});
        });

        afterEach(function() {
            contextCollection = null;
        });

        describe('things that can execute the function in beforeEach', function() {
            beforeEach(function() {
                view.createRelatedClicked({});
            });
            it('should add MassQuote to this.plugins', function() {
                expect(view.context.get('mass_collection')).toEqual(contextCollection);
            });

            it('should trigger list:massquote:fire on view layout', function() {
                expect(view.layout.trigger).toHaveBeenCalledWith('list:massquote:fire');
            });
        });

        describe('things that cannot execute the function in beforeEach', function() {
            it('should have fromSubpanelset', function() {
                view.context.parent.set('module', 'Foo');
                view.createRelatedClicked({});
                expect(view.context.get('mass_collection').fromSubpanel).toBeTruthy();
            });
            it('should not have fromSubpanel set coming from accounts', function() {
                view.context.parent.set('module', 'Accounts');
                view.createRelatedClicked({});
                expect(view.context.get('mass_collection').fromSubpanel).toBeFalsy();
            });
            it('should not have fromSubpanel set coming from opportunities', function() {
                view.context.parent.set('module', 'Opportunities');
                view.createRelatedClicked({});
                expect(view.context.get('mass_collection').fromSubpanel).toBeFalsy();
            });
            it('should not have fromSubpanel set coming from opportunities', function() {
                view.context.parent.set('module', 'Contacts');
                view.createRelatedClicked({});
                expect(view.context.get('mass_collection').fromSubpanel).toBeFalsy();
            });
        });
    });
});
