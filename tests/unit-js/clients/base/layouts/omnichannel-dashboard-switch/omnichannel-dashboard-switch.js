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
describe('Base.Layout.OmnichannelDashboardSwitch', function() {
    var parentLayout;
    var layout;
    var app;
    var contact;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'omnichannel-console');
        SugarTest.loadComponent('base', 'layout', 'omnichannel-dashboard-switch');
        parentLayout = SugarTest.createLayout('base', 'layout', 'omnichannel-console');
        layout = SugarTest.createLayout('base', 'layout', 'omnichannel-dashboard-switch');
        layout.layout = parentLayout;
        app = SugarTest.app;
        contact = {
            getContactId: function() {return 'contact1';}
        };
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        parentLayout.dispose();
    });

    describe('showDashboard', function() {
        it('should create a new dashboard when one does not exist', function() {
            var createStub = sinon.stub(layout, '_createDashboard');
            layout.contactIds = [];
            layout.showDashboard(contact);
            expect(createStub).toHaveBeenCalled();
            expect(layout.contactIds).toEqual(['contact1']);
        });

        it('should not create a new dashboard if one exists', function() {
            var createStub = sinon.stub(layout, '_createDashboard');
            var cssStub = sinon.stub();
            layout.contactIds = ['contact1'];
            layout._components = [{
                $el: {
                    css: cssStub
                },
                dispose: $.noop
            }];
            layout.showDashboard(contact);
            expect(createStub).not.toHaveBeenCalled();
            expect(cssStub).toHaveBeenCalled();
            expect(layout.contactIds).toEqual(['contact1']);
        });
    });

    describe('removeDashboard', function() {
        it('should remove dashboard', function() {
            var disposeStub = sinon.stub();
            layout._components = [
                {
                    dispose: disposeStub,
                    triggerBefore: function() {
                        return true;
                    }
                }
            ];
            layout.contactIds = ['fakeId'];
            layout.removeDashboard('fakeId');
            expect(disposeStub).toHaveBeenCalled();
            expect(layout.contactIds.length).toEqual(0);
        });
    });

    describe('removeAllDashboards', function() {
        it('should remove dashboards and show ccp only', function() {
            var disposeStub = sinon.stub();
            layout._components = [
                {
                    dispose: disposeStub,
                    triggerBefore: function() {
                        return true;
                    }
                }
            ];
            var closeStub = sinon.stub();
            layout.layout.close = closeStub;

            layout.contactIds = ['fakeId'];
            layout.removeAllDashboards();
            expect(disposeStub).toHaveBeenCalled();
            expect(layout.contactIds.length).toEqual(0);
            expect(closeStub).toHaveBeenCalled();
        });
    });

    describe('getDashboard', function() {
        it('should get the dashboard per the specified contact id', function() {
            layout.contactIds = ['123'];

            layout._components = [
                {
                    dispose: sinon.stub(),
                    triggerBefore: sinon.stub(),
                }
            ];

            var actual = layout.getDashboard('123');

            expect(actual).not.toEqual(null);
        });
    });

    describe('setContactModel', function() {
        using('different result sets, contactIds, and silent vs not silent', [
            // Conditions met
            {
                contactId: 'abc123',
                model: 'Mocked Return',
                contactIds: ['abc123'],
                matchExpected: true,
                silent: false
            },
            // No matching contactId on layout
            {
                contactId: 'abc123',
                model: 'Mocked Return',
                contactIds: ['poi098'],
                matchExpected: false,
                silent: false
            },
            // Matching contactId, but silent === true
            {
                contactId: 'abc123',
                model: 'Mocked Return',
                contactIds: ['abc123'],
                matchExpected: true,
                silent: true
            },
        ], function(values) {
            it('should set the tab model if a match is found', function() {
                var setModelStub = sinon.stub();
                var switchTabStub = sinon.stub();
                var getTabIndexForModelStub = sinon.stub().returns(1);
                layout.contactIds = values.contactIds;
                layout._components = [{
                    setModel: setModelStub,
                    dispose: function() {},
                    switchTab: switchTabStub,
                    getTabIndexForModel: getTabIndexForModelStub
                }];

                layout.setContactModel(values.contactId, values.model, values.silent);

                expect(setModelStub.callCount).toBe(values.matchExpected ? 1 : 0);
                if (values.matchExpected && !values.silent) {
                    expect(setModelStub).toHaveBeenCalledWith(1, 'Mocked Return');
                    expect(switchTabStub).toHaveBeenCalledWith(1);
                }
            });
        });
    });

    describe('setCaseModel', function() {
        using('different result sets and contactIds', [
            // Conditions met
            {
                contactId: 'abc123',
                model: 'Mocked Return',
                contactIds: ['abc123'],
                matchExpected: true
            },
            // No matching contactId on layout
            {
                contactId: 'abc123',
                model: 'Mocked Return',
                contactIds: ['abc456'],
                matchExpected: false
            }
        ], function(values) {
            it('should set the tab model if a match is found', function() {
                var setModelStub = sinon.stub();
                var switchTabStub = sinon.stub();
                var getTabIndexForModelStub = sinon.stub().returns(2);
                layout.contactIds = values.contactIds;
                layout._components = [{
                    setModel: setModelStub,
                    dispose: function() {},
                    switchTab: switchTabStub,
                    getTabIndexForModel: getTabIndexForModelStub
                }];

                layout.setCaseModel(values.contactId, values.model);

                expect(setModelStub.callCount).toBe(values.matchExpected ? 1 : 0);
                if (values.matchExpected) {
                    expect(setModelStub).toHaveBeenCalledWith(2, 'Mocked Return');
                    expect(switchTabStub).toHaveBeenCalledWith(2);
                }
            });
        });
    });

    describe('_clearButtonClicked', function() {
        using('different existing contactId sets', [
            {
                contactIds: ['1', '2', '3'],
                idParam: '2',
                expectedIndex: 1
            },{
                contactIds: ['1', '4', '8'],
                idParam: '5',
                expectedIndex: -1
            },{
                contactIds: ['2',],
                idParam: '2',
                expectedIndex: 0
            },{
                contactIds: [],
                idParam: '1',
                expectedIndex: -1
            }
        ], function(values) {
            it('should remove the dashboard with the appropriate index', function() {
                sinon.stub(layout, '_removeDashboard');
                layout.contactIds = values.contactIds;
                layout._clearButtonClicked(values.idParam);
                if (values.expectedIndex !== -1) {
                    expect(layout._removeDashboard).toHaveBeenCalledWith(values.expectedIndex);
                } else {
                    expect(layout._removeDashboard).not.toHaveBeenCalled();
                }
            });
        });
    });

    describe('when setting models or searches', function() {
        var contactDashboards;

        beforeEach(function() {
            // Mock a set of contact dashboards existing
            contactDashboards = {
                fakeContactId1: {
                    getTabIndexForModel: sinon.stub().returns(1),
                    getSearchTabIndex: sinon.stub().returns(0),
                    setModel: sinon.stub(),
                    setSearch: sinon.stub(),
                    switchTab: sinon.stub()
                },
                fakeContactId2: {
                    getTabIndexForModel: sinon.stub().returns(2),
                    getSearchTabIndex: sinon.stub().returns(0),
                    setModel: sinon.stub(),
                    setSearch: sinon.stub(),
                    switchTab: sinon.stub()
                }
            };
            sinon.stub(layout, 'getDashboard').callsFake(function(contactId) {
                return contactDashboards[contactId];
            });
        });

        describe('setModel', function() {
            var model;

            beforeEach(function() {
                model = app.data.createBean('Contacts');
            });

            it('should set the model on the correct tab index of the correct dashboard', function() {
                layout.setModel('fakeContactId1', model, false);
                expect(contactDashboards.fakeContactId1.setModel).toHaveBeenCalledWith(1, model);
                expect(contactDashboards.fakeContactId2.setModel).not.toHaveBeenCalled();
            });

            it('should switch to the matched tab is silent is false', function() {
                layout.setModel('fakeContactId1', model, false);
                expect(contactDashboards.fakeContactId1.switchTab).toHaveBeenCalledWith(1);
                expect(contactDashboards.fakeContactId2.switchTab).not.toHaveBeenCalled();
            });

            it('should not switch to the matched tab is silent is true', function() {
                layout.setModel('fakeContactId1', model, true);
                expect(contactDashboards.fakeContactId1.switchTab).not.toHaveBeenCalled();
                expect(contactDashboards.fakeContactId2.switchTab).not.toHaveBeenCalled();
            });
        });

        describe('setModels', function() {
            var models;

            beforeEach(function() {
                models = [
                    app.data.createBean('Contacts'),
                    app.data.createBean('Accounts'),
                    app.data.createBean('Leads')
                ];
            });

            it('should set each model', function() {
                layout.setModels('fakeContactId1', models, null);
                expect(contactDashboards.fakeContactId1.setModel.callCount).toBe(3);
            });

            it('should set tab focus if focusIndex is specified', function() {
                layout.setModels('fakeContactId1', models, 1);
                expect(contactDashboards.fakeContactId1.switchTab).toHaveBeenCalled();
            });

            it('should not set focus to any tab if focusIndex is not specified', function() {
                layout.setModels('fakeContactId1', models, null);
                expect(contactDashboards.fakeContactId1.switchTab).not.toHaveBeenCalled();
            });
        });

        describe('setSearch', function() {
            var searchParams;

            beforeEach(function() {
                searchParams = {
                    term: '12345',
                    module_list: 'Contacts,Accounts'
                };
            });

            it('should set the search parameters on the correct dashboard', function() {
                layout.setSearch('fakeContactId1', searchParams, false);
                expect(contactDashboards.fakeContactId1.setSearch).toHaveBeenCalledWith(searchParams);
                expect(contactDashboards.fakeContactId1.switchTab).toHaveBeenCalled();
            });

            it('should should switch tab focus to the search tab if silent is false', function() {
                layout.setSearch('fakeContactId1', searchParams, false);
                expect(contactDashboards.fakeContactId1.switchTab).toHaveBeenCalled();
            });

            it('should not switch tab focus to the search tab if silent is true', function() {
                layout.setSearch('fakeContactId1', searchParams, true);
                expect(contactDashboards.fakeContactId1.switchTab).not.toHaveBeenCalled();
            });
        });
    });

    describe('getModelPrepopulateData', function() {
        var fakeDashboard1;
        var fakeDashboard2;

        beforeEach(function() {
            // Mock two contact dashboards existing
            layout.contactIds = [
                '12345',
                '678910'
            ];
            fakeDashboard1 = {
                getModelPrepopulateData: sinon.stub()
            };
            fakeDashboard2 = {
                getModelPrepopulateData: sinon.stub()
            };
            layout._components = [fakeDashboard1, fakeDashboard2];
        });

        afterEach(function() {
            layout._components = [];
        });

        it('should get the prepopulate data from the correct contact\'s dashboard', function() {
            layout.getModelPrepopulateData('678910', 'Cases');
            expect(layout._components[1].getModelPrepopulateData).toHaveBeenCalled();
            expect(layout._components[0].getModelPrepopulateData).not.toHaveBeenCalled();
        });
    });

    describe('postQuickCreate', function() {
        var fakeDashboard1;
        var fakeDashboard2;
        var createdBean;

        beforeEach(function() {
            // Mock two contact dashboards existing
            layout.contactIds = [
                '12345',
                '678910'
            ];
            fakeDashboard1 = {
                postQuickCreate: sinon.stub()
            };
            fakeDashboard2 = {
                postQuickCreate: sinon.stub()
            };
            layout._components = [fakeDashboard1, fakeDashboard2];

            createdBean = app.data.createBean('Contacts');
        });

        afterEach(function() {
            layout._components = [];
        });

        it('should get the prepopulate data from the correct contact\'s dashboard', function() {
            layout.postQuickCreate('678910', createdBean);
            expect(layout._components[1].postQuickCreate).toHaveBeenCalledWith(createdBean);
            expect(layout._components[0].postQuickCreate).not.toHaveBeenCalled();
        });
    });
});
