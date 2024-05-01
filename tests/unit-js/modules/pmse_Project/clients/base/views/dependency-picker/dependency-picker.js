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
describe('View.Views.Base.pmse_Project.DependencyPickerView', function() {
    var view;
    var app;
    var meta = {
        fields: [
            {
                name: 'name',
                type: 'name',
            }
        ]
    };
    var br1 = {
        id: 'br1',
        name: 'br1'
    };
    var br2 = {
        id: 'br2',
        name: 'br2'
    };
    var br3 = {
        id: 'br3',
        name: 'br3'
    };
    var et1 = {
        id: 'et1',
        name: 'et1'
    };
    var et2 = {
        id: 'et2',
        name: 'et2'
    };
    var data = {
        dependencies: {
            business_rule: [br1, br2, br3],
            email_template: [et1, et2]
        }
    };

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'dependency-picker', 'pmse_Project');

        app = SUGAR.App;
        view = SugarTest.createView('base', 'pmse_Project', 'dependency-picker', null, null, 'pmse_Project');
        view.meta = meta;

    });

    afterEach(function() {
        view.context.off();
        app.view.reset();
        view = null;
        app = null;
        sinon.restore();
    });

    it('should set up collection map', function() {
        expect(Object.keys(view.collections).length).toBe(3);
    });

    it('should set up model map', function() {
        expect(Object.keys(view.models).length).toBe(2);
    });

    describe('processData', function() {
        var resetCollectionSpy;

        beforeEach(function() {
            resetCollectionSpy = sinon.spy(view, '_resetCollections');
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should have the correct number of models in the collections', function() {
            view.processData(data);
            expect(view.brCollection.length).toBe(3);
            expect(view.etCollection.length).toBe(2);
        });

        it('should reset the collection', function() {
            view.processData(data);
            expect(resetCollectionSpy.calledOnce).toBeTruthy();

            view.processData(data);
            expect(view.brCollection.length).toBe(3);
            expect(view.etCollection.length).toBe(2);
        });

        describe('hasDependencies', function() {
            using('different data values',
                [
                    [{dependencies: {}}, false],
                    [{dependencies: {email_templates: []}}, false],
                    [data, true]
                ],
                function(dataValues, result) {
                    it('should set hasDependencies properly', function() {
                        view.processData(dataValues);
                        expect(view.hasDependencies).toBe(result);
                    });
                });
        });
    });

    describe('_updateModels', function() {
        var toggleAllCheckBoxStub;
        beforeEach(function() {
            view.processData(data);
            toggleAllCheckBoxStub = sinon.stub(view, '_toggleAllCheckbox');
        });

        afterEach(function() {
            view._resetCollections();
        });

        it('should add 1 model', function() {
            view._updateModels(true, _.first(view.brCollection.models));
            expect(view.massCollection.length).toBe(1);
        });

        it('should add multiple models', function() {
            view._updateModels(true, _.first(view.brCollection.models, 2));
            expect(view.massCollection.length).toBe(2);
        });

        it('should call _toggleAllCheckbox if all models from a collection are added', function() {
            view._updateModels(true, view.brCollection.models);
            expect(toggleAllCheckBoxStub).toHaveBeenCalled();
        });

        it('should remove 1 model', function() {
            var model = _.first(view.brCollection.models);
            view._updateModels(true, view.brCollection.models);
            view._updateModels(false, model);
            expect(view.massCollection.length).toBe(2);
        });

        it('should remove multiple models', function() {
            var models = _.first(view.brCollection.models, 2);
            view._updateModels(true, view.brCollection.models);
            view._updateModels(false, models);
            expect(view.massCollection.length).toBe(1);
        });

        it('should call _toggleAllCheckbox if all models from a collection are removed', function() {
            var model = _.first(view.brCollection.models);
            view._updateModels(true, model);
            view._updateModels(false, model);
            expect(toggleAllCheckBoxStub).toHaveBeenCalled();
        });

        it('should add all models when you call _updateModels', function() {
            view._updateAllModels(true, view.brModel);
            expect(view.massCollection.length).toBe(3);
        });

        it('should remove all models when you call _updateModels', function() {
            view._updateAllModels(true, view.brModel);
            view._updateAllModels(false, view.brModel);
            expect(view.massCollection.length).toBe(0);
        });
    });

    describe('_isAllChecked', function() {

        beforeEach(function() {
            view.processData(data);
        });

        afterEach(function() {
            view._resetCollections();
        });

        it('should return true if all models from 1 collection are in the mass collection', function() {
            view.massCollection.add(view.brCollection.models);
            expect(view._isAllChecked('business_rule')).toBeTruthy();
        });

        it('should return false if all models from 1 collection are not in the mass collection', function() {
            view.massCollection.add(_.first(view.brCollection.models));
            expect(view._isAllChecked('business_rule')).toBeFalsy();
        });
    });

    describe('_resetCollections', function() {
        it('should empty all the collections', function() {
            view.processData(data);
            view._updateModels(true, _.first(view.brCollection.models));
            view._resetCollections();
            expect(view.brCollection.length).toBe(0);
            expect(view.etCollection.length).toBe(0);
            expect(view.massCollection.length).toBe(0);
        });
    });
});
