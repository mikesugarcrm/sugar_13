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
describe('Base.View.OmnichannelDetail', function() {
    var view;
    var layout;
    var ccp;
    var getComponentStub;
    var callsModel;
    var app = SUGAR.App;

    beforeEach(function() {
        app.routing.start();

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('omnichannel-detail', 'view', 'base');
        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'layout', 'omnichannel-console');
        layout = SugarTest.createLayout('base', 'layout', 'omnichannel-console', {});
        view = SugarTest.createView('base', 'Contacts', 'omnichannel-detail', null, null, false, layout);

        window.connect = {
            ContactType: {
                VOICE: 'voice',
                CHAT: 'chat'
            }
        };

        callsModel = app.data.createBean('Calls', {date_start: 'start_time'});
        ccp = {
            resize: $.noop,
            _updateConnectionRecord: $.noop,
            connectionRecords: {
                contact_id: callsModel
            },
            activeContact: {
                getContactId: sinon.stub().returns('test')
            }
        };
        getComponentStub = sinon.stub(view.layout, 'getComponent');

        sinon.stub(app.utils, 'formatNameModel').returns('formatted name');
        sinon.stub(app.metadata, 'getModule').withArgs('Calls').returns({
            fields: {
                date_start: {
                    required: true
                },
                parent_name: {
                    options: 'parent_type_display'
                }
            }
        });

        sinon.stub(app.lang, 'getAppListKeys').withArgs('parent_type_display').returns(
            ['Accounts', 'Leads', 'Contacts', 'Opportunities', 'Cases']
        );
        sinon.stub(view, '_getModuleForContact').returns('Calls');
    });

    afterEach(function() {
        app.router.stop();
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('render', function() {
        it('should call _toggleEnabledFields on render', function() {
            sinon.stub(view, '_super');
            sinon.stub(view, '_toggleEnabledFields');
            view.layout.isCallActive = true;
            view.render();

            expect(view._toggleEnabledFields).toHaveBeenCalledWith(true);
        });
    });

    describe('_toggleEnabledFields', function() {
        var fieldSetModeStub;

        beforeEach(function() {
            sinon.stub(view.model, 'clear');
            fieldSetModeStub = sinon.stub();
            view.fields = [{
                $el: 'el',
                setMode: fieldSetModeStub,
                dispose: $.noop
            }];
        });

        afterEach(function() {
            fieldSetModeStub = null;
        });

        it('should clear the model when disabling fields', function() {
            view.areFieldsEnabled = true;

            view._toggleEnabledFields(false);

            expect(view.model.clear).toHaveBeenCalled();
        });

        it('should call setMode on fields when disabling', function() {
            view.areFieldsEnabled = true;

            view._toggleEnabledFields(false);

            expect(fieldSetModeStub).toHaveBeenCalledWith('disabled');
        });

        it('should call setMode on fields when enabling', function() {
            view.areFieldsEnabled = false;

            view._toggleEnabledFields(true);

            expect(fieldSetModeStub).toHaveBeenCalledWith('edit');
        });
    });

    describe('showTab', function() {
        var dbSwitch;

        beforeEach(function() {
            dbSwitch = {
                setModel: sinon.stub()
            };
            sinon.stub(view, 'getModel').returns('testModel');
            getComponentStub.withArgs('omnichannel-dashboard-switch').returns(dbSwitch);
        });

        using('different modules and aws contact IDs', [
            {
                module: 'Cases',
                awsId: 'abcd-1234-efgh-5678'
            }, {
                module: 'Opportunities',
                awsId: null,
            }, {
                module: null,
                awsId: 'abcd-1234-efgh-5678'
            },
        ], function(values) {
            it('should call getModel and setModel with expected values', function() {
                view.currentContactId = values.awsId;
                var mockEvent = {
                    target: {
                        getAttribute: function() { return values.module; }
                    }
                };
                view.showTab(mockEvent);
                expect(view.getModel).toHaveBeenCalledWith(null, values.module);
                expect(dbSwitch.setModel).toHaveBeenCalledWith(values.awsId, 'testModel');
            });
        });
    });

    describe('set component title and model', function() {
        var contact;

        beforeEach(function() {
            contact = {
                getType: $.noop,
                getContactId: function() {return 'contact_id';}
            };
            getComponentStub.withArgs('omnichannel-ccp').returns(ccp);
        });

        it('should set the chat specific title', function() {
            sinon.stub(contact, 'getType').returns('chat');
            view.setSummaryTitle(contact);
            expect(view.summaryTitle).toEqual('LBL_OMNICHANNEL_CHAT_SUMMARY');
        });

        it('should set the chat specific title', function() {
            sinon.stub(contact, 'getType').returns('voice');
            view.setSummaryTitle(contact);
            expect(view.summaryTitle).toEqual('LBL_OMNICHANNEL_CALL_SUMMARY');
        });

        it('should set the summary model', function() {
            var titleStub = sinon.stub(view, 'setSummaryTitle');

            view.setSummary(contact);
            expect(titleStub).toHaveBeenCalled();
            expect(view.model).toEqual(callsModel);
        });

        it('should create an empty bean model if not found', function() {
            delete view.model;
            var titleStub = sinon.stub(view, 'setSummaryTitle');
            sinon.stub(view, '_isCall').returns(true);
            contact.getContactId = function() {return 'id';};

            view.setSummary(contact);
            expect(titleStub).toHaveBeenCalled();
            expect(view.model.attributes).toEqual({});
        });

        it('should set the initial summary if a model has been found', function() {
            var metaStub = sinon.stub(view, 'updateMetadata');
            var renderStub = sinon.stub(view, 'render');
            var resizeStub = sinon.stub(view, '_resizeCCP');

            view._setInitialSummary(contact);
            expect(metaStub).toHaveBeenCalledWith(contact);
            expect(view.model).toEqual(callsModel);
            expect(renderStub).toHaveBeenCalled();
            expect(resizeStub).toHaveBeenCalled();
        });

        it('should not set the initial summary if a model has not been found', function() {
            var metaStub = sinon.stub(view, 'updateMetadata');
            var renderStub = sinon.stub(view, 'render');
            var resizeStub = sinon.stub(view, '_resizeCCP');
            contact.getContactId = function() {return 'id';};

            view._setInitialSummary(contact);
            expect(metaStub).not.toHaveBeenCalled();
            expect(renderStub).not.toHaveBeenCalled();
            expect(resizeStub).not.toHaveBeenCalled();
        });
    });

    describe('updateMetadata', function() {
        beforeEach(function() {
            sinon.stub(app.metadata, 'getView');
        });

        using('different false values for contact and ccp', [
            {
                ccp: undefined,
                contact: undefined,
                expectedModule: 'Calls'
            }, {
                ccp: undefined,
                contact: 'Test',
                expectedModule: 'Calls'
            }, {
                ccp: 'Test',
                contact: undefined,
                expectedModule: 'Calls'
            }
        ], function(values) {
            it('should not set currentContactModule if either value is undefined', function() {
                getComponentStub.withArgs('omnichannel-ccp').returns(values.ccp);
                view.updateMetadata(values.contact);
                expect(view.currentContactModule).toEqual(values.expectedModule);
                expect(app.metadata.getView).toHaveBeenCalledWith(
                    values.expectedModule,
                    'omnichannel-detail'
                );
            });
        });

        it('should call getView with appropriate view when contact and ccp are defined', function() {
            var mockCcp = {
                contactTypeModule: {
                    TestType: 'TestModule'
                }
            };
            var mockContact = {
                getType: sinon.stub().returns('TestType')
            };
            getComponentStub.withArgs('omnichannel-ccp').returns(mockCcp);
            view.updateMetadata(mockContact);
            expect(mockContact.getType).toHaveBeenCalled();
            expect(app.metadata.getView).toHaveBeenCalledWith('TestModule', 'omnichannel-detail');
        });
    });

    describe('general view methods', function() {
        var contact;

        beforeEach(function() {
            contact = {
                getType: $.noop,
                getContactId: $.noop
            };
            getComponentStub.withArgs('omnichannel-ccp').returns(ccp);
        });

        it('should toggle the view element', function() {
            var toggleStub = sinon.stub(view.$el, 'toggle');
            view.toggle();
            expect(toggleStub).toHaveBeenCalled();
        });

        it('should remove cached models for a specific contact', function() {
            view.modelsByContactId = {
                contact1: {
                    'Contacts': app.data.createBean('Contacts'),
                    'Cases': app.data.createBean('Cases')
                },
                contact2: {
                    'Cases': app.data.createBean('Cases')
                }
            };
            view.currentContactId = 'contact1';
            sinon.stub(contact, 'getContactId').returns('contact1');

            view.removeContact(contact.getContactId());
            expect(view.modelsByContactId.contact1).toEqual(undefined);
            expect(Object.keys(view.modelsByContactId).length).toEqual(1);
        });

        it('should set and show a specific contact model', function() {
            view.contactModels = {};
            var contactModel = app.data.createBean('Contacts', {_module: 'Contacts'});
            sinon.stub(contact, 'getContactId').returns('contact1');
            view.currentContactId = 'contact1';
            sinon.stub(view, '_setGuestFieldFromModel');
            sinon.stub(view, '_setRelatedToFieldFromModel');
            var showStub = sinon.stub(view, 'showContact');

            view.setContactModel(contact, contactModel);
            expect(showStub).toHaveBeenCalledWith(contact);
        });

        it('should set and show a specific case model', function() {
            view.caseModels = {};
            var caseModel = app.data.createBean('Cases', {_module: 'Cases'});
            sinon.stub(contact, 'getContactId').returns('contact1');
            view.currentContactId = 'contact1';
            sinon.stub(view, '_setGuestFieldFromModel');
            sinon.stub(view, '_setRelatedToFieldFromModel');
            var showStub = sinon.stub(view, 'showContact');

            view.setCaseModel(contact, caseModel);
            expect(showStub).toHaveBeenCalledWith(contact);
        });

        it('should return a case model for a registered contact', function() {
            sinon.stub(contact, 'getContactId').returns('contact1');
            var caseModel = app.data.createBean('Cases');
            view.modelsByContactId = {
                contact1: {
                    'Cases': caseModel
                }
            };

            var model = view.getCaseModel(contact);
            expect(model).toEqual(caseModel);
        });

        it('should return the current case model if there is no contact provided', function() {
            var caseModel = app.data.createBean('Cases', {_module: 'Cases'});
            view.currentContactId = 'contact3';
            view.modelsByContactId = {
                contact3: {
                    'Cases': caseModel
                }
            };

            var model = view.getCaseModel();
            expect(model).toEqual(caseModel);
        });

        it('should return a contact model for a registered contact', function() {
            sinon.stub(contact, 'getContactId').returns('contact1');
            var contactModel = app.data.createBean('Contacts', {_module: 'Contacts'});
            view.modelsByContactId = {
                contact1: {
                    'Contacts': contactModel
                }
            };

            var model = view.getContactModel(contact);
            expect(model).toEqual(contactModel);
        });

        it('should return the current contact model if there is no contact provided', function() {
            var contactModel = app.data.createBean('Contacts', {_module: 'Contacts'});
            view.currentContactId = 'contact3';
            view.modelsByContactId = {
                contact3: {
                    'Contacts': contactModel
                }
            };

            var model = view.getContactModel();
            expect(model).toEqual(contactModel);
        });

        it('should update the metadata and trigger a render', function() {
            var updateStub = sinon.stub(view, 'updateMetadata');
            var renderStub = sinon.stub(view, 'render');
            view._updateAndRender();
            expect(updateStub).toHaveBeenCalled();
            expect(renderStub).toHaveBeenCalled();
        });

        it('should trigger a ccp resize', function() {
            var resizeStub = sinon.stub(ccp, 'resize');
            view._resizeCCP();
            expect(resizeStub).toHaveBeenCalled();
        });
    });

    describe('setModel', function() {
        var contact;
        var modelToSet;

        beforeEach(function() {
            contact = {
                getContactId: sinon.stub().returns('id1')
            };
            modelToSet = app.data.createBean('Accounts');

            sinon.stub(view, '_linkRecord');
            sinon.stub(view, 'showContact');
        });

        it('should try to link the model to the contact\'s Call/Message record', function() {
            view.setModel(contact, modelToSet);
            expect(view._linkRecord).toHaveBeenCalledWith(contact, modelToSet);
        });

        it('should re-render the contact if it is the active one', function() {
            view.currentContactId = 'id1';
            view.setModel(contact, modelToSet);
            expect(view.showContact).toHaveBeenCalledWith(contact);
        });

        it('should not re-render the contact if it is not the active one', function() {
            view.currentContactId = 'id2';
            view.setModel(contact, modelToSet);
            expect(view.showContact).not.toHaveBeenCalled();
        });
    });

    describe('model getters', function() {
        var note = app.data.createBean('Notes', {_module: 'Notes'});
        var tracker = app.data.createBean('Trackers', {_module: 'Trackers'});
        var acase = app.data.createBean('Cases', {_module: 'Cases'});
        var pli = app.data.createBean('PurchasedLineItems', {_module: 'PurchasedLineItems'});

        beforeEach(function() {
            view.modelsByContactId = {
                connect1: {
                    'Notes': note,
                    'Trackers': tracker
                },
                connect2: {
                    'Cases': acase,
                    'PurchasedLineItems': pli
                }
            };
        });

        describe('getModel', function() {
            using('different contact ids and modules', [
                {
                    id: 'connect1',
                    module: 'Notes',
                    expected: note
                }, {
                    id: 'connect2',
                    module: 'PurchasedLineItems',
                    expected: pli
                }, {
                    id: 'connect1',
                    module: 'Cases',
                    expected: undefined
                }
            ], function(values) {
                it('should return the expected model when contact exists', function() {
                    var contact = {
                        getContactId: function() { return values.id; }
                    };
                    var actual = view.getModel(contact, values.module);
                    expect(actual).toBe(values.expected);
                });
            });

            it('should be undefined when using an unsetContactId', function() {
                var contact = {
                    getContactId: function() { return 'connect4'; }
                };
                var actual = view.getModel(contact, 'Notes');
                expect(actual).toBeUndefined();
            });

            it('should return module for current contact if no contact passed in', function() {
                view.currentContactId = 'connect2';
                var actual = view.getModel(null, 'Cases');
                expect(actual).toEqual(acase);
            });
        });

        describe('getModels', function() {
            it('should return all models for the given contact', function() {
                var contact = {
                    getContactId: function() { return 'connect1'; }
                };
                expect(view.getModels(contact)).toEqual({
                    'Notes': note,
                    'Trackers': tracker
                });
            });
        });
    });

    describe('_linkRecord', function() {
        var contact;
        var model;

        beforeEach(function() {
            contact = {
                getContactId: sinon.stub().returns('id1')
            };

            sinon.stub(view, 'setModel');
            sinon.stub(view, 'getField').returns({
                setValue: function() {}
            });
            sinon.stub(view.getField('invitees'), 'setValue');
        });

        describe('on a record whose module is a valid guest module', function() {
            beforeEach(function() {
                model = app.data.createBean('Contacts');
                model.set({
                    _module: 'Contacts',
                    id: '12345',
                    name: 'Contact Name'
                });

                let ccp = {
                    getActiveContact: function() {
                        return 'my-contact';
                    }
                };
                getComponentStub.withArgs('omnichannel-ccp').returns(ccp);
            });

            describe('during a call', function() {
                var callModel;

                beforeEach(function() {
                    callModel = app.data.createBean('Calls');
                    sinon.stub(view, '_getModelForContact').withArgs(contact).returns(callModel);
                    sinon.stub(view, '_isCall').returns(true);
                });

                it('should set the Guest field of the Call record', function() {
                    view._linkRecord(contact, model);
                    expect(view.getField('invitees').setValue).toHaveBeenCalled();
                });

                it('should remove the previously-set Guest if there was one', function() {
                    sinon.stub(view, '_getContactId').returns('my_contact_id');
                    sinon.stub(callModel, 'save');
                    view.modelsByContactId = {
                        my_contact_id: {
                            Contacts: model
                        }
                    };
                    callModel.set('contacts', {id: '111'});

                    view._removePreviousLinkedGuest(contact, model);

                    expect(view.modelsByContactId.my_contact_id.Contacts).toBeUndefined();
                });
            });

            describe('during a chat', function() {
                var nonGuestModel;
                var chatModel;

                beforeEach(function() {
                    nonGuestModel = app.data.createBean('Accounts');
                    nonGuestModel.set({
                        _module: 'Accounts',
                        id: '54321',
                        name: 'Account Name'
                    });

                    chatModel = app.data.createBean('Messages');
                    sinon.stub(view, '_getModelForContact').withArgs(contact).returns(chatModel);
                    sinon.stub(view, '_isCall').returns(false);
                });

                it('should set the Relates To field of the Message record for non-Guest records', function() {
                    view._linkRecord(contact, nonGuestModel);
                    expect(chatModel.get('parent_type')).toEqual('Accounts');
                    expect(chatModel.get('parent_id')).toEqual('54321');
                    expect(chatModel.get('contact_id')).toBeUndefined();
                    expect(chatModel.get('contact_name')).toBeUndefined();
                    expect(chatModel.get('contacts')).toBeUndefined();
                });

                it('should set the Guest field of the Message record', function() {
                    view._linkRecord(contact, model);
                    expect(view.getField('invitees').setValue).toHaveBeenCalled();
                });
            });
        });

        describe('on a record whose module is not a valid guest module', function() {
            beforeEach(function() {
                model = app.data.createBean('Cases');
                model.set({
                    _module: 'Cases',
                    id: '54321'
                });

                let ccp = {
                    getActiveContact: function() {
                        return 'my-contact';
                    }
                };

                getComponentStub.withArgs('omnichannel-ccp').returns(ccp);
            });

            describe('during a call', function() {
                var callModel;

                beforeEach(function() {
                    callModel = app.data.createBean('Calls');

                    sinon.stub(view, '_getModelForContact').withArgs(contact).returns(callModel);
                    sinon.stub(view, '_isCall').returns(true);
                });

                it('should set the Relates To field of the Call record', function() {
                    view._linkRecord(contact, model);
                    expect(callModel.get('parent_type')).toEqual('Cases');
                    expect(callModel.get('parent_id')).toEqual('54321');
                });
            });

            describe('during a chat', function() {
                var chatModel;

                beforeEach(function() {
                    chatModel = app.data.createBean('Messages');

                    sinon.stub(view, '_getModelForContact').withArgs(contact).returns(chatModel);
                    sinon.stub(view, '_isCall').returns(false);
                });

                it('should set the Relates To field of the Message record', function() {
                    view._linkRecord(contact, model);
                    expect(chatModel.get('parent_type')).toEqual('Cases');
                    expect(chatModel.get('parent_id')).toEqual('54321');
                });
            });
        });
    });

    describe('_handleContactRecordsMatched', function() {
        var contact;
        var records;

        beforeEach(function() {
            records = [];
            contact = {
                isInbound: sinon.stub(),
                getAttributes: sinon.stub()
            };
            sinon.stub(view, 'setModel');
        });

        describe('for inbound', function() {
            beforeEach(function() {
                contact.isInbound.returns(true);
            });
            describe('when the contact has Sugar case and contact attributes from Portal chat', function() {
                var contactBean;
                var caseBean;
                var context;

                beforeEach(function() {
                    context = {
                        sugarContactId: '12345',
                        sugarCaseNumber: 678910
                    };

                    contactBean = app.data.createBean('Contacts', {id: '12345'});
                    contactBean.module = 'Contacts';
                    records.push(contactBean);
                    caseBean = app.data.createBean('Cases', {case_number: 678910});
                    caseBean.module = 'Cases';
                    records.push(caseBean);
                });

                it('should link the Contact as a Guest and the Case as a Related To', function() {
                    view._handleContactRecordsMatched(contact, records, context);
                    expect(view.setModel).toHaveBeenCalledWith(contact, contactBean);
                    expect(view.setModel).toHaveBeenCalledWith(contact, caseBean);
                });
            });
        });

        describe('for outbound', function() {
            var context;
            var contactBean;
            var accountBean;

            beforeEach(function() {
                contactBean = app.data.createBean('Contacts');
                contactBean.module = 'Contacts';
                records.push(contactBean);

                accountBean = app.data.createBean('Accounts');
                accountBean.module = 'Accounts';
                records.push(accountBean);

                context = {};
                contact.isInbound.returns(false);
            });

            describe('when the dialed record is a valid Guest module', function() {
                beforeEach(function() {
                    context.dialedRecord = contactBean;
                    context.focusedRecord = accountBean;
                });

                it('should link the dialed record as a Guest and the focused record as a Related To', function() {
                    view._handleContactRecordsMatched(contact, records, context);
                    expect(view.setModel).toHaveBeenCalledWith(contact, contactBean);
                    expect(view.setModel).toHaveBeenCalledWith(contact, accountBean);
                });
            });

            describe('when the dialed record is a valid Related To module', function() {
                beforeEach(function() {
                    context.dialedRecord = accountBean;
                });

                it('should link the dialed record as a Related To', function() {
                    view._handleContactRecordsMatched(contact, records, context);
                    expect(view.setModel).toHaveBeenCalledWith(contact, accountBean);
                });
            });

            describe('when there is no specific dialed record', function() {
                beforeEach(function() {
                    context.focusedRecord = accountBean;
                });

                it('should set the focused record as a Related To if it is valid', function() {
                    view._handleContactRecordsMatched(contact, records, context);
                    expect(view.setModel).toHaveBeenCalledWith(contact, accountBean);
                });
            });
        });
    });
});
