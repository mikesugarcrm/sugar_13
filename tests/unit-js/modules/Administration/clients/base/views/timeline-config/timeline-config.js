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
describe('Administration.Views.TimelineConfig', function() {
    var app;
    var view;
    var viewName = 'timeline-config';
    var moduleName = 'Administration';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', viewName, moduleName);
        var context = app.context.getContext();
        view = SugarTest.createView('base', moduleName, viewName, {}, context, true);
    });

    afterEach(function() {
        view.dispose();
        sinon.restore();
    });

    describe('getAvailableModules', function() {
        it('should set available and enabled modules', function() {
            sinon.stub(app.metadata, 'getModule').returns({
                layouts: {
                    subpanels: {
                        meta: {
                            components: [
                                {
                                    context: {link: 'cases'},
                                    label: 'LBL_CASES'
                                },
                                {
                                    context: {link: 'meetings'},
                                    label: 'LBL_MEETINGS'
                                },
                                {
                                    context: {link: 'bugs'},
                                    label: 'LBL_BUGS'
                                },
                            ]
                        }
                    }
                }
            });

            sinon.stub(app.metadata, 'getHiddenSubpanels').returns([
                'bugs'
            ]);

            var langStub = sinon.stub(app.lang, 'get');
            langStub.withArgs('LBL_CASES', 'Accounts').returns('Cases');
            langStub.withArgs('LBL_MEETINGS', 'Accounts').returns('Meetings');

            var relateStub = sinon.stub(app.data, 'getRelatedModule');
            relateStub.withArgs('Accounts', 'cases').returns('Cases');
            relateStub.withArgs('Accounts', 'meetings').returns('Meetings');
            relateStub.withArgs('Accounts', 'bugs').returns('Bugs');

            view.configModule = 'Accounts';
            view.availableModules = {};
            view.enabledModules = [];
            view.getAvailableModules();

            expect(view.availableModules).toEqual([
                {link: 'cases', label: 'Cases'},
                {link: 'meetings', label: 'Meetings'}
            ]);
            expect(view.enabledModules).toEqual([
                'meetings',
            ]);
        });
    });

    describe('_processLimitAlert', function() {
        let toggleClassStub;
        let attrStub;
        beforeEach(function() {
            toggleClassStub = sinon.stub(jQuery.fn, 'toggleClass');
            attrStub = sinon.stub(jQuery.fn, 'attr');
            sinon.stub(view, '$').returns({
                toggleClass: toggleClassStub,
                attr: attrStub,
            });
        });

        it('should add hidden class if enabledModules.length is 10', function() {
            view.enabledModules.length = 10;
            view._processLimitAlert();
            expect(jQuery.fn.toggleClass).toHaveBeenCalledWith('hidden', false);
            expect(jQuery.fn.attr).toHaveBeenCalledWith('disabled', true);
        });

        it('should remove hidden class if enabledModules.length is lower is 9', function() {
            view.enabledModules.length = 9;
            view._processLimitAlert();
            expect(jQuery.fn.toggleClass).toHaveBeenCalledWith('hidden', true);
            expect(jQuery.fn.attr).toHaveBeenCalledWith('disabled', false);
        });
    });
});
