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
describe('Leads.Base.Layout.ConvertMain', function() {
    var app, layout, createLayout, hasAccessStub, mockAccess;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addLayoutDefinition('convert-main', {
            modules: [
                {
                    module: 'Foo',
                    required: true
                },
                {
                    module: 'Bar',
                    required: true
                },
                {
                    module: 'Baz',
                    required: false,
                    dependentModules: {
                        'Foo': {
                            'fieldMapping': {
                                'foo_id': 'id'
                            }
                        }
                    }
                }
            ]
        });
        SugarTest.testMetadata.set();
        app.data.declareModels();

        mockAccess = {
            Foo: true,
            Bar: true,
            Baz: true
        };
        hasAccessStub = sinon.stub(app.acl, 'hasAccess').callsFake(function(access, module) {
            return (_.isUndefined(mockAccess[module])) ? true : mockAccess[module];
        });
    });

    afterEach(function() {
        hasAccessStub.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    createLayout = function() {
        return SugarTest.createLayout('base', 'Leads', 'convert-main', null, null, true);
    };

    describe('ACL Access Checks', function() {
        it('should have all three convert panel components created if user has access to all', function() {
            layout = createLayout();
            expect(_.keys(layout.convertPanels)).toEqual(['Foo', 'Bar', 'Baz']);
            expect(layout.noAccessRequiredModules).toEqual([]);
        });

        it('should have two convert panel components created if missing access to one optional module', function() {
            mockAccess.Baz = false;
            layout = createLayout();
            expect(_.keys(layout.convertPanels)).toEqual(['Foo', 'Bar']);
            expect(layout.noAccessRequiredModules).toEqual([]); //Baz is not required
        });

        it('should have one convert panel components created if missing access to one optional and one required module', function() {
            mockAccess.Foo = false;
            mockAccess.Baz = false;
            layout = createLayout();
            expect(_.keys(layout.convertPanels)).toEqual(['Bar']);
            expect(layout.noAccessRequiredModules).toEqual(['Foo']); //Baz is not required
        });

        it('should display an error, prevent render, and close the drawer if user is missing access to a required module', function() {
            var drawerCloseStub, renderStub,
                alertShowStub = sinon.stub(app.alert, 'show');

            app.drawer = app.drawer || {};
            app.drawer.close = app.drawer.close || function() {};
            drawerCloseStub = sinon.stub(app.drawer, 'close');

            mockAccess.Foo = false;
            layout = createLayout();
            renderStub = sinon.stub(layout, '_render');
            layout.render();

            expect(alertShowStub.calledWith('convert_access_denied')).toBeTruthy();
            expect(drawerCloseStub.called).toBeTruthy();
            expect(renderStub.called).toBeFalsy();

            alertShowStub.restore();
            drawerCloseStub.restore();
            renderStub.restore();
        });
    });

    describe('After Initialize', function() {
        var contextTriggerStub;

        beforeEach(function() {
            layout = createLayout();
            contextTriggerStub = sinon.stub(layout.context, 'trigger');
        });

        afterEach(function() {
            contextTriggerStub.restore();
        });

        it('should have created a convert options component with list of convert modules on the context', function() {
            var convertOptions, expectedConvertModuleList;

            convertOptions = layout.getComponent('convert-options');
            expectedConvertModuleList = [
                {id: 'Foo', text: 'Foo', required: true},
                {id: 'Bar', text: 'Bar', required: true},
                {id: 'Baz', text: 'Baz', required: false}
            ];

            expect(convertOptions).not.toBeUndefined();
            expect(convertOptions.context.get('convertModuleList')).toEqual(expectedConvertModuleList);
        });

        it('should pull out the dependencies based on the module metadata', function() {
            expect(layout.dependentModules['Foo']).toBeUndefined();
            expect(layout.dependentModules['Bar']).toBeUndefined();
            expect(layout.dependentModules['Baz']).not.toBeUndefined();
        });

        it('should retrieve the lead data from api and push model to the context for panel to use', function() {
            var mockModel = new Backbone.Model({id: '123'}),
                fetchStub = sinon.stub(mockModel, 'fetch').callsFake(function(options) {
                    options.success(mockModel);
                });
            layout.context.set('leadsModel', mockModel);
            layout.render();
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert:populate', mockModel]);
            fetchStub.restore();
        });

        it('should ignore hidden/shown events that are propagated to the panel body not directly on it', function() {
            var mockPropagatedEvent = {
                target: '<tooltip></tooltip>',
                currentTarget: '<panelBody></panelBody>'
            };
            layout.handlePanelCollapseEvent(mockPropagatedEvent);
            expect(contextTriggerStub.callCount).toEqual(0);
        });

        it('should pass along hidden/shown events to the context if event is fired directly on the panel body', function() {
            var mockTargetHtml = '<div data-module="Foo"></div>';
            var mockEvent = {
                type: 'shown',
                target: mockTargetHtml,
                currentTarget: mockTargetHtml
            };
            layout.handlePanelCollapseEvent(mockEvent);
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert:Foo:shown']);
        });

        it('should add/remove model from associated model array when panel is complete/reset', function() {
            var mockModel = {id: '123'};
            expect(layout.associatedModels['Foo']).toBeUndefined();
            layout.handlePanelComplete('Foo', mockModel);
            expect(layout.associatedModels['Foo']).toEqual(mockModel);
            layout.handlePanelReset('Foo');
            expect(layout.associatedModels['Foo']).toBeUndefined();
        });

        it('should enable dependent panels when dependencies are met', function() {
            layout.associatedModels['Foo'] = {id: '123'};
            layout.checkDependentModules();
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert:Baz:enable', true]);
        });

        it('should disable dependent panels when dependencies are not met', function() {
            delete layout.associatedModels['Foo'];
            layout.checkDependentModules();
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert:Baz:enable', false]);
        });

        it('should enable save button when all required modules have been complete', function() {
            layout.associatedModels['Foo'] = {id: '123'};
            layout.associatedModels['Bar'] = {id: '456'};
            layout.checkRequired();
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert-save:toggle', true]);
        });

        it('should enable save button when all required modules have been complete', function() {
            delete layout.associatedModels['Foo'];
            layout.associatedModels['Bar'] = {id: '456'};
            layout.checkRequired();
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert-save:toggle', false]);
        });

        describe('Convert Save', function() {
            var ajaxSpy, convertCompleteStub, leadConvertPattern, mockLeadConvertResponse;

            beforeEach(function() {
                ajaxSpy = sinon.spy($, 'ajax');
                convertCompleteStub = sinon.stub(layout, 'convertComplete');

                SugarTest.seedFakeServer();
                leadConvertPattern = /.*rest\/v10\/Leads\/lead123\/convert.*/;
                mockLeadConvertResponse = [200, { 'Content-Type': 'application/json'}, JSON.stringify({})];

                layout.context.set('leadsModel', new Backbone.Model({id: 'lead123'}));
            });

            afterEach(function() {
                ajaxSpy.restore();
                convertCompleteStub.restore();
            });

            it('should call lead convert api with associated models', function() {
                var expectedRequest,
                    getEditableFieldsStub = sinon.stub(app.data, 'getEditableFields').callsFake(function(model) {
                        return model;
                    });

                layout.associatedModels = {
                    Foo: {id: 123},
                    Bar: {id: 456},
                    Baz: {id: 789}
                };
                SugarTest.server.respondWith('POST', leadConvertPattern, mockLeadConvertResponse);

                layout.handleSave();
                SugarTest.server.respond();
                expectedRequest = JSON.stringify({
                    modules: layout.associatedModels,
                    allowBatching: true,
                    transfer_activities_action: 'donothing'
                });
                expect(ajaxSpy.lastCall.args[0].data).toEqual(expectedRequest);
                getEditableFieldsStub.restore();
            });

            it('should disable the save button while saving and re-enable if there is an error', function() {
                mockLeadConvertResponse[0] = 500;
                SugarTest.server.respondWith('POST', leadConvertPattern, mockLeadConvertResponse);
                layout.handleSave();
                SugarTest.server.respond();
                expect(contextTriggerStub.calledWith('lead:convert-save:toggle', false)).toBe(true);
                expect(contextTriggerStub.calledWith('lead:convert-save:toggle', true)).toBe(true);
                expect(convertCompleteStub.calledWith('error')).toBe(true);
            });
        });

        describe('parseEditableFields', function() {
            afterEach(function() {
                sinon.restore();
            });

            it('should prune fields that the user does not have access to', function() {
                var hash,
                    dm = app.data;
                    accountsModel = dm.createBean('Accounts', {id: '123'}),
                    contactsModel = dm.createBean('Contacts', {first_name: 'First', last_name: 'Last', id: '123'}),
                    associatedModels = {
                        'Accounts' : accountsModel,
                        'Contacts' : contactsModel
                    };

                sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action, model, field) {
                    return !(action === 'edit' && field === 'last_name');
                });

                hash = layout.parseEditableFields(associatedModels);

                expect(hash.Accounts['id']).toBeTruthy();
                expect(hash.Contacts['last_name']).toBeFalsy();
                expect(hash.Contacts['first_name']).toBeTruthy();
                expect(hash.Accounts['id']).toBeTruthy();
            });
        });

        describe('getTransferActivitiesAttributes', function() {
            var leadConvActivityOptBefore;

            beforeEach(function() {
                leadConvActivityOptBefore = app.metadata.getConfig().leadConvActivityOpt;
            });

            afterEach(function() {
                app.metadata.getConfig().leadConvActivityOpt = leadConvActivityOptBefore;
            });

            using('different transfer settings', [
                {
                    message: 'should pass "move" if setting is move and user opted in',
                    actionSetting: 'move',
                    transferActivities: true,
                    expectedAction: 'move'
                },
                {
                    message: 'should pass "donothing" if setting is move and user opted out',
                    actionSetting: 'move',
                    transferActivities: false,
                    expectedAction: 'donothing'
                },
                {
                    message: 'should pass "donothing" if setting is donothing and user opted in',
                    actionSetting: 'donothing',
                    transferActivities: true,
                    expectedAction: 'donothing'
                },
                {
                    message: 'should pass "donothing" if setting is donothing and user opted out',
                    actionSetting: 'donothing',
                    transferActivities: true,
                    expectedAction: 'donothing'
                },
                {
                    message: 'should pass "donothing" if setting is foo and user opted in',
                    actionSetting: 'foo',
                    transferActivities: false,
                    expectedAction: 'donothing'
                }
            ], function(data) {
                it(data.message, function() {
                    layout.model.set('transfer_activities', data.transferActivities);
                    app.metadata.getConfig().leadConvActivityOpt = data.actionSetting;
                    expect(layout.getTransferActivitiesAttributes()).toEqual({
                        transfer_activities_action: data.expectedAction
                    });
                });
            });
        });

        describe('convertSuccess', function() {
            let cjFormBatch;
            let leadsModel;

            beforeEach(function() {
                cjFormBatch = {
                    startBatchingProcess: sinon.stub()
                };

                leadsModel = app.data.createBean('Leads', {
                    id: '1234'
                });
                layout.context.set('leadsModel', leadsModel);

                sinon.stub(layout, 'convertComplete');
            });

            afterEach(function() {
                leadsModel.dispose();
            });

            it('should start the customer journey batching if there are smart guides for the lead record', function() {
                layout.cjFormBatch = cjFormBatch;
                layout.convertSuccess();
                expect(cjFormBatch.startBatchingProcess).toHaveBeenCalledWith(jasmine.objectContaining({
                    record: '1234',
                    module: 'Leads'
                }));
                expect(layout.convertComplete).not.toHaveBeenCalled();
            });

            it('should call convertComplete directly if there are no smart guides for the lead record', function() {
                layout.cjFormBatch = undefined;
                layout.convertSuccess();
                expect(layout.convertComplete).toHaveBeenCalledWith('success', 'LBL_CONVERTLEAD_SUCCESS', true);
            });
        });
    });
});
