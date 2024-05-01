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

describe('Copying text to the clipboard', function() {
    var app;
    var sandbox;
    var model;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('base', 'field', 'base', 'edit');
        SugarTest.testMetadata.set();

        sandbox = sinon.createSandbox();

        model = app.data.createBean('Contacts', {
            id: _.uniqueId(),
            first_name: 'Franklin',
            last_name: 'Roberts'
        });

        field = SugarTest.createField('base', 'first_name', 'base', 'edit', null, 'Contacts', model);
        field.render();
        field.$el.append('<button data-clipboard="enabled" data-clipboard-text="Franklin">Copy</button>');
        field.$el.appendTo('body');
    });

    afterEach(function() {
        field.$el.remove();
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('copying is successful', function() {
        var $target;

        beforeEach(function() {
            $target = field.$('[data-clipboard=enabled]');

            // Stub the copy command.
            sandbox.stub(document, 'execCommand').returns(true);
        });

        it('should alert the user', function() {
            var call;

            sandbox.stub(app.alert, 'show');

            // Simulate a click to initiate a copy.
            $target.click();

            // Verify that the success alert was shown.
            call = app.alert.show.getCall(0);
            expect(app.alert.show).toHaveBeenCalledOnce();
            expect(call.args[0]).toBe('clipboard');
            expect(call.args[1].level).toBe('success');
            expect(call.args[1].messages).toBe('LBL_TEXT_COPIED_TO_CLIPBOARD_SUCCESS');
        });

        it('should trigger a success event on the target', function() {
            $target.on('clipboard.success', function(evt, params) {
                expect(params.action).toBe('copy');
                expect(params.text).toBe('Franklin');
            });

            // Simulate a click to initiate a copy.
            $target.click();
        });
    });

    describe('copying is unsuccessful', function() {
        var $target;

        beforeEach(function() {
            $target = field.$('[data-clipboard=enabled]');

            // Stub the copy command.
            sandbox.stub(document, 'execCommand').returns(false);
        });

        it('should alert the user', function() {
            var call;

            sandbox.stub(app.alert, 'show');

            // Simulate a click to initiate a copy.
            $target.click();

            // Verify that the error alert was shown.
            call = app.alert.show.getCall(0);
            expect(app.alert.show).toHaveBeenCalledOnce();
            expect(call.args[0]).toBe('clipboard');
            expect(call.args[1].level).toBe('error');
            expect(call.args[1].messages).toBe('LBL_TEXT_COPIED_TO_CLIPBOARD_ERROR');
        });

        it('should trigger an error event on the target', function() {
            $target.on('clipboard.error', function(evt, params) {
                expect(params.action).toBe('copy');
            });

            // Simulate a click to initiate a copy.
            $target.click();
        });
    });

    it('should dismiss the clipboard alert when the app view changes', function() {
        sandbox.spy(app.alert, 'dismiss');

        app.triggerBefore('app:view:change');

        expect(app.alert.dismiss).toHaveBeenCalledWith('clipboard');
    });
});
