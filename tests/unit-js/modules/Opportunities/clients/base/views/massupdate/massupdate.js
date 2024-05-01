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
describe('Opportunities.Base.View.MassUpdate', function() {
    var app, view, layout, options, sandbox;

    beforeEach(function () {
        sandbox = sinon.createSandbox();
        options = {
            meta: {
                panels: [{
                    fields: [{
                        name: 'commit_stage',
                        massupdate: true,
                        label: 'foo',
                        type: 'foo'
                    }]
                }]
            }
        };

        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'massupdate');
        layout = SugarTest.createLayout('base', 'Opportunities', 'list', {});
    });

    afterEach(function() {
        app = null;
        view = null;
        layout = null;
        options = null;
        sandbox.restore();
    });

    describe('setMetadata', function(){
        it('should not remove commit_stage', function(){
            sandbox.stub(app.metadata, 'getModule').returns({is_setup: true, fields:[]});
            view = SugarTest.createView('base', 'Opportunities', 'massupdate', options.meta, null, true, layout);
            expect(options.meta.panels[0].fields.length).toEqual(1);
        });

        it('should remove commit_stage', function(){
            sandbox.stub(app.metadata, 'getModule').returns({is_setup: false, fields:[]});
            view = SugarTest.createView('base', 'Opportunities', 'massupdate', options.meta, null, true, layout);
            expect(options.meta.panels[0].fields.length).toEqual(0);
        });
    });
})
