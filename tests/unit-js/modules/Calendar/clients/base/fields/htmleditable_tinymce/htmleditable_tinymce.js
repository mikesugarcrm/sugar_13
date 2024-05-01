
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
describe('Base.Calendar.HtmleditableTinymceField', function() {
    var app;
    var field;
    var model;
    var module = 'Calendar';
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        context = app.context.getContext();

        context.set({
            module: module,
            model: model,
        });

        context.prepare();

        field = SugarTest.createField(
            'base',
            'event_tooltip_template',
            'htmleditable_tinymce',
            'edit',
            {},
            module,
            model,
            context,
            true
        );
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
    });

    describe('initialize()', function() {
        var listenStub;

        beforeEach(function() {
            listenStub = sinon.stub(field, 'listenTo');
        });

        it('should add listener for change:calendar_module on field.model', function() {
            field.initialize({});
            expect(listenStub).toHaveBeenCalled();
            expect(listenStub.getCall(0).args[1]).toEqual('change:calendar_module');
        });
    });

    describe('getTinyMCEConfig()', function() {
        it('should add the new button to the toolbar - insertfield_calendar', function() {
            var getConfig = field.getTinyMCEConfig();
            expect(getConfig.toolbar).toContain('insertfield_calendar');
        });
    });
});
