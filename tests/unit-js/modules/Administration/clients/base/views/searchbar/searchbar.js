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

describe('View.Views.Base.AdministrationSearchbarView', function() {
    var app;
    var view;
    var layout;
    var moduleName = 'Administration';

    beforeEach(function() {
        app = SugarTest.app;
        var context = new app.Context();
        SugarTest.loadComponent('base', 'view', 'searchbar', moduleName);
        SugarTest.loadComponent('base', 'layout', 'default');
        layout = SugarTest.createLayout('base', null, 'default', {});
        view = SugarTest.createView(
            'base',
            moduleName,
            'searchbar',
            {name: 'test'},
            context,
            true,
            layout,
            true,
            'base'
        );
        sinon.stub(layout, 'off');
    });

    afterEach(function() {
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;
        sinon.restore();
    });

    describe('_populateLibrary', function() {
        it('should populate the search library', function() {
            var defs = [
                {
                    options: [
                        {
                            label: 'label1',
                            description: 'desc1',
                            link: 'url1'
                        },
                        {
                            label: 'label2',
                            description: 'desc2',
                            link: 'url2'
                        }
                    ]
                },
                {
                    options: [
                        {
                            label: 'label3',
                            description: 'desc3',
                            link: 'url3'
                        }
                    ]
                }
            ];
            var expected = [
                {name: 'label1', description: 'desc1', href: 'url1'},
                {name: 'label2', description: 'desc2', href: 'url2'},
                {name: 'label3', description: 'desc3', href: 'url3'}
            ];
            view.layout.getAdminPanelDefs = function() {return defs;};

            view._populateLibrary();

            expect(view.library).toEqual(expected);
        });
    });
});
