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
describe('Base.View.DocusignRecipientsMultiSelectionList', function() {
    let app;
    let view;
    let context;

    beforeEach(function() {
        app = SugarTest.app;

        context = new app.Context();

        layout = SugarTest.createLayout(
            'base',
            'Accounts',
            'base',
            null,
            context
        );

        view = SugarTest.createView(
            'base',
            'Accounts',
            'docusign-recipients-multi-selection-list',
            {},
            context,
            false,
            layout,
            true
        );
    });

    afterEach(function() {
        view.dispose();
        layout.dispose();
        layout = null;
        context = null;
    });

    describe('initialize', function() {
        it('should remove right columns', function() {
            view.initialize({});
            expect(view.rightColumns).toEqual([]);
        });
    });
});
