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
describe('Base.View.OmnichannelCcpView', function() {
    var view;
    var layout;
    var app = SUGAR.App;

    beforeEach(function() {
        window.connect = {
            core: {
                terminate: sinon.stub(),
                initCCP: sinon.stub(),
                getEventBus: sinon.stub(),
                onViewContact: sinon.stub()
            },
            agent: sinon.stub(),
            contact: sinon.stub(),
            ContactType: {
                VOICE: 'voice',
                CHAT: 'chat'
            }
        };
        app.routing.start();

        SugarTest.loadComponent('base', 'layout', 'omnichannel-console');
        layout = SugarTest.createLayout('base', 'layout', 'omnichannel-console', {});
        view = SugarTest.createView('base', 'Contacts', 'omnichannel-ccp', null, null, false, layout);
    });

    afterEach(function() {
        app.router.stop();
        SugarTest.testMetadata.dispose();
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        delete window.connect;
    });

    describe('_handleConnectionEnd', function() {
        beforeEach(function() {
            sinon.stub(view, 'getContactConnectedTime').returns('start time');
            sinon.stub(view, 'getTimeAndDuration').returns({
                timeStart: 'start time',
                nowTime: 'now time',
                durationHours: 0,
                durationMinutes: 1
            });
            sinon.stub(view, '_hasConnectionRecord').returns(true);
            sinon.stub(view, '_getTranscriptForContact').returns('transcript');
        });

        afterEach(function() {
            view.connectedContacts = {};
            view.connectionRecords = {};
        });

        using('different contacts', [
            {
                contactId: 'contact1',
                contactType: 'voice',
                expected: {
                    status: 'Held',
                    duration_hours: 0,
                    duration_minutes: 1
                }
            },
            {
                contactId: 'contact2',
                contactType: 'chat',
                expected: {
                    status: 'Completed',
                    conversation: 'transcript'
                }
            }
        ], function(values) {
            it('should update connection record', function() {
                var contact = {
                    getType: function() {
                        return values.contactType;
                    },
                    getContactId: function() {
                        return values.contactId;
                    }
                };
                var expected = {
                    date_end: 'now time'
                };

                expected = _.extend(expected, values.expected);
                var updateStub = sinon.stub(view, '_updateConnectionRecord');
                view._handleConnectionEnd(contact);
                expect(updateStub).toHaveBeenCalledWith(contact, expected);
            });
        });
    });

    describe('_hasConnectionRecord', function() {
        var contact;
        beforeEach(function() {
            contact = {
                'getContactId': function() {
                    return 'testRecord';
                }
            };
        });

        afterEach(function() {
            contact = null;
        });

        it('should return false when connection record does not exist', function() {
            view.connectionRecords = {'record1': 'test record', 'record2': 'test record'};

            expect(view._hasConnectionRecord(contact)).toBeFalsy();
        });

        it('should return true when connection record exists', function() {
            view.connectionRecords = {'record1': 'test record', 'testRecord': 'demo record'};

            expect(view._hasConnectionRecord(contact)).toBeTruthy();
        });
    });

    describe('_closeConnectionRecord', function() {
        beforeEach(function() {
            sinon.stub(view, '_hasConnectionRecord').returns(true);
            sinon.stub(view, '_updateConnectionRecord');
        });

        it('should update the connection record', function() {
            var testContact = {};
            view._closeConnectionRecord(testContact);
            expect(view._updateConnectionRecord).toHaveBeenCalledWith(testContact, {}, true);
        });

    });

    describe('_updateConnectionRecord', function() {
        var contact;

        beforeEach(function() {
            contact = {
                getType: $.noop,
                getContactId: $.noop
            };
            sinon.stub(contact, 'getContactId').returns('contact1');
        });

        it('should create and fetch model with the same id', function() {
            var conRecord = app.data.createBean('Calls', {id: 'test'});
            conRecord.module = 'Calls';
            var fakeModel = {fetch: $.noop};
            var fetchStub = sinon.stub(fakeModel, 'fetch');
            var beanStub = sinon.stub(app.data, 'createBean').returns(fakeModel);
            view.connectionRecords = {
                contact1: conRecord
            };

            view._updateConnectionRecord(contact, {});
            expect(beanStub).toHaveBeenCalled();
            expect(fetchStub).toHaveBeenCalled();
            expect(beanStub.args[0][0]).toEqual('Calls');
            expect(beanStub.args[0][1]).toEqual({id: 'test'});
        });

        it('should not create a new model without the connectionRecord', function() {
            var beanStub = sinon.stub(app.data, 'createBean');
            view._updateConnectionRecord(contact, {});
            expect(beanStub).not.toHaveBeenCalled();
        });

        it('should not call baseModel.fetch if there is no contact', function() {
            sinon.stub(app.data, 'createBean');
            view._updateConnectionRecord(null, {});
            expect(app.data.createBean).not.toHaveBeenCalled();
        });
    });

    describe('_updateFetchedRecord', function() {
        var model;
        var data;

        beforeEach(function() {
            model = app.data.createBean('Calls');
            data = {
                case: {},
                date_end: ''
            };

            sinon.stub(model, 'set');
            sinon.stub(view, 'preserveDBFieldValues');
            sinon.stub(view, 'saveModel');
        });

        it('should apply the user made changes on the model', function() {
            view._updateFetchedRecord(model, data, {}, {});
            expect(model.set).toHaveBeenCalledWith(data);
            expect(view.preserveDBFieldValues).toHaveBeenCalled();
            expect(view.saveModel).toHaveBeenCalled();
        });
    });

    describe('applyChangesToModel', function() {
        var model;
        var modelSetStub;

        beforeEach(function() {
            model = app.data.createBean('Calls');
            modelSetStub = sinon.stub(model, 'set');
        });

        it('should trigger the contact field update', function() {
            var stub = sinon.stub(view, 'updateContactIdField');
            sinon.stub(view, 'isCall').returns(true);

            view.applyChangesToModel(model, {}, {}, {}, 'Contacts');
            expect(stub).toHaveBeenCalled();
        });

        it('should set the contact model id', function() {
            sinon.stub(view, 'isCall').returns(false);
            var contactModel = app.data.createBean('Contacts', {id: 'cont1'});

            view.applyChangesToModel(model, {}, {}, contactModel, 'Contacts');
            expect(modelSetStub).toHaveBeenCalled();
            expect(modelSetStub.args[0][0]).toEqual('contact_id');
            expect(modelSetStub.args[0][1]).toEqual('cont1');
        });

        it('should not take any action if there is a chat and no contact model', function() {
            sinon.stub(view, 'isCall').returns(false);
            var stub = sinon.stub(view, 'updateContactIdField');

            view.applyChangesToModel(model, {}, {}, null, 'Contacts');
            expect(stub).not.toHaveBeenCalled();
            expect(modelSetStub).not.toHaveBeenCalled();
        });

        it('should set case properties', function() {
            var caseModel = app.data.createBean('Cases', {id: 'case1'});

            view.applyChangesToModel(model, {}, {}, caseModel, 'Cases');
            expect(modelSetStub.callCount).toBe(2);
            expect(modelSetStub.args[0][0]).toEqual('parent_type');
            expect(modelSetStub.args[0][1]).toEqual('Cases');
            expect(modelSetStub.args[1][0]).toEqual('parent_id');
            expect(modelSetStub.args[1][1]).toEqual('case1');
        });

        it('should set any other data normally', function() {
            view.applyChangesToModel(model, {}, {}, 'value', 'key');
            expect(modelSetStub.callCount).toBe(1);
            expect(modelSetStub.args[0][0]).toEqual('key');
            expect(modelSetStub.args[0][1]).toEqual('value');
        });
    });

    describe('updateContactIdField', function() {
        var model;
        var dbModel;
        var contactModel;
        var setStub;

        beforeEach(function() {
            model = app.data.createBean('Messages', {});
            dbModel = app.data.createBean('Messages', {});
            contactModel = app.data.createBean('Contacts', {id: 'cid', name: 'ctest'});
            setStub = sinon.stub(model, 'set');
        });

        it('should swap the contact ids', function() {
            dbModel.set('contact_id', 'cont1');
            var expectation = {
                delete: ['cont1'],
                add: [{id: 'cid', name: 'ctest'}]
            };

            view.updateContactIdField(model, dbModel, contactModel);
            expect(setStub.args[0][1]).toEqual('cid');
            expect(setStub.args[1][1]).toEqual(expectation);
        });

        it('should set the contact attributes and id', function() {
            var expectation = {
                add: [{id: 'cid', name: 'ctest'}]
            };

            view.updateContactIdField(model, dbModel, contactModel);
            expect(setStub.args[0][1]).toEqual('cid');
            expect(setStub.args[1][1]).toEqual(expectation);
        });

        it('should set only the contact id', function() {
            dbModel.set('contact_id', 'cid');

            view.updateContactIdField(model, dbModel, contactModel);
            expect(setStub.args[0][1]).toEqual('cid');
            expect(setStub.args[1][1]).toEqual({});
        });

        it('should remove the previous contact', function() {
            dbModel.set('contact_id', 'contact1');
            var expectation = {
                delete: ['contact1']
            };

            view.updateContactIdField(model, dbModel, null);
            expect(setStub.args[0][0]).toEqual('contact_id');
            expect(setStub.args[0][1]).toEqual('');
            expect(setStub.args[1][1]).toEqual(expectation);
        });

        it('should reset the contact_id', function() {
            view.updateContactIdField(model, dbModel, null);
            expect(setStub.args[0][0]).toEqual('contact_id');
            expect(setStub.args[0][1]).toEqual('');
            expect(setStub.args[1][1]).toEqual({});
        });
    });

    describe('preserveDBFieldValues', function() {
        var model;
        var dbModel;

        beforeEach(function() {
            model = app.data.createBean('Messages', {});
            dbModel = app.data.createBean('Messages', {});
            setStub = sinon.stub(model, 'set');
        });

        it('should not change the model', function() {
            view.preserveDBFieldValues(model, dbModel);
            expect(setStub).not.toHaveBeenCalled();
        });

        it('should set the call recording url', function() {
            dbModel.set('call_recording_url', 'url');

            view.preserveDBFieldValues(model, dbModel);
            expect(setStub).toHaveBeenCalledOnce();
            expect(setStub.args[0][0]).toEqual('call_recording_url');
            expect(setStub.args[0][1]).toEqual('url');
        });
    });

    describe('onValidate', function() {
        it('should save the model with suitable callback', function() {
            var model = app.data.createBean('Calls', {});
            model.id = '123-abc';
            var saveStub = sinon.stub(model, 'save');

            const params = [model, {}, false, true, []];
            view.saveOnValidate(...params);
            model.set('status', 'Completed');
            view.saveOnValidate(...params);

            expect(saveStub).toHaveBeenCalled();
            expect(saveStub.args[1][1].success).toBeDefined();
        });

        it('should show an alert on successful save', function() {
            sinon.stub(app.lang, 'getModuleName').returns('Calls');
            var alertStub = sinon.stub(app.alert, 'show');
            var model = {attributes: {}};
            view.saveModelSuccess(model, true);
            expect(alertStub).toHaveBeenCalled();
        });

        it('should log an error on failed save', function() {
            var logStub = sinon.stub(app.logger, 'error');
            var contact = {getContactId: $.noop};
            view.saveModelError(contact);
            expect(logStub).toHaveBeenCalled();
        });

        it('should not call model.save if no model id', function() {
            var model = app.data.createBean('Calls', {});
            sinon.stub(model, 'save');

            view.saveModel(model, {});

            expect(model.save).not.toHaveBeenCalled();
        });
    });

    describe('loadAdminConfig', function() {
        using('different settings values', [
            {region: 'test-region', instance: 'test-instance', expected: true},
            {region: 'test-region', instance: 'test-instance', expected: true, instanceUrl: 'http://test_url'},
            {region: '', instance: 'test-instance', expected: false},
            {region: 'test-region', instance: '', expected: false},
            {region: undefined, instance: '', expected: false},
        ], function(values) {
            it('should load admin settings and return true if successful', function() {
                App.config.awsConnectInstanceName = values.instance;
                App.config.awsConnectRegion = values.region;
                App.config.awsConnectUrl = values.instanceUrl;
                var settingsLoaded = view._loadAdminConfig();
                expect(settingsLoaded).toEqual(values.expected);
                if (values.expected) {
                    var url = values.instanceUrl || view.urlPrefix + values.instance + view.urlSuffix;
                    expect(view.defaultCCPOptions.ccpUrl).toEqual(url);
                    expect(view.defaultCCPOptions.region).toEqual(values.region);
                } else {
                    expect(view.defaultCCPOptions.ccpUrl).toBeUndefined();
                    expect(view.defaultCCPOptions.region).toBeUndefined();
                }
                delete App.config.awsConnectInstanceName;
                delete App.config.awsConnectRegion;
            });
        });
    });

    describe('_showNonConfiguredWarning', function() {
        it('should show warning with expected params', function() {
            sinon.stub(app.alert, 'show');
            view._showNonConfiguredWarning();
            expect(app.alert.show).toHaveBeenCalledWith('omnichannel-not-configured', {
                level: 'warning',
                messages: 'ERROR_OMNICHANNEL_NOT_CONFIGURED'
            });
        });
    });

    describe('styleFooterButton', function() {
        beforeEach(function() {
            sinon.stub(view.layout.context, 'trigger');
        });

        using('different statuses', ['logged-in', 'logged-out'], function(status) {
            it('should trigger the layout context event', function() {
                view.styleFooterButton(status);
                expect(view.layout.context.trigger).toHaveBeenCalledWith('omnichannel:auth', status);
            });
        });
    });

    describe('loadGeneralEventListeners', function() {
        it('should call the relevant library functions', function() {
            var subscribeStub = sinon.stub();
            connect.core.getEventBus.returns({
                subscribe: subscribeStub
            });
            connect.EventType = {
                TERMINATED: 'TERMINATED',
                ACK_TIMEOUT: 'ACK_TIMEOUT'
            };
            connect.ContactEvents = {
                DESTROYED: 'DESTROYED'
            };
            view.loadGeneralEventListeners();
            expect(connect.core.getEventBus.calledOnce).toBeTruthy();
            expect(subscribeStub.callCount).toBe(3);
            expect(subscribeStub.getCall(0)).toHaveBeenCalledWith('TERMINATED');
            expect(subscribeStub.getCall(1)).toHaveBeenCalledWith('ACK_TIMEOUT');
            expect(subscribeStub.getCall(2)).toHaveBeenCalledWith('DESTROYED');
        });
    });

    describe('loadAgentEventListeners', function() {
        it('should call connect.agent and attach appropriate listeners', function() {
            view.loadAgentEventListeners();
            expect(connect.agent.calledOnce).toBeTruthy();
        });
    });

    describe('loadContactEventLIsteners', function() {
        it('should call connect.contact to attach event listeners', function() {
            view.loadContactEventListeners();
            expect(connect.contact.calledOnce).toBeTruthy();
        });
    });

    describe('tearDownCCP', function() {
        it('should teardown all elements of the CCP', function() {
            var emptyStub = sinon.stub();
            sinon.stub(view, 'styleFooterButton');
            sinon.stub(view.$el, 'find').callsFake(function() {
                return {empty: emptyStub};
            });
            view.ccpLoaded = true;
            view.tearDownCCP();
            expect(view.styleFooterButton).toHaveBeenCalledWith('logged-out');
            expect(emptyStub.calledOnce).toBeTruthy();
            expect(view.ccpLoaded).toBe(false);
        });
    });

    describe('initializeCCP', function() {
        using('different values for if the ccp has loaded', [true, false], function(loaded) {
            it('should initialize CCP only if not already loaded', function() {
                sinon.stub(view, 'loadAgentEventListeners');
                sinon.stub(view, 'loadGeneralEventListeners');
                sinon.stub(view, 'loadContactEventListeners');
                window.connect.core.loginWindow = {
                    focus: $.noop
                };
                view.ccpLoaded = loaded;
                view.initializeCCP();
                expect(connect.core.initCCP.callCount).toBe(loaded ? 0 : 1);
                expect(view.loadAgentEventListeners.callCount).toBe(loaded ? 0 : 1);
                expect(view.loadGeneralEventListeners.callCount).toBe(loaded ? 0 : 1);
                expect(view.loadContactEventListeners.callCount).toBe(loaded ? 0 : 1);
                expect(view.ccpLoaded).toEqual(true);
            });
        });
    });

    describe('loadCCP', function() {
        using('different admin configs and library loaded combos', [
            {adminSuccess: true, libLoaded: true},
            {adminSuccess: false, libLoaded: true},
            {adminSuccess: true, libLoaded: false},
        ], function(values) {
            it('should fetch the connect library only if configured and not already loaded', function() {
                sinon.stub(view, '_loadAdminConfig').callsFake(function() {
                    return values.adminSuccess;
                });
                sinon.stub(view, '_showNonConfiguredWarning');
                sinon.stub(view, 'initializeCCP');
                sinon.stub($, 'getScript');
                view.libraryLoaded = values.libLoaded;
                view.loadCCP();
                expect(view._loadAdminConfig.calledOnce).toBeTruthy();
                expect(view._showNonConfiguredWarning.callCount).toEqual(!values.adminSuccess ? 1 : 0);
                expect(view.initializeCCP.callCount).toEqual(values.adminSuccess && values.libLoaded ? 1 : 0);
                expect($.getScript.callCount).toEqual(values.adminSuccess && !values.libLoaded ? 1 : 0);
            });
        });
    });

    describe('addContactToContactsList', function() {
        it('should add the contact to the connected contacts list', function() {
            var id = 123;
            var timestamp = '2020-07-29T12:00:00-04:00';

            var contact = {
                getContactId: function() {
                    return id;
                },
                getStatus: function() {
                    return {
                        timestamp: timestamp,
                    };
                },
            };

            expect(view.connectedContacts).toEqual({});
            view.addContactToContactsList(contact);
            var obj = {};
            obj[id] = {connectedTimestamp: timestamp};
            expect(view.connectedContacts).toEqual(obj);
        });
    });

    describe('removeStoredContactData', function() {
        using('different combinations of contact information', [
            {
                connectedContacts: {
                    123: {
                        connectedTimestamp: '2020-07-29T12:00:00-04:00',
                    },
                },
                chatControllers: {
                    123: {getTranscript: function() { }}
                },
                chatTranscripts: {
                    123: [{DisplayName: 'SYSTEM', Content: 'Message Content'}]
                }
            }
        ], function(expected) {
            it('should remove the contact from the connected contacts list', function() {
                view.connectedContacts = _.extendOwn({}, expected.connectedContacts, {
                    456: {
                        connectedTimestamp: '2020-07-29T15:00:00-04:00',
                    },
                });
                view.chatControllers = _.extendOwn({}, expected.chatControllers, {
                    456: {getTranscript: function() { }}
                });
                view.chatTranscripts = _.extendOwn({}, expected.chatTranscripts, {
                    456: [{DisplayName: 'Customer', Content: 'Customer Message Content'}]
                });

                var contact = {
                    getContactId: function() {
                        return 456;
                    },
                };

                view.removeStoredContactData(contact);
                expect(view.connectedContacts).toEqual(expected.connectedContacts);
                expect(view.chatControllers).toEqual(expected.chatControllers);
                expect(view.chatTranscripts).toEqual(expected.chatTranscripts);
            });
        });
    });

    describe('getGenericContactInfo', function() {
        it('should get generic contact info', function() {
            var time = '2020-07-29T12:00:00-04:00';

            var contact = {
                isInbound: function() {
                    return true;
                },
                getType: function() {
                    return 'chat';
                },
                getContactId: function() {
                    return 123;
                },
                getStatus: function() {
                    return {
                        timestamp: time,
                    };
                },
            };

            view.addContactToContactsList(contact);

            var actual = view.getGenericContactInfo(contact);

            expect(actual).toEqual({
                isContactInbound: true,
                contactType: 'chat',
                startTime: app.date(time),
            });
        });
    });

    describe('getVoiceContactInfo', function() {
        it('should get contact info for voice type', function() {
            var phoneNumber = '+01234567890';

            var contact = {
                getInitialConnection: function() {
                    return {
                        getEndpoint: function() {
                            return {
                                phoneNumber: phoneNumber,
                            };
                        },
                    };
                },
            };

            var actual = view.getVoiceContactInfo(contact);

            expect(actual).toEqual({
                phone_work: phoneNumber,
                source: 'Phone',
            });
        });
    });

    describe('getChatContactInfo', function() {
        it('should get contact info for chat type', function() {
            var contact = {
                _getData: function() {
                    return {
                        connections: [
                            {
                                type: 'inbound',
                                chatMediaInfo: {
                                    customerName: 'Customer'
                                },
                            },
                        ],
                    };
                },
            };

            var actual = view.getChatContactInfo(contact);

            expect(actual).toEqual({
                last_name: 'Customer',
                name: 'Customer',
                source: 'Chat',
            });
        });
    });

    describe('getContactConnectedTime', function() {
        it('should get the timestamp as a Utils/Date', function() {
            var time = '2020-07-29T12:00:00-04:00';
            var contact = {
                getContactId: function() {
                    return 123;
                },
                getStatus: function() {
                    return {
                        timestamp: time,
                    };
                },
            };

            view.addContactToContactsList(contact);

            var actual = view.getContactConnectedTime(contact);

            expect(actual).toEqual(app.date(time));
        });
    });

    describe('getRecordTitle', function() {
        var contact;

        beforeEach(function() {
            contact = {
                getType: $.noop,
                getContactId: $.noop
            };
        });

        using('different contact data', [
            {
                module: 'Calls',
                time: '2020-07-29T12:00:00-04:00',
                formatUserStr: '07/29/2020 12:00pm',
                identifier: '+01234567890',
                contactTypeStr: 'Call',
                expected: {
                    direction: 'to'
                },
                data: {
                    contactType: 'voice',
                    phone_work: '+01234567890',
                    isContactInbound: false,
                },
            },
            {
                module: 'Messages',
                time: '2020-07-29T12:00:00-04:00',
                formatUserStr: '07/29/2020 12:00pm',
                identifier: 'Customer',
                contactTypeStr: 'Chat',
                expected: {
                    direction: 'from'
                },
                data: {
                    contactType: 'chat',
                    name: 'Customer',
                },
            },
        ], function(values) {
            it('should get the record title per contact data', function() {
                var langGetStub = sinon.stub(app.lang, 'get');
                sinon.stub(app.date.fn, 'formatUser').callsFake(function() {
                    return values.formatUserStr;
                });
                sinon.stub(contact, 'getType').returns(values.data.contactType);

                values.data.startTime = app.date(values.time);

                view.getRecordTitle(values.module, values.data, contact);
                expect(langGetStub).toHaveBeenCalledWith('TPL_OMNICHANNEL_NEW_RECORD_TITLE', values.module, {
                    type: values.contactTypeStr,
                    direction: values.expected.direction,
                    identifier: values.identifier,
                    time: values.formatUserStr,
                });
            });
        });
    });

    describe('getNewModelForContact', function() {
        var contact;

        beforeEach(function() {
            contact = {
                getType: $.noop,
                getContactId: $.noop
            };
        });

        using('different module and contact data', [
            {
                module: 'Calls',
                recordTitle: 'Call from +01234567890 at 07/29/2020 12:00pm',
                data: {
                    contactType: 'voice',
                    isContactInbound: true,
                    startTime: {
                        formatServer: function() {
                            return '2020-07-29T12:00:00-04:00';
                        }
                    }
                },
                expected: {
                    direction: 'Inbound',
                    duration_hours: 0,
                    duration_minutes: 0,
                    users: {
                        add: [{
                            id: app.user.id,
                            _module: 'Users'
                        }]
                    },
                    name: 'Call from +01234567890 at 07/29/2020 12:00pm',
                    date_start: '2020-07-29T12:00:00-04:00',
                    status: 'In Progress',
                    assigned_user_id: app.user.id,
                    aws_contact_id: '',
                    invitees: []
                },
            },
            {
                module: 'Messages',
                recordTitle: 'Chat from Customer at 07/29/2020 12:00pm',
                data: {
                    contactType: 'chat',
                    startTime: {
                        formatServer: function() {
                            return '2020-07-29T12:00:00-04:00';
                        }
                    }
                },
                expected: {
                    channel_type: 'Chat',
                    name: 'Chat from Customer at 07/29/2020 12:00pm',
                    date_start: '2020-07-29T12:00:00-04:00',
                    status: 'In Progress',
                    assigned_user_id: app.user.id,
                    aws_contact_id: '',
                    invitees: []
                },
            },
        ], function(values) {
            it('should get the record title per contact data', function() {
                sinon.stub(view, 'getRecordTitle').callsFake(function() {
                    return values.recordTitle;
                });
                sinon.stub(contact, 'getType').returns(values.data.contactType);

                var actual = view.getNewModelForContact(values.module, values.data, contact);

                expect(actual.attributes).toEqual(values.expected);
            });
        });
    });

    describe('_setActiveContact', function() {
        beforeEach(function() {
            sinon.stub(view.layout, 'trigger');
        });

        it('should set the active contact to the supplied contact id', function() {
            var expected = {
                contactId: 123,
            };

            window.connect.Agent = function() {
                this.getContacts = function() {
                    return [{contactId: expected.contactId}];
                };
            };

            view._setActiveContact(expected.contactId);

            expect(view.activeContact).toEqual(expected);
            expect(view.layout.trigger).toHaveBeenCalledWith('contact:view', expected);
        });
    });

    describe('_unsetActiveContact', function() {
        beforeEach(function() {
            view.activeContact = {
                contactId: 123,
            };

            var stub = sinon.stub();

            view.context = {
                unset: stub,
                off: sinon.stub(),
            };
        });

        it('should unset the active contact and other relevant data', function() {
            view._unsetActiveContact();
            expect(view.activeContact).toBeNull;
        });

        it('should update listening records with a null contact and model', function() {
            let eventsTrigger = sinon.stub(app.events, 'trigger');

            view._unsetActiveContact();
            expect(eventsTrigger).toHaveBeenCalledWith('omniconsole:contact:changed', null, null);
        });
    });

    describe('getActiveContactId', function() {
        it('should get the contact id for the active contact', function() {
            view.activeContact = {
                getContactId: function() {
                    return '123';
                },
            };

            var actual = view.getActiveContactId();

            expect(actual).toEqual('123');
        });
    });

    describe('_getTranscriptForContact', function() {
        using('different time formats',
            [{
                timepref: 'HH:mm',
                expected: '[SYSTEM SYSTEM_MESSAGE] 16:40\n' +
                    'You are now being placed in queue to chat with an agent.\n' +
                    '\n' +
                    '[CUSTOMER Customer] 16:40\n' +
                    'Hello I am the Customer\n' +
                    '\n' +
                    '[AGENT Jay] 16:41\n' +
                    'Hello I am the Agent'
            }, {
                timepref: 'h:mm A',
                expected: '[SYSTEM SYSTEM_MESSAGE] 4:40 PM\n' +
                    'You are now being placed in queue to chat with an agent.\n' +
                    '\n' +
                    '[CUSTOMER Customer] 4:40 PM\n' +
                    'Hello I am the Customer\n' +
                    '\n' +
                    '[AGENT Jay] 4:41 PM\n' +
                    'Hello I am the Agent',
            }], function(values) {
                it('should make human-readable transcript from JSON', function() {
                    sinon.stub(app.user, 'getPreference').returns(-240);
                    sinon.stub(app.date, 'getUserTimeFormat').returns(values.timepref);

                    var chatFixture = SugarTest.loadFixture('amazon-chat-transcript');
                    var contact = {
                        contactId: 'abc123'
                    };
                    view.chatTranscripts = {
                        abc123: chatFixture
                    };

                    var actual = view._getTranscriptForContact(contact);
                    expect(actual).toEqual(values.expected);
                });
            });
    });

    describe('_setChatTranscript', function() {
        using('different combinations of transcripts', [
            {
                existing: {
                    123: [
                        {Content: 'message 1', Id: 1},
                        {Content: 'message 2', Id: 2},
                        {Content: 'message 3', Id: 3}
                    ]
                },
                incoming: {
                    data: {
                        InitialContactId: 123,
                        Transcript: [
                            {Content: 'message 3', Id: 3},
                            {Content: 'message 4', Id: 4}
                        ]
                    }
                },
                expected: {
                    123: [
                        {Content: 'message 1', Id: 1},
                        {Content: 'message 2', Id: 2},
                        {Content: 'message 3', Id: 3},
                        {Content: 'message 4', Id: 4}
                    ]
                }
            },
            {
                existing: {123: []},
                incoming: {
                    data: {
                        InitialContactId: 123,
                        Transcript: [
                            {Content: 'message 3', Id: 3},
                            {Content: 'message 4', Id: 4}
                        ]
                    }
                },
                expected: {
                    123: [
                        {Content: 'message 3', Id: 3},
                        {Content: 'message 4', Id: 4}
                    ]
                }
            }, {
                existing: {
                    123: [
                        {Content: 'message 1', Id: 1},
                        {Content: 'message 2', Id: 2},
                        {Content: 'message 3', Id: 3}
                    ]
                },
                incoming: {
                    data: {
                        InitialContactId: 456,
                        Transcript: [
                            {Content: 'message 3', Id: 3},
                            {Content: 'message 4', Id: 4}
                        ]
                    }
                },
                expected: {
                    123: [
                        {Content: 'message 1', Id: 1},
                        {Content: 'message 2', Id: 2},
                        {Content: 'message 3', Id: 3}
                    ],
                    456: [
                        {Content: 'message 3', Id: 3},
                        {Content: 'message 4', Id: 4}
                    ]
                }
            },
        ], function(values) {
            it('should store the union between new and existing transcripts', function() {
                view.chatTranscripts = values.existing;
                view._setChatTranscript(values.incoming);
                expect(view.chatTranscripts).toEqual(values.expected);
            });
        });

    });

    describe('_searchRecordByContactId', function() {
        it('should search for a record by contact id', function() {
            var expected = app.api.serverUrl + '/Messages?filter[0][aws_contact_id][$equals]=123';
            sinon.stub(app.api, 'call');
            view._searchRecordByContactId('Messages', '123', $.noop);

            expect(app.api.call.args[0][1]).toEqual(expected);
        });
    });

    describe('_handlePostConnectionRecordCreation', function() {
        let contact;
        let model;

        beforeEach(function() {
            sinon.stub(view.layout, 'trigger');
            sinon.stub(view, '_matchRecords');
            model = {
                attributes: {
                    name: 'Chat from Jane Doe at 12/01/2020 12:00pm'
                }
            };
            contact = {
                getContactId: function() {
                    return '123';
                }
            };
        });

        it('should handle post connection record creation actions', function() {
            view._handlePostConnectionRecordCreation(model, contact);

            expect(view.connectionRecords[123]).toEqual(model);
            expect(layout.trigger).toHaveBeenCalledWith('contact:model:loaded', view.activeContact);
        });

        it('should update listening records with the contact and model', function() {
            let eventsTrigger = sinon.stub(app.events, 'trigger');

            view._handlePostConnectionRecordCreation(model, contact);

            expect(eventsTrigger).toHaveBeenCalledWith('omniconsole:contact:changed', contact, model);
        });

        it('should match records to the contact', function() {
            view._handlePostConnectionRecordCreation(model, contact);
            expect(view._matchRecords).toHaveBeenCalled();
        });
    });

    describe('resize', function() {
        var cssStub;
        var detailPanelStub;
        var detailPanel = {};
        var result;

        beforeEach(function() {
            cssStub = sinon.stub(view.$el, 'css');
            cssStub.withArgs('margin-top').returns('10px');
            detailPanel.$el = $('<div></div>');
            detailPanelStub = sinon.stub(detailPanel.$el, 'css');
            detailPanelStub.withArgs('top').returns('120px');
            detailPanelStub.withArgs('height').returns('330px');
            sinon.stub(view.layout, 'getComponent')
                .withArgs('omnichannel-detail').returns(detailPanel);
        });

        it('should not resize if the element is already disposed', function() {
            view.disposed = true;
            view.resize();
            expect(cssStub).not.toHaveBeenCalled();

        });

        it('should determine the top position accurately in non-ccpOnly mode', function() {
            view.resize();
            expect(cssStub).toHaveBeenCalled();
            expect(cssStub.getCall(0).args[0]).toEqual('top');
            expect(cssStub.getCall(0).args[1]).toEqual(451);
        });
    });

    describe('_formatRecordMatchResults', function() {
        var mockResults;

        beforeEach(function() {
            mockResults = [
                {
                    contents: {
                        records: [
                            {id: '1', _module: 'Cases'},
                        ]
                    }
                },
                {
                    contents: {
                        records: [
                            {id: '2', _module: 'Contacts'},
                        ]
                    }
                },
                {
                    contents: {
                        records: [
                            {id: '5', _module: 'Leads'},
                            {id: '3', _module: 'Accounts'},
                            {id: '4', _module: 'Contacts'}
                        ]
                    }
                }
            ];

            sinon.stub(view, '_getSearchModules').returns(['Contacts', 'Accounts', 'Leads', 'Cases']);
        });

        it('should correctly combine and format the results from multiple searches', function() {
            var formattedResults = view._formatRecordMatchResults(mockResults);
            expect(formattedResults.length).toEqual(4);
            expect(formattedResults[0].get('id')).toEqual('1');
            expect(formattedResults[1].get('id')).toEqual('2');
            expect(formattedResults[2].get('id')).toEqual('3');
            expect(formattedResults[3].get('id')).toEqual('5');
        });
    });

    describe('_matchRecords', function() {
        beforeEach(function() {
            sinon.stub(view, '_buildBulkRecordMatchingRequests');
            sinon.stub(app.api, 'call').callsFake(function(method, url, params, callbacks) {
                callbacks.success();
            });
            sinon.stub(view, '_formatRecordMatchResults').returns('record match results');
            sinon.stub(view.layout, 'trigger');
            sinon.stub(view, '_getRecordMatchContext').returns('record match context');
        });

        it('should notify the layout when the record matching is complete', function() {
            view._matchRecords('fake contact');
            expect(view.layout.trigger).toHaveBeenCalledWith('contact:records:matched',
                'fake contact', 'record match results', 'record match context');
        });
    });

    describe('_buildBulkRecordMatchingRequests', function() {
        var contact;

        beforeEach(function() {
            contact = {};
            sinon.stub(view, '_buildInboundRecordMatchingRequests').returns(['inbound requests']);
            sinon.stub(view, '_buildOutboundRecordMatchingRequests').returns(['outbound requests']);
            sinon.stub(view, '_getRecordMatchContext').returns({});
            sinon.stub(view, '_buildGlobalSearchBulkRequest').returns('phone search request');
        });

        it('should return the right requests for outbound calls', function() {
            contact.isInbound = sinon.stub().returns(false);
            sinon.stub(view, 'isCall').returns(true);
            var requests = view._buildBulkRecordMatchingRequests(contact);
            expect(requests).toEqual([
                'outbound requests',
                'phone search request'
            ]);
        });

        it('should return the right requests for inbound calls', function() {
            contact.isInbound = sinon.stub().returns(true);
            sinon.stub(view, 'isCall').returns(true);
            var requests = view._buildBulkRecordMatchingRequests(contact);
            expect(requests).toEqual([
                'inbound requests',
                'phone search request'
            ]);
        });

        it('should return the right requests for inbound chats', function() {
            contact.isInbound = sinon.stub().returns(true);
            sinon.stub(view, 'isCall').returns(false);
            var requests = view._buildBulkRecordMatchingRequests(contact);
            expect(requests).toEqual([
                'inbound requests',
            ]);
        });
    });

    describe('_buildInboundRecordMatchingRequests', function() {
        var contact;
        var context;

        beforeEach(function() {
            contact = {};
            context = {};

            sinon.stub(view, '_buildRecordFetchBulkRequest').callsFake(function(module) {
                return module;
            });
            sinon.stub(view, '_getRecordMatchContext').withArgs(contact).returns(context);
        });

        it('should add a case search if a case number is defined', function() {
            context.sugarCaseNumber = 123;
            expect(view._buildInboundRecordMatchingRequests(contact)).toEqual([
                'Cases'
            ]);
        });

        it('should add a contact search if a contact ID is defined', function() {
            context.sugarContactId = '123';
            expect(view._buildInboundRecordMatchingRequests(contact)).toEqual([
                'Contacts'
            ]);
        });

        it('should add a contact search if a contact email is defined', function() {
            context.sugarContactEmail = 'a@a.com';
            expect(view._buildInboundRecordMatchingRequests(contact)).toEqual([
                'Contacts'
            ]);
        });

        it('should add a contact search if a contact name is defined', function() {
            context.sugarContactName = 'Oscar TheGrouch';
            expect(view._buildInboundRecordMatchingRequests(contact)).toEqual([
                'Contacts'
            ]);
        });

        it('should add both case and contact searches if both are defined in attributes', function() {
            context.sugarCaseNumber = 123;
            context.sugarContactId = '123';
            expect(view._buildInboundRecordMatchingRequests(contact)).toEqual([
                'Cases',
                'Contacts'
            ]);
        });
    });

    describe('_buildOutboundRecordMatchingRequests', function() {
        var contact;
        var context;

        beforeEach(function() {
            contact = {};
            context = {};

            sinon.stub(view, '_getRecordMatchContext').withArgs(contact).returns(context);
            sinon.stub(view, '_buildRecordFetchBulkRequest').callsFake(function(module) {
                return module;
            });
        });

        it('should add a search for a dialed record if there is one', function() {
            context.dialedRecord = app.data.createBean('Contacts', {
                _module: 'Contacts'
            });
            expect(view._buildOutboundRecordMatchingRequests(contact)).toEqual([
                'Contacts'
            ]);
        });

        it('should not add a search for a dialed record if there is not one', function() {
            expect(view._buildOutboundRecordMatchingRequests(contact)).toEqual([]);
        });
    });

    describe('_createRecordMatchContext', function() {
        var contact;
        var rowModelDataLayout;
        var connection;
        var endpoint;

        beforeEach(function() {
            endpoint = {};
            connection = {
                getEndpoint: sinon.stub().returns(endpoint)
            };

            contact = {
                getContactId: sinon.stub().returns('1'),
                isInbound: sinon.stub(),
                getAttributes: sinon.stub(),
                getInitialConnection: sinon.stub().returns(connection)
            };

            rowModelDataLayout = {
                getRowModel: sinon.stub()
            };

            app.sideDrawer = {
                isOpen: sinon.stub(),
                getComponent: sinon.stub().returns(rowModelDataLayout)
            };

            sinon.stub(app.controller.context, 'get')
                .withArgs('layout').returns('record')
                .withArgs('model').returns('record view record');
        });

        describe('for calls', function() {
            beforeEach(function() {
                sinon.stub(view, 'isCall').returns(true);
                sinon.stub(view, 'isChat').returns(false);
                sinon.stub(view, '_getSearchModules').returns(['Contacts', 'Accounts', 'Leads', 'Cases']);
                endpoint.phoneNumber = '+15551234567';
            });

            it('should set the phone search parameters', function() {
                view._createRecordMatchContext(contact);
                expect(view.recordMatchContexts['1'].phoneNumber).toEqual('51234567');
            });

            describe('that are outbound', function() {
                beforeEach(function() {
                    contact.isInbound.returns(false);
                });

                it('should set the dialed record', function() {
                    view.layout.context.set('lastDialedRecord', 'fake dialed record');
                    view._createRecordMatchContext(contact);
                    expect(view.recordMatchContexts['1'].dialedRecord).toEqual('fake dialed record');
                });

                it('should set the side drawer record as the focused record if it is open', function() {
                    app.sideDrawer.isOpen.returns(true);
                    rowModelDataLayout.getRowModel.returns('fake row model');
                    view._createRecordMatchContext(contact);
                    expect(view.recordMatchContexts['1'].focusedRecord).toEqual('fake row model');
                });

                it('should set the record view record as the focused record if side drawer is closed', function() {
                    app.sideDrawer.isOpen.returns(false);
                    view._createRecordMatchContext(contact);
                    expect(view.recordMatchContexts['1'].focusedRecord).toEqual('record view record');
                });
            });
        });

        describe('for chats', function() {
            beforeEach(function() {
                contact.isInbound.returns(true);
                sinon.stub(view, 'isChat').returns(true);
                sinon.stub(view, 'isCall').returns(false);
            });

            it('should set the case number and contact ID from Portal chat', function() {
                contact.getAttributes.returns({
                    sugarCaseNumber: {
                        value: '250'
                    },
                    sugarContactId: {
                        value: '6789'
                    }
                });
                view._createRecordMatchContext(contact);
                expect(view.recordMatchContexts['1'].sugarCaseNumber).toEqual(250);
                expect(view.recordMatchContexts['1'].sugarContactId).toEqual('6789');
            });
        });
    });
});
