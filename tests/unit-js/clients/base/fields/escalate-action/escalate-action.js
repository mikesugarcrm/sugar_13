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
describe('Base.Field.EscalateAction', function() {

    var app;
    var field;
    var moduleName = 'Accounts';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'escalate-action');
        field = SugarTest.createField('base','escalate-action', 'escalate-action', 'detail', {
            'type': 'escalate-action',
            'event': 'button:escalate_button:click',
            'acl_action': 'create'
        }, moduleName);
    });

    afterEach(function() {
        app.cache.cutAll();
        field.dispose();
        sinon.restore();
    });

    describe('Escalate button render', function() {

        using('differnt configurations',
            [
                {
                    isEscalatable: true,
                    result: false
                },
                {
                    isEscalatable: false,
                    result: true
                },
            ],
            function(data) {
                it('should render the escalate button when appropriate', function() {
                    var isModuleEscalatableStub = sinon.stub(field, 'isEscalatable').callsFake(function() {
                        return data.isEscalatable;
                    });

                    field.render();
                    expect(isModuleEscalatableStub).toHaveBeenCalled();
                    expect(field.isHidden).toBe(data.result);
                });

                it('should be allowed/disallowed based on escalatable status', function() {
                    var isModuleEscalatableStub = sinon.stub(field, 'isEscalatable').callsFake(function() {
                        return data.isEscalatable;
                    });

                    expect(field.isAllowedDropdownButton()).toBe(!data.result);
                });
            }
        );
    });
    describe('isEscalatable', function() {
        using('different access data',
            [
                {
                    hasAccess: false,
                    moduleEscalatable: true,
                    expected: false
                },
                {
                    hasAccess: true,
                    moduleEscalatable: true,
                    expected: true
                },
            ],
            function(data) {
                it('should determine generic isEscalatable', function() {
                    sinon.stub(app.acl, 'hasAccess').returns(data.hasAccess);
                    sinon.stub(field, 'isModuleEscalatable').returns(data.moduleEscalatable);

                    expect(field.isEscalatable('Accounts')).toEqual(data.expected);
                });
            }
        );
    });
});
