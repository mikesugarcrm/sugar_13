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
describe('Base.View.OmnichannelRecordLinkView', function() {
    let view;
    let app = SUGAR.App;
    let model;

    beforeEach(function() {
        SugarTest.loadHandlebarsTemplate('omnichannel-record-link', 'view', 'base');
        view = SugarTest.createView('base', '', 'omnichannel-record-link');

        model = app.data.createBean('Contacts');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('setOptions()', function() {
        it('should set options', function() {
            view.setOptions({
                model: model,
                tooltip: 'test tooltip',
                className: 'linked'
            });

            expect(view.model).toEqual(model);
            expect(view.tooltip).toEqual('test tooltip');
            expect(view.className).toEqual('linked');
        });

        it('should set the icon', function() {
            view.setOptions({
                className: 'linked'
            });
            expect(view.icon).toEqual('sicon-check');

            view.setOptions({
                className: 'unlinked'
            });
            expect(view.icon).toEqual('sicon-link');
        });
    });
});
