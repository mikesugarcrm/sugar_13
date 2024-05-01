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
describe('Base.View.MultiLineSortingDropdown', function() {
    var app;
    var sinonSandbox;
    var view;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'multi-line-sorting-dropdown');
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();
        view = SugarTest.createView('base', null, 'multi-line-sorting-dropdown', {});
    });

    afterEach(function() {
        sinonSandbox.restore();
        view.dispose();
        view = null;
    });

    describe('_setSelect2', function() {
        var selectStub;

        beforeEach(function() {
            selectStub = sinonSandbox.stub();
            sinonSandbox.stub(view, '$').returns({
                val: sinonSandbox.stub().returns({
                    select2: selectStub
                })
            });
        });

        it('should not show placeholder for primary sort', function() {
            view.isPrimary = true,
            view._setSelect2();
            expect(selectStub).toHaveBeenCalledWith();
        });

        it('should show placeholder for secondary sort', function() {
            sinonSandbox.stub(app.lang, 'get').returns('select');
            view.isPrimary = false,
            view._setSelect2();
            expect(selectStub).toHaveBeenCalledWith({
                allowClear: true,
                placeholder: 'select'
            });
        });
    });
});
