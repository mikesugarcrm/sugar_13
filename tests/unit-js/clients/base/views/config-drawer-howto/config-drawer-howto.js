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
describe('Base.View.ConfigDrawerHowto', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base', null, 'config-drawer-howto')
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'on');
        });

        it('should listen for `config:howtoData:change` event', function() {
            view.bindDataChange();

            expect(view.context.on).toHaveBeenCalledWith('config:howtoData:change');
        });
    });

    describe('onHowtoDataChange()', function() {
        beforeEach(function() {
            sinon.stub(view, 'render');

            view.onHowtoDataChange('test');
        });

        it('should set howtoData from passed in value', function() {
            expect(view.howtoData).toBe('test');
        });

        it('should call render', function() {
            expect(view.render).toHaveBeenCalled();
        });
    });
});
