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
describe('Base.View.OmnichannelHeader', function() {
    var view;
    var layout;
    var app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('omnichannel-header', 'view', 'base');
        SugarTest.testMetadata.set();
        app.routing.start();
        layout = SugarTest.createLayout('base', 'Contacts', 'omnichannel-console', null, null, false);
        view = SugarTest.createView('base', 'Contacts', 'omnichannel-header', null, null, false, layout);
    });

    afterEach(function() {
        app.router.stop();
        sinon.restore();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe('updateShowHideToggleModeButton', function() {
        var toggleClassStub;

        beforeEach(function() {
            toggleClassStub = sinon.stub();
            sinon.stub(view, '$').callsFake(function() {
                return {
                    toggleClass: toggleClassStub
                };
            });
        });

        afterEach(function() {
            toggleClassStub = null;
        });

        it('should toggle class hidden when call is active', function() {
            view.updateShowHideToggleModeButton(true);

            expect(toggleClassStub).toHaveBeenCalledWith('hidden', false);
        });

        it('should toggle class hidden when call is inactive', function() {
            view.updateShowHideToggleModeButton(false);

            expect(toggleClassStub).toHaveBeenCalledWith('hidden', true);
        });
    });

    describe('updateToggleModeButton', function() {
        var toggleModeButton;
        var toggleModeButtonIcon;

        beforeEach(function() {
            toggleModeButtonIcon = {
                removeClass: sinon.stub().returnsThis(),
                addClass: sinon.stub().returnsThis()
            };
            toggleModeButton = {
                show: sinon.stub(),
                hide: sinon.stub(),
                find: sinon.stub().returns(toggleModeButtonIcon),
                attr: sinon.stub()
            };
            sinon.stub(view, '$').returns(toggleModeButton);
        });

        it('should hide the toggleMode button in ccpOnly mode', function() {
            view.updateToggleModeButton('ccpOnly');
            expect(toggleModeButton.hide).toHaveBeenCalled();
        });

        it('should show the toggleMode button and update the icon and tooltip in compact mode', function() {
            view.updateToggleModeButton('compact');
            expect(toggleModeButton.show).toHaveBeenCalled();
            expect(toggleModeButton.attr).toHaveBeenCalled();
            expect(toggleModeButtonIcon.removeClass).toHaveBeenCalled();
            expect(toggleModeButtonIcon.addClass).toHaveBeenCalled();
        });

        it('should show the toggleMode button and update the icon and tooltip in full mode', function() {
            view.updateToggleModeButton('full');
            expect(toggleModeButton.show).toHaveBeenCalled();
            expect(toggleModeButton.attr).toHaveBeenCalled();
            expect(toggleModeButtonIcon.removeClass).toHaveBeenCalled();
            expect(toggleModeButtonIcon.addClass).toHaveBeenCalled();
        });
    });
});
