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
describe('Plugins.CollectionFieldLoadAll', function() {
    var app;
    var context;
    var field;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadPlugin('CollectionFieldLoadAll');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        field = SugarTest.createField({
            name: 'attachments_collection',
            type: 'email-attachments',
            viewName: 'record',
            module: model.module,
            model: model,
            context: context,
            loadFromModule: false
        });
        field.def.fields = [
            'name',
            'filename',
            'file_size',
            'file_source',
            'file_mime_type',
            'file_ext',
            'upload_id'
        ];

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    it('should fetch all related records when the model is synchronized', function() {
        var loadingSpy = sandbox.spy();
        var loadedSpy = sandbox.spy();
        var collection = model.get(field.name);
        var pages = 3;

        model.set('id', _.uniqueId());
        field.view.on('loading_collection_field', loadingSpy);
        field.view.on('loaded_collection_field', loadedSpy);

        collection.next_offset = {
            attachments: 5
        };
        sandbox.stub(collection, 'paginate').callsFake(function(options) {
            expect(options.add).toBe(true);
            expect(options.limit).toBe(-1);
            expect(options.fields).toEqual([
                'name',
                'filename',
                'file_size',
                'file_source',
                'file_mime_type',
                'file_ext',
                'upload_id'
            ]);
            expect(options.order_by).toBe('name:asc');

            if (--pages === 0) {
                collection.next_offset.attachments = -1;
            } else {
                collection.next_offset.attachments += 5;
            }

            options.success();
        });

        model.trigger('sync');

        expect(collection.paginate).toHaveBeenCalledThrice();
        expect(loadingSpy).toHaveBeenCalledOnce();
        expect(loadedSpy).toHaveBeenCalledOnce();
    });

    it('should not fetch any additional related records when the model is synchronized', function() {
        var loadingSpy = sandbox.spy();
        var loadedSpy = sandbox.spy();
        var collection = model.get(field.name);

        model.set('id', _.uniqueId());
        field.view.on('loading_collection_field', loadingSpy);
        field.view.on('loaded_collection_field', loadedSpy);

        collection.next_offset = {
            attachments: -1
        };
        sandbox.spy(collection, 'paginate');

        model.trigger('sync');

        expect(collection.paginate).not.toHaveBeenCalled();
        expect(loadingSpy).toHaveBeenCalledOnce();
        expect(loadedSpy).toHaveBeenCalledOnce();
    });

    it('should not fetch all related records when the model is synchronized', function() {
        var loadingSpy = sandbox.spy();
        var loadedSpy = sandbox.spy();

        field.view.on('loading_collection_field', loadingSpy);
        field.view.on('loaded_collection_field', loadedSpy);
        model.trigger('sync');

        expect(loadingSpy).not.toHaveBeenCalled();
        expect(loadedSpy).not.toHaveBeenCalled();
    });
});
