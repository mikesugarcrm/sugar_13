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
describe('RelativeTime plugin', function() {
    var app;
    var field;
    var liverelativedateStub;
    var view;
    var oldLiveRelativeDate;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        oldLiveRelativeDate = $.fn && $.fn.liverelativedate ? $.fn.liverelativedate : null;
        $.fn.liverelativedate = sinon.stub();
        SugarTest.loadPlugin('RelativeTime');
        view = SugarTest.createView('base', 'dummyview', 'base');
        field = SugarTest.createField('base', 'dummy', 'base', view);
        field.fieldTag = 'date';
        field.plugins.push('RelativeTime');
        liverelativedateStub = sinon.stub();
        sinon.stub(field, '$')
            .withArgs('[datetime]')
            .returns({liverelativedate: liverelativedateStub});
    });

    afterEach(function() {
        field.dispose();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
        view = null;
        field = null;
        sinon.restore();
        if (_.isFunction(oldLiveRelativeDate)) {
            $.fn.liverelativedate = oldLiveRelativeDate;
        }
    });

    describe('onAttach', function() {
        it('should execute liverelativedate on all [datetime] elements when field renders', function() {
            app.plugins.attach(field, 'field');

            field.trigger('render');

            expect(liverelativedateStub).toHaveBeenCalled();
        });
    });

    describe('onDetach', function() {
        it('should destroy the liverelativedates', function() {
            app.plugins.detach(field, 'field');
            expect(liverelativedateStub).toHaveBeenCalledWith('destroy');
        });
    });
});
