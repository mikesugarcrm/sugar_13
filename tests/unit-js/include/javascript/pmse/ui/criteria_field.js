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
describe('includes.javascript.pmse.ui.criteria_field', function() {
    var _PMSE;
    beforeEach(function() {
        // Get the global PMSE classes variable.
        _PMSE = PMSE || {};
    });

    afterEach(function() {
        sinon.restore();
    });

    describe('init', function() {
        var expressionControlMock;

        beforeEach(function() {
            sinon.stub(_PMSE, 'Field');
            sinon.stub(CriteriaField.prototype, 'setEvaluations').returnsThis();
            sinon.stub(CriteriaField.prototype, 'setFieldWidth').returnsThis();
            sinon.stub(CriteriaField.prototype, 'setFieldHeight').returnsThis();
            sinon.stub(CriteriaField.prototype, 'setValue');
            expressionControlMock = sinon.stub(window, 'ExpressionControl');
        });

        it('should pass on the name attribute to the expression control', function() {
            var params = {
                name: 'evn_criteria'
            };
            new CriteriaField(params);
            expect(expressionControlMock).toHaveBeenCalledWith(jasmine.objectContaining(params));
        });
    });
});
