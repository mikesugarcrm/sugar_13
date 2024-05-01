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
describe('Reminder Time Defaults Plugin', function() {
    var plugin, origPlugins,
        moduleName = 'Calls';

    beforeEach(function() {
        SugarTest.loadPlugin('ReminderTimeDefaults');
        plugin = SugarTest.app.plugins.plugins.view.ReminderTimeDefaults;

        plugin.model = SugarTest.app.data.createBean(moduleName);
        plugin.model.setDefault({
            reminder_time: -1,
            email_reminder_time: -1
        });
    });

    afterEach(function() {
        SugarTest.app.user.unset('preferences');
    });

    describe('Reminder time defaults', function() {
        it('should be set from the preferences', function() {
            SugarTest.app.user.set('preferences', {
                reminder_time: "60",
                email_reminder_time: "30"
            });

            plugin._defaultReminderTimes();

            expect(plugin.model.get('reminder_time')).toBe(60);
            expect(plugin.model.get('email_reminder_time')).toBe(30);
        });

        it('should not be set from the preferences if the values are empty string', function() {
            SugarTest.app.user.set('preferences', {
                reminder_time: '',
                email_reminder_time: ''
            });

            plugin._defaultReminderTimes();

            expect(plugin.model.get('reminder_time')).toBe(-1);
            expect(plugin.model.get('email_reminder_time')).toBe(-1);
        });

        it('should not be set from the preferences if values have already changed', function() {
            SugarTest.app.user.set('preferences', {
                reminder_time: "60",
                email_reminder_time: "30"
            });

            plugin.model.set({
                reminder_time: 10,
                email_reminder_time: 10
            });

            plugin._defaultReminderTimes();

            expect(plugin.model.get('reminder_time')).toBe(10);
            expect(plugin.model.get('email_reminder_time')).toBe(10);
        });
    });
});
