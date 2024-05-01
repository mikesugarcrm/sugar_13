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
describe('Base.View.FilterModuleDropdownSelectionList', function() {
    var app, view, layout, server, langGetStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'filter-module-dropdown');
        SugarTest.loadComponent('base', 'view', 'filter-module-dropdown-selection-list');
        SugarTest.loadHandlebarsTemplate('filter-module-dropdown', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('filter-module-dropdown', 'view', 'base', 'result-partial');
        SugarTest.loadHandlebarsTemplate('filter-module-dropdown', 'view', 'base', 'selection-partial');
        SugarTest.testMetadata.set();

        langGetStub = sinon.stub(app.lang, 'get').callsFake(function(label, module) {
            return module;
        });

        //faking out server so we can use 'simple' layout which doesn't really exist
        //simple layout is a way to use the plain sidecar layout
        server = sinon.fakeServer.create();
        server.respondWith(/\/clients\/base\/layouts\/simple/, [200, {}, '']);
        layout = SugarTest.createLayout('base', 'Contacts', 'simple');
        server.restore();

        view = SugarTest.createView('base', 'Contacts', 'filter-module-dropdown-selection-list', {}, null, null, layout);
    });

    afterEach(function() {
        view.dispose();
        layout.dispose();
        langGetStub.restore();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
    });

    describe('Filter Dropdown', function() {
        it('Should contain all modules that are specified on filterList', function() {
            var options = [{
                id: 'Accounts',
                text: 'Accounts'
            }, {
                id: 'Contacts',
                text: 'Contacts'
            }, {
                id: 'Leads',
                text: 'Leads'
            }];

            view.context.set('filterList', options);
            view.render();

            expect(view.filterNode.data('select2').opts.data).toEqual(options);
        });

        it('Should only have one option if activity stream is shown', function() {
            view.layout.showingActivities = true;
            view.render();

            expect(view.filterNode.data('select2').opts.data).toEqual([{
                id: 'Activities',
                text: 'Contacts'
            }]);
        });

        it('Should set the value to the current module', function() {
            var options = [{
                id: 'Accounts',
                text: 'Accounts'
            }, {
                id: 'Contacts',
                text: 'Contacts'
            }, {
                id: 'Leads',
                text: 'Leads'
            }];

            view.context.set('filterList', options);
            view.render();

            expect(view.filterNode.select2('val')).toBe('Contacts');
        });

        it('Should reload the layout when value has changed', function() {
            var contextTriggerStub = sinon.stub(),
                options = [{
                    id: 'Accounts',
                    text: 'Accounts'
                }, {
                    id: 'Contacts',
                    text: 'Contacts'
                }, {
                    id: 'Leads',
                    text: 'Leads'
                }];

            view.context.on('selection-list:reload', contextTriggerStub);
            view.context.set('filterList', options);
            view.render();

            view.filterNode.select2('val', 'Accounts');
            view.filterNode.trigger('change');

            expect(contextTriggerStub.calledOnce).toBe(true);

            view.context.off('selection-list:reload');
        });
    });
});
