
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
describe('Base.HintAccountsLogoField', function() {
    var app;
    var field;
    var model;
    var module = 'Accounts';
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        app.hint = {
            isDarkMode: function() {},
        };

        model = app.data.createBean(module);

        context = app.context.getContext();

        context.set({
            module: module,
            model: model,
        });

        field = SugarTest.createField(
            'base',
            'test',
            'hint-accounts-logo',
            'record',
            {},
            module,
            model,
            context
        );
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model = null;
        field = null;
    });

    describe('initialize()', function() {
        it('should add plugins', function() {
            expect(field.plugins).toContain('MetadataEventDriven');
            expect(field.plugins).toContain('Stage2CssLoader');
        });

        it('should add listeners', function() {
            expect(field.events['click .image_preview']).toEqual('addDeleteImageLabel');
            expect(field.events['click .image_btn_label']).toEqual('deleteImage');
        });

        it('should have activeClass', function() {
            expect(field.activeClass).toContain('hint-accounts-logo--record-view');
        });
    });

    describe('_render()', function() {
        it('should render the field', function() {
            var renderStub = sinon.stub(field, '_render');
            field._render();
            expect(renderStub).toHaveBeenCalledOnce();
        });
    });

    it('should bind data change', function() {
        var stub = sinon.stub(app.view.Field.prototype, 'bindDataChange');
        field.view = new app.view.View({});
        field.view.name = 'detail';
        field.view.options = {viewName: 'edit'};
        field.bindDataChange();
        expect(stub).toHaveBeenCalled();
        stub.resetHistory();

        field.view.name = 'detail';
        field.view.options = {viewName: 'detail'};
        field.bindDataChange();
        expect(stub).toHaveBeenCalled();
    });
});
