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
describe('Plugins.EditAllRecurrences', function() {
    var moduleName = 'Meetings',
        view,
        pluginsBefore,
        app,
        sandbox,
        navigateStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        sandbox = sinon.createSandbox();

        navigateStub = sandbox.stub(app.router, 'navigate');
        view = SugarTest.createView('base', moduleName, 'record');
        view.model.set({'id': '456', 'repeat_type': 'Daily'});
        pluginsBefore = view.plugins;
        view.plugins = ['EditAllRecurrences'];
        SugarTest.loadPlugin('EditAllRecurrences');
        SugarTest.app.plugins.attach(view, 'view');

        view.trigger('init');
    });

    afterEach(function() {
        sandbox.restore();
        view.plugins = pluginsBefore;
        view.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        app.cache.cutAll();
        view = null;
    });

    it('should turn on all recurrence mode when all_recurrences:edit event is fired from parent', function() {
        view.model.set('repeat_parent_id', '');
        view.allRecurrencesMode = false;
        view.context.trigger('all_recurrences:edit');
        expect(view.allRecurrencesMode).toEqual(true);
    });

    it('should redirect to parent when all_recurrences:edit event is fired from child', function() {
        var repeatParentId = '123';

        view.model.set('repeat_parent_id', repeatParentId);
        view.allRecurrencesMode = false;
        view.context.trigger('all_recurrences:edit');
        expect(navigateStub.callCount).toEqual(1);
        expect(navigateStub.lastCall.args[0]).toEqual('#Meetings/123/edit/all_recurrences');
    });

    it('should init all recurrence mode to false if not coming from all_recurrence route', function() {
        view.allRecurrencesMode = true;
        view.trigger('init');
        expect(view.allRecurrencesMode).toEqual(false);
    });

    it('should go into edit all recurrence mode when all_recurrences from a route', function() {
        view.allRecurrencesMode = undefined;
        view.context.set('all_recurrences', true);
        view.trigger('init');
        expect(view.allRecurrencesMode).toEqual(true);
        // all_recurrences should be cleared out too
        expect(view.context.get('all_recurrences')).toBeUndefined();
    });

    it('should turn off all recurrence mode when the cancel button is clicked', function() {
        view.allRecurrencesMode = true;
        view.cancelClicked();
        expect(view.allRecurrencesMode).toEqual(false);
    });

    it('should add all_recurrences flag to save options when in all recurrence mode', function() {
        var options;
        view.allRecurrencesMode = true;
        options = view.getCustomSaveOptions();
        expect(options.params.all_recurrences).toEqual(true);
    });

    it('should prevent toggling out of all recurrence mode when repeat_type is blank', function() {
        view.allRecurrencesMode = true;
        view.model.set('repeat_type', '');
        view.toggleAllRecurrencesMode(false);
        expect(view.allRecurrencesMode).toEqual(true);
    });

    it('should force into all recurrence mode when repeat_type is blank on sync', function() {
        view.allRecurrencesMode = false;
        view.model.set('repeat_type', '');
        view.model.trigger('sync');
        expect(view.allRecurrencesMode).toEqual(true);
    });

    it('should toggle repeat type into edit mode when not recurring and coming from edit route', function() {
        var toggleEditStub = sandbox.stub(view, 'toggleEdit');
        view.context.set('action', 'edit'); //simulate coming from edit route
        view.model.set('repeat_type', '');
        view.model.trigger('sync');
        expect(toggleEditStub).toHaveBeenCalledWith(true);
    });

    it('should update noEditFields when toggling all recurrence mode', function() {
        view.toggleAllRecurrencesMode(true);
        expect(view.noEditFields).toEqual([]);
        view.toggleAllRecurrencesMode(false);
        expect(view.noEditFields).toEqual([
            'repeat_type',
            'recurrence',
            'repeat_interval',
            'repeat_dow',
            'repeat_until',
            'repeat_count',
            'repeat_days'
        ]);
    });

    it('should show a /edit/all_recurrences route when editing all recurrences', function() {
        view.allRecurrencesMode = true;
        view.model.set('id', 'foo_id');
        view.editClicked();
        expect(navigateStub).toHaveBeenCalledWith('Meetings/foo_id/edit/all_recurrences', {trigger: false});
    });

    it('should show a /edit route when editing non-recurring meeting, even though allRecurrences is true', function() {
        view.allRecurrencesMode = true;
        view.model.set('repeat_type', '');
        view.model.set('id', 'foo_id');
        view.editClicked();
        expect(navigateStub).toHaveBeenCalledWith('Meetings/foo_id/edit', {trigger: false});
    });

    describe('Next/Previous Button Behavior', function() {
        var listCollection;

        beforeEach(function() {
            listCollection = app.data.createBeanCollection(moduleName);
            sandbox.stub(listCollection, 'fetch').callsFake(function(options) {
                options.success();
            });
            view.context.set('listCollection', listCollection);

            view.$el = $('<div><div class="btn-group-previous-next">' +
                '<div class="next-row"></div><div class="previous-row"></div>' +
                '</div></div>');
        });

        it('should disable next/prev buttons when saving all recurrences of an event', function() {
            view.allRecurrencesMode = true;
            view._doAfterSave();
            expect(view.$('.next-row').hasClass('disabled')).toBe(true);
            expect(view.$('.previous-row').hasClass('disabled')).toBe(true);
        });

        it('should not disable next/prev buttons when saving one occurrence of a repeating event', function() {
            view.allRecurrencesMode = false;
            view._doAfterSave();
            expect(view.$('.next-row').hasClass('disabled')).toBe(false);
            expect(view.$('.previous-row').hasClass('disabled')).toBe(false);
        });

        it('should attempt to re-fetch the list collection after all recurrences of a meeting are saved', function() {
            var saveOptions,
                refetchListCollectionStub = sandbox.stub(view, '_refetchListCollection');

            view.allRecurrencesMode = true;
            saveOptions = view.getCustomSaveOptions();
            //trigger sync to mimic what happens after the model is saved - passing through the custom save options
            view.model.trigger('sync', view.model, view.model.attributes, saveOptions);

            expect(refetchListCollectionStub).toHaveBeenCalled();
        });

        it('should not re-fetch the list collection after editing one occurrence of a recurring meeting', function() {
            var saveOptions,
                refetchListCollectionStub = sandbox.stub(view, '_refetchListCollection');

            view.allRecurrencesMode = false;
            saveOptions = view.getCustomSaveOptions();
            //trigger sync to mimic what happens after the model is saved - passing through the custom save options
            view.model.trigger('sync', view.model, view.model.attributes, saveOptions);

            expect(refetchListCollectionStub).not.toHaveBeenCalled();
        });

        it('should not enable next/prev buttons after fetch if collection is empty', function() {
            view._disableNextPrevButtons();
            view._refetchListCollection();
            expect(view.$('.next-row').hasClass('disabled')).toBe(true);
            expect(view.$('.previous-row').hasClass('disabled')).toBe(true);
        });

        it('should only enable next button after fetch if there is a next model in the collection', function() {
            listCollection.add(view.model);
            listCollection.add(app.data.createBean(moduleName, {id: 'next_record'}));
            view._disableNextPrevButtons();
            view._refetchListCollection();
            expect(view.$('.next-row').hasClass('disabled')).toBe(false);
            expect(view.$('.previous-row').hasClass('disabled')).toBe(true);
        });

        it('should only enable previous button after fetch if there is a previous model in the collection', function() {
            listCollection.add(app.data.createBean(moduleName, {id: 'prev_record'}));
            listCollection.add(view.model);
            view._disableNextPrevButtons();
            view._refetchListCollection();
            expect(view.$('.next-row').hasClass('disabled')).toBe(true);
            expect(view.$('.previous-row').hasClass('disabled')).toBe(false);
        });

        it('should enable both next/prev after fetch if there are next/prev models in the collection', function() {
            listCollection.add(app.data.createBean(moduleName, {id: 'prev_record'}));
            listCollection.add(view.model);
            listCollection.add(app.data.createBean(moduleName, {id: 'next_record'}));
            view._disableNextPrevButtons();
            view._refetchListCollection();
            expect(view.$('.next-row').hasClass('disabled')).toBe(false);
            expect(view.$('.previous-row').hasClass('disabled')).toBe(false);
        });

    });
});
