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
describe('Administration.Views.HelpletView', function() {
    var app;
    var view;
    var viewName = 'helplet';
    var context;
    var model;
    var module = 'Administration';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadComponent('base', 'view', viewName, module);

        SugarTest.testMetadata.set();

        context = app.context.getContext();
        sinon.stub(app.controller, 'context').value(context);

        model = app.data.createBean(module);

        context.set({
            module,
            model,
            layout: 'maps-config',
        });
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

    describe('_beforeInit()', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', module, viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will set context atttributes properly', function() {
            expect(view._helpMeta).toBeDefined();
            expect(view._helpMeta).toEqual({});
        });
    });

    describe('_computeHelpMeta()', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', module, viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will set helpMeta properly', function() {
            var extectedHelpMeta = {
                route: 'maps-config',
                moduleName: 'MapsAdmin',
                label: 'LBL_SUGAR_MAPS',
            };

            expect(view._helpMeta).toEqual({});

            view._computeHelpMeta();

            expect(Object.keys(view._helpMeta).length).toEqual(3);
            expect(view._helpMeta).toEqual(extectedHelpMeta);
        });
    });

    describe('createHelpObject()', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', module, viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will call _createFeatureHelpMeta', function() {
            view._computeHelpMeta();
            var spy = sinon.stub(view, '_createFeatureHelpMeta');

            view.createHelpObject();

            expect(spy).toHaveBeenCalled();
        });
    });

    describe('_createFeatureHelpMeta()', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', module, viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will call _createMoreHelpLink method to create helpUrl', function() {
            var spy = sinon.stub(view, '_createMoreHelpLink');

            view._createFeatureHelpMeta();

            expect(spy).toHaveBeenCalled();
        });

        it('will create the help object for core applications', function() {
            expect(view.helpObject).toBeUndefined();

            view._createFeatureHelpMeta();

            expect(view.helpObject).toBeDefined();
        });
    });

    describe('_createMoreHelpLink()', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', module, viewName, {
                model: model,
            }, context);

            view._computeHelpMeta();
        });

        afterEach(function() {
            view.dispose();
        });

        it('should return the url to maps documentation based on instance info', function() {
            var actualHelpLink = 'https://www.sugarcrm.com/crm/product_doc.php?edition=ENT&version=12.2.0&lang=en_us&' +
                                    'module=MapsAdmin&products=SUGAR_SELL_PREMIER_BUNDLE';
            var actualTemplateHelpLink = '<a href="' + actualHelpLink + '" target="_blank">';

            sinon.stub(app.metadata, 'getServerInfo').callsFake(function() {
                return {
                    flavor: 'ENT',
                    version: '12.2.0'
                };
            });

            sinon.stub(app.lang, 'getLanguage').callsFake(function() {
                return 'en_us';
            });

            sinon.stub(app.user, 'getProductCodes').returns({
                join: sinon.stub().returns('SUGAR_SELL_PREMIER_BUNDLE'),
            });

            var expectedHelpLink = view._createMoreHelpLink();

            expect(expectedHelpLink).toEqual(actualTemplateHelpLink);
        });
    });
});
