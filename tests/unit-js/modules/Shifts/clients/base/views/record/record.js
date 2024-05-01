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
describe('Shifts.Views.Record', function() {
    let app;
    let view;
    let fields;
    const moduleName = 'Shifts';
    const viewName = 'record';
    const day = 'sunday';

    beforeEach(function() {
        app = SugarTest.app;

        fields = [
            {
                name: 'is_open_' + day,
                type: 'bool',
            },
            {
                name: day + '_open',
                type: 'timeselect',
                fields: [
                    day + '_open_hours',
                    day + '_open_minutes',
                ]
            },
        ];

        const meta = {
            panels: [
                {
                    name: 'panel_header',
                    fields: ['name'],
                },
                {
                    name: 'panel_body',
                    fields: fields,
                }
            ]
        };

        view = SugarTest.createView(
            'base',
            moduleName,
            viewName,
            meta,
            null,
            true
        );

        view.render();
    });

    afterEach(function() {
        view.dispose();
        sinon.restore();
    });

    describe('validateHoursList', function() {
        it('should call the callback function', function() {
            const callback = sinon.stub();

            sinon.stub(view, 'validateHours');
            view.validateHoursList(fields, {}, callback);
            expect(callback).toHaveBeenCalled();
        });
    });

    describe('validateHours', function() {
        it('should return a empty response', function() {
            sinon.stub(view.model, 'get')
                .withArgs(day + '_open').returns({hour: 1, minute: 0})
                .withArgs(day + '_close').returns({hour: 2, minute: 0})
                .withArgs('is_open_' + day).returns(true);

            const res = view.validateHours(day);
            expect(res).toEqual({});
        });

        it('should return a not empty response', function() {
            sinon.stub(view.model, 'get')
                .withArgs(day + '_open').returns({hour: 2, minute: 0})
                .withArgs(day + '_close').returns({hour: 1, minute: 0})
                .withArgs('is_open_' + day).returns(true);

            sinon.stub(view, 'getField')
                .withArgs(day + '_open').returns({label: 'open-field label'})
                .withArgs(day + '_close').returns({label: 'close-field label'});

            const res = view.validateHours(day);
            expect(res).not.toEqual({});
        });
    });
});
