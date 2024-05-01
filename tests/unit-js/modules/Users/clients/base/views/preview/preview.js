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
describe('Users.Base.View.Preview', function() {
    let app;
    let layout;
    let view;
    let options;

    beforeEach(function() {
        options = {
            meta: {
                panels: [
                    {
                        fields: [
                            {
                                name: 'foo'
                            }
                        ]
                    }
                ]
            }
        };

        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'preview');
        layout = SugarTest.createLayout('base', 'Users', 'preview', {});
        view = SugarTest.createView('base', 'Users', 'preview', options.meta, null, true, layout);
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

    describe('handleEdit', function() {
        beforeEach(function() {
            sinon.stub(app.alert, 'show');
        });

        it('should show alert to update readonly fields in SugarCloud Settings on IDM instance', function() {
            app.config.idmModeEnabled = true;
            view.handleEdit();
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should not show an alert when not in IDM mode', function() {
            app.config.idmModeEnabled = false;
            view.handleEdit();
            expect(app.alert.show).not.toHaveBeenCalled();
        });
    });
});
