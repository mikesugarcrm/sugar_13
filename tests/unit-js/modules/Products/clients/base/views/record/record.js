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
describe('Products.Base.Views.Record', function() {
    var app;
    var view;
    var viewMeta;

    beforeEach(function() {
        app = SugarTest.app;

        viewMeta = {
            panels: [{
                fields: ['field1', 'field2']
            }]
        };
        SugarTest.loadComponent('base', 'view', 'record');

        view = SugarTest.createView('base', 'Products', 'record', viewMeta, null, true);
        sinon.stub(view, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('delegateButtonEvents()', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'on');
            view.delegateButtonEvents();
        });

        it('should set context listener for button:convert_to_quote:click', function() {
            expect(view.context.on).toHaveBeenCalledWith('button:convert_to_quote:click');
        });

        it('should set context listener for editable:record:toggleEdit', function() {
            expect(view.context.on).toHaveBeenCalledWith('editable:record:toggleEdit');
        });

        it('should call _super with delegateButtonEvents', function() {
            expect(view._super).toHaveBeenCalledWith('delegateButtonEvents');
        });
    });

    describe('_toggleRecordEdit()', function() {
        beforeEach(function() {
            sinon.stub(view, 'setButtonStates');
            view._toggleRecordEdit();
        });

        it('should call setButtonStates', function() {
            expect(view.setButtonStates).toHaveBeenCalledWith(view.STATE.EDIT);
        });
    });

    describe('cancelClicked()', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'trigger');
            view.cancelClicked();
        });

        it('should trigger record:cancel:clicked on the context', function() {
            expect(view.context.trigger).toHaveBeenCalledWith('record:cancel:clicked');
        });

        it('should call _super with cancelClicked', function() {
            expect(view._super).toHaveBeenCalledWith('cancelClicked');
        });
    });
});
