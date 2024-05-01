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
describe('Products.Base.Fields.QuoteDataRelate', function() {
    var app;
    var field;
    var fieldDef;
    var fieldType = 'quote-data-relate';
    var fieldModule = 'Products';

    beforeEach(function() {
        app = SugarTest.app;
        fieldDef = {
            type: fieldType,
            label: 'testLbl',
            css_class: ''
        };

        field = SugarTest.createField('base', fieldType, fieldType, 'detail',
            fieldDef, fieldModule, null, null, true);

        sinon.stub(field, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.stub(app.lang, 'get').callsFake(function(key) {
                return key;
            });
        });

        it('should set createNewLabel text', function() {
            field.initialize({});
            expect(field.createNewLabel).toBe('LBL_CREATE_NEW_QLI_IN_DROPDOWN');
        });

        it('should set newQLIId', function() {
            field.initialize({});
            expect(field.newQLIId).toBe('newQLIId');
        });
    });

    describe('_getPopulateMetadata()', function() {
        beforeEach(function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {});
        });

        it('should set template to empty when in edit mode', function() {
            field._getPopulateMetadata();
            expect(app.metadata.getModule).toHaveBeenCalledWith('Products');
        });
    });

    describe('_getSelect2Options()', function() {
        var result;
        beforeEach(function() {
            field._super.restore();
            sinon.stub(field, '_super').callsFake(function() {
                return {
                    parentOptions: 'yup'
                };
            });
        });

        it('should return parent options', function() {
            result = field._getSelect2Options();
            expect(result.parentOptions).toBe('yup');
        });

        it('should return this field custom options', function() {
            result = field._getSelect2Options();
            expect(result.createSearchChoice).toBeDefined();
        });
    });

    describe('format()', function() {
        var paramValue;
        var modelThisNameValue;
        var modelNameValue;

        beforeEach(function() {
            paramValue = 'paramValue';
            modelThisNameValue = 'modelThisNameValue';
            modelNameValue = 'modelNameValue';
        });

        it('should use passed in value param if it exists', function() {
            field.format(paramValue);
            expect(field._super).toHaveBeenCalledWith('format', [paramValue]);
        });

        it('should use value set on model by the field.name if it exists', function() {
            field.model.set(field.name, modelThisNameValue);
            field.format();
            expect(field._super).toHaveBeenCalledWith('format', [modelThisNameValue]);
        });

        it('should use value set as name on model if it exists', function() {
            field.model.set('name', modelNameValue);
            field.format();
            expect(field._super).toHaveBeenCalledWith('format', [modelNameValue]);
        });
    });

    describe('_buildRoute()', function() {
        beforeEach(function() {
            field.model.set({
                id: 'fieldModelId1',
                _module: 'Products'
            });
            app.routing.start();
        });

        afterEach(function() {
            app.router.stop();
        });

        it('should set href', function() {
            sinon.stub(app.acl, 'hasAccess').returns(true);
            sinon.stub(app.router, 'buildRoute').returns('link');
            field._buildRoute();
            expect(field.href).toBe('#link');
        });

        it('should not set href', function() {
            sinon.stub(app.acl, 'hasAccess').returns(false);
            field._buildRoute();
            expect(field.href).toBeUndefined();
        });
    });

    describe('_getRelateId()', function() {
        beforeEach(function() {
            field.model.set({
                idDefName: 'testId1',
                id: 'testId2'
            });
        });

        it('should return model attrib by def.id_name by default', function() {
            field.def.id_name = 'idDefName';
            expect(field._getRelateId()).toBe('testId1');
        });

        it('should return model id if model def.id_name does not exist', function() {
            field.def.id_name = 'nothing';
            expect(field._getRelateId()).toBe('testId2');
        });
    });
});
