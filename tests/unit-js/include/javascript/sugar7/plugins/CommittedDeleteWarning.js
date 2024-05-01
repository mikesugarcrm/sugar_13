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
describe('CommittedDeleteWarning Plugin', function () {
    var app,
        plugin,
        deletedModel,
        result;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadPlugin('CommittedDeleteWarning');
        plugin = app.plugins.plugins.view.CommittedDeleteWarning;
    });

    afterEach(function() {
        deletedModel = null;
        sinon.restore();
    });

    describe('_checkDeletedModel()', function() {
        beforeEach(function() {
            deletedModel = new Backbone.Model({});
        });

        describe('Opportunities', function() {
            beforeEach(function() {
                deletedModel.module = 'Opportunities';
            });

            it('should return true when there are included RLIs are on the Opp', function() {
                deletedModel.set('included_revenue_line_items', 1);
                expect(plugin._checkDeletedModel(deletedModel)).toBeTruthy();
            });

            it('should return false when no included RLIs are on the Opp', function() {
                deletedModel.set('included_revenue_line_items', 0);
                expect(plugin._checkDeletedModel(deletedModel)).toBeFalsy();
            });
        });

        describe('RevenueLineItems', function() {
            beforeEach(function() {
                deletedModel.module = 'Revenue Line Items';
                sinon.stub(app.metadata, 'getModule').callsFake(function() {
                    return {
                        commit_stages_included: ['include']
                    }
                })
            });

            it('should return true RLI commit stage is included', function() {
                deletedModel.set('commit_stage', 'include');
                expect(plugin._checkDeletedModel(deletedModel)).toBeTruthy();
            });

            it('should return false RLI commit stage is not included', function() {
                deletedModel.set('commit_stage', 'exclude');
                expect(plugin._checkDeletedModel(deletedModel)).toBeFalsy();
            });
        });
    });

    describe('checkDeletedModel() array of models', function() {
        beforeEach(function() {
            deletedModel = [
                new Backbone.Model({}),
                new Backbone.Model({})
            ];

        });

        describe('Opportunities', function() {
            beforeEach(function() {
                deletedModel[0].module = 'Opportunities';
                deletedModel[1].module = 'Opportunities';
            });

            it('should return true when there are included RLIs are on the Opp', function() {
                deletedModel[0].set('included_revenue_line_items', 1);
                deletedModel[1].set('included_revenue_line_items', 0);
                expect(plugin.checkDeletedModel(deletedModel)).toBeTruthy();
            });

            it('should return false when no included RLIs are on the Opp', function() {
                deletedModel[0].set('included_revenue_line_items', 0);
                deletedModel[1].set('included_revenue_line_items', 0);
                expect(plugin.checkDeletedModel(deletedModel)).toBeFalsy();
            });
        });

        describe('RevenueLineItems', function() {
            beforeEach(function() {
                deletedModel[0].module = 'Revenue Line Items';
                deletedModel[1].module = 'Revenue Line Items';
                sinon.stub(app.metadata, 'getModule').callsFake(function() {
                    return {
                        commit_stages_included: ['include']
                    }
                })
            });

            it('should return true RLI commit stage is included', function() {
                deletedModel[0].set('commit_stage', 'include');
                deletedModel[1].set('commit_stage', 'exclude');
                expect(plugin.checkDeletedModel(deletedModel)).toBeTruthy();
            });

            it('should return false RLI commit stage is not included', function() {
                deletedModel[0].set('commit_stage', 'exclude');
                deletedModel[1].set('commit_stage', 'exclude');
                expect(plugin.checkDeletedModel(deletedModel)).toBeFalsy();
            });
        });
    });
});
