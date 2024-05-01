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
describe("Taggable Plugin", function() {
    var plugin, filtersBeanPrototype;
    var app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        SugarTest.loadPlugin('Taggable');
        plugin = app.plugins.plugins.view.Taggable;
        plugin.useAvatarNamePills();

        SugarTest.app.data.declareModels();
        SugarTest.declareData('base', 'Filters');
        filtersBeanPrototype = SugarTest.app.data.getBeanClass('Filters').prototype;
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
    });

    describe("unformatTags", function() {
        it("Should translate HTML tags into text based format", function() {
            var $input = $('<div></div>'),
                $tag1 = $('<span></span>').data({
                    id: '123',
                    module: 'Accounts',
                    name: 'foo bar'
                }),
                $tag2 = $('<span></span>').data({
                    id: '456',
                    module: 'Contacts',
                    name: 'test test'
                });

            $input
                .append('foo')
                .append($tag1)
                .append('bar')
                .append($tag2);

            expect(plugin.unformatTags($input).value).toBe('foo@[Accounts:123:foo bar]bar@[Contacts:456:test test]');
        });

        it("Should parse out taggable data as objects", function() {
            var $input = $('<div></div>'),
                tagData1 = {
                    id: '123',
                    module: 'Accounts',
                    name: 'foo bar'
                },
                tagData2 = {
                    id: '456',
                    module: 'Contacts',
                    name: 'test test'
                },
                $tag1 = $('<span></span>').data(tagData1),
                $tag2 = $('<span></span>').data(tagData2),
                result;

            $input
                .append('foo')
                .append($tag1)
                .append('bar')
                .append($tag2);

            result = plugin.unformatTags($input);

            expect(result.tags[0].id).toBe(tagData1.id);
            expect(result.tags[0].module).toBe(tagData1.module);
            expect(result.tags[0].name).toBe(tagData1.name);

            expect(result.tags[1].id).toBe(tagData2.id);
            expect(result.tags[1].module).toBe(tagData2.module);
            expect(result.tags[1].name).toBe(tagData2.name);
        });

        it("Should replace all non-breaking spaces into actual spaces", function() {
            var $input = $('<div></div>'),
                $tag = $('<span></span>').data({
                    id: '123',
                    module: 'Accounts',
                    name: 'foo bar'
                }),
                result,
                regexp = new RegExp(String.fromCharCode(160), 'g');

            $input
                .append('foo&nbsp;')
                .append($tag)
                .append('&nbsp; bar');

            result = plugin.unformatTags($input);

            // replace all non-breaking spaces to regular spaces
            result.value = result.value.replace(regexp, String.fromCharCode(32));

            expect(result.value).toBe('foo @[Accounts:123:foo bar]  bar');
        });

    });

    describe("formatTags", function() {
        var oldRouter;

        beforeEach(function() {
            oldRouter = SugarTest.app.router;
            SugarTest.app.router = {
                buildRoute: function(module, id, action) {
                    return module + '/' + id;
                }
            };

            sinon.stub(app.metadata, 'getModule')
                .withArgs('Accounts')
                .returns({
                    color: 'green',
                    icon: 'sicon-accounts-lg',
                    display_type: 'icon'
                })
                .withArgs('Leads')
                .returns({
                    color: 'orange',
                    icon: 'sicon-contacts-lg',
                    display_type: 'icon'
                })
                .withArgs('Contacts')
                .returns({
                    color: 'red',
                    icon: 'sicon-contacts-lg',
                    display_type: 'icon'
                });
        });

        afterEach(function() {
            SugarTest.app.router = oldRouter;
        });

        it("Should translate text based tags into HTML format", function() {
            var result = plugin.formatTags('foo @[Accounts:1234:foo bar] bar @[Contacts:321:foo bar]');
            expect($('<div></div>').append(result).html()).toBe('foo ' +
                '<span class="label label-module-color-green sugar_tag"><a href="#Accounts/1234">foo bar</a></span> ' +
                'bar <span class="label label-module-color-red sugar_tag"><a href="#Contacts/321">foo bar</a></span>'
            );
        });

        it("should unescape html entities in tags", function() {
            var result = plugin.formatTags('foo @[Accounts:1234:Jim O&#x27;Connor] bar');
            expect(result).toContain('Jim O&#x27;Connor');
            expect(result).toNotContain('&amp;');
        });

        it('should replace erased names with "Value erased"', function() {
            var str = 'foo @[Accounts:1234:LBL_VALUE_ERASED] bar @[Leads:890:biz] baz @[Contacts:321:LBL_VALUE_ERASED]';
            var expected = 'foo <span class="label label-module-color-green sugar_tag">' +
                '<a href="#Accounts/1234">Value erased</a></span> bar ' +
                '<span class="label label-module-color-orange sugar_tag"><a href="#Leads/890">biz</a></span> ' +
                'baz ' +
                '<span class="label label-module-color-red sugar_tag"><a href="#Contacts/321">Value erased</a></span>';
            var actual = plugin.formatTags(str);

            expect($('<div></div>').append(actual).html()).toBe(expected);
        });

        it('should replace "LBL_NO_DATA_AVAILABLE_NO_PERIOD"', function() {
            var str = 'foo @[Accounts:1234:LBL_NO_DATA_AVAILABLE_NO_PERIOD]';
            var expected = 'foo ' +
                '<span class="label label-module-color-green sugar_tag">' +
                '<a href="#Accounts/1234">LBL_NO_DATA_AVAILABLE_NO_PERIOD</a></span>';
            var actual = plugin.formatTags(str);

            expect($('<div></div>').append(actual).html()).toBe(expected);
        });
    });

    describe("_searchForTags", function() {
        var searchStub, beanStub;

        beforeEach(function() {
            searchStub = sinon.stub(SugarTest.app.api, 'search');
            beanStub = sinon.mock(SugarTest.app.data);
            plugin.taggableSearchAfter = 3;
        });

        afterEach(function() {
            searchStub.restore();
            beanStub.verify();
        });

        it("Should search for users when @ is specified", function() {
            var spy = sinon.spy();
            beanStub.expects('createBeanCollection')
                .once().withExactArgs('Users')
                .returns({fetch: spy});
            filtersBeanPrototype.buildSearchTermFilter = sinon.mock().returns([]);

            plugin._searchForTags('@foo');

            expect(spy.calledOnce).toBe(true);
            filtersBeanPrototype.buildSearchTermFilter.verify();
        });

        it("Should search for all modules except users when # is specified", function() {
            plugin._searchForTags('#foo');

            expect(searchStub.calledOnce).toBe(true);
            expect(searchStub.args[0][0].module_list).toBeUndefined();
        });

        it("Should not search when trying to search for terms that are less than what is specified by taggableSearchAfter", function() {
            var spy = sinon.spy();
            beanStub.expects('createBeanCollection').never().returns({fetch: spy});
            plugin._searchForTags('@fo');

            expect(spy.called).toBe(false);
        });

        it("Should only search once when searching the same terms twice", function() {
            var spy = sinon.spy();
            filtersBeanPrototype.buildSearchTermFilter = sinon.mock().returns([]);
            beanStub.expects('createBeanCollection').once().returns({fetch: spy});
            plugin._searchForTags('@foo');
            plugin._searchForTags('@foo');

            expect(spy.calledOnce).toBe(true);
            filtersBeanPrototype.buildSearchTermFilter.verify();
        });

        it("Should not search but instead reset taggable when the previous search returned no results and is searching for tags that starts with the same text", function() {
            var resetTaggableStub = sinon.stub(plugin, '_resetTaggable'),
                spy = sinon.spy();

            plugin._taggableListOpen = false;
            plugin._taggableLastSearchTerm = 'foo';

            plugin._searchForTags('@foo bar');

            expect(resetTaggableStub.calledOnce).toBe(true);
            expect(spy.called).toBe(false);

            resetTaggableStub.restore();
        });
    });

    describe("_initializeDropdown", function() {
        var view;

        beforeEach(function() {
            view = app.view.createView({type: 'base'});
            _.extend(view, plugin);
        });

        afterEach(function() {
            view.dispose();
        });

        it("Should create a new dropdown if none already exists", function() {
            view.$el.append('<div class="taggable"></div>');
            view._initializeDropdown();

            expect(view.$('.activitystream-tag-dropdown').length).toBe(1);
        });

        it("Should return an existing dropdown if it already exists", function() {
            var result;

            view.$el.append('<div class="taggable"></div><ul class="activitystream-tag-dropdown" data-test="foo"></ul>');
            result = view._initializeDropdown();

            expect(result.data('test')).toBe('foo');
        });
    });

    describe("_populateTagList", function() {
        var dropdown, input, initializeDropdownStub, getDropdownStub, getTaggableInputStub;

        beforeEach(function() {
            dropdown = $('<ul class="activitystream-tag-dropdown"></ul>');
            input = $('<div class="taggable"><span class="sugar_tagging"></span></div>');
            initializeDropdownStub = sinon.stub(plugin, '_initializeDropdown').callsFake(function() {
                return dropdown;
            });
            getDropdownStub = sinon.stub(plugin, '_getDropdown').callsFake(function() {
                return dropdown;
            });
            getTaggableInputStub = sinon.stub(plugin, '_getTaggableInput').callsFake(function() {
                return input;
            });
        });

        afterEach(function() {
            initializeDropdownStub.restore();
            getDropdownStub.restore();
            getTaggableInputStub.restore();
        });

        it("Should have two tags in the dropdown when two matches have been returned from the server", function() {
            var collection = new Backbone.Collection([{
                    _module: 'Accounts',
                    id: '123',
                    name: 'foo bar'
                }, {
                    _module: 'Accounts',
                    id: '345',
                    name: 'test 123'
                }]);

            input.find('.sugar_tagging').text('@foo');
            plugin._populateTagList(collection, 'foo');

            expect(dropdown.children().length).toBe(2);
        });

        it("Should have nothing in the list when there are no matches", function() {
            input.find('.sugar_tagging').text('@foo');
            plugin._populateTagList(new Backbone.Collection(), 'foo');

            expect(dropdown.children().length).toBe(0);
        });

        it("Should mark the first tag as active", function() {
            var collection = new Backbone.Collection([{
                _module: 'Accounts',
                id: '123',
                name: 'foo bar'
            }, {
                _module: 'Accounts',
                id: '345',
                name: 'test 123'
            }]);

            input.find('.sugar_tagging').text('@foo');
            plugin._populateTagList(collection, 'foo');

            expect(dropdown.children().first().hasClass('active')).toBe(true);
            expect(dropdown.children().last().hasClass('active')).toBe(false);
        });

        it("Should display the dropdown list when there are matches", function() {
            var collection = new Backbone.Collection([{
                _module: 'Accounts',
                id: '123',
                name: 'foo bar'
            }, {
                _module: 'Accounts',
                id: '345',
                name: 'test 123'
            }]);

            input.find('.sugar_tagging').text('@foo');
            plugin._populateTagList(collection, 'foo');

            expect(dropdown.css('display')).not.toBe('none');
        });

        it("Should hide the dropdown list when there are no matches", function() {
            input.find('.sugar_tagging').text('@foo');
            plugin._populateTagList(new Backbone.Collection(), 'foo');

            expect(dropdown.css('display')).toBe('none');
        });

        it("Should display nothing in the dropdown list when the search result doesn't match the current search term", function() {
            var collection = new Backbone.Collection([{
                _module: 'Accounts',
                id: '123',
                name: 'foo bar'
            }, {
                _module: 'Accounts',
                id: '345',
                name: 'test 123'
            }]);

            input.find('.sugar_tagging').text('@foobar');
            plugin._populateTagList(collection, 'foo');

            expect(dropdown.children().length).toBe(0);
        });
    });

    describe("_selectNextListOption", function() {
        var dropdown, getDropdownStub;

        beforeEach(function() {
            dropdown = $('<ul class="activitystream-tag-dropdown"><li><a><div class="label label-module-mini label-Accounts pull-left">Ac</div><strong>foo</strong> bar</a></li><li><a><div class="label label-module-mini label-Accounts pull-left">Ac</div>test 123</a></li></ul>');
            getDropdownStub = sinon.stub(plugin, '_getDropdown').callsFake(function() {
                return dropdown;
            });
        });

        afterEach(function() {
            getDropdownStub.restore();
        });

        it("Should make the first item on the list active when none is active", function() {
            plugin._selectNextListOption(true);

            expect(dropdown.children().first().hasClass('active')).toBe(true);
            expect(dropdown.children().last().hasClass('active')).toBe(false);
        });

        it("Should make the second item on the list active when the first option is disabled", function() {
            dropdown.children().first().addClass('disabled');

            plugin._selectNextListOption(true);

            expect(dropdown.children().first().hasClass('active')).toBe(false);
            expect(dropdown.children().last().hasClass('active')).toBe(true);
        });

        it("Should make the second item on the list active when we go down once", function() {
            dropdown.children().first().addClass('active');

            plugin._selectNextListOption(true);

            expect(dropdown.children().first().hasClass('active')).toBe(false);
            expect(dropdown.children().last().hasClass('active')).toBe(true);
        });

        it("Should make the first item on the list active when we go up", function() {
            dropdown.children().first().addClass('active');

            plugin._selectNextListOption(false);

            expect(dropdown.children().first().hasClass('active')).toBe(true);
            expect(dropdown.children().last().hasClass('active')).toBe(false);
        });

        it("Should make the first item on the list active when we go down but the second item is disabled", function() {
            dropdown.children().last().addClass('disabled');

            plugin._selectNextListOption(true);

            expect(dropdown.children().first().hasClass('active')).toBe(true);
            expect(dropdown.children().last().hasClass('active')).toBe(false);
        });

        it("Should make the first item on the list active when we go down and then up", function() {
            dropdown.children().first().addClass('active');

            plugin._selectNextListOption(true);
            plugin._selectNextListOption(false);

            expect(dropdown.children().first().hasClass('active')).toBe(true);
            expect(dropdown.children().last().hasClass('active')).toBe(false);
        });

        it("Should make the second item on the list active when we go down twice", function() {
            dropdown.children().first().addClass('active');

            plugin._selectNextListOption(true);
            plugin._selectNextListOption(true);

            expect(dropdown.children().first().hasClass('active')).toBe(false);
            expect(dropdown.children().last().hasClass('active')).toBe(true);
        });
    });

    describe("_filterOutDuplicateTags", function() {
        var tags;

        beforeEach(function() {
            tags = [{id: 1}, {id: 2}, {id: 2}, {id: 3}];
        });

        it("Should filter out all duplicate tags based on IDs", function() {
            var expected = [{id: 1}, {id: 2}, {id: 3}],
                result = plugin._filterOutDuplicateTags(tags);

            expect(JSON.stringify(result)).toBe(JSON.stringify(expected));
        });
    });

    describe('getCurrentSearchTerm', function() {
        using('different textarea values', [
            {
                value: '@hello', caretPosition: 5, expected: '@hello',
            },
            {
                value: 'hello', caretPosition: 4, expected: '',
            },
            {
                value: '@hello @', caretPosition: 7, expected: '@',
            },
            {
                value: '@hello @', caretPosition: 5, expected: '@hello',
            },
        ], function(data) {
            it('should get correct searchTerms', function() {
                var $input = $('<textarea>' + data.value + '</textarea>');
                plugin.taggableCursorPosition = data.caretPosition;
                expect(plugin.getCurrentSearchTerm($input)).toEqual(data.expected);
            });
        });
    });
});
