
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
describe('Base.HintNewsDashletSearchField', function() {
    var app;
    var field;
    var model;
    var module = 'Home';
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
            'hint-news-dashlet-search',
            'record',
            {},
            module,
            model,
            context
        );
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
    });

    describe('initialize()', function() {
        it('should add listeners', function() {
            expect(field.events['keyup #hint-news-dashlet-searchinput']).toEqual('filterOnEnter');
            expect(field.events['click #hint-news-dashlet-searchbtn']).toEqual('filterNews');
            expect(field.events['click #hint-news-dashlet-resetbtn']).toEqual('resetFilter');
        });
    });

    describe('_render()', function() {
        it('should render the field', function() {
            var renderStub = sinon.stub(field, '_render');
            field._render();
            expect(renderStub).toHaveBeenCalledOnce();
            renderStub.restore();
        });
    });
});
