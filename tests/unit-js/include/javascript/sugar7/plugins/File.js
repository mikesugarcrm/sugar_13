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
describe('File Plugin', function() {
    var moduleName = 'Accounts';
    var fields, view;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('image', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('file', 'field', 'base', 'detail');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        var model = SugarTest.app.data.createBean(moduleName);

        fields = {
            file: SugarTest.createField('base', 'testFile', 'file', 'edit', {}, moduleName, model),
            image: SugarTest.createField('base', 'test_image_upload', 'image', 'edit', {}, moduleName, model),
            avatar: SugarTest.createField('base', 'testPicture', 'avatar', 'edit', {}, moduleName, model)
        };

        view = SugarTest.createView('base', moduleName, 'record', null, null);
    });

    afterEach(function() {
        view.dispose();
        SugarTest.app.view.reset();
        SugarTest.testMetadata.dispose();
        SugarTest.app.cache.cutAll();
        view = null;
        fields = null;
    });

    it('should have the plugin defined', function() {
        _.each(fields, function(field) {
            expect(_.indexOf(field.plugins, 'File')).not.toEqual(-1);
        });
    });
});
