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

describe('Base.Views.ContentsearchDashlet', function() {
    var app;
    var view;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        var context = new app.Context();
        SugarTest.loadComponent('base', 'view', 'contentsearchdashlet');
        SugarTest.loadComponent('base', 'layout', 'default');
        layout = SugarTest.createLayout('base', null, 'default', {});
        view = SugarTest.createView(
            'base',
            null,
            'contentsearchdashlet',
            {name: 'test'},
            context,
            true,
            layout,
            true,
            'base'
        );
        sinon.stub(layout, 'off');
        sinon.stub(view, '$').callsFake(function() {
            return {
                val: function() {
                    return 'val';
                },
                after: $.noop
            };
        });
        sinon.stub(app.utils, 'buildUrl').returns('url');
    });

    afterEach(function() {
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;
        sinon.restore();
    });

    describe('searchCases', function() {
        it('should create dropdown and search', function() {
            sinon.stub(app.view, 'createLayout').callsFake(function() {
                return {
                    initComponents: $.noop,
                    render: $.noop,
                    hide: $.noop,
                    show: $.noop
                };
            });
            view.layout = {
                _components: {
                    push: $.noop
                }
            };
            var searchStub = sinon.stub(view, '_search');
            view.searchCases();
            expect(view.searchDropdown).toBeDefined();
            expect(searchStub).toHaveBeenCalled();
        });

        it('should search without creating new dropdown', function() {
            var dropdown = {
                hide: $.noop,
                show: $.noop
            };
            view.searchDropdown = dropdown;
            var dropdownStub = sinon.stub(app.view, 'createLayout');
            var searchStub = sinon.stub(view, '_search');
            view.searchCases();
            expect(dropdownStub).not.toHaveBeenCalled();
            expect(searchStub).toHaveBeenCalled();
        });
    });

    describe('_search', function() {
        it('should call search api', function() {
            var urlStub = sinon.stub(app.api, 'buildURL').returns('url');
            var callStub = sinon.stub(app.api, 'call');
            view._search();
            expect(urlStub).toHaveBeenCalled();
            expect(urlStub.lastCall.args[0]).toEqual('genericsearch');
            expect(urlStub.lastCall.args[3]).toEqual({
                max_num: 4,
                module_list: 'KBContents',
                offset: 0
            });
            expect(callStub).toHaveBeenCalled();
            expect(callStub.lastCall.args[0]).toEqual('read');
            expect(callStub.lastCall.args[1]).toEqual('url');
        });
    });

    describe('_parseData', function() {
        beforeEach(function() {
            view.searchOptions.max_num = 2;
        });

        it('should parse empty data', function() {
            var data = {
                next_offset: -1,
                records: [],
                total: 0
            };
            var expected = {
                options: view.searchOptions,
                currentPage: 0,
                records: [],
                totalPages: 0
            };
            expect(view._parseData(data)).toEqual(expected);
        });

        it('should parse first page data', function() {
            var data = {
                next_offset: 2,
                records: [
                    {
                        name: 'name1',
                        description: 'desc1',
                        url: 'url1'
                    },
                    {
                        name: 'name2',
                        description: 'desc2',
                        url: 'url2'
                    }
                ],
                total: 5
            };
            var expected = {
                options: view.searchOptions,
                currentPage: 1,
                records: [
                    {
                        name: 'name1',
                        description: 'desc1',
                        url: 'url'
                    },
                    {
                        name: 'name2',
                        description: 'desc2',
                        url: 'url'
                    }
                ],
                totalPages: 3
            };
            expect(view._parseData(data)).toEqual(expected);
        });

        it('should parse last page data', function() {
            var data = {
                next_offset: -1,
                records: [
                    {
                        name: 'name1',
                        description: 'desc1',
                        url: 'url1'
                    }
                ],
                total: 5
            };
            var expected = {
                options: view.searchOptions,
                currentPage: 3,
                records: [
                    {
                        name: 'name1',
                        description: 'desc1',
                        url: 'url'
                    }
                ],
                totalPages: 3
            };
            expect(view._parseData(data)).toEqual(expected);
        });
    });

    describe('_truncate', function() {
        it('should truncate a string if it is longer than max chars', function() {
            view.maxChars = 10;
            var str = 'This str is longer than 10 chars';
            var shortened = view._truncate(str);
            expect(shortened).toEqual('This str ...');
        });
    });
});
