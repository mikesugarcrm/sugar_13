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
describe('Users.Base.View.RecordList', function() {
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
        layout = SugarTest.createLayout('base', 'Users', 'records', {});
        view = SugarTest.createView('base', 'Users', 'recordlist', options.meta, null, true, layout);
        sinon.stub(view, '_super');
        sinon.stub(app.metadata, 'getModule').returns(['foo']);
        sinon.stub(app.utils, 'setIDMEditableFields');
    });

    afterEach(function() {
        sinon.restore();
        view = null;
        layout = null;
    });

    describe('parseFields', function() {
        it('should call app.utils.setIDMEditableFields', function() {
            view.parseFields();
            expect(app.utils.setIDMEditableFields).toHaveBeenCalledWith(options.meta.panels[0].fields, 'recordlist');
        });
    });
})
