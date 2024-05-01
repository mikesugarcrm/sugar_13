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
describe('PortalChatInterfaceView', function() {
    var app;
    var view;
    var setAWSCredentials = function() {
        app.config.awsConnectRegion = 'test';
        app.config.awsConnectApiGatewayUrl = 'https://4onpqhjf82.execute-api.eu-central-1.amazonaws.com/Prod';
        app.config.awsConnectInstanceId = '3j4nrfio23n-efinfid-hakdgns-342mamkgg954';
        app.config.awsConnectContactFlowId = '19dksgha23k-2sj1k4-75klmgf-kfjm265sfq4m';
    };
    var cleanAWSCredentials = function() {
        delete app.config.awsConnectRegion;
        delete app.config.awsConnectApiGatewayUrl;
        delete app.config.awsConnectInstanceId;
        delete app.config.awsConnectContactFlowId;
    };
    var setAWSConfig = function(config) {
        app.config = _.extend(app.config, config);
    };
    var cleanAWSConfig = function(config) {
        app.config = _.omit(app.config, _.keys(config));
    };

    beforeEach(function() {
        var viewName = 'chat-interface';
        var buttonName = 'portal-chat-button';

        app = SUGAR.App;
        app.config.contactInfo = {};

        SugarTest.loadComponent('portal', 'view', viewName);
        SugarTest.loadComponent('portal', 'view', buttonName);
        view = SugarTest.createView('portal', '', viewName);
        view.controlButton = SugarTest.createView('portal', '', buttonName);

        window.connect = {
            ChatInterface: {
                init: function() {},
                initiateChat: function() {}
            },
            ChatSession: {
                setGlobalConfig: function() {}
            }
        };
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        view = null;
        delete window.connect;
        sinon.restore();
    });

    describe('chat visibility', function() {
        it('should toggle the chat visiblity', function() {
            var showStub = sinon.stub();
            expect(view.isExpanded).toEqual(false);
            sinon.stub(view.controlButton, 'toggleButtonState');
            sinon.stub(view.$el, 'find').returns({show: showStub});
            view.showChat();
            expect(view.isExpanded).toEqual(true);
            expect(view.controlButton.toggleButtonState.callCount).toEqual(1);
            expect(view.controlButton.toggleButtonState).toHaveBeenCalledWith('open');
            if (view.chatSession && view.chatSession.contactStatus === 'connected') {
                expect(showStub).toHaveBeenCalled();
            }
            view.hideChat();
            expect(view.isExpanded).toEqual(false);
            expect(view.controlButton.toggleButtonState.callCount).toEqual(2);
            expect(view.controlButton.toggleButtonState).toHaveBeenCalledWith('closed');
        });
    });

    describe('chat configuration', function() {
        it('should not be configured and show the corresponding message', function() {
            var alertStub = sinon.stub(app.alert, 'show');
            expect(view.isConfigured()).toEqual(false);
            expect(alertStub).toHaveBeenCalled();
        });

        it('should be configured', function() {
            setAWSCredentials();
            expect(view.isConfigured()).toEqual(true);
            cleanAWSCredentials();
        });
    });

    describe('creating a chat instance', function() {
        it('should handle succesfull instantiation', function() {
            var chatSession = {
                onChatDisconnected: function() {}
            };
            var chatSessionStub = sinon.stub(chatSession, 'onChatDisconnected');

            view.successHandler(chatSession);
            expect(chatSessionStub).toHaveBeenCalled();
            expect(chatSession.incomingItemDecorator).toBeDefined();
        });

        it('should show a message on failed instantiation', function() {
            var alertStub = sinon.stub(app.alert, 'show');
            view.failureHandler({});
            expect(alertStub).toHaveBeenCalled();
        });

        it('should be able to instantiate a chat', function() {
            var stub = sinon.stub(connect.ChatInterface, 'initiateChat');
            var hideStub = sinon.stub();
            sinon.stub(view.$el, 'find').returns({hide: hideStub});
            view.instantiateChat();
            expect(stub).toHaveBeenCalled();
            expect(hideStub).toHaveBeenCalled();
        });

        it('should load any missing dependencies before creating a new instance', function() {
            sinon.stub(view, 'isConfigured').returns(true);
            var dependencyStub = sinon.stub(view, 'loadDependencies');
            view.initializeChat();
            expect(dependencyStub).toHaveBeenCalled();
        });

        it('should create the chat widget if dependencies have been loaded already', function() {
            sinon.stub(view, 'isConfigured').returns(true);
            var widgetStub = sinon.stub(view, 'createChatWidget');
            view.libraryLoaded = true;
            view.initializeChat();
            expect(widgetStub).toHaveBeenCalled();
        });
    });

    describe('create chat widget', function() {
        it('should create the widget', function() {
            var config = {
                region: 'test'
            };
            var initStub = sinon.stub(connect.ChatInterface, 'init');
            var configStub = sinon.stub(connect.ChatSession, 'setGlobalConfig');
            var instanceStub = sinon.stub(view, 'delayedInstantiateChat');
            var showStub = sinon.stub();
            var hideStub = sinon.stub();
            sinon.stub(view.$el, 'find').returns({
                show: showStub,
                hide: hideStub
            });

            setAWSCredentials();
            view.libraryLoaded = true;
            view.initializeChat();

            expect(initStub).toHaveBeenCalled();
            expect(configStub).toHaveBeenCalledWith(config);
            expect(instanceStub).toHaveBeenCalled();

            if (view.chatSession && view.chatSession.contactStatus === 'connected') {
                expect(showStub).toHaveBeenCalled();
                expect(hideStub).toHaveBeenCalled();
            }

            cleanAWSCredentials();
        });
    });

    describe('load libraries', function() {
        it('should make a call for the first library', function() {
            var loaderSpy = sinon.spy(view, 'loadLibrary');
            var scriptStub = sinon.stub($, 'getScript');
            view.loadDependencies();
            expect(loaderSpy).toHaveBeenCalled();
            expect(scriptStub).toHaveBeenCalled();
        });

        it('should display warning for logException', function() {
            var alertStub = sinon.stub(app.alert, 'show');
            var loggerStub = sinon.stub(app.logger, 'error');
            view.logException({name: 'test'});
            expect(alertStub).toHaveBeenCalled();
            expect(loggerStub).toHaveBeenCalled();
        });
    });

    describe('itemDecorator', function() {
        it('should replace the system message display name', function() {
            var item1 = {
                displayName: 'June Arends',
                message: 'Custom message'
            };
            var item2 = {
                displayName: 'SYSTEM_MESSAGE',
                message: 'Message sent my the system'
            };
            expect(view.itemDecorator(item1)).toEqual(item1);
            expect(view.itemDecorator(item2)).toEqual({
                displayName: 'System Message',
                message: 'Message sent my the system'
            });
        });
    });

    describe('_getChatHeader', function() {
        it('should get header options and compile template with those options', function() {
            var options = {
                logoUrl: 'testurl',
                title: 'testTitle',
                subtitle: 'testSubtitle'
            };
            sinon.stub(view, '_getHeaderOptions').returns(options);
            view.chatHeaderTemplate = sinon.stub();
            view._getChatHeader();
            expect(view.chatHeaderTemplate).toHaveBeenCalledWith(options);
        });
    });

    describe('_getHeaderOptions', function() {
        using('different config options', [
            {awsHeaderTitle: 'test title 1', awsHeaderSubtitle: 'test subtitle 1', awsHeaderImageUrl: 'test url 1'},
            {awsHeaderTitle: 'test title 2', awsHeaderSubtitle: 'test subtitle 2', awsHeaderImageUrl: 'test url 2'},
        ], function(config) {
            it('should return appropriate items from admin config', function() {
                setAWSConfig(config);
                var options = view._getHeaderOptions();
                expect(options).toEqual({
                    logoUrl: config.awsHeaderImageUrl,
                    title: config.awsHeaderTitle,
                    subtitle: config.awsHeaderSubtitle
                });
                cleanAWSConfig(config);
            });
        });
    });

    describe('_getChatFooter', function() {
        it('should get footer options and compile template with those options', function() {
            var options = {
                title: 'testTitle',
            };
            sinon.stub(view, '_getFooterOptions').returns(options);
            view.chatFooterTemplate = sinon.stub();
            view._getChatFooter();
            expect(view.chatFooterTemplate).toHaveBeenCalledWith(options);
        });
    });

    describe('_getFooterOptions', function() {
        using('different config options', [
            {awsFooterTitle: 'test title 1'},
            {awsFooterTitle: 'test title 2'},
        ], function(config) {
            it('should return appropriate items from admin config', function() {
                setAWSConfig(config);
                var options = view._getFooterOptions();
                expect(options).toEqual({
                    title: config.awsFooterTitle
                });
                cleanAWSConfig(config);
            });
        });
    });

    describe('generateChatStyles', function() {
        using('different combinations of admin config', [
            {
                config: {},
                expected: {
                    awsHeaderBackgroundColor: '#265a8d',
                    awsHeaderTitleColor: '#ffffff',
                    awsHeaderSubtitleColor: '#ffffff',
                    awsHeaderImageUrl: '../themes/default/images/company_logo_inverted.png',
                    awsFooterTitleColor: '#9aa5ad',
                    awsEndChatButtonTextColor: '#000000',
                    awsEndChatButtonWidth: '140',
                    awsEndChatButtonHeight: '40',
                    awsEndChatButtonFill: '#0679c8',
                    awsMessageCustomerBubbleColor: '#dae8f7',
                    awsMessageAgentBubbleColor: '#d5ece0',
                    awsMessageTextColor: '#000000'
                }
            }, {
                config: {
                    awsHeaderBackgroundColor: '#000000',
                    awsEndChatButtonWidth: '100',
                    awsMessageCustomerBubbleColor: '#ffffff'
                },
                expected: {
                    awsHeaderBackgroundColor: '#000000',
                    awsHeaderTitleColor: '#ffffff',
                    awsHeaderSubtitleColor: '#ffffff',
                    awsHeaderImageUrl: '../themes/default/images/company_logo_inverted.png',
                    awsFooterTitleColor: '#9aa5ad',
                    awsEndChatButtonTextColor: '#000000',
                    awsEndChatButtonWidth: '100',
                    awsEndChatButtonHeight: '40',
                    awsEndChatButtonFill: '#0679c8',
                    awsMessageCustomerBubbleColor: '#ffffff',
                    awsMessageAgentBubbleColor: '#d5ece0',
                    awsMessageTextColor: '#000000'
                }
            }, {
                config: {
                    awsFooterTitleColor: '#9aa5ae',
                    awsEndChatButtonTextColor: '#000001',
                    awsEndChatButtonWidth: '150',
                    awsEndChatButtonHeight: '30',
                    awsEndChatButtonFill: '#0679dd',
                    awsMessageCustomerBubbleColor: '#ffffee',
                    awsMessageAgentBubbleColor: '#d5ecff',
                    awsMessageTextColor: '#000011'
                },
                expected: {
                    awsHeaderBackgroundColor: '#265a8d',
                    awsHeaderTitleColor: '#ffffff',
                    awsHeaderSubtitleColor: '#ffffff',
                    awsHeaderImageUrl: '../themes/default/images/company_logo_inverted.png',
                    awsFooterTitleColor: '#9aa5ae',
                    awsEndChatButtonTextColor: '#000001',
                    awsEndChatButtonWidth: '150',
                    awsEndChatButtonHeight: '30',
                    awsEndChatButtonFill: '#0679dd',
                    awsMessageCustomerBubbleColor: '#ffffee',
                    awsMessageAgentBubbleColor: '#d5ecff',
                    awsMessageTextColor: '#000011'
                }
            }
        ], function(values) {
            it('should only override values provided by admin', function() {
                setAWSConfig(values.config);
                var styles = view.generateChatStyles();
                expect(styles).toEqual(values.expected);
                cleanAWSConfig(values.config);
            });
        });
    });

    describe('_warnOnRefresh', function() {
        it('should return a warning if the chat room is active', function() {
            view.chatSession = {region: 'test'};
            expect(view._warnOnRefresh()).toEqual('LBL_PORTAL_CHAT_WARN_ACTIVE_CCP_UNSAVED_CHANGES');
        });
        it('should not return a warning if the chat is inactive', function() {
            view.chatSession = null;
            expect(view._warnOnRefresh()).toEqual(undefined);
        });
    });
});
