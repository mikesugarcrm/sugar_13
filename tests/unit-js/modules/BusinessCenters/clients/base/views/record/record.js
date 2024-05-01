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
describe('BusinessCenters.Views.Record', function() {
    var app;
    var fields;
    var moduleName = 'BusinessCenters';
    var view;
    var viewName = 'record';

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'headerpane');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'tabspanels');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'businesscard');
        SugarTest.loadHandlebarsTemplate('record-decor', 'field', 'base', 'record-decor');
        SugarTest.loadComponent('base', 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'view', viewName, moduleName);
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'field', 'business-day-status', moduleName);
        SugarTest.loadComponent('base', 'field', 'timeselect');
        SugarTest.loadComponent('base', 'field', 'record-decor');

        app = SugarTest.app;
        app.data.declareModels();

        fields = [
            {
                name: 'is_open_sunday',
                type: 'business-day-status'
            },
            {
                name: 'sunday_open',
                type: 'timeselect',
                fields: [
                    'sunday_open_hours',
                    'sunday_open_minutes'
                ]
            },
            {
                name: 'sunday_close',
                type: 'timeselect',
                fields: [
                    'sunday_close_hours',
                    'sunday_close_minutes'
                ]
            },
            {
                name: 'is_open_monday',
                type: 'business-day-status'
            },
            {
                name: 'monday_open',
                type: 'timeselect',
                fields: [
                    'monday_open_hours',
                    'monday_open_minutes'
                ]
            },
            {
                name: 'monday_close',
                type: 'timeselect',
                fields: [
                    'monday_close_hours',
                    'monday_close_minutes'
                ]
            },
            {
                name: 'is_open_tuesday',
                type: 'business-day-status'
            },
            {
                name: 'tuesday_open',
                type: 'timeselect',
                fields: [
                    'tuesday_open_hours',
                    'tuesday_open_minutes'
                ]
            },
            {
                name: 'tuesday_close',
                type: 'timeselect',
                fields: [
                    'tuesday_close_hours',
                    'tuesday_close_minutes'
                ]
            },
            {
                name: 'is_open_wednesday',
                type: 'business-day-status'
            },
            {
                name: 'wednesday_open',
                type: 'timeselect',
                fields: [
                    'wednesday_open_hours',
                    'wednesday_open_minutes'
                ]
            },
            {
                name: 'wednesday_close',
                type: 'timeselect',
                fields: [
                    'wednesday_close_hours',
                    'wednesday_close_minutes'
                ]
            },
            {
                name: 'is_open_thursday',
                type: 'business-day-status'
            },
            {
                name: 'thursday_open',
                type: 'timeselect',
                fields: [
                    'thursday_open_hours',
                    'thursday_open_minutes'
                ]
            },
            {
                name: 'thursday_close',
                type: 'timeselect',
                fields: [
                    'thursday_close_hours',
                    'thursday_close_minutes'
                ]
            },
            {
                name: 'is_open_friday',
                type: 'business-day-status'
            },
            {
                name: 'friday_open',
                type: 'timeselect',
                fields: [
                    'friday_open_hours',
                    'friday_open_minutes'
                ]
            },
            {
                name: 'friday_close',
                type: 'timeselect',
                fields: [
                    'friday_close_hours',
                    'friday_close_minutes'
                ]
            },
            {
                name: 'is_open_saturday',
                type: 'business-day-status'
            },
            {
                name: 'saturday_open',
                type: 'timeselect',
                fields: [
                    'saturday_open_hours',
                    'saturday_open_minutes'
                ]
            },
            {
                name: 'saturday_close',
                type: 'timeselect',
                fields: [
                    'saturday_close_hours',
                    'saturday_close_minutes'
                ]
            },
        ];

        var meta = {
            panels: [
                {
                    name: 'panel_header',
                    fields: ['name']
                },
                {
                    name: 'panel_body',
                    fields: fields
                }
            ]
        };

        SugarTest.testMetadata.addViewDefinition(viewName, meta, moduleName);

        SugarTest.testMetadata.set();

        view = SugarTest.createView(
            'base',
            moduleName,
            viewName,
            meta,
            null,
            true
        );

        sinon.stub(app.date, 'getUserTimeFormat').returns('hh:mma');
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        sinon.restore();
    });

    describe('validateBusinessHours', function() {
        it('should ensure that when a center is open, it has valid hours', function() {
            view.render();
            var attributes = {
                name: 'Quebec Business Center',
                // closed on Sunday (valid)
                is_open_sunday: 0,
                sunday_open_hour: 0,
                sunday_open_minutes: 0,
                sunday_close_hour: 0,
                sunday_close_minutes: 0,
                // open on Monday, 9AM - 5PM (valid)
                is_open_monday: 1,
                monday_open_hour: 9,
                monday_open_minutes: 0,
                monday_close_hour: 17,
                monday_close_minutes: 0,
                // open on Tuesday, 9AM-6AM (invalid)
                is_open_tuesday: 1,
                tuesday_open_hour: 9,
                tuesday_open_minutes: 0,
                tuesday_close_hour: 6,
                tuesday_close_minutes: 0,
                // open all day on Wednesday (valid)
                is_open_wednesday: 2,
                wednesday_open_hour: 0,
                wednesday_open_minutes: 0,
                wednesday_close_hour: 23,
                wednesday_close_minutes: 45,
                // open on Thursday, 9AM-9AM (invalid)
                is_open_thursday: 1,
                thursday_open_hour: 9,
                thursday_open_minutes: 0,
                thursday_close_hour: 9,
                thursday_close_minutes: 0,
                // open on Friday, but only for 30 minutes (valid)
                is_open_friday: 1,
                friday_open_hour: 14,
                friday_open_minutes: 15,
                friday_close_hour: 14,
                friday_close_minutes: 45,
                // open on Saturday, but only for 45 minutes, crossing an hour boundary (valid)
                is_open_saturday: 1,
                saturday_open_hour: 16,
                saturday_open_minutes: 30,
                saturday_close_hour: 17,
                saturday_close_minutes: 15
            };
            var callback = sinon.stub();
            view.model.set(attributes);
            view.validateBusinessHours(fields, {}, callback);
            expect(callback).toHaveBeenCalled(); // FIXME assert arguments
        });
    });
});
