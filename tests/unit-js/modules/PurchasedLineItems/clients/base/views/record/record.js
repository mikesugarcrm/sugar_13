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
describe('PurchasedLineItems.Base.View.Record', function() {
    var app;
    var view;
    var options;

    beforeEach(function() {
        app = SugarTest.app;

        options = {
            def: {view: 'record'},
            module: 'PurchasedLineItems',
            name: 'record',
        };

        SugarTest.loadFile('../modules/PurchasedLineItems/clients/base/plugins',
            'PurchaseAndServiceChangeHandler', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        view = SugarTest.createView('base', 'PurchasedLineItems',
            'record', {}, null, true);
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.stub(view, 'bindDataChange');
        });

        it('should add PurchaseAndServiceChangeHandler to plugins', function() {
            view.plugins = [];
            view.initialize(options);

            expect(view.plugins).toEqual(['PurchaseAndServiceChangeHandler']);
        });

        it('should call _super initialize method', function() {
            view.initialize(options);

            expect(view._super).toHaveBeenCalledWith('initialize');
        });

        it('should call bindDataChange method', function() {
            view.initialize(options);

            expect(view.bindDataChange).toHaveBeenCalledWith();
        });
    });
})
