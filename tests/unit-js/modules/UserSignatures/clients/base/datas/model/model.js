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

describe('Data.Base.UserSignaturesBean', function() {
    var app;
    var sandbox;
    var model;
    var callback;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.declareData('base', 'UserSignatures', true, false);
        app.data.declareModels();

        sandbox = sinon.createSandbox();
        sandbox.stub(app.api, 'call').callsFake(function(method, url, data, callbacks, options) {
            if (callbacks && callbacks.success) {
                callbacks.success();
            }
        });

        callback = sandbox.spy();

        model = app.data.createBean('UserSignatures', {is_default: 0});
    });

    afterEach(function() {
        app.cache.cutAll();
        sandbox.restore();
        SugarTest.testMetadata.dispose();
    });

    it('should not make a ping request when the `is_default` field has not changed', function() {
        model.set('name', 'my signature');

        model.save(null, {
            success: callback
        });

        expect(callback).toHaveBeenCalledOnce();
        expect(SugarTest.app.api.call.calledOnce).toBe(true);
        expect(SugarTest.app.api.call.getCall(0).args[1]).toMatch(/.*\UserSignatures.*/);
    });

    it('should make a ping request when the `is_default` field has changed', function() {
        model.set('is_default', 1);

        model.save(null, {
            success: callback
        });

        expect(callback).toHaveBeenCalledOnce();
        expect(SugarTest.app.api.call.calledTwice).toBe(true);
        expect(SugarTest.app.api.call.getCall(0).args[1]).toMatch(/.*\UserSignatures.*/);
        expect(SugarTest.app.api.call.getCall(1).args[1]).toMatch(/.*\ping.*/);
    });

    it('should not execute the parent success callback if options.success is not passed in', function() {
        model.set('is_default', 1);

        model.save(null, {});

        expect(callback).not.toHaveBeenCalled();
        expect(SugarTest.app.api.call.calledTwice).toBe(true);
        expect(SugarTest.app.api.call.getCall(0).args[1]).toMatch(/.*\UserSignatures.*/);
        expect(SugarTest.app.api.call.getCall(1).args[1]).toMatch(/.*\ping.*/);
    });
});
