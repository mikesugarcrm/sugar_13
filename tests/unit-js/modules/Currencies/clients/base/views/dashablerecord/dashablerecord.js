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

describe('Currencies.Base.Views.Dashablerecord', function() {
    let app;
    let view;
    let sinonSandbox;
    let model;

    afterEach(function() {
        sinonSandbox.restore();
    });

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();
        view = SugarTest.createView('base', 'Currencies', 'dashablerecord', null, null, true, null);
        model = app.data.createBean('Currencies');
        sinonSandbox.stub(view, '_super').callsFake(function() {});
        spyOn(view, '_super');
    });

    describe('_setReadonlyFields', function() {
        it('should call isBaseCurrency', function() {
            spyOn(view, 'isBaseCurrency');
            view._setReadonlyFields();

            expect(view.isBaseCurrency).toHaveBeenCalled();
        });
    });

    describe('isBaseCurrency', function() {
        it('should set isBase to false if currencyId is not -99', function() {
            model.set('id', '1');
            view.isBaseCurrency(model);

            expect(view.isBase).toBeFalsy();
        });

        it('should set isBase to true if currencyId is -99', function() {
            model.set('id', '-99');
            view.isBaseCurrency(model);

            expect(view.isBase).toBeTruthy();
        });
    });
});
