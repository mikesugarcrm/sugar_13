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

describe('Base.Fields.SortOrder', function() {
    var app;
    var fieldType = 'sortorder';
    var field;
    var sortOrderDefaultValue = 'asc';

    beforeEach(function() {
        app = SugarTest.app;

        Handlebars.templates = {};
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit');
        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'field', fieldType);

        const fieldDef = {
            'name': fieldType,
            'type': fieldType,
            'default': sortOrderDefaultValue,
        };

        field = SugarTest.createField('base', 'sortorder', 'sortorder', 'edit', fieldDef);

        SugarTest.app.data.declareModels();
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        field.dispose();

        Handlebars.templates = {};
        app = null;
        field = null;
        meta = null;
        fieldType = null;
        sortOrderDefaultValue = null;
    });

    describe('initialize()', function() {
        it('should properly set the field parameters', function() {
            expect(field.model.get(field.name)).toEqual(sortOrderDefaultValue);
        });
    });

    describe('switchOrder()', function() {
        it('should properly switch the columns order', function() {
            field.switchOrder({
                currentTarget: {
                    value: 'desc'
                },
            });
            expect(field.model.get(field.name)).toEqual('desc');
        });
    });
});
