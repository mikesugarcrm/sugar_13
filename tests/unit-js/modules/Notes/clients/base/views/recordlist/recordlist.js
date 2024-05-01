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
describe('Notes.Base.Views.RecordList', function() {

    var moduleName = 'Notes';

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'recordlist', moduleName);
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base');
        view = SugarTest.createView(
            'base',
            moduleName,
            'recordlist'
        );
    });

    afterEach(function() {
        view.fields = {};
        view.dispose();
    });

    describe('getModelRowFields', function() {
        it('should get collection of the model fields except "multi-attachments" type', function() {
            var model = new Backbone.Model({
                id: _.uniqueId('getRowFields-model-id-')
            });

            view.fields[_.uniqueId('getRowFields-field-id-')] = {
                model: model,
                type: 'fieldset'
            };
            view.fields[_.uniqueId('getRowFields-field-id-')] = {
                model: model,
                type: 'multi-attachments'
            };
            view.fields[_.uniqueId('getRowFields-field-id-')] = {
                model: model,
                type: null
            };
            view.fields[_.uniqueId('getRowFields-field-id-')] = {
                model: model,
                type: 'multi-attachments'
            };

            view.trigger('render');
            expect(view.rowFields[model.id].length).toEqual(4);

            var modelRowFields = view.getModelRowFields(model.id);
            expect(modelRowFields.length).toEqual(2);
            expect(_.where(modelRowFields, {type: 'multi-attachments'}).length).toEqual(0);
        });
    });
});
