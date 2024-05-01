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
describe('Reports.Fields.DrillthroughLabels', function() {
    var app;
    var field;
    var chartModule = 'Accounts';

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField({
            client: 'base',
            name: 'drillthrough-labels',
            type: 'drillthrough-labels',
            viewName: 'detail',
            module: 'Reports',
            loadFromModule: true
        });
    });

    afterEach(function() {
        field.dispose();
        field = null;
    });

    describe('format', function() {
        var langStub;
        var getGroupingStub;
        var getFieldDefStub;

        beforeEach(function() {
            field.context.set('chartModule', chartModule);
            field.context.set('dashConfig', {groupLabel: 'Industry', seriesLabel: 'Type'});
            SugarTest.testMetadata.init();
            langStub = sinon.stub(app.lang, 'get')
                .withArgs('LBL_INDUSTRY', chartModule).returns('Industry');
            langStub.withArgs('LBL_TYPE', chartModule).returns('Type');
            getGroupingStub = sinon.stub(SUGAR.charts, 'getGrouping');
            getFieldDefStub = sinon.stub(SUGAR.charts, 'getFieldDef');
        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
            sinon.restore();
        });

        it('should only set series when group isn\'t present', function() {
            var meta = {
                fields: {
                    industry: {
                        name: 'industry',
                        type: 'enum',
                        vname: 'LBL_INDUSTRY'
                    }
                }
            };
            SugarTest.testMetadata.updateModuleMetadata(chartModule, meta);
            SugarTest.testMetadata.set();

            var dashConfig = {groupLabel: 'Biotechnology'};
            field.context.set('dashConfig', dashConfig);

            var groupDefs = [{
                name: 'industry',
                table_key: 'self',
                label: 'Industry'
            }];
            field.context.set('reportData', {group_defs: groupDefs});

            var filterDef = [{
                'self:industry': 'Biotechnology'
            }];
            field.context.set('filterDef', filterDef);

            getFieldDefStub.withArgs(groupDefs[0]).returns({
                    name: 'industry',
                    type: 'enum',
                    vname: 'LBL_INDUSTRY',
                    module: 'Accounts'
                });
            field.format();
            expect(field.groupName).toBe('Industry: ');
            expect(field.groupValue).toBe('Biotechnology');
            expect(field.seriesName).toBe(undefined);
            expect(field.seriesValue).toBe(undefined);

        });

        it('should set series and group when both are present', function() {
            var meta = {
                fields: {
                    industry: {
                        name: 'industry',
                        type: 'enum',
                        vname: 'LBL_INDUSTRY'
                    },
                    account_type: {
                        name: 'account_type',
                        type: 'enum',
                        vname: 'LBL_TYPE'
                    }
                }
            };
            SugarTest.testMetadata.updateModuleMetadata(chartModule, meta);
            SugarTest.testMetadata.set();

            var dashConfig = {groupLabel: 'Biotechnology', seriesLabel: 'Customer'};
            field.context.set('dashConfig', dashConfig);

            var groupDefs = [
                {
                    name: 'industry',
                    table_key: 'self',
                    label: 'Industry'
                },
                {
                    name: 'account_type',
                    table_key: 'self',
                    label: 'Type'
                }
            ];
            field.context.set('reportData', {group_defs: groupDefs});

            var filterDef = [
                {'self:industry': 'Biotechnology'},
                {'self:account_type': 'Customer'}
            ];
            field.context.set('filterDef', filterDef);

            getFieldDefStub.withArgs(groupDefs[0]).returns({
                name: 'industry',
                type: 'enum',
                vname: 'LBL_INDUSTRY',
                module: 'Accounts'
            });

            getFieldDefStub.withArgs(groupDefs[1]).returns({
                name: 'account_type',
                type: 'enum',
                vname: 'LBL_TYPE',
                module: 'Accounts'
            });

            field.format();
            expect(field.groupName).toBe('Industry: ');
            expect(field.groupValue).toBe('Biotechnology');
            expect(field.seriesName).toBe('Type: ');
            expect(field.seriesValue).toBe('Customer');
        });

        it('should set group only when both are present but only filter by one', function() {
            var meta = {
                fields: {
                    industry: {
                        name: 'industry',
                        type: 'enum',
                        vname: 'LBL_INDUSTRY'
                    },
                    account_type: {
                        name: 'account_type',
                        type: 'enum',
                        vname: 'LBL_TYPE'
                    }
                }
            };
            SugarTest.testMetadata.updateModuleMetadata(chartModule, meta);
            SugarTest.testMetadata.set();

            var dashConfig = {groupLabel: 'Biotechnology'};
            field.context.set('dashConfig', dashConfig);

            var groupDefs = [
                {
                    name: 'industry',
                    table_key: 'self',
                    label: 'Industry'
                },
                {
                    name: 'account_type',
                    table_key: 'self',
                    label: 'Type'
                }
            ];
            field.context.set('reportData', {group_defs: groupDefs});

            var filterDef = [
                {'self:industry': 'Biotechnology'}
            ];
            field.context.set('filterDef', filterDef);

            getFieldDefStub.withArgs(groupDefs[0]).returns({
                name: 'industry',
                type: 'enum',
                vname: 'LBL_INDUSTRY',
                module: 'Accounts'
            });

            getFieldDefStub.withArgs(groupDefs[1]).returns({
                name: 'account_type',
                type: 'enum',
                vname: 'LBL_TYPE',
                module: 'Accounts'
            });

            field.format();
            expect(field.groupName).toBe('Industry: ');
            expect(field.groupValue).toBe('Biotechnology');
            expect(field.seriesName).toBe(undefined);
            expect(field.seriesValue).toBe(undefined);
        });
    });
});
