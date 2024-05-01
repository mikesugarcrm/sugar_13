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
describe('Activity Stream Bottom View', function() {
    var app;
    var view;
    var activityStreamsEnabledBefore;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('list-bottom', 'view', 'base');
        SugarTest.testMetadata.set();

        activityStreamsEnabledBefore = app.config.activityStreamsEnabled;

        view = SugarTest.createView('base', 'Cases', 'activitystream-bottom');
    });

    afterEach(function() {
        app.config.activityStreamsEnabled = activityStreamsEnabledBefore;
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    it('Should hide when there is no more data to fetch and collection is not empty', function() {
        view.collection = {
            next_offset: -1,
            length: 1,
            off: $.noop
        };

        app.config.activityStreamsEnabled = true;
        view.render();

        expect(view.$el.hasClass('hide')).toBe(true);
    });

    it('Should render and be visible when there is more data to fetch', function() {
        view.collection = {
            next_offset: 10,
            length: 10,
            off: $.noop
        };

        app.config.activityStreamsEnabled = true;
        view.render();

        expect(view.$el.hasClass('hide')).toBe(false);
    });

    it('Should render and be visible when there is no more data to fetch and collection is empty', function() {
        view.collection = {
            next_offset: -1,
            length: 0,
            off: $.noop
        };
        app.config.activityStreamsEnabled = true;
        view.render();

        expect(view.$el.hasClass('hide')).toBe(false);
    });

    it('Should not render layout when activity streams is disabled', function() {
        view.collection = {
            next_offset: 10,
            length: 10,
            off: $.noop
        };

        app.config.activityStreamsEnabled = false;
        view.render();

        expect(view.$el.hasClass('hide')).toBe(true);
    });
});
