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
describe('DocuSignEnvelopes.Base.Layouts.TemplatesListComposite', function() {
    let app;
    let context;

    beforeEach(function() {
        app = SugarTest.app;

        context = new app.Context();

        context.set('collection', app.data.createBeanCollection());

        layout = SugarTest.createLayout(
            'base',
            'DocuSignEnvelopes',
            'templates-list-composite',
            null,
            context,
            true
        );

        layout.data = {
            'templates': [
                {
                    'id': '3731b2e6-2e4e-46bb-9701-4363c3452e11',
                    'name': 'Project Agreement'
                },
                {
                    'id': 'fa1091e2-ecd7-464f-aa99-fdee79f99e4a',
                    'name': 'test - 1 document'
                },
                {
                    'id': '1ca18407-7015-4a75-9c90-b9847f398cb8',
                    'name': 'test 1 page s'
                }
            ]
        };
    });

    afterEach(function() {
        layout.dispose();
        layout = null;
        context = null;
    });

    describe('_resetCollection', function() {
        it('should build the collection based on given keyword searched', function() {
            layout._resetCollection('Agreement');
            nrOfTemplatesFound = layout.collection.models.length;
            expect(nrOfTemplatesFound).toBe(0);

            layout._resetCollection('Project');
            nrOfTemplatesFound = layout.collection.models.length;
            expect(nrOfTemplatesFound).toBe(1);

            layout._resetCollection('%1 page');
            nrOfTemplatesFound = layout.collection.models.length;
            expect(nrOfTemplatesFound).toBe(1);

            layout._resetCollection('test');
            nrOfTemplatesFound = layout.collection.models.length;
            expect(nrOfTemplatesFound).toBe(2);
        });
    });
});
