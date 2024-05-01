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
describe('Base.Field.CJ_Forms.ModuleTrigger', function() {
    let field;
    let fieldType = 'module-trigger';
    let app;
    let model;
    let fieldName = 'module_trigger';
    let module = 'CJ_Forms';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(module);
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', {}, module, model, null, true);
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
        app = null;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('_loadTemplate', function() {
        it('should call the loadTemplate', function() {
            sinon.stub(field, '_super');
            field._loadTemplate();
            expect(field.type).toBe('enum');
            expect(field._super).toHaveBeenCalledWith('_loadTemplate');
        });
    });

    describe('_render', function() {
        it('should call the render', function() {
            sinon.stub(field, '_super');
            sinon.stub(field, 'prepareItems');
            field._render();
            expect(field.prepareItems).toHaveBeenCalled();
            expect(field._super).toHaveBeenCalledWith('_render');
        });
    });

    describe('getTemplateID', function() {
        it('should return the workflow template record', function() {
            field.model.set('smart_guide_template_id', '123');
            field.getTemplateID();
            expect(field.getTemplateID()).toBe('123');
        });
    });

    describe('loadEnumOptions', function() {
        it('should load the dropdown options', function() {
            let bean = app.data.createBean('DRI_Workflow_Templates');
            sinon.stub(app.data, 'createBeanCollection').returns(bean);
            this.templatesCollection = [];
            field.loadEnumOptions();
            expect(app.data.createBeanCollection).toHaveBeenCalled();
        });
    });

    describe('prepareItems', function() {
        it('should  prepare the items object and render the field', function() {
            let object = {
                get: sinon.stub().returns(['Accounts', 'Contacts'])
            };
            field.templatesCollection = {
                get: sinon.stub().returns(object)
            };
            sinon.stub(field, 'getTemplateID').returns('123');
            field.prepareItems();
            expect(field.templatesCollection.get).toHaveBeenCalled();
            expect(field.getTemplateID).toHaveBeenCalled();
        });
    });
});

