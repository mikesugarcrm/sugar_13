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
describe('KBContents.Base.Views.PanelTopForRevisions', function() {

    var app, view, sandbox, context, meta, moduleName = 'KBContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        context = app.context.getContext({
            module: moduleName
        });
        context.set('model', new app.Bean());
        context.parent = new Backbone.Model();
        meta = {};

        SugarTest.loadComponent(
            'base',
            'view',
            'panel-top-for-revisions',
            moduleName
        );
        SugarTest.loadHandlebarsTemplate(
            'panel-top-for-revisions',
            'view',
            'base',
            null,
            moduleName
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'panel-top-for-revisions',
            meta,
            context,
            moduleName
        );
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        Handlebars.templates = {};
        view = null;
    });

    describe('createRelatedClicked()', function() {
        var createRelatedContentStab, contextParentGetStub;

        beforeEach(function() {
            createRelatedContentStab = sandbox.stub(view, 'createRelatedContent');
        });

        it('should call createRelatedContent() when parentModule exists',
            function() {
                contextParentGetStub = sandbox.stub(
                    context.parent,
                    'get').callsFake(function() {
                        return {name: 'Test'};
                    }
                );
                view.createRelatedClicked();
                expect(contextParentGetStub).toHaveBeenCalledWith('model');
                expect(createRelatedContentStab).toHaveBeenCalledWith(
                    {name: 'Test'},
                    view.CONTENT_REVISION
                );
            }
        );

        it('should not call createRelatedContent() when parentModule not exists',
            function() {
                contextParentGetStub = sandbox.stub(
                    context.parent,
                    'get').callsFake(function() {
                        return undefined;
                    }
                );
                view.createRelatedClicked();
                expect(contextParentGetStub).toHaveBeenCalledWith('model');
                expect(createRelatedContentStab).not.toHaveBeenCalled();
            }
        );
    });
});
