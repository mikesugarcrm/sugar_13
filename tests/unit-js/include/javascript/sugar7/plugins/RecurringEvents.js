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
describe('Plugins.RecurringEvents', function() {
    var app;
    var plugin;
    var field;
    var sandbox;
    var module = 'Meetings';
    var createFieldProperties = {
        client: 'base',
        name: 'repeat_interval',
        type: 'enum',
        viewName: 'edit',
        module: module,
        loadFromModule: true
    };
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadPlugin('RecurringEvents');

        plugin = app.plugins.plugins.field.RecurringEvents;

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.testMetadata.set();
        sandbox = sinon.createSandbox();

        field = SugarTest.createField(createFieldProperties);
        view = SugarTest.createView('base', 'Meetings', 'record');
    });

    afterEach(function() {
        sandbox.restore();

        if (field) {
            field.dispose();
            field = null;
        }

        if (view) {
            view.dispose();
            view = null;
        }

        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        sinon.restore();
        app.cache.cutAll();

        app = null;
    });

    describe('onAttach', function() {
        beforeEach(function() {
            sinon.stub(plugin, '_fieldOnAttach').callsFake(function() {});
            sinon.stub(plugin, '_viewOnAttach').callsFake(function() {});
        });

        it('should call _fieldOnAttach when called with a Field component', function() {
            plugin.onAttach(field);

            expect(plugin._fieldOnAttach).toHaveBeenCalled();
            expect(plugin._viewOnAttach).not.toHaveBeenCalled();
            expect(plugin.isField).toBeTruthy();
            expect(plugin.isView).toBeFalsy();
        });

        it('should call _viewOnAttach when called with a View component', function() {
            plugin.onAttach(view);

            expect(plugin._fieldOnAttach).not.toHaveBeenCalled();
            expect(plugin._viewOnAttach).toHaveBeenCalled();
            expect(plugin.isField).toBeFalsy();
            expect(plugin.isView).toBeTruthy();
        });
    });

    describe('_fieldOnAttach', function() {
        it('should attach render event handler', function() {
            plugin.before = sinon.stub();

            plugin._fieldOnAttach();

            expect(plugin.before).toHaveBeenCalledWith('render');
        });
    });

    describe('_viewOnAttach', function() {
        it('should attach init event handler', function() {
            plugin.once = sinon.stub();

            plugin._viewOnAttach();

            expect(plugin.once).toHaveBeenCalled();
        });
    });

    describe('prepareRepeatIntervalValues', function() {
        it('should call prepareRepeatIntervalValues', function() {
            var stub = sandbox.stub(field, 'prepareRepeatIntervalValues');

            field.render();

            expect(stub).toHaveBeenCalled();
        });

        it('should call getRepeatIntervalKeyword', function() {
            var stub = sandbox.stub(field, 'getRepeatIntervalKeyword');

            field.render();

            expect(stub).toHaveBeenCalled();
        });

        it('should call getRepeatIntervalString', function() {
            field.items = {
                '1': '1',
                '2': '2',
            };

            var stub = sandbox.stub(field, 'getRepeatIntervalString');
            field.model.set('repeat_type', 'Daily');

            field.render();

            expect(stub).toHaveBeenCalled();
        });

        it('should generate the corresponding values', function() {
            field.items = {
                '1': '1',
                '2': '2',
                '3': '3',
                '4': '4',
            };

            var keyword;
            var interval = '';

            sinon.stub(app.lang, 'get').callsFake(function(vname, module) {
                switch (vname) {
                    case 'LBL_CALENDAR_DAY':
                        keyword = 'day';
                        return keyword;
                    case 'LBL_CALENDAR_REPEAT_INTERVAL_VALUE_2':
                        interval = '2nd';
                        return interval;
                    case 'LBL_CALENDAR_REPEAT_INTERVAL_VALUE_3':
                        interval = '3rd';
                        return interval;
                    case 'LBL_CALENDAR_REPEAT_INTERVAL_VALUE_4':
                        interval = '4th';
                        return interval;
                    case 'TPL_REPEAT_INTERVAL':
                        return `Every ${interval} ${keyword}`;
                }
            });

            field.model.set('repeat_type', 'Daily');

            field.render();

            expect(field.items).toEqual({
                '1': 'Every  day',
                '2': 'Every 2nd day',
                '3': 'Every 3rd day',
                '4': 'Every 4th day',
            });
        });
    });

    describe('prepareRepeatOrdinalValues', function() {
        it('should generate the values as strings with the first letter capitalized ', function() {
            field.name = 'repeat_ordinal';
            field.action = 'detail';
            field.items = {
                'first': 'first',
                'second': 'second',
            };

            field.model.set('repeat_ordinal', 'first');
            field.prepareRepeatOrdinalValues();

            expect(field.items).toEqual({
                'first': 'First',
                'second': 'second',
            });
        });
    });
});
