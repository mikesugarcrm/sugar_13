
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
describe('Base.CalendarsField', function() {
    var app;
    var field;
    var model;
    var module = 'Calendar';
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('calendars', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('calendars', 'field', 'base', 'items.calendars');

        SugarTest.loadComponent('base', 'field', 'calendars');

        SugarTest.declareData('base', 'Calendar', true, true);

        SugarTest.testMetadata.set();

        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        context = app.context.getContext();

        context.set({
            module: module,
            model: model,
        });

        field = SugarTest.createField(
            'base',
            'calendars',
            'calendars',
            'edit',
            {
                'name': 'myCalendars',
                'label': 'LBL_CALENDAR_MY_CALENDARS',
                'type': 'calendars',
                'view': 'edit',
                'view_source': 'main-panel',
                'calendar_type': 'main',
                'calendar_filter': 'my_calendars',
                'css_class': 'calendar'
            },
            module,
            model,
            context,
            false
        );
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('initialize()', function() {
        it('should add listeners', function() {
            listenToStub = sinon.stub(field, 'listenTo');
            field.initialize({});

            expect(listenToStub.getCall(0).args[0]).toEqual(field);
            expect(listenToStub.getCall(0).args[1]).toEqual('calendars:selectAll');
        });
    });

    describe('render()', function() {
        it('should load enum options', function() {
            var superStub = sinon.stub(field, '_super');
            var loadEnumOptionsStub = sinon.stub(field, 'loadEnumOptions');

            field.render();

            expect(loadEnumOptionsStub).toHaveBeenCalledOnce();

            superStub.restore();
            loadEnumOptionsStub.restore();
        });

        it('should update count', function() {
            var superStub = sinon.stub(field, '_super');
            var loadEnumOptionsStub = sinon.stub(field, 'loadEnumOptions');
            var updateCountStub = sinon.stub(field, 'updateCount');

            field.render();

            expect(updateCountStub).toHaveBeenCalledOnce();

            superStub.restore();
            loadEnumOptionsStub.restore();
            updateCountStub.restore();
        });
    });

    describe('_render()', function() {
        it('should render the template for the field in DOM', function() {

            field.items = [{
                'id': '7f0a9f2c-f36a-11eb-9236-0242ac140005:user:1',
                'name': 'Administrator\'s calls',
                'calendarId': '7f0a9f2c-f36a-11eb-9236-0242ac140005',
                'selected': true,
                'color': '#c0edff',
                'userId': '1',
                'userName': 'Administrator',
                'dotColor': '#5e86d0'
            }];
            field.bkItems = field.items;
            field._render();

            var calendarsSearch = field.$el.find('.calendars-search');
            expect(calendarsSearch.length).toEqual(1);

            var calendarItemsListed = field.$el.find('ul li');
            expect(calendarItemsListed.length).toEqual(1);
        });
    });
});
