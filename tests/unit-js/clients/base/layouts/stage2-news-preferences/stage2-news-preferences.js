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
describe('View.Layouts.Base.BaseStage2NewsPreferencesLayout', function() {
    var app;
    var layout;
    var module = 'Home';
    var sinonSandbox;
    let model;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.declareData('base', module, true, true);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();

        layout = SugarTest.createLayout(
            'base',
            module,
            'stage2-news-preferences',
            {}
        );
    });

    afterEach(function() {
        sinonSandbox.restore();
        model = null;
        layout = null;
    });

    describe('initialize', function() {
        it('should add plugins', function() {
            expect(layout.plugins).toContain('Stage2CssLoader');
            expect(layout.plugins).toContain('PushNotifications');
        });

        it('should add events', function() {
            expect(layout.events['click [data-action=addNewPreference]']).toEqual('addNewPreference');
        });

        it('should add target fields', function() {
            expect(layout.targetFields).toContain('sugar');
            expect(layout.targetFields).toContain('browser');
            expect(layout.targetFields).toContain('email-immediate');
            expect(layout.targetFields).toContain('email-daily');
            expect(layout.targetFields).toContain('email-weekly');
        });
    });

    describe('bindEventHandlers', function() {
        beforeEach(function() {
            sinonSandbox.stub(app.events, 'on').callsFake(function() {});
        });

        it('should call app.events.on with specific event', function() {
            layout.bindEventHandlers();
            expect(app.events.on).toHaveBeenCalledWith('news-preference:remove');
            expect(app.events.on).toHaveBeenCalledWith('news-preferences:cancel');
            expect(app.events.on).toHaveBeenCalledWith('news-preferences:save');
            expect(app.events.on).toHaveBeenCalledWith('news-preference:enable-notifications');
        });
    });
});
