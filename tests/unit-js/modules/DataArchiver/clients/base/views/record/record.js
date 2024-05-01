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
describe('Data Archiver Record View', function() {
    let moduleName = 'DataArchiver';
    let app;
    let viewName = 'record';
    let view;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'view', viewName, moduleName);
        SugarTest.app.data.declareModels();

        view = SugarTest.createView('base', moduleName, viewName, null, null, true);
    });

    afterEach(function() {
        view.dispose();
        view = null;
    });

    describe('On save', function() {
        let doValidateStub;
        let hasAccessToModelStub;
        let getFieldsStub;
        let showModuleRequirementsError;

        beforeEach(function() {
            doValidateStub = sinon.stub(view.model, 'doValidate');
            hasAccessToModelStub = sinon.stub(app.acl, 'hasAccessToModel').returns(true);
            getFieldsStub = sinon.stub(view, 'getFields');
            showModuleRequirementsError = sinon.stub(view, 'showModuleRequirementsError');
            view.model.set('filter_module_name', 'pmse_Inbox');
            view.moduleRequirements = {
                'pmse_Inbox': [
                    'cas_status',
                ],
            };
        });

        afterEach(function() {
            hasAccessToModelStub.restore();
            getFieldsStub.restore();
        });

        it('Should save as expected when reqs are met', function() {
            view.model.set('filter_def',JSON.stringify(
                [
                    {
                        'cas_status': {
                            '$in': [
                                'COMPLETED'
                            ]
                        }
                    }
                ]
            ));
            view.saveClicked();
            expect(showModuleRequirementsError).not.toHaveBeenCalled();
        });

        it('Should not save and display error when reqs are not met ', function() {
            view.model.set('filter_def', JSON.stringify(
                [
                    {
                        'name': {
                            '$equals': 'Test'
                        }
                    }
                ]
            ));
            view.saveClicked();
            expect(showModuleRequirementsError).toHaveBeenCalled();
        });
    });
});
