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
describe('DocuSignEnvelopes.Base.View.RecipientsList', function() {
    var app;
    var view;
    var context;
    var meta;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();

        SugarTest.app.data.declareModels();

        context = new app.Context();

        context.set('model', new Backbone.Model());

        context.parent = new app.Context({module: 'DocuSignEnvelopes'});

        layout = SugarTest.createLayout(
            'base',
            'DocuSignEnvelopes',
            'base',
            null,
            context
        );

        meta = {
            panels: [
                {
                    name: 'recipients-list-panel',
                    fields: []
                }
            ]
        };
        view = SugarTest.createView(
            'base',
            'DocuSignEnvelopes',
            'recipients-list',
            meta,
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        layout = null;
        context = null;
    });

    describe('initialize', function() {
        it('should remove right columns', function() {
            view.initialize({
                context: context,
                meta: meta
            });
            expect(view.rightColumns).toEqual([]);
        });
    });
    describe('_render', function() {
        it('should register shortcut', function() {
            var superStub = sinon.stub(view, '_super');
            var createSessionShortcutsStub = sinon.stub(view, 'createShortcutSession');
            var registerShortcutsStub = sinon.stub(view, 'registerShortcuts');
            view._render();

            expect(registerShortcutsStub).toHaveBeenCalledOnce();

            superStub.restore();
            createSessionShortcutsStub.restore();
            registerShortcutsStub.restore();
        });
    });
});
