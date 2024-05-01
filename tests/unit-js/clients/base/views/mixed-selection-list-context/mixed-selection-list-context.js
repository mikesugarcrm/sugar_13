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
describe('Base.View.MixedSelectionListContext', function() {
    let app;
    let view;
    let context;

    beforeEach(function() {
        app = SugarTest.app;

        context = new app.Context();

        const collection = new Backbone.Collection();
        context.set('mass_collection', collection);

        layout = SugarTest.createLayout(
            'base',
            'DocuSignEnvelopes',
            'base',
            null,
            context
        );

        view = SugarTest.createView(
            'base',
            'DocuSignEnvelopes',
            'mixed-selection-list-context',
            {},
            context,
            false,
            layout,
            true
        );
    });

    afterEach(function() {
        view.dispose();
        layout.dispose();
        layout = null;
        context = null;
    });

    describe('initialize', function() {
        it('should create a mixed collection', function() {
            view.initialize({});
            const mixedCollectionDefined = !_.isUndefined(view.mixedCollection);
            expect(mixedCollectionDefined).toEqual(true);
        });
    });

    describe('_render', function() {
        beforeEach(function() {
            sinon.stub(view, 'makeSureAllPillsHaveNames');
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should check for all displayed pills to have names', function() {
            view.pills = [];

            view._render({});

            expect(view.makeSureAllPillsHaveNames).toHaveBeenCalled();
        });
    });
});
