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
describe('View.Layouts.Base.RecipientsListCompositeLayout', function() {
    let app;
    let layout;
    let context;

    beforeEach(function() {
        app = SugarTest.app;

        context = new app.Context();

        layout = SugarTest.createLayout('base', null, 'recipients-list-composite', {}, context);

        sinon.stub(context, 'set');
    });

    afterEach(function() {
        sinon.restore();
    });

    describe('initialize', function() {
        it('should set modules available to switch', function() {
            const options = {
                context: context
            };
            layout.initialize(options);
            expect(options.context.set).toHaveBeenCalledWith('filterList', ['Users', 'Accounts', 'Contacts', 'Leads']);
        });
    });
});
