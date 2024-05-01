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
describe('Plugins.CJForms', function() {
    let moduleName = 'Accounts';
    let view;
    let pluginsBefore;
    let app;
    let target;
    let triggerEvent;
    let layoutName = 'record';
    let context;
    let layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName
        });
        context.prepare();
        context.parent = app.context.getContext();
        layout = app.view.createLayout({
            name: layoutName,
            context: context
        });
        view = SugarTest.createView('base', moduleName, 'dri-workflow', {}, context, null, layout);
        app.view.fields = {
            BaseEmailsHtmleditable_tinymceField: {
                prototype: {
                    _applyTemplate: {
                        call: sinon.stub(),
                    }
                }
            },
        };
        pluginsBefore = view.plugins;
        view.plugins = ['CJForms'];
        SugarTest.loadPlugin('CJForms','customer-journey');
        SugarTest.loadPlugin('CJEvents', 'customer-journey');
        SugarTest.app.plugins.attach(view, 'view');
        sinon.stub(view, 'listenTo');
        view.trigger('init');
    });

    afterEach(function() {
        sinon.restore();
        view.plugins = pluginsBefore;
        view.fields = null;
        view.dispose();
        layout.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        app.cache.cutAll();
        view = null;
        delete app.drawer;
    });

    describe('handleForms', function() {
        let callback;
        beforeEach(function() {
            target = {
                id: '99',
                get: sinon.stub(),
            };
            triggerEvent = '';
            callback = sinon.stub();
            sinon.stub(view, 'getFormsOrStageAndJourneyForms').returns(true);
            sinon.stub(app.api, 'buildURL').returns('www.google.com');
        });
        it('should handle the forms logic by calling callback and return', function() {
            let form = {
                action_trigger_type: 'automatic_create',
            };
            sinon.stub(_, 'first').returns(form);
            view.handleForms(target, triggerEvent, callback);
            expect(view.getFormsOrStageAndJourneyForms).toHaveBeenCalled();
            expect(app.api.buildURL).toHaveBeenCalled();
            expect(_.first).toHaveBeenCalled();
        });
        it('should handle the forms logic by calling app.api.call', function() {
            let form = {
                action_trigger_type: 'Accounts',
            };
            view._loadingFormTarget = false;
            sinon.stub(app.alert, 'show');
            sinon.stub(app.api, 'call');
            sinon.stub(_, 'first').returns(form);
            view.handleForms(target, triggerEvent, callback);
            expect(view.getFormsOrStageAndJourneyForms).toHaveBeenCalled();
            expect(app.api.buildURL).toHaveBeenCalled();
            expect(_.first).toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalledOnce();
            expect(app.api.call).toHaveBeenCalledOnce();
            expect(view._loadingFormTarget).toBe(true);
        });
    });

    describe('validateRealtionship', function() {
        it('When modules relationship is valid', function() {
            let relationship = {
                lhs_module: 'Tasks',
                rhs_module: 'Calls'
            };
            expect(view.validateRealtionship(relationship)).toBeTruthy();
        });

        it('when modules relationship is not valid', function() {
            let relationship = {
                lhs_module: 'Tasks'
            };
            expect(view.validateRealtionship(relationship)).toBeFalsy();
        });
    });

    describe('handleFormsSuccess', function() {
        let form;
        let response;
        it('should handle Forms Read API Success by clling createUpdate function', function() {
            form = {
                action_trigger_type: 'manual_update',
                action_type: 'view_',
                ignore_errors: false
            };
            response = {
                parent: {
                    id: 99,
                },
                target: {
                    id: 56,
                },
                module: 'Accounts',
            };
            let callback = sinon.stub();
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(view, 'createUpdate');
            view.handleFormsSuccess(callback, form, response);
            expect(app.alert.dismiss).toHaveBeenCalled();
            expect(view.createUpdate).toHaveBeenCalled();
        });
        it('should handle Forms Read API Success', function() {
            form = {
                action_type: 'view_record',
                ignore_errors: false
            };
            response = {
                parent: {
                    id: 99,
                },
                target: {
                    id: 56,
                },
                module: 'Accounts',
            };
            let callback = sinon.stub();
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(view, 'reRoute');
            view.handleFormsSuccess(callback, form, response);
            expect(app.alert.dismiss).toHaveBeenCalled();
            expect(view.reRoute).toHaveBeenCalled();
        });
        it('id is null should call app.alert.show function', function() {
            form = {
                action_type: 'view_record',
                ignore_errors: false
            };
            response = {
                parent: {
                    id: '',
                },
                target: {
                    id: 56,
                },
                module: 'Accounts',
            };
            let callback = sinon.stub();
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(app.alert, 'show');
            sinon.stub(app.lang, 'get');
            view.handleFormsSuccess(callback, form, response);
            expect(app.alert.dismiss).toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalled();
            expect(app.lang.get).toHaveBeenCalled();
        });
    });

    describe('recordCreateCancel', function() {
        let recordCreate;
        beforeEach(function() {
            app.drawer = {
                close: $.noop,
                count: $.noop,
                open: $.noop
            };
            app.router = {
                navigate: $.noop
            };
            recordCreate = {
                $el: {
                    off: function() {
                        return true;
                    }
                },
                _dismissAllAlerts: sinon.stub(),
            };
            sinon.stub(app.events, 'trigger');
        });
        it('should call app.drawer.close', function() {
            sinon.stub(app.drawer, 'count').returns(true);
            sinon.stub(app.drawer, 'close');
            view.recordCreateCancel(recordCreate);
            expect(app.drawer.count).toHaveBeenCalled();
            expect(app.drawer.close).toHaveBeenCalled();
        });
        it('should call app.router.navigate', function() {
            sinon.stub(app.drawer, 'count').returns(false);
            sinon.stub(app.router, 'navigate');
            view.recordCreateCancel(recordCreate);
            expect(app.drawer.count).toHaveBeenCalled();
            expect(app.router.navigate).toHaveBeenCalled();
        });
    });

    describe('handleSaveSuccess', function() {
        let recordCreate;
        beforeEach(function() {
            app.drawer = {
                close: $.noop,
            };
        });
        it('should call toggleButtons function and Buttons will be re-enabled after save call is complete', function() {
            recordCreate = {
                disposed: false,
                editOnly: true,
                _saveModel: sinon.stub(),
                toggleButtons: sinon.stub(),
                $: sinon.stub().returns(jQuery()),
                hide: sinon.stub(),
                closestComponent: sinon.stub().returns(true),
            };
            sinon.stub(app.drawer, 'close');
            view.handleSaveSuccess(recordCreate);
            expect(app.drawer.close).toHaveBeenCalled();
        });
        it('should call setButtonStates function properly', function() {
            recordCreate = {
                disposed: false,
                editOnly: false,
                STATE: {
                    VIEW: 'Accounts',
                },
                setButtonStates: sinon.stub(),
                unsetContextAction: sinon.stub(),
                toggleEdit: sinon.stub(),
                _saveModel: sinon.stub(),
                $: sinon.stub().returns(jQuery()),
                hide: sinon.stub(),
                closestComponent: sinon.stub().returns(true),
            };
            sinon.stub(app.drawer, 'close');
            view.handleSaveSuccess(recordCreate);
            expect(app.drawer.close).toHaveBeenCalled();
        });
    });

    describe('handleDateTimeField', function() {
        let pf;
        let currentLayout;
        it('should set Date according to selective type', function() {
            pf = {
                childFieldsData: {
                    main_date: {
                        value: '45',
                    },
                    selective_date: {
                        value: 'relative',
                    },
                    int_date: {
                        value: 'relative',
                    },
                    relative_date: {
                        value: 'minutes',
                    }
                },
                actualFieldName: 'populateField',
            };
            currentLayout = {
                model: new Backbone.Model(),
            };
            sinon.stub(view, 'getIncrementedDate').returns('IncrementDate');
            view.handleDateTimeField(pf, currentLayout);
            expect(view.getIncrementedDate).toHaveBeenCalled();
            expect(currentLayout.model.attributes.populateField).toBe('IncrementDate');
        });
        it('should set Date according to selective type', function() {
            pf = {
                childFieldsData: {
                    main_date: {
                        value: 45,
                    },
                    selective_date: {
                        value: 'fixed',
                    },
                    int_date: {
                        value: '',
                    },
                },
                actualFieldName: 'populateField',
            };
            currentLayout = {
                model: new Backbone.Model(),
            };
            view.handleDateTimeField(pf, currentLayout);
            expect(currentLayout.model.attributes.populateField).toBe(45);
        });
    });

    describe('handleFormsForStageSuccess', function() {
        let isPrevStageCompleted;
        let startNewJourney;
        let stage;
        let activity;
        beforeEach(function() {
            activity = {
                get: function() {
                    return 'completed';
                }
            };
            sinon.stub(view, 'getFormsOrStageAndJourneyForms');
        });
        it('should be re-enabled after save call is complete', function() {
            stage = {
                id: 0,
                get: function() {
                    return 'completed';
                },
            };
            view.stages = [{
                activities: {
                    'Calls': {
                        model: {
                            get: function() {
                                return 'completed';
                            }
                        }
                    },
                    'Tasks': {
                        model: {
                            get: function() {
                                return 'completed';
                            },
                        },
                    },
                    'Meetings': {
                        model: {
                            get: function() {
                                return 'completed';
                            }
                        }
                    }
                },
                trigger_event: 'completed',
                data: {
                    forms: 'Completed',
                }
            }
            ];
            sinon.stub(view, 'reloadAllJourneys');
            sinon.stub(view, 'checkNextStageRSA');
            sinon.stub(view, 'handleFormsForJourney');
            sinon.stub(_, 'first').returns(view.stages[stage.id]);
            view.handleFormsForStageSuccess(isPrevStageCompleted, stage, startNewJourney,activity);
            expect(_.first).toHaveBeenCalled();
            expect(view.getFormsOrStageAndJourneyForms).toHaveBeenCalled();
            expect(view.reloadAllJourneys).toHaveBeenCalled();
            expect(view.checkNextStageRSA).toHaveBeenCalled();
            expect(view.handleFormsForJourney).toHaveBeenCalled();
        });

        it('should not re-enabled after the trigger-event is in_progress', function() {
            stage = {
                id: 0,
                get: function() {
                    return 'in_progress';
                },
            };
            view.stages = [{
                activities: {
                    'Calls': {
                        model: {
                            get: function() {
                                return 'completed';
                            }
                        }
                    },
                    'Tasks': {
                        model: {
                            get: function() {
                                return 'completed';
                            },
                        },
                    },
                    'Meetings': {
                        model: {
                            get: function() {
                                return 'completed';
                            }
                        }
                    }
                },
                trigger_event: 'in_progress',
                data: {
                    forms: 'Completed',
                }
            }
            ];
            sinon.stub(_, 'first').returns(view.stages[stage.id]);
            view.handleFormsForStageSuccess(isPrevStageCompleted, stage, startNewJourney,activity);
            expect(_.first).toHaveBeenCalled();
            expect(view.getFormsOrStageAndJourneyForms).toHaveBeenCalled();
        });
    });

    describe('handleFormsForStage', function() {
        it('should Fetch stage for a given id and Uses handleForms() for RSA related to stage', function() {
            let stage = {
                fetch: function() {
                    return true;
                }
            };
            let isPrevStageCompleted = true;
            let stageId = '0';
            let activity = {
                get: function() {
                    return true;
                }
            };
            sinon.stub(view, 'handleFormsForJourneySuccess');
            sinon.stub(app.data, 'createBean').returns(stage);
            view.handleFormsForStage(activity, stageId, isPrevStageCompleted);
            expect(app.data.createBean).toHaveBeenCalled();
        });
    });

    describe('handleFormsForJourney', function() {
        it('should fetch journey for a given id Uses handleForms() for RSA related to journey', function() {
            let stage = {
                id: 0,
                get: function() {
                    return true;
                }
            };
            let journeyId = '0';
            let journey = {
                fetch: function() {
                    return true;
                }
            };
            sinon.stub(view, 'handleFormsForJourneySuccess');
            sinon.stub(app.data, 'createBean').returns(journey);
            view.handleFormsForJourney(stage, journeyId);
            expect(app.data.createBean).toHaveBeenCalled();
        });
    });

    describe('handleFormsForJourneySuccess', function() {
        it('should success of Fetching journey for a given id', function() {
            view.journey = {
                get: function() {
                    return 'completed';
                },
                forms: 'completed',
            };
            let jForm = {
                trigger_event: 'completed',
            };
            sinon.stub(view, 'getFormsOrStageAndJourneyForms');
            sinon.stub(view, 'handleForms');
            sinon.stub(_, 'first').returns(jForm);
            view.handleFormsForJourneySuccess(view.journey);
            expect(view.getFormsOrStageAndJourneyForms).toHaveBeenCalled();
            expect(view.handleForms).toHaveBeenCalled();
        });
    });

    describe('checkNextStageRSA', function() {
        it('should complete RSA for next stage, if stage exists', function() {
            view.stages = [{
                activities: {
                    'Calls': {
                        model: {
                            get: function() {
                                return 'completed';
                            }
                        }
                    },
                    'Tasks': {
                        model: {
                            get: function() {
                                return 'completed';
                            }
                        }
                    },
                    'Meetings': {
                        model: {
                            get: function() {
                                return 'completed';
                            }
                        }
                    }
                },
                model: {
                    get: function() {
                        return 'completed';
                    }
                },
            }
            ];
            activity = {
                get: function() {
                    return 'completed';
                }
            };
            let stageId = 0;
            sinon.stub(_, 'indexOf').returns(-1);
            view.checkNextStageRSA(activity, stageId);
            expect(_.indexOf).toHaveBeenCalled();
        });

        it('should fetch data for given id if event is inProgress', function() {
            view.stages = [{
                activities: {
                    'Calls': {
                        model: {
                            get: function() {
                                return 'completed';
                            }
                        }
                    },
                    'Tasks': {
                        model: {
                            get: function() {
                                return 'completed';
                            }
                        }
                    },
                    'Meetings': {
                        model: {
                            get: function() {
                                return 'completed';
                            }
                        },
                    },
                },
                model: {
                    get: function() {
                        return 'in_progress';
                    },
                },
            }
            ];
            activity = {
                get: function() {
                    return 'completed';
                },
            };
            let stageId = 0;
            sinon.stub(_, 'indexOf').returns(-1);
            sinon.stub(view, 'handleFormsForStage');
            view.checkNextStageRSA(activity, stageId);
            expect(_.indexOf).toHaveBeenCalled();
            expect(view.handleFormsForStage).toHaveBeenCalled();
        });
    });

    describe('reloadAllJourneys', function() {
        it('should reload all Journeys after creation of new Journey', function() {
            view.layout.loadDataClicked = false;
            sinon.stub(view.layout.context, 'set').returns(false);
            sinon.stub(view.layout.context, 'trigger');
            view.reloadAllJourneys();
            expect(view.layout.loadDataClicked).toBe(true);
            expect(view.layout.context.set).toHaveBeenCalled();
            expect(view.layout.context.trigger).toHaveBeenCalled();
        });
    });

    describe('toggleDuplicateCheck', function() {
        let obj;
        let sidebarLayout;
        let mainPaneLayout;
        let mainObj;
        beforeEach(function() {
            app.drawer = {
                _components: $.noop,
            };
            target = {
                module: 'Quotes',
            };
            sinon.stub(_, 'bind');
        });
        it('should Enable or Disable the duplicate Check', function() {
            mainObj = {
                handleCancel: false,
                handleSave: false,
                STATE: {
                    EDIT: 'Accounts',
                },
                setButtonStates: function() {
                    return false;
                },
                toggleEdit: function() {
                    return true;
                },
                enableDuplicateCheck: true,
                hasUnsavedChanges: sinon.stub(),
                model: {
                    revertAttributes: function() {
                        return false;
                    },
                },
            };
            mainPaneLayout = {
                getComponent: function() {
                    return mainObj;
                }
            };
            sidebarLayout = {
                getComponent: function() {
                    return mainPaneLayout;
                }
            };
            obj = {
                getComponent: function() {
                    return sidebarLayout;
                }
            };
            view.layout.loadDataClicked = false;
            sinon.stub(_, 'last').returns(obj);
            view.disposeComponents = sinon.stub();
            expect(view.toggleDuplicateCheck(target)).toBe(obj);
            expect(_.last).toHaveBeenCalled();
            expect(_.bind).toHaveBeenCalled();
            expect(view.disposeComponents).toHaveBeenCalled();
        });
    });

    describe('createUpdate', function() {
        let form;
        let response;
        beforeEach(function() {
            app.drawer = {
                open: $.noop,
            };
            target = {
                module: 'Emails',
                set: function() {
                    return true;
                }
            };
        });
        it('should Enable or Disable the duplicate Check', function() {
            form = {
                action_trigger_type: 'manual_create',
                action_type: 'create_record',
                email_templates_id: '99',
                populate_fields: '[{"id":"99",' +
                '"actualFieldName":"phone_alternate",' + '"actual_id_name":"popuateFieldId",' +
                '"id_value":"8585",' + '"value":"50",' + '"type":"relate"}]',
            };
            response = {
                parent: {
                    _module: 'Emails',
                },
                module: 'Emails',
                emailData: {
                    subject: 'response',
                    body_html: 'html',
                },
                linkName: 'Emails-link',

            };
            mainObj = {
                getComponent: function() {
                    return mainObj;
                }
            };
            mainPaneLayout = {
                getComponent: function() {
                    return mainObj;
                }
            };
            sidebarLayout = {
                getComponent: function() {
                    return mainPaneLayout;
                }
            };
            obj = {
                getComponent: function() {
                    return sidebarLayout;
                },
                model: {
                    set: function() {
                        return true;
                    }
                },
            };
            let populateModel = new Backbone.Model();
            let callback = sinon.stub();
            view.disposeComponents = sinon.stub();
            sinon.stub(view, 'populateModelFromLinkedData').returns(populateModel);
            sinon.stub(view, 'toggleDuplicateCheck').returns(obj);
            sinon.stub(app.data, 'createBean').returns(populateModel);
            view.createUpdate(callback, form, response, target);
            expect(view.disposeComponents).toHaveBeenCalled();
            expect(view.populateModelFromLinkedData).toHaveBeenCalled();
            expect(view.toggleDuplicateCheck).toHaveBeenCalled();
        });
    });
});
