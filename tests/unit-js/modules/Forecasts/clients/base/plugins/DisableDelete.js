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

describe('Forecasts.Base.Plugins.DisableDelete', function() {

    var app, field, moduleName = 'Opportunities', context, def, model, getField;

    beforeEach(function() {
        app = SUGAR.App;
        context = app.context.getContext();

        SugarTest.loadFile('../modules/Forecasts/clients/base/plugins', 'DisableDelete', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        def = {
            type: 'rowaction',
            event: 'button:delete_button:click',
            name: 'delete_button',
            label: 'LBL_DELETE_BUTTON_LABEL',
            acl_action: 'delete'
        };

        model = new Backbone.Model({
            id: 'aaa',
            name: 'boo',
            module: moduleName

        });

        SugarTest.seedMetadata(true);
        app.metadata.getModule('Forecasts', 'config').is_setup = 1;
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');

        getField = function() {
            var field = SugarTest.createField({
                name: 'delete_button',
                client: 'base',
                type: 'rowaction',
                viewName: 'detail',
                fieldDef: def,
                module: moduleName,
                model: model,
                context: context,
                loadFromModule: true
            });
            sinon.stub(field, 'getFieldElement').callsFake(function() {
                return $('<a></a>');
            });
            return field;
        };

    });

    afterEach(function() {
        sinon.restore();
        delete app.plugins.plugins['field']['DisableDelete'];
        field = null;
        app = null;
        context = null;
        def = null;
        model = null;
    });

    describe('_getFieldName', function() {
        describe('when using opps with rlis', function() {
            beforeEach(function() {
                app.metadata.getModule('Opportunities', 'config').opps_view_by = 'RevenueLineItems';
            });
            describe('and on opps module', function() {
                beforeEach(function() {
                    model.module = 'Opportunities';
                    field = getField();
                });

                it('should return sales_status', function() {
                    expect(field._getFieldName()).toEqual('sales_status');
                });
            });

            describe('and on rli module', function() {
                beforeEach(function() {
                    model.module = 'RevenueLineItems';
                    field = getField();
                });

                it('should return sales_stage', function() {
                    expect(field._getFieldName()).toEqual('sales_stage');
                });
            });
        });

        describe('when using opps without rlis', function() {
            beforeEach(function() {
                app.metadata.getModule('Opportunities', 'config').opps_view_by = 'Opportunities';
            });
            describe('and on opps module', function() {
                beforeEach(function() {
                    model.module = 'Opportunities';
                    field = getField();
                });

                it('should return sales_stage', function() {
                    expect(field._getFieldName()).toEqual('sales_stage');
                });
            });
        });
    });

    describe('using sales_status: with closed items', function() {
        var sales_field = 'sales_status',
            label_key = '_STATUS';

        beforeEach(function() {
            // testing when view_by is RevenueLineItems
            model.module = 'Opportunities';
            app.metadata.getModule('Opportunities', 'config').opps_view_by = 'RevenueLineItems';
        });

        afterEach(function() {
            // testing when view_by is RevenueLineItems
            delete model.module;
        });

        describe('when there are no closed RLIs', function() {

            beforeEach(function() {
                model.set('closed_revenue_line_items', 0);
            });

            describe('when status is Closed Won', function() {
                it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                    model.set(sales_field, 'Closed Won');
                    field = getField();
                    var message = field.removeDelete();
                    expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
                });
            });

            describe('when status is not closed', function() {
                it('message should contain nothing', function() {
                    model.set(sales_field, 'In Progress');
                    field = getField();
                    var message = field.removeDelete();
                    expect(message).toEqual(null);
                });
            });
        });

        describe('when there are closed RLIs', function() {
            beforeEach(function() {
                model.set('closed_revenue_line_items', 1);
            });

            describe('when status is Closed Won', function() {
                it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                    model.set(sales_field, 'Closed Won');
                    field = getField();
                    var message = field.removeDelete();
                    expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
                });
            });

            describe('when status is not closed', function() {
                it('message should contain NOTICE_NO_DELETE_CLOSED_RLIS', function() {
                    model.set(sales_field, 'In Progress');
                    field = getField();
                    var message = field.removeDelete();
                    expect(message).toEqual('NOTICE_NO_DELETE_CLOSED_RLIS');
                });
            });
        });
    });

    describe('using sales_stage: with closed items', function() {
        var sales_field = 'sales_stage',
            label_key = '_STAGE';

        beforeEach(function() {
            // testing when view_by is Opportunities
            app.metadata.getModule('Opportunities', 'config').opps_view_by = 'Opportunities';
        });

        afterEach(function() {
            app.metadata.getModule('Opportunities', 'config').opps_view_by = 'RevenueLineItems';
        });

        describe('when sales_stage is used', function() {
            it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                model.set('closed_revenue_line_items', 0);
                model.set('sales_stage', 'Closed Won');
                field = getField();
                var message = field.removeDelete();
                expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
            });
        });

        describe('when the button event is list:deleterow:fire', function() {
            it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                model.set('closed_revenue_line_items', 0);
                model.set('sales_stage', 'Closed Won');
                field = getField();
                field.def.event = 'list:deleterow:fire';
                var message = field.removeDelete();
                expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
            });
        });

        describe('when sales_stage is used', function() {
            it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                model.set('closed_revenue_line_items', 0);
                model.set(sales_field, 'Closed Won');
                field = getField();
                var message = field.removeDelete();
                expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
            });
        });

        describe('when the button event is list:deleterow:fire', function() {
            it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                model.set('closed_revenue_line_items', 0);
                model.set(sales_field, 'Closed Won');
                field = getField();
                field.def.event = 'list:deleterow:fire';
                var message = field.removeDelete();
                expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
            });
        });

    });
});
