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
describe('ProductTemplates.Base.Layout.Filterpanel', function() {
    var app, meta, moduleName = 'ProductTemplates', context;
    let getModuleStub;
    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadComponent('base', 'layout', 'togglepanel');
        SugarTest.loadComponent('base', 'layout', 'filterpanel');
        SugarTest.loadComponent('base', 'layout', 'filterpanel', moduleName);

        getModuleStub = sinon.stub(app.metadata, 'getModule');

        getModuleStub.withArgs(moduleName).returns({
            'isPipelineEnabled': false,
            'activityStreamEnabled': false
        });

        context = app.context.getContext({'layout' : 'record'});
    });

    afterEach(function() {
        sinon.restore();
        getModuleStub = null;
        SugarTest.testMetadata.dispose();
    });

    describe('when no subpanels are defined', function() {
        it('in subpanels meta template will be empty', function() {
            //client, module, layoutName, meta, context, loadFromModule, params
            getModuleStub.withArgs(moduleName, 'layouts').returns({
                'subpanels': {
                    'meta': {
                        'components': []
                    }
                }
            });

            var layout = SugarTest.createLayout('base', moduleName, 'filterpanel', {}, context, true);
            expect(layout.template).toEqual(app.template.empty);
        });

        it('in layout metadata template will be empty', function() {
            //client, module, layoutName, meta, context, loadFromModule, params
            getModuleStub.withArgs(moduleName, 'layouts').returns({});

            var layout = SugarTest.createLayout('base', moduleName, 'filterpanel', {}, context, true);
            expect(layout.template).toEqual(app.template.empty);
        });
    });
});
