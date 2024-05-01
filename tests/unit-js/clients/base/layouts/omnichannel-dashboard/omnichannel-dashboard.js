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
describe('Base.Layout.OmnichannelDashboard', function() {
    var layout;
    var app;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'omnichannel-dashboard');
        layout = SugarTest.createLayout('base', 'layout', 'omnichannel-dashboard', {});
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
    });

    describe('_render', function() {
        var onStub;
        var onContextStub;

        beforeEach(function() {
            sinon.stub(layout, '_super');
            onStub = sinon.stub();
            onContextStub = sinon.stub();
            sinon.stub(layout, '_getTabbedDashboard').returns({
                context: {
                    on: onContextStub
                },
                on: onStub
            });
        });

        afterEach(function() {
            sinon.restore();
            layout.dispose();
        });

        it('should register to tabbed-dashboard event', function() {
            layout._onTabEvent = false;
            layout._render();
            expect(onStub).toHaveBeenCalled();
            expect(onContextStub).toHaveBeenCalled();
            expect(layout._onTabEvent).toBeTruthy();
        });

        it('should not register to tabbed-dashboard event', function() {
            layout._onTabEvent = true;
            layout._render();
            expect(onStub).not.toHaveBeenCalled();
            expect(onContextStub).not.toHaveBeenCalled();
            expect(layout._onTabEvent).toBeTruthy();
        });
    });

    describe('initComponents', function() {
        it('should replace with omnichannel dashboard', function() {
            var fakeComponents = [
                {
                    layout: {
                        type: 'dashboard',
                        components: ['fake'],
                    }
                }
            ];
            var expectedDashboard = [
                {
                    view: {
                        name: 'omnichannel-dashboard',
                        type: 'omnichannel-dashboard',
                        sticky: false
                    }
                },
                {
                    layout: 'dashlet-main'
                }
            ];
            var superStub = sinon.stub(layout, '_super');
            layout.initComponents(fakeComponents, null, null);
            expect(superStub.lastCall.args[1][0][0].layout.components).toEqual(expectedDashboard);
        });
    });

    describe('setTabModes', function() {
        var setTab;

        beforeEach(function() {
            setStub = sinon.stub();
            sinon.stub(layout, '_getTabbedDashboard').returns({
                tabs: ['tab 1', 'tab2'],
                setTabMode: setStub
            });
        });

        afterEach(function() {
            sinon.restore();
            layout.dispose();
        });

        it('should disable tab2', function() {
            layout.tabModels = ['model1'];
            layout.setTabModes();
            expect(setStub.lastCall.args[1]).toBeFalsy();
        });

        it('should enable tab2', function() {
            layout.tabModels = ['model1', 'model2'];
            layout.setTabModes();
            expect(setStub.lastCall.args[1]).toBeTruthy();
        });
    });

    describe('getTabIndexForModel', function() {
        var contactsModel;
        var accountsModel;
        var leadsModel;
        var casesModel;

        beforeEach(function() {
            contactsModel = app.data.createBean('Contacts', {_module: 'Contacts'});
            accountsModel = app.data.createBean('Accounts', {_module: 'Accounts'});
            leadsModel = app.data.createBean('Leads', {_module: 'Leads'});
            casesModel = app.data.createBean('Cases', {_module: 'Cases'});
        });

        it('should return the correct tab index for the module of the model', function() {
            expect(layout.getTabIndexForModel(contactsModel)).toBe(1);
            expect(layout.getTabIndexForModel(accountsModel)).toBe(2);
            expect(layout.getTabIndexForModel(leadsModel)).toBe(3);
            expect(layout.getTabIndexForModel(casesModel)).toBe(4);
        });
    });

    describe('getModelForTabIndex', function() {
        it('should return the correct model for the given index', function() {
            layout.tabModels = [
                'fakeModel1',
                'fakeModel2'
            ];
            expect(layout.getModelForTabIndex(0)).toBe('fakeModel1');
            expect(layout.getModelForTabIndex(1)).toBe('fakeModel2');
        });
    });

    describe('getModelPrepopulateData', function() {
        beforeEach(function() {
            layout.populateLists = {
                Cases: {
                    Contacts: {
                        primary_contact_id: 'id',
                        primary_contact_name: 'name'
                    }
                }
            };
            layout.moduleTabIndex = {
                Contacts: 1,
                Cases: 2
            };
            layout.tabModels = [];
            layout.tabModels[1] = app.data.createBean('Contacts', {
                id: 123,
                name: 'Fake Contact'
            });
            layout.tabModels[2] = app.data.createBean('Cases', {id: 456});
        });

        it('should include data from the defined populateLists for the given target module', function() {
            expect(layout.getModelPrepopulateData('Cases')).toEqual({
                primary_contact_id: 123,
                primary_contact_name: 'Fake Contact'
            });
        });

        it('should include no data if there is no defined populateLists for the given target module', function() {
            expect(layout.getModelPrepopulateData('Opportunities')).toEqual({});
        });
    });

    describe('postQuickCreate', function() {
        var createdModel;

        beforeEach(function() {
            layout.postQuickCreateFunctions = {
                Cases: [
                    '_setContactModelFromCaseModel'
                ]
            };

            sinon.stub(layout, '_setContactModelFromCaseModel');
        });

        it('should call the correct post quick-create code for the given created model', function() {
            createdModel = app.data.createBean('Cases', {_module: 'Cases'});
            layout.postQuickCreate(createdModel);
            expect(layout._setContactModelFromCaseModel).toHaveBeenCalledWith(createdModel);
        });
    });

    describe('_setContactModelFromCaseModel', function() {
        var caseModel;
        var mockContactBean;

        beforeEach(function() {
            layout.moduleTabIndex = {
                Contacts: 1,
                Cases: 2
            };
            layout.tabModels = [];
            layout.tabModels[1] = app.data.createBean('Contacts', {id: 123});

            caseModel = app.data.createBean('Cases');
            mockContactBean = app.data.createBean('Contacts');
            sinon.stub(app.data, 'createBean').returns(mockContactBean);
        });

        describe('when the Case\'s primary_contact_id is the same as the dashboard\'s Contact\'s ID', function() {
            beforeEach(function() {
                caseModel.set({
                    _module: 'Cases',
                    primary_contact_id: 123
                });
                sinon.stub(mockContactBean, 'fetch');
            });

            it('should not replace the dashboard\'s Contact', function() {
                layout._setContactModelFromCaseModel(caseModel);
                expect(mockContactBean.fetch).not.toHaveBeenCalled();
            });
        });

        describe('when the Case\'s primary_contact_id is different than the dashboard\'s Contact\'s ID', function() {
            beforeEach(function() {
                caseModel.set({
                    _module: 'Cases',
                    primary_contact_id: 456
                });

                sinon.stub(mockContactBean, 'fetch').callsFake(function(callbacks) {
                    callbacks.success();
                });
                sinon.stub(layout, 'setModel');
            });

            it('should replace the dashboard\'s Contact', function() {
                layout._setContactModelFromCaseModel(caseModel);
                expect(mockContactBean.fetch).toHaveBeenCalled();
                expect(layout.setModel).toHaveBeenCalled();
            });
        });

        describe('setSearch', function() {
            it('should set the search parameters on the layout', function() {
                layout.setSearch({
                    term: '12345',
                    module_list: ['Contacts', 'Accounts']
                });
                expect(layout.savedSearchTerm).toEqual('12345');
                expect(layout.searchCollection.selectedModules).toEqual(['Contacts', 'Accounts']);
            });
        });
    });
});
