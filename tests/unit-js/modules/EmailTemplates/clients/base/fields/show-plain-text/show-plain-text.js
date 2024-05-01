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

describe('EmailTemplates.Field.ShowPlainText', function() {
    var app;
    var field;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'field', 'show-plain-text', 'EmailTemplates');
        app = SugarTest.app;
        field = SugarTest.createField({
            name: 'plaintext',
            type: 'show-plain-text',
            viewName: 'record',
            module: 'EmailTemplates',
            loadFromModule: true
        });
    });

    afterEach(function() {
        field.dispose();
        app.view.reset();
    });

    describe('buttonClicked', function() {
        using('different initial states', [[true, false], [false, true]], function(initial, expected) {
            it('should toggle the body field based on context', function() {
                sinon.stub(field, 'toggleExpandPlainText');
                field.plainTextExpanded = initial;

                field.buttonClicked();

                expect(field.toggleExpandPlainText).toHaveBeenCalledWith(!field.plainTextExpanded);
                expect(field.plainTextExpanded).toBe(expected);
            });
        });
    });
});
