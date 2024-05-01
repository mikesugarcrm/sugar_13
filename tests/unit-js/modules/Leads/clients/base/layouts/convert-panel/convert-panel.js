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
describe('Leads.Base.Layout.ConvertPanel', function() {
    var app, layout, sandbox, triggerStub, contextTriggerStub, dupeViewContextTriggerStub, doValidateStub, isValidAsyncStub;

    // `collapsing` class is applied during transitions.
    // `show` class will be available only when they will be finished.
    // `.collapse` -> `.collapsing` -> delay -> `.collapse.show`
    const isCollapsing = () =>
        layout.$(layout.accordionBody).hasClass('collapsing');

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('convert-panel-header', 'view', 'base', null, 'Leads');
        SugarTest.loadHandlebarsTemplate('convert-panel', 'layout', 'base', null, 'Leads');
        SugarTest.loadComponent('base', 'layout', 'toggle');
        SugarTest.testMetadata.set();
        SugarTest.testMetadata.addViewDefinition(
            'create',
            {'panels': [{'fields': [{'name': 'last_name'}]}]},
            'Contacts'
        );
        SugarTest.testMetadata.addLayoutDefinition(
            'dupecheck',
            {'components': [{'name': 'dupecheck-list', 'view': 'dupecheck-list'}]},
            'Contacts'
        );
        SugarTest.app.data.declareModels();

        layout = SugarTest.createLayout('base', 'Leads', 'convert-panel', {
            moduleNumber: 1,
            module: 'Contacts',
            copyData: true,
            required: true,
            enableDuplicateCheck: true,
            duplicateCheckOnStart: true,
            dependentModules: {
                'Foo': {
                    'fieldMapping': {
                        'foo_id': 'id'
                    }
                }
            }
        }, null, true);

        sandbox = sinon.createSandbox();
        triggerStub = sandbox.stub(layout, 'trigger');
        contextTriggerStub = sandbox.stub(layout.context, 'trigger');
        dupeViewContextTriggerStub = sandbox.stub(layout.duplicateView.context, 'trigger');
        doValidateStub = sandbox.stub(layout.createView.model, 'doValidate');
        isValidAsyncStub = sandbox.stub(layout.createView.model, 'isValidAsync');
    });

    afterEach(function() {
        sandbox.restore();
        layout.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    describe('_initSubpanelsData', function() {
        it('should pick up subpanel meta set up in the panel meta', function() {
            layout.meta.subpanels = [
                {
                    layout: 'subpanel-create',
                    otherSubpanelProperty: 'fakeProperty'
                }
            ];

            layout._initSubpanelsData();
            let subpanelMeta = _.findWhere(layout.subpanelsMeta, {
                layout: 'subpanel-create',
                otherSubpanelProperty: 'fakeProperty'
            });
            expect(subpanelMeta).not.toBeEmpty();
        });

        it('should set up the RLI subpanel meta for Opportunities panels if enableRlis is true', function() {
            layout.meta.module = 'Opportunities';
            layout.meta.enableRlis = true;
            layout._initSubpanelsData();
            expect(layout.subpanelsMeta).not.toBeEmpty();
            let rliSubpanelMeta = _.findWhere(layout.subpanelsMeta, {
                override_subpanel_list_view: 'subpanel-for-opportunities-create'
            });
            expect(rliSubpanelMeta).not.toBeEmpty();
        });
    });

    it('should set up dependency listeners if dependencies exist', function() {
        layout.addDependencyListeners();

        expect(_.has(layout.context._events, 'lead:convert:Foo:complete')).toBe(true);
        expect(_.has(layout.context._events, 'lead:convert:Foo:reset')).toBe(true);
    });

    it('should show dupecheck subview when dupe check is complete and duplicates were found', function() {
        layout.duplicateView.collection.length = 1;
        layout.dupeCheckComplete();

        expect(layout.currentToggle).toEqual(layout.TOGGLE_DUPECHECK);
        expect(layout.currentState.dupeCount).toEqual(1);
    });

    it('should show create subview when dupe check is complete and no duplicates were found', function() {
        layout.duplicateView.collection.length = 0;
        layout.dupeCheckComplete();

        expect(layout.currentToggle).toEqual(layout.TOGGLE_CREATE_LAYOUT);
        expect(layout.currentState.dupeCount).toEqual(0);
    });

    it('should not show create subview when no dupes found, but already toggled previously', function() {
        layout.duplicateView.collection.length = 0;
        layout.dupeCheckComplete();
        layout.showComponent(layout.TOGGLE_DUPECHECK);
        layout.dupeCheckComplete();

        expect(layout.currentToggle).toEqual(layout.TOGGLE_DUPECHECK);
    });

    it('should display dupecheck view after an initial search with results followed by a search with no results', function() {
        layout.duplicateView.collection.length = 2;
        layout.dupeCheckComplete();

        layout.duplicateView.collection.length = 0;
        layout.dupeCheckComplete();

        expect(layout.currentToggle).toEqual(layout.TOGGLE_DUPECHECK);
        expect(layout.currentState.dupeCount).toEqual(0);
    });

    it('should select first duplicate if module is required and duplicates were found', function() {
        var duplicate1 = app.data.createBean('Contacts', {id: '123'}),
            duplicate2 = app.data.createBean('Contacts', {id: '456'}),
            selectFirstDuplicateSpy = sandbox.spy(layout, 'selectFirstDuplicate');

        layout.meta.required = true;
        layout.duplicateView.collection.reset([duplicate1, duplicate2]);
        expect(selectFirstDuplicateSpy).toHaveBeenCalled();
    });

    it('should not select first duplicate if module is not required and duplicates were found', function() {
        var duplicate1 = app.data.createBean('Contacts', {id: '123'}),
            duplicate2 = app.data.createBean('Contacts', {id: '456'}),
            selectFirstDuplicateSpy = sandbox.spy(layout, 'selectFirstDuplicate');

        layout.meta.required = false;
        layout.duplicateView.collection.reset([duplicate1, duplicate2]);
        expect(selectFirstDuplicateSpy).not.toHaveBeenCalled();
    });

    it('should auto-run create validation for required modules', function() {
        layout.meta.required = true;
        layout.duplicateView.collection.reset();
        expect(isValidAsyncStub).toHaveBeenCalled();
    });

    it('should not auto-run create validation for optional modules', function() {
        layout.meta.required = false;
        layout.duplicateView.collection.reset();
        expect(isValidAsyncStub).not.toHaveBeenCalled();
    });

    it('should remove fields from metadata that are marked as to be hidden in the convert metadata', function() {
        var meta = {
            panels: [
                {
                    fields: [
                        {name: 'foo', type: 'blah'},
                        {name: 'bar', type: 'blah'},
                        'baz'
                    ]
                }
            ]
        };
        var convertMeta = {
            hiddenFields: {
                'foo': 'Foo',
                'baz': 'Foo'
            }
        };
        var expectedMeta = {
            panels: [
                {
                    fields: [
                        {name: 'foo', type: 'blah', readonly: true, required: false},
                        {name: 'bar', type: 'blah'},
                        {name: 'baz', readonly: true, required: false}
                    ]
                }
            ]
        };

        layout.removeFieldsFromMeta(meta, convertMeta);

        expect(meta).toEqual(expectedMeta);
    });

    it('should pass along requests to open if panel is already complete', function() {
        layout.autoCompleteCheckComplete = true;
        layout.currentState.complete = true;
        layout.handleOpenRequest();

        expect(contextTriggerStub.lastCall.args[0]).toEqual('lead:convert:2:open');
    });

    it('should pass along requests to open if panel is disabled', function() {
        layout.autoCompleteCheckComplete = true;
        layout.$(layout.accordionHeading).removeClass('enabled');
        layout.handleOpenRequest();

        expect(contextTriggerStub.lastCall.args[0]).toEqual('lead:convert:2:open');
    });

    it('should open panel if enabled, not complete, and a request has been made to open', function() {
        layout.autoCompleteCheckComplete = true;
        layout.currentState.complete = false;
        layout.$(layout.accordionHeading).addClass('enabled');

        expect(layout.isPanelOpen()).toBe(false);

        layout.handleOpenRequest();
        expect(isCollapsing()).toBe(true);
    });

    it('should wait to handle open request until auto-complete check has completed', function() {
        triggerStub.restore();
        layout.autoCompleteCheckComplete = false;
        layout.currentState.complete = false;
        layout.$(layout.accordionHeading).addClass('enabled');

        layout.handleOpenRequest();
        expect(isCollapsing()).toBe(false);

        layout.markAutoCompleteCheckComplete();
        expect(isCollapsing()).toBe(true);
    });

    describe('Associate Button Click', function() {
        var runValidationStub, markCompleteStub, clickEvent;

        beforeEach(function() {
            runValidationStub = sandbox.stub(layout, 'runCreateValidation');
            markCompleteStub = sandbox.stub(layout, 'markPanelComplete');
            clickEvent = {
                currentTarget: '<span></span>',
                stopPropagation: $.noop
            };
        });

        it('should ignore associate button clicks if button is disabled', function() {
            clickEvent.currentTarget = '<span class="disabled"></span>';

            layout.handleAssociateClick(clickEvent);

            expect(runValidationStub.callCount).toBe(0);
            expect(markCompleteStub.callCount).toBe(0);
        });

        it('should run create validation if associate button clicked and current toggle is create', function() {
            layout.currentToggle = layout.TOGGLE_CREATE_LAYOUT;
            layout.handleAssociateClick(clickEvent);

            expect(runValidationStub.callCount).toBe(1);
            expect(markCompleteStub.callCount).toBe(0);
        });

        it('should mark panel complete if associate button clicked and current toggle is dupecheck', function() {
            layout.currentToggle = layout.TOGGLE_DUPECHECK;
            layout.handleAssociateClick(clickEvent);

            expect(runValidationStub.callCount).toBe(0);
            expect(markCompleteStub.callCount).toBe(1);
        });
    });

    it('should close the current panel and fire appropriate events when marking panel complete', function() {
        var createModel = layout.createView.model;

        createModel.set({id: '123', full_name: 'Foo Bar'});
        layout.$(layout.accordionHeading).addClass('enabled');
        layout.openPanel();
        layout.currentState.complete = false;
        layout.markPanelComplete(createModel);

        expect(layout.currentState.associatedName).toEqual(createModel.get('full_name'));
        expect(layout.currentState.complete).toBe(true);
        expect(triggerStub.firstCall.args).toEqual(['lead:convert-panel:complete', createModel.get('full_name')]);
        expect(contextTriggerStub.firstCall.args).toEqual([
            'lead:convert-panel:complete',
            layout.meta.module,
            createModel
        ]);
    });

    it('should re-run validation when model changes after marking panel complete', function() {
        var createModel = layout.createView.model,
            runValidationStub = sandbox.stub(layout, 'runCreateValidation');

        createModel.set({full_name: 'Foo Bar'});
        layout.$(layout.accordionHeading).addClass('enabled');
        layout.openPanel();
        layout.currentState.complete = false;
        layout.markPanelComplete(createModel);

        expect(runValidationStub.callCount).toEqual(0);
        createModel.set('full_name', 'Foo Bar 2');
        expect(runValidationStub.callCount).toEqual(1);
    });

    it('should not mark panel complete for valid model when re-running validation after marking panel complete', function() {
        var createModel = layout.createView.model,
            markPanelCompleteSpy = sandbox.spy(layout, 'markPanelComplete');

        sandbox.stub(layout, 'runCreateValidation').callsFake(function(callbacks) {
            callbacks.valid();
        });

        createModel.set({full_name: 'Foo Bar'});
        layout.markPanelComplete(createModel);

        expect(markPanelCompleteSpy.callCount).toEqual(1);
        createModel.set('full_name', 'Foo Bar 2');
        expect(markPanelCompleteSpy.callCount).toEqual(1);
    });

    it('should reset panel complete for invalid model when re-running validation after marking panel complete', function() {
        var createModel = layout.createView.model,
            resetPanelSpy = sandbox.spy(layout, 'resetPanel');

        sandbox.stub(layout, 'runCreateValidation').callsFake(function(callbacks) {
            callbacks.invalid();
        });

        createModel.set({full_name: 'Foo Bar'});
        layout.markPanelComplete(createModel);

        expect(resetPanelSpy.callCount).toEqual(0);
        createModel.set('full_name', 'Foo Bar 2');
        expect(resetPanelSpy.callCount).toEqual(1);
    });

    it('should not attempt to close panel and open next panel when marking complete and already closed', function() {
        var closePanelSpy,
            requestNextPanelOpenSpy = sandbox.spy(layout, 'requestNextPanelOpen');

        layout.closePanel();
        closePanelSpy = sandbox.spy(layout, 'closePanel');
        layout.markPanelComplete(layout.createView.model);

        expect(closePanelSpy).not.toHaveBeenCalled();
        expect(requestNextPanelOpenSpy).not.toHaveBeenCalled();
    });

    it('should trigger the dupe check if dupe check enabled and all required dupe check fields are set', function() {
        layout.createView.model.set({foo: 'Foo', bar: 'Bar'});
        layout.meta.duplicateCheckRequiredFields = ['foo', 'bar'];
        layout.meta.enableDuplicateCheck = true;
        layout.triggerDuplicateCheck();

        expect(dupeViewContextTriggerStub.callCount).toBe(1);
    });

    it('should not trigger dupe check if dupe check disabled', function() {
        layout.meta.enableDuplicateCheck = false;
        layout.triggerDuplicateCheck();

        expect(dupeViewContextTriggerStub.callCount).toBe(0);
    });

    it('should not trigger dupe check if any required dupe check fields are not set', function() {
        layout.createView.model.set({foo: 'Foo'});
        layout.meta.duplicateCheckRequiredFields = ['foo', 'bar'];
        layout.meta.enableDuplicateCheck = true;
        layout.triggerDuplicateCheck();

        expect(dupeViewContextTriggerStub.callCount).toBe(0);
    });

    it('should set dupe count to 0 and fire appropriate trigger if dupe check is triggered but not run', function() {
        layout.meta.enableDuplicateCheck = false;
        layout.triggerDuplicateCheck();

        expect(layout.currentState.dupeCount).toBe(0);
        expect(triggerStub.lastCall.args).toEqual(['lead:convert-dupecheck:complete', 0]);
    });

    it('should populate create model with lead fields and trigger dupe check when lead model passed on context', function() {
        var createModel = layout.createView.model,
            leadModel = app.data.createBean('Leads', {
                foo: 'Foo',
                bar: 'Bar',
                baz: 'Baz',
                north: 'Lead Value for NORTH',
                south: 'Lead Value for SOUTH',
                east: 'Lead Value for EAST',
                _module: 'Leads'
            });

        sandbox.stub(leadModel, 'setDefault');
        sandbox.stub(createModel, 'setDefault');

        createModel.set({
            north: 'Contact Value for NORTH',
            west: 'Contact Value for WEST',
            east: 'Contact Value for EAST'
        });
        layout.meta.duplicateCheckOnStart = true;
        layout.meta.fieldMapping = {
            'contact_foo': 'foo',
            'contact_baz': 'baz',
            'east': 'north'
        };

        sandbox.stub(app.metadata, 'getModule')
            .withArgs('Leads', 'fields').returns({north: 'north', south: 'south', east: 'east'})
            .withArgs('Contacts', 'fields').returns({north: 'north', west: 'west', east: 'east'});

        layout.handlePopulateRecords(leadModel);

        expect(createModel.get('contact_foo')).toEqual('Foo');
        expect(createModel.get('contact_bar')).toBeUndefined();
        expect(createModel.get('contact_baz')).toEqual('Baz');
        expect(createModel.get('north')).toEqual('Lead Value for NORTH');
        expect(createModel.get('south')).toBeUndefined();
        expect(createModel.get('east')).toEqual('Lead Value for NORTH');
        expect(createModel.get('west')).toEqual('Contact Value for WEST');
        expect(dupeViewContextTriggerStub.callCount).toBe(1);
    });

    it('should not populate create model with lead fields when copyData meta attribute is false', function() {
        var createModel = layout.createView.model,
            leadModel = app.data.createBean('Leads', {
                north: 'Lead Value for NORTH',
                _module: 'Leads'
            });

        createModel.set('north', 'Contact Value for NORTH');
        layout.meta.duplicateCheckOnStart = true;
        layout.meta.copyData = false;

        sandbox.stub(app.metadata, 'getModule')
            .withArgs('Leads', 'fields').returns({north: 'north'})
            .withArgs('Contacts', 'fields').returns({north: 'north'});

        layout.handlePopulateRecords(leadModel);

        expect(createModel.get('north')).toEqual('Contact Value for NORTH');
        expect(dupeViewContextTriggerStub.callCount).toBe(0);
    });

    it('should trigger duplicate:field on the model when attributes are copied from the leads model', function() {
        var leadAttributes = {
                north: 'Lead Value for NORTH',
                picture: 'e11a95e3-3658-c6c4-73fc-5474eb6e1703'
            },
            leadModel = app.data.createBean('Leads', _.extend(leadAttributes, {
                    _module: 'Leads'
            })),
            contactAttributes = {
                north2: 'Contact Value for NORTH',
                picture: ''
            },
            createModel = app.data.createBean('Contacts', _.extend(contactAttributes, {
                _module: 'Contacts'
            })),
            fieldMappings = {
                north2: 'north',
                picture: 'picture'
            };

        layout.currentToggle = layout.TOGGLE_CREATE_LAYOUT;
        layout.meta.copyData = true;

        layout.createView.model = createModel;
        layout.createView.meta = layout.createView.meta || {};
        layout.createView.meta.useTabsAndPanels = true;

        var createViewContextTriggerStub = sinon.stub(layout.createView.model, 'trigger');

        sandbox.stub(app.metadata, 'getModule')
            .withArgs('Leads', 'fields').returns(leadAttributes)
            .withArgs('Contacts', 'fields').returns(contactAttributes);

        layout.populateRecords(leadModel, fieldMappings);

        expect(createModel.get('north2')).toEqual(leadAttributes.north);
        expect(createModel.get('picture')).toEqual(leadAttributes.picture);

        layout.handleShowComponent();
        expect(createViewContextTriggerStub).toHaveBeenCalledWith('duplicate:field');

        createViewContextTriggerStub.restore();
    });

    it('should not trigger duplicate:field on the model when no attributes copied from the leads model', function() {
        var leadAttributes = {
                north: 'Lead Value for NORTH',
                picture: 'e11a95e3-3658-c6c4-73fc-5474eb6e1703'
            },
            leadModel = app.data.createBean('Leads', _.extend(leadAttributes, {
                _module: 'Leads'
            })),
            contactAttributes = {
                north2: 'Contact Value for NORTH',
                picture: ''
            },
            createModel = app.data.createBean('Contacts', _.extend(contactAttributes, {
                _module: 'Contacts'
            })),
            fieldMappings = {};

        layout.currentToggle = layout.TOGGLE_CREATE_LAYOUT;
        layout.meta.copyData = true;

        layout.createView.model = createModel;
        layout.createView.meta = layout.createView.meta || {};
        layout.createView.meta.useTabsAndPanels = true;

        var createViewContextTriggerStub = sinon.stub(layout.createView.model, 'trigger');

        sandbox.stub(app.metadata, 'getModule')
            .withArgs('Leads', 'fields').returns(leadAttributes)
            .withArgs('Contacts', 'fields').returns(contactAttributes);

        layout.populateRecords(leadModel, fieldMappings);

        expect(createModel.get('north2')).toEqual(contactAttributes.north2);
        expect(createModel.get('picture')).toEqual(contactAttributes.picture);

        layout.handleShowComponent();
        expect(createViewContextTriggerStub).not.toHaveBeenCalledWith('duplicate:field');

        createViewContextTriggerStub.restore();
    });

    it('should not populate create model with lead fields when user does not have edit access to field', function() {
        var createModel = layout.createView.model,
            leadModel = app.data.createBean('Leads', {
                north: 'Lead Value for NORTH',
                south: 'Lead Value for SOUTH',
                east: 'Lead Value for EAST',
                birthdate: '01/01/2010',
                _module: 'Leads'
            });

        sandbox.stub(app.acl, 'hasAccessToModel').callsFake(function(action, model, field) {
            return action !== 'edit' || field !== 'birthdate';
        });
        sandbox.stub(leadModel, 'setDefault');
        sandbox.stub(createModel, 'setDefault');

        createModel.set({
            north: 'Contact Value for NORTH',
            west: 'Contact Value for WEST',
            east: 'Contact Value for EAST',
            birthdate: '01/01/1975'
        });
        layout.meta.duplicateCheckOnStart = false;

        sandbox.stub(app.metadata, 'getModule')
            .withArgs('Leads', 'fields').returns({
                north: 'north',
                south: 'south',
                east: 'east',
                birthdate: 'birthdate'
            })
            .withArgs('Contacts', 'fields').returns({
                north: 'north',
                west: 'west',
                east: 'east',
                birthdate: 'birthdate'
            });

        layout.handlePopulateRecords(leadModel);

        expect(createModel.get('north')).toEqual('Lead Value for NORTH');
        expect(createModel.get('south')).toBeUndefined();
        expect(createModel.get('west')).toEqual('Contact Value for WEST');
        expect(createModel.get('birthdate')).toEqual('01/01/1975');
    });

    it('should trigger dupe check when panel is enabled and not already complete', function() {
        layout.currentState.complete = false;
        layout.handleEnablePanel(true);

        expect(dupeViewContextTriggerStub.callCount).toBe(1);
    });

    it('should not trigger dupe check when panel is enabled but already complete', function() {
        layout.currentState.complete = true;
        layout.handleEnablePanel(true);

        expect(dupeViewContextTriggerStub.callCount).toBe(0);
    });

    it('should update create model if dependency module changes and trigger dupe check', function() {
        var createModel = layout.createView.model,
            fooModel = app.data.createBean('Leads', {id: '456'});

        sandbox.stub(createModel, 'setDefault');
        sandbox.stub(fooModel, 'setDefault');

        layout.updateFromDependentModuleChanges('Foo', fooModel);

        expect(createModel.get('foo_id')).toEqual('456');
        expect(dupeViewContextTriggerStub.callCount).toBe(1);
    });

    it('should not trigger dupe check if dependency module changes but no changes to create model', function() {
        var createModel = layout.createView.model,
            fooModel = app.data.createBean('Leads', {nonMappedField: 'bar'});

        layout.updateFromDependentModuleChanges('Foo', fooModel);

        expect(createModel.attributes).toEqual({});
        expect(dupeViewContextTriggerStub.callCount).toBe(0);
    });

    it('should reset the panel if a dependency module changes', function() {
        layout.currentState.complete = true;
        layout.resetFromDependentModuleChanges('Foo');

        expect(layout.currentState.complete).toBe(false);
    });

    it('should reset the dupe collection if a dependency module changes and dupes were found previously', function() {
        layout.currentState.dupeCount = 1;
        layout.resetFromDependentModuleChanges('Foo');

        expect(layout.currentState.dupeCount).toEqual(0);
    });

    it('should clear dependency mapped fields if a dependency module is reset', function() {
        layout.createView.model.set('foo_id', '123');
        layout.resetFromDependentModuleChanges('Foo');

        expect(layout.createView.model.get('foo_id')).toBeUndefined();
    });

    it('should reset toggle state flag if a dependency module is reset', function() {
        layout.toggledOffDupes = true;
        layout.resetFromDependentModuleChanges('Foo');

        expect(layout.toggledOffDupes).toBe(false);
    });

    it('should remove unsaved changes when turnOffUnsavedChanges is called', function() {
        var hasUnsavedChanges = function(view) {
            return view.model.isNew() && !_.isEqual(_.keys(view.model.getDefault()), _.keys(view.model.attributes));
        };

        layout.createView.model.set('test', '123'); //add unsaved changes
        expect(hasUnsavedChanges(layout.createView)).toBeTruthy();

        layout.turnOffUnsavedChanges(); //happens when lead convert completes successfully
        expect(hasUnsavedChanges(layout.createView)).toBeFalsy();
    });

    describe("field transfer validation", function() {
        it("should return true for source field values that should be transferred", function() {
            expect(layout.shouldSourceValueBeCopied(25)).toBeTruthy();
            expect(layout.shouldSourceValueBeCopied(1.234)).toBeTruthy();
            expect(layout.shouldSourceValueBeCopied(0)).toBeTruthy();
            expect(layout.shouldSourceValueBeCopied('Foo')).toBeTruthy();
            expect(layout.shouldSourceValueBeCopied(false)).toBeTruthy();
            expect(layout.shouldSourceValueBeCopied([1,2,3])).toBeTruthy();
            expect(layout.shouldSourceValueBeCopied({a:25})).toBeTruthy();
        });

        it("should return false for source field values that should not be transferred", function() {
            expect(layout.shouldSourceValueBeCopied('')).toBeFalsy();
            expect(layout.shouldSourceValueBeCopied(null)).toBeFalsy();
            expect(layout.shouldSourceValueBeCopied([])).toBeFalsy();
            expect(layout.shouldSourceValueBeCopied({})).toBeFalsy();
            expect(layout.shouldSourceValueBeCopied(undefined)).toBeFalsy();
        });
    });

    describe('runCreateValidation', function() {
        beforeEach(function() {
            layout.createView.validateModelWaterfall = sinon.stub();
            layout.createView.validateSubpanelModelsWaterfall = sinon.stub();
        });

        it('should validate the main model', function() {
            layout.runCreateValidation();
            expect(layout.createView.validateModelWaterfall).toHaveBeenCalled();
        });

        it('should validate the subpanel models', function() {
            layout.runCreateValidation();
            expect(layout.createView.validateSubpanelModelsWaterfall).toHaveBeenCalled();
        });
    });
});
