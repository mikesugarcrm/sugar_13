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

/**
 * Tests the basic behavior for commentlog field
 * */
describe('commentlog field', function() {
    var app;
    var field;
    var template;
    var editTemplate;
    var module = 'Bugs';
    var fieldName = 'commentlog';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'textarea');
        template = SugarTest.loadHandlebarsTemplate(fieldName, 'field', 'base', 'detail');
        editTemplate = SugarTest.loadHandlebarsTemplate(fieldName, 'field', 'base', 'edit');
        SugarTest.seedMetadata(false, 'fixtures');
        SugarTest.testMetadata.set();
        var fieldDef = {
            name: 'commentlog',
            type: 'collection'
        };
        app.config.commentlog = {maxchars: 500};
        field = SugarTest.createField('base', fieldName, 'commentlog', 'detail', fieldDef, module);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
        sinon.restore();
    });

    it('should contain the taggable plugin', function() {
        expect(field.plugins).toContain('Taggable');
    });

    describe('setUpTaggable', function() {
        it('should call setTaggableRecord', function() {
            var setTaggableRecordStub = sinon.stub(field, 'setTaggableRecord');
            field.setUpTaggable();
            expect(setTaggableRecordStub).toHaveBeenCalled();
        });
    });

    describe('Detailed View Behavior', function() {
        beforeEach(function() {
            field.tplName = 'detail';
        });

        using('Test if formating function would parse passed in json data correctly',
            [
                {
                    'msgs': [] // when no message has been recorded in the past
                },
                {
                    'msgs': [
                        {
                            'created_by_name': 'I\' the author, I authored',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' +
                            '\' Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when ' +
                            'an unknown printer took a galley of type and scrambled it to make a type specimen bo' +
                            'ok. It has survived not only five centuries, but also the leap into electronic types' +
                            'etting, remaining essentially unchanged. It was popularised in the 1960s with the re' +
                            'lease of Letraset sheets containing Lorem Ipsum passages, and more recently with des' +
                            'ktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'
                        }
                    ]
                },
                {
                    'msgs': [
                        {
                            'created_by_name': 'I\' the author, I authored',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' +
                            '\' Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when' +
                            '\' an unknown printer took a galley of type and scrambled it to make a type specimen ' +
                            'book. It has survived not only five centuries, but also the leap into electronic ty' +
                            'pesetting, remaining essentially unchanged. It was popularised in the 1960s with th' +
                            'e release of Letraset sheets containing Lorem Ipsum passages, and more recently wit' +
                            'h desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'
                        },
                        {
                            'created_by_name': 'I am another author, and a wizard',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Ur a wizard Harry.'
                        }
                    ]
                },
                {
                    'msgs': [
                        {
                            'created_by_name': 'I\' the author, I authored',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' +
                            '\' Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when' +
                            '\' an unknown printer took a galley of type and scrambled it to make a type specimen \'' +
                            'book. It has survived not only five centuries, but also the leap into electronic ty' +
                            'pesetting, remaining essentially unchanged. It was popularised in the 1960s with th' +
                            'e release of Letraset sheets containing Lorem Ipsum passages, and more recently wit' +
                            'h desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'
                        },
                        {
                            'created_by_name': 'I am another author, and a wizard',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Ur a wizard Harry.'
                        },
                        {
                            'created_by_name': 'U are a wizard, but I am a lizard',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Lizards are better'
                        }
                    ]
                }
            ], function(value) {
                it('format() should correctly parse passed in function', function() {
                    var resultValue = field.format(value.msgs);

                    // the amount of the messages should remain the same
                    expect(resultValue.length).toEqual(value.msgs.length);

                    // the order of each message should remain the same
                    for (var i = 0; i < value.msgs.length; i++) {
                        // the entry should be exactly the same, since commentlog.js is not responsible
                        // for formatting
                        expect(resultValue[i].created_by_name).toEqual(value.msgs[i].created_by_name);
                        expect(resultValue[i].date_entered).toEqual(value.msgs[i].date_entered);
                        expect(resultValue[i].entry).toEqual(value.msgs[i].entry);
                    }
                });
            }, function(value) {
                it('showCommentLog() should handle data from showCommentLog correctly', function() {
                    field.model.set(fieldName, value.msgs);
                    field.showCommentLog();

                    // the amount of the messages should remain the same
                    expect(field.msgs.length).toEqual(value.msgs.length);

                    // the order of each message should remain the same
                    for (var i = 0; i < value.msgs.length; i++) {
                        // the entry should be exactly the same, since commentlog.js is not responsible
                        // for formatting
                        expect(field.msgs[i].created_by_name).toEqual(value.msgs[i].created_by_name);
                        expect(field.msgs[i].date_entered).toEqual(value.msgs[i].date_entered);
                        expect(field.msgs[i].entry).toEqual(value.msgs[i].entry);
                    }
                });
            });
    });

    describe('Edit View Behavior', function() {
        beforeEach(function() {
            field.tplName = 'edit';
        });

        using('unformat()', [
            '\'\'', // nothing
            '\'     \'', // nothing but spaces
            '<p>I\'m a paragraph, with terms of html</p>',
            '<scr' + 'ipt>console.log(\'Im vicious\')</scr' + 'ipt>',
            'I\'m a muggle, i don\'t magic',
        ], function(value) {
            it('should return whatever was entered in the textarea', function() {
                expect(field.unformat(value)).toEqual(value);
            });
        });
    });

    describe('Bugs from past', function() {
        var userDateFormatStub;
        var userTimeFormatStub;
        var aclStub;

        beforeEach(function() {
            field.tplName = 'detail';
            userDateFormatStub = sinon.stub(app.date, 'getUserDateFormat').callsFake(function() { return 'YYMMDD'; });
            userTimeFormatStub = sinon.stub(app.date, 'getUserTimeFormat').callsFake(function() { return 'H:m'; });
            aclStub = sinon.stub(app.acl, 'hasAccess').callsFake(function() { return true; });
        });

        afterEach(function() {
            userDateFormatStub.restore();
            userTimeFormatStub.restore();
            aclStub.restore();
        });

        using('Going into edit mode and then going back to detail mode will not display all past messages',
            [
                {
                    'msgs': [
                        {
                            'id': 'a',
                            'created_by_name': 'I\' the author, I authored',
                            'date_entered': '2018-08-29T22:50:17+00:00',
                            'entry': 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' +
                            '\' Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when ' +
                            'an unknown printer took a galley of type and scrambled it to make a type specimen bo' +
                            'ok. It has survived not only five centuries, but also the leap into electronic types' +
                            'etting, remaining essentially unchanged. It was popularised in the 1960s with the re' +
                            'lease of Letraset sheets containing Lorem Ipsum passages, and more recently with des' +
                            'ktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
                            'created_by_link': {
                                '_acl': 'stub'
                            }
                        },
                        {
                            'id': 'b',
                            'created_by_name': 'I am another author, and a wizard',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Ur a wizard Harry.',
                            'created_by_link': {
                                '_acl': 'stub'
                            }
                        },
                        {
                            'id': 'c',
                            'created_by_name': 'U are a wizard, but I am a lizard',
                            'date_entered': '2018-08-29T22:52:17+00:00',
                            'entry': 'Lizards are better',
                            'created_by_link': {
                                '_acl': 'stub'
                            }
                        }
                    ],
                    'entered': 'Please don\'t kill me, I have a family\''
                }
            ],
            function(value) {
                it('should still show the same past messages after coming back from edit mode without saving anything',
                    function() {
                        // first render the details mode
                        var collOptions = {
                            parentBean: field.model,
                            collectionField: fieldName,
                            links: ['commentlog_link']
                        };
                        var coll = app.data.createMixedBeanCollection(
                            [],
                            collOptions
                        );
                        var oldCollection = app.data.createMixedBeanCollection(value.msgs, collOptions);
                        field.model.set(fieldName, coll);
                        field.model.get(fieldName).add(value.msgs);
                        field.model.setSyncedAttributes({commentlog: coll});
                        field.model.save();
                        field.render();

                        // the amount of the messages should remain the same
                        expect(field.msgs.length).toEqual(value.msgs.length);

                        // go into edit mode
                        field.setMode('edit');
                        field.$el.find(field.fieldTag).val(value.entered).change();

                        // return to detail view without saving
                        sinon.stub(field.model, 'changedAttributes').returns({commentlog: oldCollection});
                        sinon.stub(coll, 'hasDelta').returns(true);
                        sinon.stub(field.model, 'getCollectionFields').returns({commentlog: oldCollection});
                        field.model.revertAttributes();
                        field.setMode('detail');

                        // the amount of the messages should be the same as before
                        expect(field.msgs.length).toEqual(value.msgs.length);
                    }
                );
            },
            function(value) {
                it('should still keep entered message after entering, focus on something outside of testarea, then ' +
                   'come back',
                    function() {
                        // first render the record mode
                        field.model.set(fieldName, value.msgs);
                        field.render();

                        // the amount of the messages should remain the same
                        expect(field.msgs.length).toEqual(value.msgs.length);

                        // get into edit mode
                        field.tplName = 'edit';
                        field.model.set(fieldName, value.entered);
                        field.render();

                        // should read in the value as entered
                        expect(field.value).toEqual(value.entered);

                        // render edit mode
                        field.render();

                        // previously entered value should be still there even came back from somewhere else
                        expect(field.value).toEqual(value.entered);
                    });
            });
    });

    describe('showCommentLog', function() {
        var collOptions;

        beforeEach(function() {
            field.tplName = 'detail';
            collOptions = {
                parentBean: field.model,
                collectionField: fieldName,
                links: {commentlog_link: field.model.getRelatedCollection('commentlog_link')}
            };

            app.routing.start();
            sinon.stub(app.date, 'getUserDateFormat').returns('YYMMDD');
            sinon.stub(app.date, 'getUserTimeFormat').returns('H:m');
            sinon.stub(app.metadata, 'getModule')
                .withArgs('Employees')
                .returns({
                    color: 'pink',
                    icon: 'sicon-user',
                    display_type: 'icon'
                });
        });

        afterEach(function() {
            app.router.stop();
        });

        it('should set href on msg when created_by_link exists and user has access to it', function() {
            var modelAttr = {
                'created_by_name': 'James Bond',
                'date_entered': '2018-08-29T22:52:17+00:00',
                'entry': 'I am Bond. James Bond',
                'created_by_link': {
                    '_acl': 'stub',
                    'id': '007'
                }
            };
            sinon.stub(app.acl, 'hasAccess').returns(true);
            sinon.stub(app.router, 'buildRoute')
                .withArgs('Employees', modelAttr.created_by_link.id, 'detail')
                .returns('007');

            var bean = app.data.createBean(fieldName, modelAttr);
            var coll = app.data.createMixedBeanCollection([], collOptions);
            coll.add(bean);
            field.model.set(fieldName, coll);
            field.showCommentLog();
            expect(field.msgs[0].href).toEqual('#007');
        });

        it('should not set href on msg when user does not have access to created_by_link', function() {
            var modelAttr = {
                'created_by_name': 'James Bond',
                'date_entered': '2018-08-29T22:52:17+00:00',
                'entry': 'I am Bond. James Bond',
                'created_by_link': {
                    '_acl': 'stub',
                    'id': '007'
                }
            };
            sinon.stub(app.acl, 'hasAccess').returns(false);

            var bean = app.data.createBean(fieldName, modelAttr);
            var coll = app.data.createMixedBeanCollection([], collOptions);
            coll.add(bean);
            field.model.set(fieldName, coll);
            field.showCommentLog();

            expect(field.msgs[0]).toBeTruthy();
            expect(field.msgs[0].href).toBeUndefined();
        });

        it('should set href on msg when created_by exists', function() {
            var modelAttr = {
                'created_by_name': 'Bat Man',
                'date_entered': '2018-08-29T22:52:17+00:00',
                'entry': 'I am Batman. Guardian of Gotham',
                'created_by': '212'
            };
            sinon.stub(app.router, 'buildRoute')
                .withArgs('Employees', modelAttr.created_by, 'detail')
                .returns('212');

            var bean = app.data.createBean(fieldName, modelAttr);
            var coll = app.data.createMixedBeanCollection([], collOptions);
            coll.add(bean);
            field.model.set(fieldName, coll);
            field.showCommentLog();

            expect(field.msgs[0].href).toEqual('#212');
        });

        it('should not have dangerous chars in it', function() {
            var modelAttr = {
                'created_by_name': 'Bat Man',
                'date_entered': '2018-08-29T22:52:17+00:00',
                'entry': '<a>&',
                'created_by': '212',
            };

            var bean = app.data.createBean(fieldName, modelAttr);
            var coll = app.data.createMixedBeanCollection([], collOptions);
            coll.add(bean);
            field.model.set(fieldName, coll);
            field.showCommentLog();

            expect(field.msgs[0].entry instanceof Handlebars.SafeString).toBeTruthy();
            expect(field.msgs[0].entry.toString()).toEqual('&lt;a&gt;&amp;');
        });

        it('should contain html links', function() {
            var modelAttr = {
                'created_by_name': 'Bat Man',
                'date_entered': '2018-08-29T22:52:17+00:00',
                'entry': 'Site: www.sugarcrm.com',
                'created_by': '212',
            };

            var bean = app.data.createBean(fieldName, modelAttr);
            var coll = app.data.createMixedBeanCollection([], collOptions);
            coll.add(bean);
            field.model.set(fieldName, coll);
            field.showCommentLog();
            var expected =
                'Site: <a href="http://www.sugarcrm.com" target="_blank" ' +
                'rel="nofollow noopener noreferrer">www.sugarcrm.com</a>';
            expect(field.msgs[0].entry.toString()).toEqual(expected);
        });

        it('should allow @mention tags', function() {
            var modelAttr = {
                'created_by_name': 'Bat Man',
                'date_entered': '2018-08-29T22:52:17+00:00',
                'entry': '@[Users:seed_sally_id:Sally Bronson]',
                'created_by': '212',
            };
            var bean = app.data.createBean(fieldName, modelAttr);
            var coll = app.data.createMixedBeanCollection([], collOptions);
            coll.add(bean);
            field.model.set(fieldName, coll);
            field.showCommentLog();
            var expected =
                '<span class="label label-module-color-pink sugar_tag">' +
                '<a href="#Employees/seed_sally_id">Sally Bronson</a></span>';
            expect(field.msgs[0].entry.toString()).toEqual(expected);
        });

        using('different collection values',[
            [false, 0, false],
            [true, 5, true],
            [true, -1, false]
        ], function(dataFetched, nextOffset, expected) {
            it('should set _showViewAll', function() {
                var collection = app.data.createBeanCollection('CommentLog');
                sinon.stub(field, 'getCollection').returns(collection);
                collection.dataFetched = dataFetched;
                collection.next_offset = nextOffset;
                field.showCommentLog();
                expect(field._showViewAll).toEqual(expected);
            });
        });

    });

    describe('_escapeValue', function() {
        it('should escape dangerous chars', function() {
            var badText = '<>`""';
            var result = field._escapeValue(badText);
            var expected = '&lt;&gt;&#x60;&quot;&quot;';
            expect(result).toEqual(expected);
        });
    });

    describe('save', function() {
        it('should blank out textarea and reload data after save', function() {
            var textarea = $('<textarea>test</textarea>');
            sinon.stub(field, 'getTextArea').returns(textarea);
            var newComment = app.data.createBean();
            sinon.stub(app.data, 'createRelatedBean').returns(newComment);
            sinon.stub(newComment, 'sync').yieldsTo('success');
            var loadDataStub = sinon.stub(field.view, 'loadData');
            field.save();
            expect(loadDataStub).toHaveBeenCalled();
            expect(textarea.val()).toEqual('');
        });

        it('should not work if this is a preview of the dashlet', function() {
            field.view._mode = 'preview';
            var bean = app.data.createBean('Commentlog');
            var syncStub = sinon.stub(bean, 'sync');
            sinon.stub(app.data, 'createRelatedBean').returns(bean);
            field.save();
            expect(syncStub).not.toHaveBeenCalled();
        });
    });
});
