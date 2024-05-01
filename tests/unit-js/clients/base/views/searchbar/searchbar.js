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

describe('View.Views.Base.SearchbarView', function() {
    var app;
    var view;
    var layout;
    var el;

    beforeEach(function() {
        app = SugarTest.app;
        var context = new app.Context();
        SugarTest.loadComponent('base', 'view', 'searchbar');
        SugarTest.loadComponent('base', 'layout', 'default');
        layout = SugarTest.createLayout('base', null, 'default', {});
        view = SugarTest.createView(
            'base',
            null,
            'searchbar',
            {name: 'test'},
            context,
            true,
            layout,
            true,
            'base'
        );
        el = {
            addClass: sinon.stub(),
            removeClass: sinon.stub()
        };
        sinon.stub(view, '$').callsFake(function() {
            return {
                val: function() {
                    return 'val';
                },
                first: function() {
                    return el;
                },
                after: $.noop
            };
        });
    });

    afterEach(function() {
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;
        sinon.restore();
    });

    describe('toggleSearchIcon', function() {
        using('different icons',
            [
                {
                    currentIcon: 'search',
                    newIcon: 'close',
                    classToAdd: 'sicon-close',
                    classToRemove: 'sicon-search'
                },
                {
                    currentIcon: 'close',
                    newIcon: 'search',
                    classToAdd: 'sicon-search',
                    classToRemove: 'sicon-close'
                }
            ],
            function(values) {
                it('should toggle search icon properly', function() {
                    view.searchButtonIcon = values.currentIcon;

                    view.toggleSearchIcon(values.newIcon);

                    expect(el.addClass).toHaveBeenCalledWith(values.classToAdd);
                    expect(el.removeClass).toHaveBeenCalledWith(values.classToRemove);
                });
            }
        );
    });

    describe('doSearch', function() {
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
            view.fuse = {search: function(term) {return term;}};
            view.doSearch();
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
            view.fuse = {search: function(term) {return term;}};
            var searchStub = sinon.stub(view, '_search');
            view.doSearch();
            expect(dropdownStub).not.toHaveBeenCalled();
            expect(searchStub).toHaveBeenCalled();
        });
    });

    describe('_search', function() {
        it('should call trigger and parseData with correct params', function() {
            var triggerStub = sinon.stub(view.context, 'trigger');
            var parseStub = sinon.stub(view, '_parseData');

            view.totalRecords = 3;
            view.maxNum = 2;
            view.matches = ['aaa', 'bbb', 'ccc'];

            view._search({pageNum: 1});

            expect(parseStub).toHaveBeenCalled();
            expect(parseStub.lastCall.args[0]).toEqual({records: ['aaa', 'bbb'], total: 3, next_offset: 2});

            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub.lastCall.args[0]).toEqual('data:fetched');
        });
    });

    describe('_parseData', function() {
        beforeEach(function() {
            view.maxNum = 2;
            sinon.stub(app.utils, 'buildUrl').returns('url');
        });

        it('should parse empty data', function() {
            var data = {
                next_offset: -1,
                records: [],
                total: 0
            };
            var expected = {
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
                        href: 'url1'
                    },
                    {
                        name: 'name2',
                        description: 'desc2',
                        href: 'url2'
                    }
                ],
                total: 5
            };
            var expected = {
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
                        href: 'url'
                    }
                ],
                total: 5
            };
            var expected = {
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
});
