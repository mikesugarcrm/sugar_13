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
describe('Base.View.ListPaginationView', function() {
    var app;
    var view;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;

        layout = app.view.createLayout({type: 'base'});
        view = SugarTest.createView('base', 'Accounts', 'list-pagination', null, null, false, layout);
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();
        app.view.reset();
        view = null;
        app = null;
    });

    describe('initialize', function() {
        var options;
        var collection;
        beforeEach(function() {
            collection = app.data.createBeanCollection('Accounts');
            collection.on = sinon.stub();
            collection.off = sinon.stub();
            options = {};

            sinon.stub(view.context, 'get').callsFake(function() {
                return collection;
            });
            view.initialize(options);
        });

        it('should call view._super method', function() {
            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should get collection from the context', function() {
            expect(view.context.get).toHaveBeenCalledWith('collection');
        });
    });

    describe('_renderHtml', function() {
        beforeEach(function() {
            sinon.stub(view, 'setVisibility');

            view._renderHtml();
        });

        it('should call setVisibility method', function() {
            expect(view.setVisibility).toHaveBeenCalled();
        });

        it('should call super _renderHtml method', function() {
            expect(view._super).toHaveBeenCalledWith('_renderHtml');
        });
    });

    describe('handlePageInput', function() {
        var event;
        var target;
        beforeEach(function() {
            view.pagesCount = 3;
            view.page = 2;
            sinon.stub(view, 'getPage');
        });

        afterEach(function() {
            event = null;
            target = null;
        });

        it('should not do anything if event target is missing', function() {
            event = {};
            target = {};

            sinon.stub(view, '$').returns(target);
            view.handlePageInput(event);
            expect(view.getPage).not.toHaveBeenCalled();

        });

        using('different input values',
            [
                [-1, 1],
                [21, 3],
                [3, 3],
                [1, 1]
            ],
            function(inputVal, result) {
                it('should set page number and call getPage', function() {
                    event = {
                        target: {
                            value: '100'
                        }
                    };
                    target = {
                        length: 1,
                        val: function() {
                            return inputVal;
                        }
                    };
                    sinon.stub(view, '$').returns(target);
                    sinon.stub(view, 'validatePageNumber').returns(result);

                    view.handlePageInput(event);
                    expect(view.validatePageNumber).toHaveBeenCalledWith(inputVal);
                    expect(event.target.value).toEqual(result);
                    expect(view.getPage).toHaveBeenCalled();
                });
            }
        );

        it('should not call get page if input is same as view.page', function() {
            event = {
                target: {
                    value: 10
                }
            };
            target = {
                length: 1,
                val: function() {
                    return 2;
                }
            };
            sinon.stub(view, '$').returns(target);
            sinon.stub(view, 'validatePageNumber').returns(2);

            view.handlePageInput(event);
            expect(view.validatePageNumber).toHaveBeenCalledWith(2);
            expect(event.target.value).toEqual(2);
            expect(view.getPage).not.toHaveBeenCalled();
        });
    });

    describe('validatePageNumber', function() {
        using('different input values for page number',
            [
                [-1, 3, 1],
                [21, 3, 3],
                [3, 3, 3],
                [1, 3, 1],
                [2, 0, 2],
                [-1, 0, 1],
            ],
            function(input, pageCount, result) {
                it('should return a valid page number', function() {
                    view.pagesCount = pageCount;

                    expect(view.validatePageNumber(input)).toEqual(result);
                });
            }
        );
    });

    describe('isNumberInput', function() {
        using('different input values for page number',
            [
                ['0', true],
                ['3', true],
                ['9', true],
                ['ArrowLeft', true],
                ['ArrowUp', true],
                ['ArrowRight', true],
                ['ArrowDown', true],
                ['Delete', true],
                ['Enter', true],
                ['Backspace', true],
                ['(', false],
                ['!', false],
                ['F', false],
                ['z', false],
            ],
            function(input, result) {
                it('should validate user input correctly', function() {
                    expect(view.isNumberInput(input)).toEqual(result);
                });
            }
        );
    });

    describe('getPage', function() {
        it('should paginate collection', function() {
            sinon.stub(view.collection, 'getOption');
            sinon.stub(view.collection, 'paginate');

            view.getPage();
            expect(view.collection.paginate).toHaveBeenCalled();
        });
    });

    describe('getPreviousPage', function() {
        it('should get correct page', function() {
            view.page = 2;
            sinon.stub(view, 'validatePageNumber').returns(view.page - 1);
            sinon.stub(view, 'getPage');

            view.getPreviousPage();
            expect(view.validatePageNumber).toHaveBeenCalledWith(view.page - 1);
            expect(view.getPage).toHaveBeenCalledWith(1);
        });
    });

    describe('getNextPage', function() {
        it('should get correct page', function() {
            view.page = 1;
            sinon.stub(view, 'validatePageNumber').returns(view.page + 1);
            sinon.stub(view, 'getPage');

            view.getNextPage();
            expect(view.validatePageNumber).toHaveBeenCalledWith(view.page + 1);
            expect(view.getPage).toHaveBeenCalledWith(2);
        });
    });

    describe('getPageCount', function() {
        it('should trigger paginate on context', function() {
            sinon.stub(view.context, 'trigger');

            view.getPageCount();
            expect(view.context.trigger).toHaveBeenCalledWith('paginate');
        });
    });

    describe('fetchCount', function() {
        beforeEach(function() {
            sinon.stub(app.alert, 'show');
            sinon.stub(app.lang, 'get');
            sinon.stub(view.collection, 'fetchTotal');
            sinon.stub(view.collection, 'trigger').callsFake(function() {});
        });

        it('should show loading alert message', function() {
            view.collection.total = null;
            view.fetchCount();

            expect(app.alert.show).toHaveBeenCalledWith('fetch_count_alert', {
                level: 'process',
                title: app.lang.get('LBL_LOADING'),
                autoClose: false
            });
        });

        it('should trigger list:page-total:fetching on collection', function() {
            view.isLoadingCount = false;
            view.fetchCount();

            expect(view.collection.trigger).toHaveBeenCalledWith('list:page-total:fetching');
            expect(view.isLoadingCount).toBeTruthy();
        });

        it('should call fetchTotal method on collection', function() {
            view.fetchCount();

            expect(view.collection.fetchTotal).toHaveBeenCalled();
        });
    });

    describe('pageTotalFetched', function() {
        beforeEach(function() {
            view.pagesCount = 0;
            view.isLoadingCount = true;

            sinon.stub(view, 'render');
        });

        using('total, limit, result',
            [
                [20, 5, 4],
                [21, 5, 5],
                [1, 10, 1],
                [21, undefined, 2],
            ],
            function(total, limit, result) {
                it('should calculate pages count correctly', function() {
                    sinon.stub(view.collection, 'getOption').callsFake(() => limit);

                    view.pageTotalFetched(total);
                    expect(view.pagesCount).toEqual(result);
                });
            }
        );

        it('should set isLoadingCount to false', function() {
            view.pageTotalFetched(2);

            expect(view.isLoadingCount).toBeFalsy();
        });

        it('should call render method', function() {
            view.pageTotalFetched(2);

            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('loadingPageTotal', function() {
        beforeEach(function() {
            view.isLoadingCount = false;

            sinon.stub(view, 'render');
        });

        it('should set isLoadingCount to true', function() {
            view.loadingPageTotal();

            expect(view.isLoadingCount).toBeTruthy();
        });

        it('should call render method', function() {
            view.loadingPageTotal();

            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('setVisibility', function() {
        it('should return if pagesCount is greater than 0', function() {
            view.pagesCount = 2;
            view.setVisibility();

            expect(view.pagesCount).toEqual(2);
        });

        it('should not do anything if dataFetched is false', function() {
            view.collection.dataFetched = false;
            view.collection.next_offset = -1;
            view.pagesCount = 0;
            view.setVisibility();

            expect(view.pagesCount).toEqual(0);
        });

        it('should not do anything if next_offset is not -1', function() {
            view.collection.dataFetched = true;
            view.collection.next_offset = 2;
            view.pagesCount = 0;
            view.setVisibility();

            expect(view.pagesCount).toEqual(0);
        });

        it('should update pagesCount if view.collection.page is not equal to pagesCount',
            function() {
                view.collection.dataFetched = true;
                view.collection.next_offset = -1;
                view.collection.page = 2;
                view.pagesCount = 0;
                view.page = 2;
                view.setVisibility();

                expect(view.pagesCount).toEqual(2);
            });

        it('should show pagination for list views if there is only 1 page',
            function() {
            view.layout.type = 'list';
            view.collection.dataFetched = true;
            view.collection.next_offset = -1;
            view.collection.page = 1;
            view.pagesCount = 0;
            view.setVisibility();

            expect(view.pagesCount).toEqual(1);
        });

        it('should set pagesCount to -1 for subpanels',
            function() {
            view.layout.type = 'subpanel';
            view.collection.dataFetched = true;
            view.collection.next_offset = -1;
            view.collection.page = 1;
            view.pagesCount = 0;
            view.setVisibility();

            expect(view.pagesCount).toEqual(-1);
        });
    });

    describe('handlePaginate', function() {
        using('different event data', [
            {
                dataAction: 'paginate-prev',
                eventName: 'list:paginate:previous',
                triggerLayout: true
            },
            {
                dataAction: 'paginate-next',
                eventName: 'list:paginate:next',
                triggerLayout: true
            },
            {
                dataAction: 'paginate-input',
                eventName: 'list:paginate:input',
                triggerLayout: true
            },
            {
                dataAction: 'paginate-input',
                eventName: 'list:paginate:input',
                triggerLayout: false
            },
        ], function(values) {
            it('should trigger the correct event based on the data action', function() {
                let event = {
                    currentTarget: {
                        getAttribute: function(attrName) {
                            switch (attrName) {
                                case 'data-action':
                                    return values.dataAction;
                                default:
                                    return '';
                            }
                        }
                    }
                };

                sinon.stub(view.layout, 'trigger');

                if (values.triggerLayout) {
                    view.layout._events[values.eventName] = $.noop;
                }

                view.handlePaginate(event);

                if (values.triggerLayout) {
                    expect(view.layout.trigger).toHaveBeenCalledWith(values.eventName);
                } else {
                    expect(view.layout.trigger).not.toHaveBeenCalled();
                }
            });
        });

        using('different page counts', [
            {
                dataAction: 'paginate-prev',
                eventName: 'list:paginate:previous',
                pagesCount: 0,
                expected: true
            },
            {
                dataAction: 'paginate-next',
                eventName: 'list:paginate:next',
                pagesCount: 0,
                expected: true
            },
            {
                dataAction: 'paginate-next',
                eventName: 'list:paginate:next',
                pagesCount: 5,
                expected: false
            },
            {
                dataAction: 'paginate-input',
                eventName: 'list:paginate:input',
                pagesCount: 0,
                expected: false
            },
        ], function(values) {
            it('should appropriately fetch the page count', function() {
                let event = {
                    currentTarget: {
                        getAttribute: function(attrName) {
                            switch (attrName) {
                                case 'data-action':
                                    return values.dataAction;
                                default:
                                    return '';
                            }
                        }
                    }
                };

                sinon.stub(view, 'getPageCount');

                view.pagesCount = values.pagesCount;

                view.handlePaginate(event);

                if (values.expected) {
                    expect(view.getPageCount).toHaveBeenCalled();
                } else {
                    expect(view.getPageCount).not.toHaveBeenCalled();
                }
            });
        });
    });

    describe('handleListSort', function() {
        beforeEach(function() {
            sinon.stub(view, 'render');
            sinon.stub(view.context, 'trigger');
            sinon.stub(view.collection, 'paginate');
        });

        it('should go to page 1 and render, without paginating the collection', function() {
            view.page = 2;

            view.handleListSort();

            expect(view.page).toEqual(1);
            expect(view.render).toHaveBeenCalled();
            expect(view.collection.paginate).not.toHaveBeenCalled();
        });

        it('should trigger refresh:count if context is not empty', function() {
            view.handleListSort();

            expect(view.context.trigger).toHaveBeenCalledWith('refresh:count');
        });
    });

    describe('handleCollectionReset', function() {
        beforeEach(function() {
            view.pagesCount = 5;
            view.isFilterChanged = () => false;
            sinon.stub(view, 'render');
        });

        using('different reset events', [
            {
                action: 'FILTER',
                expected: true,
            },
            {
                action: 'PAGINATE',
                expected: false,
            },
        ], function(values) {
            it('should properly handle collection reset event', function() {
                view.paginationAction = values.action;

                view.handleCollectionReset();

                if (values.expected) {
                    expect(view.pagesCount).toEqual(0);
                    expect(view.render).toHaveBeenCalled();
                } else {
                    expect(view.pagesCount).toEqual(5);
                    expect(view.render).not.toHaveBeenCalled();
                }
            });
        });
    });

    describe('setPaginationAction', function() {
        beforeEach(function() {
            view.paginationAction = '';
            sinon.stub(view.context, 'set');
        });

        using('different actions', [
            {
                action: 'filter',
                expected: 'FILTER',
            },
            {
                action: 'paginate',
                expected: 'PAGINATE',
            },
        ], function(values) {
            it('should set the appropriate pagination action', function() {
                view.setPaginationAction(values.action);

                expect(view.paginationAction).toEqual(values.expected);
                expect(view.context.set).toHaveBeenCalledWith('paginationAction', values.expected);
            });
        });
    });

    describe('clearPaginationAction', function() {
        beforeEach(function() {
            view.paginationAction = 'PAGINATE';
            sinon.stub(view.context, 'set');
        });

        it('should clear the pagination action', function() {
            view.clearPaginationAction();
            expect(view.paginationAction).toEqual('');
        });
    });

    describe('getFirstPage', function() {
        beforeEach(function() {
            view.page = 5;
            sinon.stub(view, 'getPage');
            sinon.stub(view, 'render');
        });

        using('different fetch conditions', [
            {
                fetch: false,
            },
        ], function(values) {
            it('should appropriately paginate to the first page', function() {
                view.getFirstPage(values.fetch);

                if (values.fetch) {
                    expect(view.getPage).toHaveBeenCalledWith(1);
                } else {
                    expect(view.page).toEqual(1);
                    expect(view.render).toHaveBeenCalled();
                    expect(view.getPage).not.toHaveBeenCalled();
                }
            });
        });

        it('should appropriately set the cache collection for the first page', function() {
            sinon.stub(view, 'setCache');

            view.cachedCollection = {};
            view.getFirstPage(false);

            expect(view.setCache).toHaveBeenCalled();
        });
    });

    describe('handleFocusPageInput', function() {
        beforeEach(function() {
            sinon.stub(view, 'getPageCount');
        });

        using('different focus events', [
            {
                pagesCount: 0,
                expected: true,
            },
            {
                pagesCount: 5,
                expected: false,
            },
        ], function(values) {
            it('should properly handle focus event on page input', function() {
                view.pagesCount = values.pagesCount;

                view.handleFocusPageInput();

                if (values.expected) {
                    expect(view.getPageCount).toHaveBeenCalled();
                } else {
                    expect(view.getPageCount).not.toHaveBeenCalled();
                }
            });
        });
    });

    describe('setCache', function() {
        it('should set cache variable', function() {
            const data = {
                models: [
                    {id: 'id1'},
                    {id: 'id2'},
                ],
                next_offset: '123',
                page: 1
            };
            view.collection.models = data.models;
            view.collection.next_offset = data.next_offset;
            view.page = 1;

            view.setCache();
            expect(view.cachedCollection).toEqual({1: data});
        });
    });

    describe('clearCache', function() {
        it('should clear cache variable', function() {
            view.cachedCollection = {1: 'test'};

            view.clearCache();
            expect(view.cachedCollection).toEqual({});
        });
    });

    describe('restoreFromCache', function() {
        beforeEach(function() {
            sinon.stub(view.collection, 'reset');
        });

        it('should collection.reset with data from cache variable', function() {
            const models = ['test'];
            view.cachedCollection = {
                1: {
                    models: models,
                },
            };
            view.page = 1;

            view.restoreFromCache();
            expect(view.collection.reset).toHaveBeenCalledWith(models);
        });
    });
});
