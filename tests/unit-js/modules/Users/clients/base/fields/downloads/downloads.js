
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
describe('View.Fields.Base.Users.DownloadsField', function() {
    let app;
    let field;
    let mockPlugins;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('downloads', 'field', 'base', 'detail', 'Users');
        SugarTest.testMetadata.set();

        mockPlugins = {
            Excel: {
                name: 'Sugar Plug-in for Excel',
                desc: 'Integrate Sugar with spreadsheets for better analysis of key metrics.',
                plugins: [
                    {
                        link: 'https://fake-link.com',
                        label: 'Microsoft Excel Plugin'
                    }
                ]
            }
        };

        field = SugarTest.createField({
            client: 'base',
            name: 'downloads',
            type: 'downloads',
            viewName: 'detail',
            module: 'Users',
            loadFromModule: true,
        });
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
    });

    describe('fetchPlugins', function() {
        it('should fetch the list of plugins and render them', function() {
            SugarTest.seedFakeServer();
            SugarTest.server.respondImmediately = true;
            SugarTest.server.respondWith('GET', /.*\/rest\/v10\/me\/plugins.*/,
                [200, {'Content-Type': 'application/json'}, JSON.stringify(mockPlugins)]);
            field.fetchPlugins();
            expect(field._pluginCategories).toEqual(mockPlugins);
            expect(field.$('a').attr('href')).toEqual('https://fake-link.com');
        });
    });
});
