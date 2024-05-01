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
describe('modules.kbcontents.clients.base.fields.enum-config', function() {
    var app, field, sandbox,
        module = 'KBContents',
        fieldName = 'language',
        fieldType = 'enum-config',
        model;

    beforeEach(function() {
        sandbox = sinon.createSandbox();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', {}, module, model, null, true);
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        model = null;
        field = null;
    });

    it('should call loadEnumOptions and set items during render', function() {
        var loadEnumSpy = sandbox.spy(field, 'loadEnumOptions');
        expect(field.items).toBeNull();
        field.render();
        expect(loadEnumSpy.called).toBe(true);
        expect(field.items).toEqual({});
    });

    it('should be disabled if model has related languages and relate article', function() {
        field.model.set({
            related_languages: ['en', 'us'],
            kbarticle_id: 'kb_id'
        });
        field.def.readonly = false;
        field.setMode('edit');
        expect(field.action).toEqual('disabled');
    });

    it('should remove related languages from items and be editable', function() {
        field._setItems([
            {
                'us': 'English',
                'primary': false
            },
            {
                'be': 'Belgium',
                'primary': true
            }
        ]);
        field.model.set({
            related_languages: ['en', 'be']
        });
        expect(_.keys(field.items)).toEqual(['us', 'be']);
        expect(field.model.get('language')).toEqual('be');
        field.setMode('edit');
        expect(field.action).toEqual('edit');
        expect(_.keys(field.items)).toEqual(['us']);
        expect(field.model.get('language')).toEqual('us');
    });

    it('should be disabled if model is not new record', function() {
        field.model.set({
            id: 'test_id'
        });
        field.def.readonly = false;
        field.setMode('edit');
        expect(field.action).toEqual('disabled');
    });

    it('should set languages from meta and server correctly', function() {
        var metaDef = {
            'category_root': '__',
            'languages': [
                {
                    'en': 'English',
                    'primary': true
                }
            ]
            },
            configDef = {
                'category_root': '__',
                'languages': [
                    {
                        'be': 'Belgium',
                        'primary': true
                    }
                ]
            },
            setItems = sandbox.spy(field, '_setItems'),
            urlRegExp = new RegExp('.*rest/v10/' + module + '/config.*');
        field.def = {
            module: module,
            key: 'languages'
        };
        sandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return metaDef;
        });

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith(
            'GET',
            urlRegExp,
            [200, {'Content-Type': 'application/json'}, JSON.stringify(configDef)]
        );
        field.loadEnumOptions(true, $.noop);
        expect(setItems.callCount).toEqual(1);
        expect(field.items).toEqual({'en': 'English'});
        SugarTest.server.respond();
        expect(setItems.callCount).toEqual(2);
        expect(field.items).toEqual({'be': 'Belgium'});
    });
});
