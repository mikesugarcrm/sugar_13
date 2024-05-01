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
describe('Base.Field.ShowHelpButton', function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
        Handlebars.templates = {};
    });

    describe('initialize', function() {
        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.set();
        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
        });

        it('should init the default properties', function() {
            field = SugarTest.createField('base', 'test_c', 'show-help-button', 'record', {}, 'Accounts');

            // createField() does not set the view type on itself through the class constructor
            field.view.type = 'record';
            field.initialize({
                view: field.view
            });

            expect(field._helpModal).toEqual(false);
        });
    });

    describe('render', function() {
        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.loadHandlebarsTemplate('show-help-button', 'field', 'base', 'detail');
            SugarTest.testMetadata.set();
            field = SugarTest.createField('base', 'test_c', 'show-help-button', 'record', {}, 'Accounts');

            // createField() does not set the view type on itself through the class constructor
            field.view.type = 'record';
            field.initialize({
                view: field.view,
            });

            field.options.def.popupTitle = 'testTitle';
        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
            field = null;
        });

        it('should render the modal', function() {
            expect(field._helpModal).toEqual(false);

            field.render();

            expect(field._helpModal).not.toEqual(false);
            expect(field._helpModal.options.popupTitle).toEqual('testTitle');
        });
    });
});
