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

describe('Currencies.Base.Views.Preview', function() {
    let view;
    let sinonSandbox;

    afterEach(function() {
        sinonSandbox.restore();
    });

    beforeEach(function() {
        sinonSandbox = sinon.createSandbox();
        view = SugarTest.createView('base', 'Currencies', 'preview', null, null, true, null);
        sinonSandbox.stub(view, '_super').callsFake(function() {});
        spyOn(view, '_super');
    });

    describe('hasUnsavedChanges', function() {
        it('should call super with hasUnsavedChanges for not base currency', function() {
            view.model.set('id', '1');
            view.hasUnsavedChanges();

            expect(view._super).toHaveBeenCalledWith('hasUnsavedChanges');
        });

        it('should not call super for base currency', function() {
            view.model.set('id', '-99');
            view.hasUnsavedChanges();

            expect(view._super).not.toHaveBeenCalled();
        });
    });
});
