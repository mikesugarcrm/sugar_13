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
describe('Shifts.Views.Create', function() {
    let app;
    let view;
    const moduleName = 'Shifts';
    const viewName = 'create';

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'view', 'record', moduleName);
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();

        view = SugarTest.createView(
            'base',
            moduleName,
            viewName,
            {},
            null,
            true
        );

        view.render();
    });

    afterEach(function() {
        view.dispose();
        sinon.restore();
    });

    describe('validateHoursList', function() {
        it('should call the validation from the record view', function() {
            var recordValidateStub = sinon.stub(
                app.view.views.BaseShiftsRecordView.prototype,
                'validateHoursList'
            );

            view.validateHoursList([{name: 'test'}], {}, _.noop);
            expect(recordValidateStub).toHaveBeenCalledWith([{name: 'test'}], {}, _.noop);
        });
    });
});
