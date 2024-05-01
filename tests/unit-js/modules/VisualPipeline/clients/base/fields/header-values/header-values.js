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

describe('VisualPipeline.Base.Fields.HeaderValuesField', function() {
    var app;
    var sandbox;
    var context;
    var model;
    var moduleName;
    var field;

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

        field = SugarTest.createField('base', 'header-values', 'header-values',
            'detail', {}, 'VisualPipeline', model, context, true);
    });

    afterEach(function() {
        sandbox.restore();
        sinon.restore();
        app = null;
        context = null;
        model = null;
        field = null;
        moduleName = null;
    });

    describe('bindDataChange', function() {
        it('should call field.model.on method with change:table_header', function() {
            sinon.stub(field.model, 'on').callsFake(function() {});
            field.bindDataChange();

            expect(field.model.on).toHaveBeenCalledWith('change:table_header');
        });
    });

    describe('_render', function() {
        beforeEach(function() {
            sinon.stub(field, 'populateHeaderValues');
            sinon.stub(field, 'handleDraggableActions');
            sinon.stub(field.context, 'set').callsFake(function() {});
            sinon.stub(field, '_super').callsFake(function() {});
            field._render();
        });

        describe('when field.context is not empty', function() {
            it('should call field.context.set with selectedValues', function() {

                expect(field.context.set).toHaveBeenCalledWith('selectedValues', {});
            });
        });

        it('should call field.populateHeaderValues method', function() {

            expect(field.populateHeaderValues).toHaveBeenCalled();
        });

        it('should call field._super method wtih _render', function() {

            expect(field._super).toHaveBeenCalledWith('_render');
        });

        it('should call field.handleDraggableActions method', function() {

            expect(field.handleDraggableActions).toHaveBeenCalled();
        });
    });

    describe('populateHeaderValues', function() {
        beforeEach(function() {
            sinon.stub(field.model, 'get').withArgs('enabled_module').returns(['Opportunities', 'Tasks'])
            .withArgs('table_header').returns('status');
            sinon.stub(app.metadata, 'getModule')
            .withArgs(['Opportunities', 'Tasks'], 'fields')
            .returns({
                status: {
                    name: 'status',
                    options: 'case_status_dom'
                }
            });
        });

        it('should call _createHeaderValueLists with header and values', function() {
            var statusDom = {
                New: 'New',
                Assigned: 'Assigned'
            };
            sinon.stub(app.lang, 'getAppListStrings').withArgs('case_status_dom').returns(statusDom);
            var stub = sinon.stub(field, '_createHeaderValueLists');
            field.populateHeaderValues();
            expect(stub).toHaveBeenCalledWith('status', statusDom);
        });

        it('should call enum api if no header values', function() {
            sinon.stub(field, '_createHeaderValueLists');
            sinon.stub(app.lang, 'getAppListStrings').returns(undefined);
            var stub = sinon.stub(app.api, 'enumOptions');
            field.populateHeaderValues();
            expect(stub).toHaveBeenCalled();
        });
    });

    describe('_createHeaderValueLists', function() {
        beforeEach(function() {
            sinon.stub(field, 'getBlackListedArray').callsFake(function() {
                return ['Closed', 'New'];
            });

            sinon.stub(field, '_setAvailableColumnsEdited');
            sinon.stub(field, '_hasProp').returns(true);
            sinon.stub(field.model, 'set');
        });

        describe('when tableHeader is empty', function() {
            it('should call this.model.set with empty whiteListed and blackListed values', function() {
                field._createHeaderValueLists(null, null);
                expect(field.model.set).toHaveBeenCalledWith({
                    'white_listed_header_vals': [],
                    'black_listed_header_vals': []
                });
            });

            it('should not call field.getBlackListArray method', function() {
                field._createHeaderValueLists(null, null);
                expect(field.getBlackListedArray).not.toHaveBeenCalled();
            });
        });

        describe('when tableHeader is not empty', function() {
            var tableHeader;
            var translated;

            beforeEach(function() {
                tableHeader = 'status';
                translated = {
                    Assigned: 'Assigned',
                    Closed: 'Closed',
                    Duplicate: 'Duplicate',
                    New: 'New'
                };
                sinon.stub(field.model, 'get')
                    .withArgs('available_columns').returns({'Assigned': 'Assigned', 'Duplicate': 'Duplicate'});
            });

            it('should call field.getBlackListArray method', function() {
                field._createHeaderValueLists(tableHeader, translated);
                expect(field.getBlackListedArray).toHaveBeenCalled();
            });

            it('should call this.model.set with not empty whiteListed and blackListed values', function() {
                field._createHeaderValueLists(tableHeader, translated);
                expect(field.model.set).toHaveBeenCalledWith({
                    'white_listed_header_vals': [
                        {
                            key: 'Assigned',
                            translatedLabel: 'Assigned'
                        },
                        {
                            key: 'Duplicate',
                            translatedLabel: 'Duplicate'
                        }
                    ],
                    'black_listed_header_vals': [
                        {
                            key: 'Closed',
                            translatedLabel: 'Closed'
                        },
                        {
                            key: 'New',
                            translatedLabel: 'New'
                        }
                    ]
                });
            });

            it('should call field._setAvailableColumnsEdited method', function() {
                field._createHeaderValueLists(tableHeader, translated);
                expect(field._setAvailableColumnsEdited).toHaveBeenCalledWith(
                    'status',
                    ['Assigned','Duplicate']
                );
            });

            it('should call field._hasProp method', function() {
                field._createHeaderValueLists(tableHeader, translated);
                expect(field._hasProp).toHaveBeenCalled();
            });
        });
    });

    describe('_hasProp', function() {
        it('if have own property should return true', function() {
            expect(field._hasProp('foo', {'foo': 'bar'})).toBeTruthy();
        });

        it('if have required property value should return true', function() {
            expect(field._hasProp('foo', {'bar': 'foo'})).toBeTruthy();
        });

        it('if should return false', function() {
            expect(field._hasProp('foo', {'bar': 'bar'})).toBeFalsy();
        });
    });

    describe('handleDraggableActions', function() {
        it('should call this.$.sortable method', function() {
            sinon.stub(jQuery.fn, 'sortable').callsFake(function() {});
            field.handleDraggableActions();

            expect(jQuery.fn.sortable).toHaveBeenCalled();
        });
    });

    describe('getBlackListArray', function() {
        it('should call field.model.set with hidden_values', function() {
            sinon.stub(field.model, 'get').callsFake(function() {
                return ['Closed', 'New'];
            });
            field.getBlackListedArray();

            expect(field.model.get).toHaveBeenCalledWith('hidden_values');
        });

        describe('when there are no hidden values', function() {
            var res;
            it('should return an empty array', function() {
                sinon.stub(field.model, 'get').callsFake(function() {
                    return [];
                });
                res = field.getBlackListedArray();

                expect(res).toEqual([]);
            });
        });

        describe('when the hidden values are not an array', function() {
            var res;
            it('should still return the result as an array', function() {
                sinon.stub(field.model, 'get').callsFake(function() {
                    return '{"Closed": "Closed", "New": "New"}';
                });
                res = field.getBlackListedArray();

                expect(res).toEqual({'Closed': 'Closed', 'New': 'New'});
            });
        });
    });

    describe('_setAvailableColumnsEdited', function() {
        let tableHeader;
        let availableColumns;
        beforeEach(function() {
            tableHeader = 'status';
            availableColumns = ['Assigned','Duplicate'];
            sinon.stub(field.model, 'set');
        });

        it('should call this.model.set with not empty availableColumnsEdited values', function() {
            field._setAvailableColumnsEdited(tableHeader, availableColumns);
            expect(field.model.set).toHaveBeenCalledWith(
                'available_columns_edited',
                {
                    'status':
                    {
                        'Assigned': 'Assigned',
                        'Duplicate': 'Duplicate'
                    }
                }
            );
        });
    });

    describe('_dispose', function() {
        beforeEach(function() {
            sinon.stub(field.model, 'off').callsFake(function() {});
            sinon.stub(field, '_super').callsFake(function() {});
            field._dispose();
        });

        it('should call view.model.off method with change:table_header', function() {

            expect(field.model.off).toHaveBeenCalledWith('change:table_header');
        });

        it('should call view._super with _dispose', function() {

            expect(field._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
