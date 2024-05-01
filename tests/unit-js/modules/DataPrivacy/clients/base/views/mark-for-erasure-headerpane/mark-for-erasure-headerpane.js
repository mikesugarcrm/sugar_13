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
describe('View.Views.Base.DataPrivacy.MarkForErasureHeaderpaneView', function() {
    var view;
    var app;
    var beans;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'headerpane');

        app = SUGAR.App;
        app.drawer = {
            close: sinon.stub()
        };

        var model = app.data.createBean();
        beans = [app.data.createBean('MarkForErasureView', {id: '5'})];
        var context = new app.Context({
            model: model,
            mass_collection: app.data.createBeanCollection('MarkForErasureView', beans),
            modelForErase: app.data.createBean('Contacts')
        });
        var layout = SugarTest.createLayout('base', null, 'base');
        view = SugarTest.createView(
            'base',
            'DataPrivacy',
            'mark-for-erasure-headerpane',
            null,
            context,
            'DataPrivacy',
            layout
        );
    });

    afterEach(function() {
        app.view.reset();
        view = null;
        delete app.drawer;
        app = null;
        sinon.restore();
    });

    describe('close', function() {
        it('should close the drawer', function() {
            view.close();
            expect(app.drawer.close).toHaveBeenCalled();
        });
    });

    describe('markForErasure', function() {
        it('should trigger markforerasure:mark', function() {
            var triggerStub = sinon.stub(view.context, 'trigger');
            view.markForErasure();
            expect(triggerStub).toHaveBeenCalledWith('markforerasure:mark');
        });
    });

    describe('Enabling and disabling the mark for erasure button', function() {
        it('should be enabled if there has been a change and disabled if there have been no changes', function() {
            var toggleClass = sinon.stub();
            sinon.stub(view, '$').returns({
                toggleClass: toggleClass
            });

            var massCollection = view.context.get('mass_collection');
            view.context.trigger('change:mass_collection');
            view.context.trigger('markforerasure:masscollection:init', beans);

            massCollection.remove(beans[0]);
            expect(toggleClass).toHaveBeenCalledWith('disabled', false);

            massCollection.add(beans[0]);
            expect(toggleClass).toHaveBeenCalledWith('disabled', true);
        });
    });

    describe('_formatTitle', function() {
        it('should return EMPTY string when neither record name nor default value is available', function() {
            title = view._formatTitle();
            expect(title).toEqual('');
        });

        it('should return title with full name if it exists on model', function() {
            var model = view.context.get('modelForErase');
            model.fields = {name: {type: 'fullname'}};
            var fullName = 'Mr. Dummy_Name';
            var formattedTitle = 'PII for ' + fullName;
            sinon.stub(app.utils, 'formatNameModel')
                .withArgs(model.module, model.attributes)
                .returns(fullName);
            sinon.stub(app.lang, 'get')
                .withArgs('TPL_DATAPRIVACY_PII_TITLE', model.module, {name: fullName})
                .returns(formattedTitle);
            title = view._formatTitle();
            expect(title).toEqual(formattedTitle);
        });

        it('should return title with record name if it is available', function() {
            var model = view.context.get('modelForErase');
            var recordName = 'Dummy_Name';
            var formattedTitle = 'PII for ' + recordName;
            sinon.stub(app.utils, 'getRecordName').withArgs(model).returns(recordName);
            sinon.stub(app.lang, 'get')
                .withArgs('TPL_DATAPRIVACY_PII_TITLE', model.module, {name: recordName})
                .returns(formattedTitle);
            title = view._formatTitle();
            expect(title).toEqual(formattedTitle);
        });

        it('should return default title if default value exists but record name is empty', function() {
            var model = view.context.get('modelForErase');
            var defaultTitle = 'PII';
            var defaultValue = 'LBL_DATAPRIVACY_PII';
            sinon.stub(app.utils, 'getRecordName')
                .withArgs(model).returns('');
            sinon.stub(app.lang, 'get')
                .withArgs(defaultValue, 'DataPrivacy').returns(defaultTitle);
            title = view._formatTitle(defaultValue);
            expect(title).toEqual(defaultTitle);
        });
    });
});
