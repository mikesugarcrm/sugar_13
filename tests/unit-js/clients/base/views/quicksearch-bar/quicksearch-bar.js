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
describe('View.Views.Base.QuicksearchBarView', function() {
    var viewName = 'quicksearch-bar',
        view, layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        sinon.stub(SugarTest.app.metadata, 'getModules').callsFake(function() {
            var fakeModuleList = {
                Accounts: {ftsEnabled: true, globalSearchEnabled: true},
                Contacts: {ftsEnabled: true, globalSearchEnabled: true},
                ftsDisabled: {ftsEnabled: false, globalSearchEnabled: true},
                ftsNotSet: {},
                NoAccess: {ftsEnabled: true}
            };
            return fakeModuleList;
        });
        sinon.stub(SugarTest.app.acl, 'hasAccess').callsFake(function(action, module) {
            return module !== 'NoAccess';
        });
        sinon.stub(SugarTest.app.api, 'isAuthenticated').returns(true);

        layout = SugarTest.app.view.createLayout({});
        view = SugarTest.createView('base', null, viewName, null, null, null, layout);
        // Required to define `this.$input` value.
        view._renderHtml();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.restore();
        layout.dispose();
        layout = null;
        view = null;
    });

    describe('navigation', function() {
        var keyDisposeStub, triggerBeforeStub, triggerStub;
        beforeEach(function() {
            keyDisposeStub = sinon.stub(view, 'disposeKeyEvents');
            triggerBeforeStub = sinon.stub(view.layout, 'triggerBefore').callsFake(function() {
                return true;
            });
            triggerStub = sinon.stub(view.layout, 'trigger');
        });

        describe('moveForward', function() {
            it('should run the appropriate functions and fire the appropriate events when moving forward', function() {
                view.moveForward();
                expect(triggerBeforeStub).toHaveBeenCalledOnce();
                expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:next:component');
                expect(keyDisposeStub).toHaveBeenCalledOnce();
                expect(triggerStub).toHaveBeenCalledOnce();
                expect(triggerStub).toHaveBeenCalledWith('navigate:next:component');
            });
        });

        describe('moveBackward', function() {
            it('should run the appropriate functions and fire the appropriate events when moving backward', function() {
                view.moveBackward();
                expect(triggerBeforeStub).toHaveBeenCalledOnce();
                expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:previous:component');
                expect(keyDisposeStub).toHaveBeenCalledOnce();
                expect(triggerStub).toHaveBeenCalledOnce();
                expect(triggerStub).toHaveBeenCalledWith('navigate:previous:component');
            });
        });

        describe('requestFocus', function() {
            it('should trigger navigate:to:component on the layout', function() {
                view.requestFocus();
                expect(triggerStub).toHaveBeenCalledOnce();
                expect(triggerStub).toHaveBeenCalledWith('navigate:to:component', viewName);
            });
        });

        describe('navigate:focus:lost', function() {
            it('should disposeKeyEvents', function() {
                view.trigger('navigate:focus:lost');
                //expect(keyDisposeStub).toHaveBeenCalledOnce();
                expect(keyDisposeStub).toHaveBeenCalled();
            });
        });
    });
});
