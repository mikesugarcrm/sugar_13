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
describe('ConsoleConfiguration.Fields.PreviewTable', function() {
    var app;
    var model;
    var field;
    var moduleName = 'ConsoleConfiguration';

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean('ConsoleConfiguration', {
            enabled_module: 'Cases'
        });
        var name = 'preview-table';
        SugarTest.loadComponent('base', 'field', 'base');
        field = SugarTest.createField('base', name, name, 'view', {}, moduleName, model, null, true);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
    });

    describe('preview generation', function() {
        it('should trigger render on update', function() {
            sinon.spy(field, 'render');
            field.context.trigger('consoleconfig:preview:Cases');
            expect(field.render).toHaveBeenCalled();
        });

        it('should create a mapping of classes that can be rendered through the template', function() {
            var fieldList = {
                case_number: [
                    {name: ''}
                ],
                status: [
                    {name: ''},
                    {name: ''}
                ],
                follow_up_datetime: [
                    {name: ''},
                    {name: ''}
                ],
                name: [
                    {name: ''}
                ],
                business_center: [
                    {name: ''}
                ],
                account_name: [
                    {name: ''},
                    {name: ''}
                ],
                assigned_user_name: [
                    {name: ''}
                ]
            };

            var rowDesign = [
                [
                    ['cell-bar--long'],
                    ['cell-bar--long', 'cell-bar--short'],
                    ['cell-bar--long', 'cell-bar--short'],
                    ['cell-bar--long'],
                    ['cell-bar--long'],
                    ['cell-bar--long', 'cell-bar--short'],
                    ['cell-bar--long']
                ], [
                    ['cell-bar--short'],
                    ['cell-bar--long', 'cell-bar--short'],
                    ['cell-bar--long', 'cell-bar--short'],
                    ['cell-bar--short'],
                    ['cell-bar--short'],
                    ['cell-bar--long', 'cell-bar--short'],
                    ['cell-bar--short']
                ]
            ];

            expect(field.rowDesign).toEqual([]);
            field.previewRows = 2;
            field.setRowDesign(fieldList);
            expect(field.rowDesign).toEqual(rowDesign);
        });
    });
});
