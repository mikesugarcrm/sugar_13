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
describe('View.Fields.Base.ParticipantsField', function() {
    var app, context, field, fieldDef, fixture, model, module, participants, sandbox;

    module = 'Meetings';

    participants = [
        {_module: 'Users', id: '1', name: 'Jim Brennan', accept_status_meetings: 'accept', _erased_fields: []},
        {_module: 'Users', id: '2', name: 'Will Weston', accept_status_meetings: 'decline', _erased_fields: []},
        {_module: 'Contacts', id: '3', name: 'Jim Gallardo', accept_status_meetings: 'tentative', _erased_fields: []},
        {_module: 'Leads', id: '4', name: 'Sallie Talmadge', accept_status_meetings: 'none', _erased_fields: []}
    ];

    fieldDef = {
        name: 'invitees',
        source: 'non-db',
        type: 'collection',
        vname: 'LBL_INVITEES',
        links: ['contacts', 'leads', 'users'],
        order_by: 'name:asc',
        fields: [
            {
                name: 'name',
                type: 'name',
                label: 'LBL_SUBJECT'
            },
            'accept_status_meetings',
            'picture'
        ]
    };

    fixture = {
        _hash: '12345678910',
        fields: {
            id: {
                name: 'id',
                type: 'id'
            },
            first_name: {
                name: 'first_name',
                type: 'varchar',
                len: 20
            },
            last_name: {
                name: 'last_name',
                type: 'varchar'
            },
            full_name: {
                name: 'full_name',
                type: 'varchar',
                concat: ['first_name', 'last_name']
            }
        }
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.updateModuleMetadata('Users', _.extend({}, fixture, {isBwcEnabled: true}));
        SugarTest.testMetadata.updateModuleMetadata('Contacts', {isBwcEnabled: false});
        SugarTest.testMetadata.updateModuleMetadata('Leads', _.extend({}, fixture, {isBwcEnabled: false}));
        SugarTest.loadHandlebarsTemplate('participants', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('participants', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('participants', 'field', 'base', 'timeline-header.partial');
        SugarTest.loadHandlebarsTemplate('participants', 'field', 'base', 'search-result.partial');
        SugarTest.declareData('base', module, true, false);
        SugarTest.loadPlugin('VirtualCollection');
        SugarTest.testMetadata.set();
        app.data.declareModelClass('Users', null, 'base', fixture);
        app.data.declareModelClass('Leads', null, 'base', fixture);
        SugarTest.app.data.declareModels();

        context = app.context.getContext({module: module});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.createSandbox();
        sandbox.stub(app.api, 'call').callsFake(function(method, url, data, callbacks, options) {
            if (callbacks.success) {
                callbacks.success({});
            }
        });
        sandbox.stub(app.data, 'getRelatedModule');
        app.data.getRelatedModule.withArgs('Meetings', 'users').returns('Users');
        app.data.getRelatedModule.withArgs('Meetings', 'contacts').returns('Contacts');
        app.data.getRelatedModule.withArgs('Meetings', 'leads').returns('Leads');
    });

    afterEach(function() {
        sandbox.restore();

        if (field) {
            field.dispose();
        }

        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('accessing the field value', function() {
        beforeEach(function() {
            sandbox.stub(model, 'isNew').returns(false);
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
            // skip rendering
            field.model.off('change:' + field.name);
        });

        it('should return the collection', function() {
            field.model.set(field.name, participants);
            expect(field.getFieldValue().length).toBe(participants.length);
        });

        it('should throw an exception when the field value has not been set', function() {
            expect(function() {
                var value = field.getFieldValue();
            }).toThrow();
        });
    });

    describe('when creating a new meeting', function() {
        it('should create a new empty collection', function() {
            sandbox.stub(model, 'isNew').returns(true);
            sandbox.spy(model, 'set');
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'edit',
                fieldDef,
                module,
                model,
                context
            );
            expect(model.set.withArgs(fieldDef.name, []).calledOnce).toBe(true);
            expect(field.getFieldValue().length).toBe(0);
        });
    });

    describe('when copying a meeting', function() {
        it('should include the participants from the copied record', function() {
            var collection = app.data.createMixedBeanCollection();
            collection.add({_module: 'Contacts', id: '1', name: 'Foo Bar'});
            model.set(fieldDef.name, collection);
            sandbox.stub(model, 'isNew').returns(true);
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'edit',
                fieldDef,
                module,
                model,
                context
            );
            expect(field.getFieldValue().length).toBe(1);
            expect(field.getFieldValue().at(0).id).toBe('1');
        });
    });

    describe('when the participants field is in detail mode', function() {
        beforeEach(function() {
            sandbox.stub(model, 'isNew').returns(false);
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
            field.model.set(field.name, participants);
        });

        it('should render one row for each participant', function() {
            field.render();
            expect(field.$('div.row.participant').length).toBe(participants.length);
        });

        it('should preview the participant when the preview button is clicked', function() {
            var previewBtn, spy;
            spy = sandbox.spy();
            app.events.on('preview:render', spy, this);
            field.render();
            previewBtn = field.$('button[data-action=previewRow]:not(.disabled)').first();
            previewBtn.click();
            expect(spy).toHaveBeenCalled();
            app.events.off('preview:render', this);
        });
    });

    describe('when the participants field is in edit mode', function() {
        beforeEach(function() {
            sandbox.stub(model, 'isNew').returns(false);
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'edit',
                fieldDef,
                module,
                model,
                context
            );
            field.action = 'edit';
            field.model.set(field.name, participants);
        });

        it('should show the select when there are no participants', function() {
            sandbox.stub($.fn, 'select2');
            field.model.set(field.name, []);
            field.render();
            expect(field.$('[name=newRow]').css('display')).toEqual('table-row');
            expect(field.$('button[data-action=removeRow]').hasClass('disabled')).toBe(true);
        });

        it('should hide the select when there are participants', function() {
            field.render();
            expect(field.$('[name=newRow]').css('display')).toEqual('none');
        });

        it('should only have one plus button', function() {
            field.render();
            expect(field.$('button[data-action=addRow]').length).toBe(1);
        });

        it('should show the select and hide the plus button when the plus button is clicked', function() {
            sandbox.stub($.fn, 'select2');
            field.render();
            field.$('button[data-action=addRow]').click();
            expect(field.$('[name=newRow]').css('display')).toEqual('table-row');
            expect(field.$('button[data-action=addRow]').css('display')).toEqual('none');
        });

        it("should hide the select and show the plus button when the select row's minus button is clicked", function() {
            sandbox.stub($.fn, 'select2');
            field.render();
            field.$('button[data-action=addRow]').click();
            field.$('button[data-action=removeRow]').last().click();
            expect(field.$('[name=newRow]').css('display')).toEqual('none');
            expect(field.$('button[data-action=addRow]').css('display')).not.toEqual('none');
        });

        it("should remove a participant when that participant's minus button is clicked", function() {
            var spy = sandbox.spy(field.getFieldValue(), 'remove');
            field.render();
            field.$('button[data-action=removeRow]').first().click();
            expect(spy).toHaveBeenCalled();
            expect(field.$('div.row.participant').length).toBe(participants.length - 1);
        });

        it("should not disable a participant's delete button when the participant is not the assigned user", function() {
            field.model.set('assigned_user_id', '1');
            field.render();
            expect(field.$('button[data-action=removeRow][data-id=2]').hasClass('disabled')).toBe(false);
        });

        it("should disable a participant's delete button when the participant is the assigned user", function() {
            field.model.set('assigned_user_id', '1');
            field.render();
            expect(field.$('button[data-action=removeRow][data-id=1]').hasClass('disabled')).toBe(true);
        });

        it("should disable a participant's delete button when the participant is marked as not deletable", function() {
            var participant;
            participant = app.data.createBean('Leads', {id: '1', name: 'Foo Bar'});
            participant.deletable = false;
            field.model.set(field.name, participant);
            field.render();
            expect(field.$('button[data-action=removeRow][data-id=1]').hasClass('disabled')).toBe(true);
        });

        it('should add a participant when a new participant is selected', function() {
            var spy = sandbox.spy(field.getFieldValue(), 'add');
            field.render();
            field.getFieldElement().select2('data', {
                id: '5',
                text: 'George Walton',
                attributes: {_module: 'Contacts', id: '5', name: 'George Walton'}
            }, true);
            expect(spy).toHaveBeenCalled();
            expect(field.$('div.row.participant').length).toBe(participants.length + 1);
        });

        it('should search for more participants and add them to the options', function() {
            var data, query;

            query = {
                term: 'George',
                callback: sandbox.spy()
            };

            sandbox.stub(field.getFieldValue(), 'search').callsFake(function(options) {
                var records = app.data.createMixedBeanCollection([
                    {_module: 'Contacts', id: '5', name: 'George Walton'}
                ]);
                options.success(records);
                options.complete();
            });

            field.search(query);
            data = query.callback.getCall(0).args[0];
            expect(data.more).toBe(false);
            expect(data.results.length).toBe(1);
        });

        it('should not include participants that are already invited', function() {
            var query = {
                term: 'Jim',
                callback: sandbox.spy()
            };

            sandbox.stub(field.getFieldValue(), 'search').callsFake(function(options) {
                var records = app.data.createMixedBeanCollection([
                    {_module: 'Users', id: '1', name: 'Jim Brennan'},
                    {_module: 'Contacts', id: '3', name: 'Jim Gallardo'},
                    {_module: 'Leads', id: '6', name: 'Jim Long'}
                ]);
                options.success(records);
                options.complete();
            });

            field.search(query);
            expect(query.callback.getCall(0).args[0].results.length).toBe(1);
        });

        it('should produce no results when an exception is thrown', function() {
            var query = {
                term: 'Jim',
                callback: sandbox.spy()
            };

            sandbox.stub(field, 'getFieldValue').throws();
            field.search(query);
            expect(query.callback.getCall(0).args[0].results.length).toBe(0);
        });

        it('should request the picture field so that an avatar can be shown upon selection', function() {
            var collection;

            collection = field.getFieldValue();
            sandbox.stub(collection, 'search');

            field.search({});

            expect(collection.search.getCall(0).args[0].fields).toContain('picture');
        });

        it('should request the fields from the definition as well as the fields to be searched', function() {
            var calledWithAllFieldsFromFieldDef, collection, fields;
            collection = field.getFieldValue();
            sandbox.stub(collection, 'search');

            field.search({});
            fields = collection.search.getCall(0).args[0].fields;
            calledWithAllFieldsFromFieldDef = _.every(field.def.fields, function(f) {
                var name = _.isObject(f) ? f.name : f;
                return _.contains(fields, name);
            });

            expect(calledWithAllFieldsFromFieldDef).toBe(true);
        });
    });

    describe('formatting the view model for render', function() {
        beforeEach(function() {
            sandbox.stub(model, 'isNew').returns(false);
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
        });

        it('should return an empty array when an exception is thrown', function() {
            field.model.set(field.name, participants);
            sandbox.stub(field, 'getFieldValue').throws();
            expect(field.format(undefined).length).toBe(0);
        });

        it('should only return an array of 4 participants', function() {
            var collection;
            field.model.set(field.name, participants);
            collection = field.getFieldValue();
            collection.add([
                {_module: 'Contacts', id: '5', name: 'George Walton'},
                {_module: 'Contacts', id: '6', name: 'Jim Long'}
            ]);
            collection.remove(['2', '4']);
            expect(field.format(field.model.get(field.name)).length).toBe(4);
        });

        it('should verify that participant name is not erased', function() {
            var collection;
            var value;
            field.model.set(field.name, participants);
            collection = field.getFieldValue();
            value = field.format(collection);
            expect(value[2].isNameErased).toBe(false);
        });

        it('should verify that one participant has an erased name', function() {
            var collection;
            var value;
            participants[2]._erased_fields = ['first_name', 'last_name'];
            participants[2].name = '';
            field.model.set(field.name, participants);
            collection = field.getFieldValue();
            value = field.format(collection);
            expect(value[2].isNameErased).toBe(true);
            expect(value[2].name).toBe('Value erased');
        });

        it('should set the last property to true for only the final participant', function() {
            var isLast;
            field.model.set(field.name, participants);
            isLast = _.findWhere(field.format(field.model.get(field.name)), {last: true});
            expect(isLast.name).toEqual('Sallie Talmadge');
        });

        it('should only include an avatar property when the participant has a picture field', function() {
            var hasAvatar;
            sandbox.stub(field, '_render');
            field.model.set(field.name, [
                {_module: 'Contacts', id: '5', name: 'George Walton', picture: '5'},
                {_module: 'Contacts', id: '6', name: 'Jim Long'}
            ]);
            hasAvatar = _.filter(field.format(field.model.get(field.name)), function(participant) {
                return !_.isUndefined(participant.avatar);
            });
            expect(hasAvatar.length).toBe(1);
        });

        it('should set the accept status appropriately', function() {
            var formatted;
            field.model.set(field.name, participants);
            field.getFieldValue().add([
                {_module: 'Contacts', id: '5', name: 'George Walton', accept_status_meetings: ''}
            ]);
            formatted = field.format(field.model.get(field.name));
            expect(formatted[0].accept_status.label).toEqual('LBL_CALENDAR_EVENT_RESPONSE_ACCEPT');
            expect(formatted[0].accept_status.css_class).toEqual('success');
            expect(formatted[1].accept_status.label).toEqual('LBL_CALENDAR_EVENT_RESPONSE_DECLINE');
            expect(formatted[1].accept_status.css_class).toEqual('important');
            expect(formatted[2].accept_status.label).toEqual('LBL_CALENDAR_EVENT_RESPONSE_TENTATIVE');
            expect(formatted[2].accept_status.css_class).toEqual('warning');
            expect(formatted[3].accept_status.label).toEqual('LBL_CALENDAR_EVENT_RESPONSE_NONE');
            expect(formatted[3].accept_status.css_class).toEqual('');
            expect(formatted[4].accept_status.label).toEqual('LBL_CALENDAR_EVENT_RESPONSE_NONE');
            expect(formatted[4].accept_status.css_class).toEqual('');
        });
    });

    describe('rendering the timeline', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
            field.model.set(field.name, participants);
            field.model.off();
            field.model.set('date_start', '2014-08-27T08:45');
            field.model.set('date_end', '2014-08-27T10:15');

            sandbox.stub(field, 'getTimeFormat').callsFake(function() {
                return 'ha';
            });
        });

        it('should render the header starting 4 hours before the start date and end 5 hours after', function() {
            field.render();

            expect(field.$('[data-render=timeline-header] .timeblock span').first().text()).toBe('4am');
            expect(field.$('[data-render=timeline-header] .timeblock span').last().text()).toBe('12pm');
        });

        it('should render the header alternating in colors', function() {
            field.render();

            expect(field.$('[data-render=timeline-header] .timeblock').filter(':nth-child(odd)').hasClass('alt')).toBe(true);
            expect(field.$('[data-render=timeline-header] .timeblock').filter(':nth-child(even)').hasClass('alt')).toBe(false);
        });
    });

    describe('rendering the meeting start and end overlay', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
            field.model.set(field.name, participants);
            field.model.off();
            field.model.set('date_start', '2014-08-27T08:45');
            field.model.set('date_end', '2014-08-27T10:15');

            sandbox.stub(field, 'getTimeFormat').callsFake(function() {
                return 'ha';
            });
        });

        it('should only happen for Users', function() {
            var $overlays;

            field.render();
            $overlays = field.$('.start_end_overlay');

            expect($overlays.length).toBe(2);
            expect($overlays.first().closest('.participant').data('id')).toBe(1);
            expect($overlays.last().closest('.participant').data('id')).toBe(2);
        });

        it('should display the right border when the length of the meeting does not go past the timeline', function() {
            var $overlays;

            field.render();
            $overlays = field.$('.start_end_overlay');

            expect($overlays.hasClass('right_border')).toBe(true);
        });

        it('should not display the right border when the length of the meeting goes past the timeline', function() {
            var $overlays;

            field.model.set('date_end', '2014-08-27T16:00');
            field.render();
            $overlays = field.$('.start_end_overlay');

            expect($overlays.hasClass('right_border')).toBe(false);
        });

        it('should make width 1px if the meeting is 0 minutes long', function() {
            var $overlays;

            field.model.set('date_end', '2014-08-27T08:45');
            field.render();
            $overlays = field.$('.start_end_overlay');

            expect($overlays.width()).toBe(1);
        });
    });

    describe('rendering the free/busy information', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
            field.model.off();
            field.getFieldValue().reset(participants);
            field.model.set('date_start', '2014-08-27T08:45:00-04:00');
            field.model.set('date_end', '2014-08-27T10:15:00-04:00');

            sandbox.stub(field, 'getTimeFormat').callsFake(function() {
                return 'ha';
            });
        });

        it('should only fetch information for Users', function() {
            var freebusyFetchSpy = sandbox.spy(field.freebusy, 'fetch');

            field.render();

            expect(freebusyFetchSpy.args[0][0][0].url).toContain('rest/v10/Users/1/freebusy');
            expect(freebusyFetchSpy.args[0][0][1].url).toContain('rest/v10/Users/2/freebusy');
        });

        it('should not fetch information for a user if free/busy information has been cached for that user', function() {
            var freebusyFetchSpy = sandbox.spy(field.freebusy, 'fetch');

            field.cacheFreeBusyInformation({
                id: '1',
                module: 'Users',
                freebusy: []
            });
            field.render();

            expect(_.size(freebusyFetchSpy.args[0][0])).toBe(1);
            expect(freebusyFetchSpy.args[0][0][0].url).toContain('rest/v10/Users/2/freebusy');
        });

        it('should fetch free busy information if date_start has changed', function() {
            var freebusyFetchSpy = sandbox.spy(field.freebusy, 'fetch');

            field.cacheFreeBusyInformation({module: 'Users', id: '1', freebusy: []});
            field.cacheFreeBusyInformation({module: 'Users', id: '2', freebusy: []});
            field.bindDataChange();
            field.render();

            expect(_.size(freebusyFetchSpy.args[0][0])).toBe(0);

            field.model.set('date_start', '2014-08-27T08:00:00-04:00');

            expect(_.size(freebusyFetchSpy.args[1][0])).toBe(2);
        });

        it('should mark busy indicators on timeslots that are taken up by other meetings', function() {
            var $blocks, $busyBlocks;

            field.render();
            field.fillInFreeBusyInformation({
                id: '1',
                module: 'Users',
                freebusy: [{
                    start: '2014-08-27T08:00:00-04:00',
                    end: '2014-08-27T08:30:00-04:00'
                }, {
                    start: '2014-08-27T10:30:00-04:00',
                    end: '2014-08-27T11:00:00-04:00'
                }]
            });

            $blocks = field.getTimelineBlocks('Users', '1');
            $busyBlocks = $blocks.filter('.busy');

            expect($busyBlocks.length).toBe(4);
            expect($blocks.index($busyBlocks.eq(0))).toBe(16);
            expect($blocks.index($busyBlocks.eq(1))).toBe(17);
            expect($blocks.index($busyBlocks.eq(2))).toBe(26);
            expect($blocks.index($busyBlocks.eq(3))).toBe(27);
        });

        it('should mark busy indicators on timeslots where start/end times not on 15 minute boundaries', function() {
            var $blocks;
            var $busyBlocks;

            field.render();
            field.fillInFreeBusyInformation({
                id: '1',
                module: 'Users',
                freebusy: [{
                    start: '2014-08-27T08:50:00-04:00',
                    end: '2014-08-27T09:30:00-04:00'
                }, {
                    start: '2014-08-27T10:00:00-04:00',
                    end: '2014-08-27T10:35:00-04:00'
                }, {
                    start: '2014-08-27T11:55:00-04:00',
                    end: '2014-08-27T12:05:00-04:00'
                }]
            });

            $blocks = field.getTimelineBlocks('Users', '1');
            $busyBlocks = $blocks.filter('.busy');

            expect($busyBlocks.length).toBe(8);
            expect($blocks.index($busyBlocks.eq(0))).toBe(19);
            expect($blocks.index($busyBlocks.eq(1))).toBe(20);
            expect($blocks.index($busyBlocks.eq(2))).toBe(21);
            expect($blocks.index($busyBlocks.eq(3))).toBe(24);
            expect($blocks.index($busyBlocks.eq(4))).toBe(25);
            expect($blocks.index($busyBlocks.eq(5))).toBe(26);
            expect($blocks.index($busyBlocks.eq(6))).toBe(31);
            expect($blocks.index($busyBlocks.eq(7))).toBe(32);
        });

        it('should not show any busy timeslots if other meetings are outside the displayed timeline range', function() {
            var $blocks, $busyBlocks;

            field.render();
            field.fillInFreeBusyInformation({
                id: '1',
                module: 'Users',
                freebusy: [{
                    start: '2014-08-27T03:30:00-04:00',
                    end: '2014-08-27T04:00:00-04:00'
                }, {
                    start: '2014-08-27T13:00:00-04:00',
                    end: '2014-08-27T13:30:00-04:00'
                }]
            });

            $blocks = field.getTimelineBlocks('Users', '1');
            $busyBlocks = $blocks.filter('.busy');

            expect($busyBlocks.length).toBe(0);
        });
    });

    describe('caching the free/busy information', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
        });

        it('should save the information to be retrieved again', function() {
            var data = {
                id: '123',
                module: 'Users',
                freebusy: []
            };

            field.cacheFreeBusyInformation(data);

            expect(field.getFreeBusyInformationFromCache('Users', '123')).toBe(data);
        });

        it('should return nothing if data is not found for the particular user', function() {
            expect(field.getFreeBusyInformationFromCache('Users', '123')).toBeUndefined();
        });

        it('should only have the last data if it has been saved more than once for the same user', function() {
            var data1 = {
                    id: '123',
                    module: 'Users',
                    freebusy: [{
                        start: 'foo'
                    }]
                },
                data2 = {
                    id: '123',
                    module: 'Users',
                    freebusy: [{
                        start: 'bar'
                    }]
                };

            field.cacheFreeBusyInformation(data1);
            field.cacheFreeBusyInformation(data2);

            expect(field._freeBusyCache.length).toBe(1);
            expect(field.getFreeBusyInformationFromCache('Users', '123').freebusy[0].start).toBe('bar');
        });
    });

    describe('fetching the free/busy information', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
        });

        it('second time should not happen if fetching is already in process', function() {
            var freebusyFetchStub = sandbox.stub(field.freebusy, 'fetch').callsFake(function(requests, options) {
                this.isFetching(true);
            });

            field.model.off();
            field.getFieldValue().reset(participants);
            field.model.set('date_start', '2014-08-27T08:45:00-04:00');
            field.model.set('date_end', '2014-08-27T10:15:00-04:00');

            field.fetchFreeBusyInformation();

            expect(freebusyFetchStub.calledOnce).toBe(true);

            field.fetchFreeBusyInformation();

            expect(freebusyFetchStub.calledOnce).toBe(true);
        });

        it('should occur when the model is saved', function() {
            var freebusyFetchSpy = sandbox.stub(field.freebusy, 'fetch');

            field.model.off();
            field.getFieldValue().reset(participants);
            field.model.set('date_start', '2014-08-27T08:45:00-04:00');
            field.model.set('date_end', '2014-08-27T10:15:00-04:00');
            field.bindDataChange();

            field.model.trigger('sync');

            expect(freebusyFetchSpy.calledOnce).toBe(true);
        });
    });

    describe('parseModuleAndIdFromUrl', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
        });

        it('should parse module and ID from freebusy URL', function() {
            expect(field.parseModuleAndIdFromUrl('/v10/Users/123/freebusy')).toEqual({
                module: 'Users',
                id: '123'
            });
            expect(field.parseModuleAndIdFromUrl('/v10_8/Users/123/freebusy')).toEqual({
                module: 'Users',
                id: '123'
            });
            expect(field.parseModuleAndIdFromUrl(
                '/v11_8/Users/1/freebusy?start=2020-04-09T06%3A00%3A00-04%3A00&end=2020-04-09T15%3A00%3A00-04%3A00'
            )).toEqual({
                module: 'Users',
                id: '1'
            });
        });

        it('should return an empty object if module and id has not been found', function() {
            expect(field.parseModuleAndIdFromUrl('/v10/freebusy')).toEqual({});
        });
    });

    describe('formatSearchResult', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'invitees',
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
        });

        it('should only show module and name when no highlighted field', function() {
            var searchResultTemplateStub = sandbox.stub(field, 'searchResultTemplate'),
                result = app.data.createBean('Baz', {
                    full_name: 'Foo Bar',
                    email: [{
                        email_address: 'foo@bar.com',
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }]
                });
            result.module = 'Baz';
            result.searchInfo = {};
            field.formatSearchResult(result);
            expect(searchResultTemplateStub.lastCall.args).toEqual([{
                module: 'Baz',
                name: 'Foo Bar',
                // Always passed, but not displayed.
                email: 'foo@bar.com',
                name_is_erased: false
            }]);
        });

        it('should show module, name, and highlighted field when highlighted field is given', function() {
            var searchResultTemplateStub = sandbox.stub(field, 'searchResultTemplate'),
                result = app.data.createBean('Baz', {
                    full_name: 'Foo Bar',
                    email: [{
                        email_address: 'foo@bar.com',
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }]
                });

            result.module = 'Baz';
            result.searchInfo = {
                highlighted: [{
                    label: 'LBL_ACCOUNT_NAME',
                    module: 'Baz',
                    text: 'Bar Enterprises Inc.'
                }]
            };

            field.formatSearchResult(result);
            expect(searchResultTemplateStub.lastCall.args).toEqual([{
                module: 'Baz',
                name: 'Foo Bar',
                // Always passed, but not displayed.
                email: 'foo@bar.com',
                name_is_erased: false,
                field_name: 'LBL_ACCOUNT_NAME',
                field_value: 'Bar Enterprises Inc.'
            }]);
        });
    });

    describe('paging for more participants', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'invitees',
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
            field.model.set(field.name, participants);
        });

        it('should render the show more button', function() {
            sandbox.stub(model, 'isNew').returns(false);
            sandbox.stub(field.getFieldValue(), 'hasMore').returns(true);
            field.render();

            expect(field.$('[data-action=show-more]').css('display')).not.toEqual('none');
        });

        it('should hide the show more button during create', function() {
            sandbox.stub(model, 'isNew').returns(true);
            sandbox.stub(field.getFieldValue(), 'hasMore').returns(true);
            field.render();

            expect(field.$('[data-action=show-more]').css('display')).toEqual('none');
        });

        it('should hide the show more button when there are no more records to fetch', function() {
            sandbox.stub(model, 'isNew').returns(false);
            sandbox.stub(field.getFieldValue(), 'hasMore').returns(false);
            field.render();

            expect(field.$('[data-action=show-more]').css('display')).toEqual('none');
        });

        it('should hide the show more button when `sync` is triggered on the model and there are no more records to fetch', function() {
            sandbox.stub(model, 'isNew').returns(false);
            field.render();

            expect(field.$('[data-action=show-more]').css('display')).not.toEqual('none');

            sandbox.stub(field.getFieldValue(), 'hasMore').returns(false);
            field.model.trigger('sync:' + field.name);

            expect(field.$('[data-action=show-more]').css('display')).toEqual('none');
        });

        it('should paginate when the show more button is clicked', function() {
            var args, collection, fieldNames;

            sandbox.stub(model, 'isNew').returns(false);

            collection = field.getFieldValue();
            sandbox.stub(collection, 'hasMore').returns(true);
            sandbox.stub(collection, 'paginate');

            field.render();
            field.$('[data-action=show-more]').click();
            args = collection.paginate.lastCall.args[0];

            fieldNames = _.map(field.def.fields, function (f) {
                return _.isObject(f) ? f.name : f;
            });
            expect(args.fields).toEqual(fieldNames);
            expect(args.order_by).toEqual('name:asc');
        });
    });

    describe('getTimeFormat', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'invitees',
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
        });

        using('time formats', [
                ['HH:mm', 'H'],
                ['HH.mm', 'H'],
                ['hh:mma', 'ha'],
                ['hh:mm a', 'ha'],
                ['hh:mmA', 'hA'],
                ['hh:mm A', 'hA']
            ], function(timeFormat, expected) {
                it('should return display time format correctly', function() {
                    sandbox.stub(app.date, 'getUserTimeFormat').callsFake(function() {
                        return timeFormat;
                    });

                    expect(field.getTimeFormat()).toEqual(expected);
                });
            }
        );
    });

    describe('getStartAndEndDates()', function() {
        var results;

        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'invitees',
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
        });

        describe('should return empty', function() {
            it('if date_start does not exist', function() {
                results = field.getStartAndEndDates();

                expect(_.isEmpty(results)).toBeTruthy();
            });

            it('if date_start does exists but date_end/duration_hours/minutes does not exist', function() {
                field.model.set('date_start', '2016-04-06Z')
                results = field.getStartAndEndDates();

                expect(_.isEmpty(results)).toBeTruthy();
            });
        });

        describe('should return meeting start/end Object', function() {
            it('with date_start and date_end defined', function() {
                field.model.set({
                    date_start: '2016-04-06T14:00:00Z',
                    date_end: '2016-04-06T15:30:00Z'
                });
                results = field.getStartAndEndDates();

                expect(results.meetingStart).toBeDefined();
                expect(results.meetingEnd).toBeDefined();
                expect(results.timelineStart).toBeDefined();
                expect(results.timelineEnd).toBeDefined();

                expect(results.meetingStart.toISOString()).toBe('2016-04-06T14:00:00.000Z');
                expect(results.meetingEnd.toISOString()).toBe('2016-04-06T15:30:00.000Z');
            });

            it('with date_start, duration_hours, and duration_minutes defined', function() {
                field.model.set({
                    date_start: '2016-04-06T14:00:00Z',
                    duration_hours: 1,
                    duration_minutes: 30
                });
                results = field.getStartAndEndDates();

                expect(results.meetingStart).toBeDefined();
                expect(results.meetingEnd).toBeDefined();
                expect(results.timelineStart).toBeDefined();
                expect(results.timelineEnd).toBeDefined();

                expect(results.meetingStart.toISOString()).toBe('2016-04-06T14:00:00.000Z');
                expect(results.meetingEnd.toISOString()).toBe('2016-04-06T15:30:00.000Z');
            });
        });
    });
});
