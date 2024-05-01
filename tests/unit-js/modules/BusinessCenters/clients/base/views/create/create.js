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
describe('BusinessCenters.Views.Create', function() {
    var app;
    var moduleName = 'BusinessCenters';
    var view;
    var viewName = 'create';

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'view', 'record', moduleName);
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();

        view = SugarTest.createView('base', moduleName, viewName, {}, null, true);
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        sinon.restore();
    });

    describe('validateBusinessHours', function() {
        it('should defer to the record view', function() {
            var recordValidateStub = sinon.stub(
                app.view.views.BaseBusinessCentersRecordView.prototype,
                'validateBusinessHours'
            );
            view.validateBusinessHours([{name: 'a field'}], {}, _.noop);
            expect(recordValidateStub).toHaveBeenCalledWith([{name: 'a field'}], {}, _.noop);
        });
    });
});
