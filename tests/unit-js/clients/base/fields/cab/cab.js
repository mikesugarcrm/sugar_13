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
describe('View.Views.Base.CabField', function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'cab', 'cab', 'detail');
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should run field action', function() {
        field.testAction = sinon.stub();
        field.runAction({}, 'testAction');
        expect(field.testAction).toHaveBeenCalled();
    });

    it('should run view action', function() {
        field.view = {
            testAction: sinon.stub()
        };

        field.runAction({}, 'testAction');
        expect(field.view.testAction).toHaveBeenCalled();
    });
});
