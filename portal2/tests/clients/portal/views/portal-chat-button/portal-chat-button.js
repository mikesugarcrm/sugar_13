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
describe('PortalPortalChatButtonView', function() {
    var app;
    var view;
    var portalChat;
    var interfaceName = 'chat-interface';
    var viewName = 'portal-chat-button';

    beforeEach(function() {
        SugarTest.loadComponent('portal', 'view', viewName);
        SugarTest.loadComponent('portal', 'view', interfaceName);
        app = SUGAR.App;
        app.config.contactInfo = {};
        view = SugarTest.createView('portal', '', viewName);
        portalChat = SugarTest.createView('portal', '', interfaceName);
        window.connect = {
            ChatInterface: {
                initiateChat: function() {}
            }
        };
    });

    afterEach(function() {
        portalChat.dispose();
        view.dispose();
        app.view.reset();
        view = null;
        delete window.connect;
        sinon.restore();
    });

    describe('isSupportedBrowser', function() {
        it('should recognize the agent as supported', function() {
            var firefoxUA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:81.0) Gecko/20100101 Firefox/81.0';
            expect(view.isSupportedBrowser(firefoxUA)).toEqual(true);
        });

        it('should recognize the agent as not supported and display a warning', function() {
            var safariUA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko)' +
                ' Version/14.0 Safari/605.1.15';
            var warningStub = sinon.stub(app.alert, 'show');
            expect(view.isSupportedBrowser(safariUA)).toEqual(false);
            expect(warningStub).toHaveBeenCalled();
        });
    });

    describe('createChatComponent', function() {
        it('should create the chat component', function() {
            var onStub = sinon.stub(app.events, 'on');
            view.createChatComponent();
            expect(onStub.lastCall.args[0]).toEqual('app:login');
            expect(view.portalChat).toBeDefined();
            expect(view.portalChat.type).toEqual('chat-interface');
            view.portalChat.dispose();
        });
    });

    describe('setAvailableFlag', function() {
        beforeEach(function() {
            sinon.stub(app.user, 'get').withArgs('type').returns('support_portal');
            app.config.isServe = true;
            app.config.awsConnectEnablePortalChat = true;
        });

        afterEach(function() {
            delete app.config.isServe;
            delete app.config.awsConnectEnablePortalChat;
        });

        it('should allow displaying of the component', function() {
            view.setAvailableFlag();
            expect(view.isAvailable).toEqual(true);
        });

        it('should prohibit displaying of the component', function() {
            app.config.isServe = false;
            view.setAvailableFlag();
            expect(view.isAvailable).toEqual(false);
        });
    });

    describe('openChat', function() {
        beforeEach(function() {
            sinon.stub(view, 'isSupportedBrowser').returns(true);
            sinon.stub(app.view, 'createView').returns(portalChat);
        });

        it('should be able to create the chat component', function() {
            var chatSpy = sinon.spy(view, 'createChatComponent');
            view.openChat();
            expect(chatSpy).toHaveBeenCalled();
        });

        it('should create a new chat session', function() {
            view.createChatComponent();
            var chatStub = sinon.stub(view.portalChat, 'delayedInstantiateChat');
            view.openChat();
            expect(chatStub).toHaveBeenCalled();
        });

        it('should toggle the chat interface visibility', function() {
            view.createChatComponent();
            view.portalChat.chatSession = {};
            var showStub = sinon.spy(view.portalChat, 'showChat');
            var hideStub = sinon.spy(view.portalChat, 'hideChat');
            view.openChat();
            expect(showStub).toHaveBeenCalled();
            view.openChat();
            expect(hideStub).toHaveBeenCalled();
        });
    });
});
