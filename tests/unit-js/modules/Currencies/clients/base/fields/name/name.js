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

describe('Currencies.Base.Fields.Name', function() {
    let layout;
    let view;
    let field;

    beforeEach(function() {
        let def = {
            name: 'name',
            type: 'name'
        };
        layout = SugarTest.createLayout('base', 'Currencies', 'record', {});
        view = SugarTest.createView('base', 'Currencies', 'record', null, null, true, layout);
        field = SugarTest.createField({
            name: 'name',
            type: 'name',
            viewName: 'edit',
            fieldDef: def,
            module: 'Currencies',
            model: view.model,
            loadFromModule: true
        });
        sinon.stub(field, '_super');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();
        field.dispose();
        view = null;
        layout = null;
    });

    describe('initialize', function() {
        it('should set a field change listener for \'iso4217\' and \'name\'', function() {
            sinon.spy(field.model, 'on');
            listenToStub = sinon.stub(field, 'listenTo');
            field.view.name = 'name';
            field.initialize({});

            expect(field.model.on).toHaveBeenCalledWith('change:iso4217');
            expect(listenToStub.getCall(0).args[1]).toEqual('change:name');
        });
    });
});
