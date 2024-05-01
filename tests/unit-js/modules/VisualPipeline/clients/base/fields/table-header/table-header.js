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

describe('VisualPipeline.Base.Fields.ModulesListField', function() {
    var app;
    var sandbox;
    var context;
    var model;
    var moduleName;
    var field;
    var options;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        moduleName = 'Opportunities';
        model = app.data.createBean(moduleName, {
            id: '123test',
            name: 'Lórem ipsum dolor sit àmêt, ut úsu ómnés tatión imperdiet.'
        });

        context = new app.Context();
        context.set({model: model});

        field = SugarTest.createField('base', 'table-header', 'table-header',
            'detail', {}, 'VisualPipeline', model, context, true);
        sinon.stub(field, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sandbox.restore();
        sinon.restore();
        app = null;
        context = null;
        model = null;
        field = null;
        moduleName = null;
        options = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            sinon.stub(field.model, 'set').callsFake(function() {});
        });

        it('should call field._super method with initialize', function() {
            options = {
                def: {
                    name: 'test'
                }
            };
            field.initialize(options);

            expect(field._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should call field.model.get method with tabContent', function() {
            options = {
                def: {
                    name: 'test'
                }
            };
            sinon.stub(field.model, 'get')
                .withArgs('tabContent').returns({
                dropdownFields: {
                    status: 'Status',
                    type: 'Type'
                },
                fields: {
                    name: 'Subject',
                    priority: 'Priority',
                }
            })
                .withArgs('enabled_module').returns('Cases');

            field.initialize(options);

            expect(field.model.get).toHaveBeenCalledWith('tabContent');
        });

        it('should call field.model.get method with tile_body_fields', function() {
            options = {
                def: {
                    name: 'tile_body_fields'
                }
            };
            sinon.stub(field.model, 'get').withArgs('tabContent').returns({
                dropdownFields: {
                    status: 'Status',
                    type: 'Type'
                },
                fields: {
                    name: 'Subject',
                    priority: 'Priority',
                }
            })
                .withArgs('tile_body_fields').returns(['account_name', 'priority']);
            field.initialize(options);

            expect(field.model.get).toHaveBeenCalledWith('tile_body_fields');
        });

        describe('when tabContent is empty', function() {
            beforeEach(function() {
                options = {
                    def: {
                        name: 'enabled_modules'
                    }
                };
                sinon.stub(field.model, 'get').withArgs('tabContent').returns({})
                    .withArgs('enabled_module').returns('Cases')
                    .withArgs('tile_body_fields').returns(['account_name', 'priority']);

                sinon.stub(field, 'getTabContent').callsFake(function() {});
                field.initialize(options);
            });

            it('should call field.model.get method with enabled_module', function() {

                expect(field.model.get).toHaveBeenCalledWith('enabled_module');
            });

            it('should call field.getTabContent with Cases', function() {

                expect(field.getTabContent).toHaveBeenCalledWith('Cases');
            });
        });

        describe('when tabContent is not empty', function() {
            beforeEach(function() {
                options = {
                    def: {
                        name: 'enabled_modules'
                    }
                };
                sinon.stub(field.model, 'get').withArgs('tabContent').returns({
                    dropdownFields: {
                        status: 'Status',
                        type: 'Type'
                    },
                    fields: {
                        name: 'Subject',
                        priority: 'Priority',
                    }
                })
                    .withArgs('enabled_module').returns('Cases')
                    .withArgs('tile_body_fields').returns(['account_name', 'priority']);

                sinon.stub(field, 'getTabContent').callsFake(function() {});
                field.initialize(options);
            });

            it('should not call field.model.get method with enabled_module', function() {

                expect(field.model.get).not.toHaveBeenCalledWith('enabled_module');
            });

            it('should not call field.getTabContent with Cases', function() {

                expect(field.getTabContent).not.toHaveBeenCalledWith('Cases');
            });
        });

        describe('when options.def.name is table_header', function() {
            beforeEach(function() {
                options = {
                    def: {
                        name: 'table_header'
                    }
                };
                sinon.stub(field.model, 'get').withArgs('tabContent').returns({
                    dropdownFields: {
                        status: 'Status',
                        type: 'Type'
                    },
                    fields: {
                        name: 'Subject',
                        priority: 'Priority',
                    }
                }).withArgs('tile_body_fields').returns(['account_name', 'priority']);

                field.initialize(options);
            });

            it('should assign tabContent.dropdownFields to the field.items', function() {

                expect(field.items).toEqual({
                    status: 'Status',
                    type: 'Type'
                });
            });
        });

        describe('when options.def.name is tile_body_fields or tile_header', function() {
            beforeEach(function() {
                sinon.stub(field.model, 'get').withArgs('tabContent').returns({
                    dropdownFields: {
                        status: 'Status',
                        type: 'Type'
                    },
                    fields: {
                        name: 'Subject',
                        priority: 'Priority',
                    }
                }).withArgs('tile_body_fields').returns(['account_name', 'priority']);
            });

            it('should assign tabContent.fields to the field.items when tile_body_fields', function() {
                options = {
                    def: {
                        name: 'tile_body_fields'
                    }
                };
                field.initialize(options);

                expect(field.items).toEqual({
                    name: 'Subject',
                    priority: 'Priority',
                });
            });

            it('should assign tabContent.fields to the field.items when tile_header', function() {
                options = {
                    def: {
                        name: 'tile_header'
                    }
                };
                field.initialize(options);

                expect(field.items).toEqual({
                    name: 'Subject',
                    priority: 'Priority',
                });
            });
        });

        describe('when optionsBody is empty', function() {
            it('should not call field.model.set', function() {
                options = {
                    def: {
                        name: 'tile_body_fields'
                    }
                };
                sinon.stub(field.model, 'get').withArgs('tabContent').returns({
                    dropdownFields: {
                        status: 'Status',
                        type: 'Type'
                    },
                    fields: {
                        name: 'Subject',
                        priority: 'Priority',
                    }
                })
                    .withArgs('tile_body_fields').returns();
                field.initialize(options);

                expect(field.model.set).not.toHaveBeenCalled();
            });
        });

        describe('when optionsBody is not empty but not an Array', function() {
            it('should call field.model.set', function() {
                options = {
                    def: {
                        name: 'tile_body_fields'
                    }
                };
                sinon.stub(field.model, 'get').withArgs('tabContent').returns({
                    dropdownFields: {
                        status: 'Status',
                        type: 'Type'
                    },
                    fields: {
                        name: 'Subject',
                        priority: 'Priority',
                    }
                })
                    .withArgs('tile_body_fields').returns('["account_name", "priority"]');
                field.initialize(options);

                expect(field.model.set).toHaveBeenCalledWith('tile_body_fields', ['account_name', 'priority']);
            });
        });
    });

    describe('getTabContent', function() {
        it('should call app.metadata.getModule with module arg', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    name: {
                        vname: 'testVname',
                        type: 'name'
                    },
                    table_header: {
                        name: 'table_header',
                        type: 'text',
                        vname: 'LBL_PIPELINE_TABLE_HEADER'
                    }
                };
            });
            field.getTabContent('VisualPipeline');

            expect(app.metadata.getModule).toHaveBeenCalledWith('VisualPipeline', 'fields');
        });
    });

    describe('_render', function() {
        it('should not render when there are no totalable fields in a module', function() {
            field.def.name = 'total_field';
            field.model.set('tabContent', {
                allTotalableFields: {},
            });
            field._render();
            expect(field._super).not.toHaveBeenCalled();
        });
        it('should render when there are totalable fields in a module', function() {
            field.def.name = 'total_field';
            field.model.set('tabContent', {
                allTotalableFields: {
                    Field: 'Sum Field'
                }
            });
            field._render();
            expect(field._super).toHaveBeenCalledWith('_render');
        });
        it('should render when fetching a non-dropdown', function() {
            field.def.name = 'foo';
            field._render();
            expect(field._super).toHaveBeenCalledWith('_render');
        });
    });
});
