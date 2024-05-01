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
describe('Users.Base.View.Record', function() {
    let app;
    let layout;
    let view;
    let options;

    beforeEach(function() {
        options = {
            meta: {
                panels: [
                    {
                        fields: {
                            foo: {
                                name: 'foo'
                            }
                        }
                    }
                ]
            }
        };

        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'record');
        layout = SugarTest.createLayout('base', 'Users', 'record', {});
        view = SugarTest.createView('base', 'Users', 'record', options.meta, null, true, layout);
        sinon.stub(view, '_super');
        sinon.stub(app.utils, 'setIDMEditableFields');
    });

    afterEach(function() {
        sinon.restore();
        view = null;
        layout = null;
    });

    describe('initialize()', function() {
        it('should call app.utils.setIDMEditableFields', function() {
            view.initialize(options);

            expect(app.utils.setIDMEditableFields).toHaveBeenCalled();
        });
    });

    describe('editClicked', function() {
        beforeEach(function() {
            sinon.stub(app.alert, 'show');
        });

        it('should show alert to update readonly fields in SugarCloud Settings on IDM instance', function() {
            app.config.idmModeEnabled = true;
            view.editClicked();
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should not show an alert when not in IDM mode', function() {
            app.config.idmModeEnabled = false;
            view.editClicked();
            expect(app.alert.show).not.toHaveBeenCalled();
        });
    });

    describe('_initUserTypeViews', function() {
        let groupMeta = {
            panels: [
                {
                    fields: [
                        {name: 'group-field'}
                    ]
                }
            ]
        };
        let portalMeta = {
            panels: [
                {
                    fields: [
                        {name: 'portal-field'}
                    ]
                }
            ]
        };

        beforeEach(function() {
            sinon.stub(app.metadata, 'getView').withArgs(null, 'record').returns({})
                .withArgs('Users', 'record-group').returns(groupMeta)
                .withArgs('Users', 'record-portalapi').returns(portalMeta);
            sinon.stub(view, 'render');
        });

        it('should implement a listener to update view meta when is_group is true', function() {
            view._initUserTypeViews();
            view.model.set('is_group', true);
            expect(view.meta.panels[0].fields[0].name).toEqual('group-field');
        });

        it('should implement a listener to update view meta when portal_only is true', function() {
            view._initUserTypeViews();
            view.model.set('portal_only', true);
            expect(view.meta.panels[0].fields[0].name).toEqual('portal-field');
        });
    });
});
