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
describe('Base.Views.RecentUsedProduct', function() {
    var app;
    var view;
    var viewMeta;
    var context;
    var layout;
    var addClassStub;
    var removeClassStub;
    var showStub;
    var hideStub;
    var offStub;
    var closestComponent;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        viewMeta = {
            config: false
        };
        layout = SugarTest.createLayout('base', 'Opportunities', 'create', {});
        closestComponent = {
            on: $.noop,
            off: $.noop,
            triggerBefore: sinon.stub()
        };
        sinon.stub(layout, 'closestComponent').callsFake(function() {
            return closestComponent;
        });
        view = SugarTest.createView('base', null, 'product-quick-picks', viewMeta, context, true, layout);

        removeClassStub = sinon.stub();
        addClassStub = sinon.stub();
        showStub = sinon.stub();
        hideStub = sinon.stub();
        offStub = sinon.stub();

        sinon.stub(view, '$').callsFake(function() {
            return {
                data: function() {
                    return 3;
                },
                closest: function() {
                    return {
                        data: function() {
                            return 'some1-Test2-Text3';
                        }
                    };
                },
                hasClass: function() {
                    return true;
                },
                addClass: addClassStub,
                removeClass: removeClassStub,
                show: showStub,
                hide: hideStub,
                off: offStub
            };
        });
    });

    afterEach(function() {
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;

        sinon.restore();
        addClassStub = null;
        removeClassStub = null;
        showStub = null;
        hideStub = null;
        offStub = null;
    });

    describe('initialize()', function() {
        it('should remove the Paginate plugin and should have the Tooltip plugin', function() {
            view.dispose();
            view.plugins = ['Tooltip'];
            view.initialize({});

            expect(view.plugins).not.toContain('Paginate');
            expect(view.plugins).toContain('Tooltip');
        });

        it('should set pageNumClicked to 1', function() {
            expect(view.pageNumClicked).toBe(1);
        });

        it('should set pagination length to 0', function() {
            expect(view.paginationLength).toBe(0);
        });

        it('should set activeTab as empty string', function() {
            expect(view.activeTab).toEqual('');
        });

        it('should set pageNumList to []', function() {
            expect(view.pageNumList).toEqual([]);
        });

        it('should set dataFetched to False', function() {
            expect(view.dataFetched).toBeFalsy();
        });

        it('should set isPrevDisabled to False', function() {
            expect(view.isPrevDisabled).toBeFalsy();
        });

        it('should set isNextDisabled to False', function() {
            expect(view.isNextDisabled).toBeFalsy();
        });

        it('should set isPageNumDisabled to False', function() {
            expect(view.isPageNumDisabled).toBeFalsy();
        });

        it('should create a new Collection object and assign it to recentCollection', function() {
            expect(view.recentCollection).toBeDefined();
        });
    });

    describe('_initTabs', function() {
        beforeEach(function() {
           view.tabs = [
               {
                   label: 'LBL_DASHLET_PRODUCT_QUICK_PICKS_RECENT_TAB',
               },
               {
                   label: 'LBL_DASHLET_PRODUCT_QUICK_PICKS_FAVORITES_TAB',
               },
           ];

           sinon.stub(view, '_super').callsFake(function() {});
       });

        afterEach(function() {
           view.tabs = null;
       });

        describe('when Opps Config opps_view_by is Opportunities', function() {
            it('should not set listener on app.controller.context', function() {
                sinon.stub(app.metadata, 'getModule').callsFake(function() {
                    return {
                        opps_view_by: 'Opportunities'
                    };
                });
                view._initTabs();

                expect(view.tabs[0].label).not.toBe('LBL_DASHLET_PRODUCT_QUICK_PICKS_RECENT_TAB');
            });
        });

        it('should have both tabs if the dashlet is not in the Opportunity only mode', function() {
            expect(view.tabs[0].label).toBe('LBL_DASHLET_PRODUCT_QUICK_PICKS_RECENT_TAB');
        });
    });

    describe('getUrl()', function() {
        var activeTabIndex;

        beforeEach(function() {
            view.tabs = [
                {
                    label: 'LBL_DASHLET_PRODUCT_QUICK_PICKS_RECENT_TAB',
                },
                {
                    label: 'LBL_DASHLET_PRODUCT_QUICK_PICKS_FAVORITES_TAB',
                },
            ];
        });

        it('should set tab to recent products', function() {
            view.settings = new Backbone.Model({
                activeTab: 0,
                label: 'LBL_PRODUCT_QUICK_PICKS_DASHLET_NAME',
                type: 'product-quick-picks-dashlet',
            });
            activeTabIndex = view.settings.get('activeTab');
            expect(view.tabs[activeTabIndex].label).toBe('LBL_DASHLET_PRODUCT_QUICK_PICKS_RECENT_TAB');
        });

        it('should set tab to favorites products', function() {
            view.settings = new Backbone.Model({
                activeTab: 1,
                label: 'LBL_PRODUCT_QUICK_PICKS_DASHLET_NAME',
                type: 'product-quick-picks-dashlet',
            });
            activeTabIndex = view.settings.get('activeTab');
            expect(view.tabs[activeTabIndex].label).toBe('LBL_DASHLET_PRODUCT_QUICK_PICKS_FAVORITES_TAB');
        });
    });

    describe('getCurrentObj', function() {
        beforeEach(function() {
            view.pageNumList = [
                {
                    obj: 1,
                    pageNum: 1
                },
                {
                    obj: 2,
                    pageNum: 2
                },
                {
                    obj: 3,
                    pageNum: 3
                },
                {
                    obj: 4,
                    pageNum: 4
                }
            ];

            view.pageNumClicked = 3;
        });

        it('should return the current Object', function() {
            expect(view.getCurrentObj()).toEqual(view.pageNumList[2]);
        });

    });

    describe('loadData()', function() {
        beforeEach(function() {
            view.tabs = [
                {
                    active: 'true',
                    label: 'LBL_DASHLET_PRODUCT_QUICK_PICKS_RECENT_TAB'
                },
                {
                    active: 'false',
                    label: 'LBL_DASHLET_PRODUCT_QUICK_PICKS_FAVORITES_TAB'
                }
            ];

            view.pageNumClicked = 3;

            sinon.stub(view, 'getUrl').callsFake(function() {
                return 'testUrl';
            });

            sinon.stub(view, 'toggleLoading').callsFake(function() {});
            sinon.stub(app.api, 'call').callsFake(function() {});
        });

        describe('when options and options.pageNum is defined', function() {
            beforeEach(function() {
                view.options = {
                    pageNum: 3
                };
            });

            it('should assign options.pageNum to view.pageNumClicked', function() {
                expect(view.pageNumClicked).toBe(view.options.pageNum);
            });
        });

        it('should call getUrl with payloadData', function() {
            view.loadData();

            expect(view.getUrl).toHaveBeenCalledWith({});
        });

        it('should call toggleLoading with true', function() {
            view.loadData();

            expect(view.toggleLoading).toHaveBeenCalledWith(true);
        });

        it('should not call getUrl if the tabs is empty', function() {
            view.tabs = undefined;
            view.loadData();

            expect(view.getUrl).not.toHaveBeenCalled();
        });


        it('should call app.api.call with read and url', function() {
            view.loadData();

            expect(app.api.call).toHaveBeenCalledWith('read', 'testUrl');
        });
    });

    describe('onProductFetchSuccess', function() {
        var result;

        beforeEach(function() {
            result = {
                records: [
                    {
                        id: 1,
                        name: 'asd'
                    },
                    {
                        id: 2,
                        name: 'asdasdasdasdasdasdasdasdasd'
                    }
                ],
                totalPages: 0
            };

            sinon.spy(view.recentCollection, 'reset');
            sinon.stub(view, 'render').callsFake(function() {});
            sinon.stub(view, 'toggleLoading').callsFake(function() {});
        });

        afterEach(function() {
            result = null;
        });

        it('should reset the view.pageNumList', function() {
            view.onProductFetchSuccess(result);

            expect(view.pageNumList).toEqual([]);
        });

        it('should reset view.recentCollection', function() {
            view.onProductFetchSuccess(result);

            expect(view.recentCollection.reset).toHaveBeenCalled();
        });

        it('should set view.dataFetched to true', function() {
            view.onProductFetchSuccess(result);

            expect(view.dataFetched).toBeTruthy();
        });

        it('should set the view.paginationLength to 0', function() {
            view.onProductFetchSuccess(result);

            expect(view.paginationLength).toBe(0);
        });

        describe('when records are present in the result', function() {
            describe('when recent-products tab is active', function() {
                beforeEach(function() {
                    result = {
                        records: [
                            {
                                id: 1,
                                name: 'test1'
                            },
                            {
                                id: 2,
                                name: 'test2'
                            }
                        ],
                        totalPages: 0
                    };
                    view.activeTab = 'recent-product';
                    view.onProductFetchSuccess(result);
                });
                afterEach(function() {
                   view.activeTab = null;
               });

                it('should assign the results to the view.recentCollection', function() {
                    expect(view.recentCollection.reset).toHaveBeenCalledWith(result.records);
                });
            });
            describe('when favorites tab is active', function() {
                beforeEach(function() {
                    result = {
                        records: [
                            {
                                id: 1,
                                name: 'test1'
                            },
                            {
                                id: 2,
                                name: 'test2'
                            },
                            {
                                id: 3,
                                name: 'test3'
                            }
                        ],
                        totalPages: 3
                    };
                    view.activeTab = 'favorites';
                });

                afterEach(function() {
                    result = null;
                });

                it('should assign the totalPages to view.paginationLength', function() {
                    view.onProductFetchSuccess(result);

                    expect(view.paginationLength).toBe(3);
                });

                describe('when view.pageNumClicked is greater than view.paginationLength', function() {
                    beforeEach(function() {
                        view.pageNumClicked = 5;
                        view.onProductFetchSuccess(result);
                    });

                    it('should decrease view.pageNumClicked by 1', function() {
                        expect(view.pageNumClicked).toBe(4);
                    });
                });

                describe('when view.pageNumClicked is less than view.paginationLength', function() {
                    beforeEach(function() {
                        view.pageNumClicked = 2;
                        view.onProductFetchSuccess(result);
                    });

                    it('should not change the value of view.pageNumClicked', function() {
                        expect(view.pageNumClicked).toBe(2);
                    });
                });

                describe('when view.pageNumClicked is equal to view.paginationLength', function() {
                    beforeEach(function() {
                        view.pageNumClicked = 3;
                        view.onProductFetchSuccess(result);
                    });

                    it('should set view.isNextDisabled to true', function() {
                        expect(view.isNextDisabled).toBeTruthy();
                    });
                });

                describe('when view.paginationLength is 1', function() {
                    beforeEach(function() {
                        result = {
                            records: [
                                {
                                    id: 1,
                                    name: 'test1'
                                }
                            ],
                            totalPages: 1
                        };
                        view.onProductFetchSuccess(result);
                    });

                    it('should set view.isNextDisabled to true', function() {
                        expect(view.isNextDisabled).toBeTruthy();
                    });

                    it('should set view.isPrevDisabled to true', function() {
                        expect(view.isPrevDisabled).toBeTruthy();
                    });
                });

                describe('when view.pageNumClicked is 1', function() {
                    beforeEach(function() {
                        view.pageNumClicked = 1;
                        view.onProductFetchSuccess(result);
                    });

                    it('should set view.isPrevDisabled to true', function() {
                        expect(view.isPrevDisabled).toBeTruthy();
                    });
                });

                describe('when view.pageNumClicked and view.paginationLength are equal to 1', function() {
                    beforeEach(function() {
                        result = {
                            records: [
                                {
                                    id: 1,
                                    name: 'test1'
                                }
                            ],
                            totalPages: 1
                        };
                        view.pageNumClicked = 1;
                        view.onProductFetchSuccess(result);
                    });

                    it('should set view.isPageNumDisabled to true', function() {
                        expect(view.isPageNumDisabled).toBeTruthy();
                    });
                });

                describe('when view.pageNumClicked and view.paginationLength are not equal to 1', function() {
                    beforeEach(function() {
                        view.pageNumClicked = 2;
                        view.paginationLength = 2;
                        view.onProductFetchSuccess(result);
                    });

                    it('should set view.isPageNumDisabled to false', function() {
                        expect(view.isPageNumDisabled).toBeFalsy();
                    });
                });

                describe('when view.pageNumList is populated', function() {
                    beforeEach(function() {
                        result = {
                            records: [
                                {
                                    id: 1,
                                    name: 'test1'
                                }
                            ],
                            totalPages: 2
                        };
                    });
                    it('should populate the view.pageNumList and should have length equal to totalPages',
                        function() {
                        view.onProductFetchSuccess(result);

                        expect(view.pageNumList.length).toBe(result.totalPages);
                    });

                    describe('when view.pageNumClicked is equal to pageNum and view.isPageNumDisabled is false',
                        function() {
                        beforeEach(function() {
                            view.pageNumClicked = 2;
                            view.isPageNumDisabled = false;
                        });

                        it('should have all the hbs classes assigned to each pageNumList object', function() {
                            view.onProductFetchSuccess(result);

                            expect(view.pageNumList[1].isIcon).toBeFalsy();
                            expect(view.pageNumList[1].listClass).toEqual('favorite-pagination');
                            expect(view.pageNumList[1].subListClass)
                                .toEqual('paginate-num-button btn btn-link btn-invisible');
                            expect(view.pageNumList[1].pageNum).toBe(2);
                        });

                        it('should set view.pageNumList isActive to true', function() {
                            view.onProductFetchSuccess(result);
                            expect(view.pageNumList[1].isActive).toBeTruthy();
                        });

                        describe('when view.pageNumClicked is not equal to pageNum', function() {
                            beforeEach(function() {
                                view.pageNumClicked = 1;
                                view.onProductFetchSuccess(result);
                            });

                            it('should set view.pageNumList isActive to false', function() {
                                expect(view.pageNumList[1].isActive).toBeFalsy();
                            });
                        });

                        describe('when view.isPageNumDisabled is true', function() {
                            beforeEach(function() {
                                view.isPageNumDisabled = true;
                                result.totalPages = 1;
                                view.onProductFetchSuccess(result);
                            });

                            it('should set view.pageNumList isActive to true', function() {
                                expect(view.pageNumList[0].isActive).toBeFalsy();
                            });
                        });
                    });

                    describe('when totalPages are more than 4', function() {
                        beforeEach(function() {
                            result = {
                                records: [
                                    {
                                        id: 1,
                                        name: 'test1'
                                    },
                                    {
                                        id: 2,
                                        name: 'test2'
                                    },
                                    {
                                        id: 3,
                                        name: 'test3'
                                    }
                                ],
                                totalPages: 5
                            };
                            view.pageNumClicked = 3;
                        });

                        it('should populate the view.pageNumList and should have length equal to 5', function() {
                            view.onProductFetchSuccess(result);

                            expect(view.pageNumList.length).toBe(5);
                        });

                        it('should have all the hbs classes assigned to each pageNumList object', function() {
                            view.onProductFetchSuccess(result);

                            expect(view.pageNumList[2].isIcon).toBeFalsy();
                            expect(view.pageNumList[2].listClass).toEqual('favorite-pagination');
                            expect(view.pageNumList[2].subListClass)
                                .toEqual('paginate-num-button btn btn-link btn-invisible');
                            expect(view.pageNumList[2].pageNum).toBe(3);
                        });

                        describe('when current Index is not zero and less than view.pageNumList.length - 1',
                            function() {
                            it('should Add ellipsis to the front of the view.pageNumList', function() {
                                view.onProductFetchSuccess(result);

                                expect(view.pageNumList[0].isIcon).toBeTruthy();
                                expect(view.pageNumList[0].listClass).toEqual('favorite-pagination');
                                expect(view.pageNumList[0].subListClass).toEqual(
                                    'left-ellipsis-icon sicon sicon-ellipsis'
                                );
                            });
                        });

                        describe('when difference between totalPages and current Index is more than 0r equal to 3',
                            function() {
                            it('should Add ellipsis to the end of the view.pageNumList', function() {
                                view.onProductFetchSuccess(result);

                                expect(view.pageNumList[4].isIcon).toBeTruthy();
                                expect(view.pageNumList[4].listClass).toEqual('favorite-pagination');
                                expect(view.pageNumList[4].subListClass).toEqual(
                                    'right-ellipsis-icon sicon sicon-ellipsis'
                                );
                            });
                        });

                        describe('when view.pageNumClicked is equal to pageNum and view.isPageNumDisabled is false',
                            function() {
                                beforeEach(function() {
                                    view.pageNumClicked = 1;
                                    view.isPageNumDisabled = false;
                                });

                                it('should set view.pageNumList isActive to true', function() {
                                    view.onProductFetchSuccess(result);

                                    expect(view.pageNumList[0].isActive).toBeTruthy();
                                });

                                describe('when view.pageNumClicked is not equal to pageNum', function() {
                                    beforeEach(function() {
                                        view.pageNumClicked = 2;
                                        view.onProductFetchSuccess(result);
                                    });

                                    it('should set view.pageNumList isActive to false', function() {
                                        expect(view.pageNumList[0].isActive).toBeFalsy();
                                    });
                                });

                                describe('when view.isPageNumDisabled is true', function() {
                                    beforeEach(function() {
                                        view.isPageNumDisabled = true;
                                        view.onProductFetchSuccess(result);
                                    });

                                    it('should set view.pageNumList isActive to true', function() {
                                        expect(view.pageNumList[0].isActive).toBeTruthy();
                                    });
                                });
                            }
                        );
                    });
                });

                it('should save the records in a variable and assign it to the view.recentCollection', function() {
                    view.onProductFetchSuccess(result);

                    expect(view.recentCollection.reset).toHaveBeenCalledWith(result.records);
                });
            });
        });

        it('should set name in the view.recentCollection.models', function() {
            view.onProductFetchSuccess(result);

            expect(view.recentCollection.get(1).toJSON()).toEqual(
                {
                    id: 1,
                    name: 'asd',
                }
            );
            expect(view.recentCollection.get(2).toJSON()).toEqual(
                {
                    id: 2,
                    name: 'asdasdasdasdasdasdasdasdasd',
                }
            );
        });

        it('should call toggleLoading with false', function() {
            view.onProductFetchSuccess(result);
            expect(view.toggleLoading).toHaveBeenCalledWith(false);
        });

        it('should call render', function() {
            view.onProductFetchSuccess(result);
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('getPageNumClicked()', function() {
        var evt;

        beforeEach(function() {
            evt = {
                preventDefault: $.noop,
            };

            sinon.stub(view, 'loadData').callsFake(function() {});
            sinon.stub(evt, 'preventDefault').callsFake(function() {});
            sinon.stub(view, 'toggleLoading').callsFake(function() {});
            sinon.stub(view, 'render').callsFake(function() {});

            view.pageNumClicked = 5;
        });

        afterEach(function() {
            evt = null;
        });

        describe('when loadData should be called', function() {
            beforeEach(function() {
                view.getPageNumClicked(evt);
            });
            it('should call preventDefault function', function() {
                expect(evt.preventDefault).toHaveBeenCalled();
            });

            it('should call the toggle loading function with false', function() {
                expect(view.toggleLoading).toHaveBeenCalledWith(false);
            });

            it('should call the render function', function() {
                expect(view.render).toHaveBeenCalled();
            });

            it('should call loadData with a page Number', function() {
                expect(view.loadData).toHaveBeenCalledWith({
                    pageNum: 3
                });
            });
        });

        describe('when the same page is clicked twice loadData should not be called', function() {
            beforeEach(function() {
                view.pageNumClicked = 3;
                view.getPageNumClicked(evt);
            });
            afterEach(function() {
                view.pageNumClicked = null;
            });

            it('should not call the loadData function', function() {
                expect(view.loadData).not.toHaveBeenCalledWith({
                    pageNum: 3
                });
            });
        });
    });

    describe('onPageNavClicked()', function() {
        var evt;

        beforeEach(function() {
            evt = {
                preventDefault: $.noop,
            };

            sinon.stub(view, 'loadData').callsFake(function() {});
            sinon.stub(evt, 'preventDefault').callsFake(function() {});
            sinon.stub(view, 'toggleLoading').callsFake(function() {});
            sinon.stub(view, 'render').callsFake(function() {});
        });

        afterEach(function() {
            evt = null;
        });

        describe('when onPageNavClicked gets called', function() {
            beforeEach(function() {
                view.onPageNavClicked(evt);
            });
            it('should call preventDefault function', function() {
                expect(evt.preventDefault).toHaveBeenCalled();
            });

            it('should call the toggle loading function with false', function() {
                expect(view.toggleLoading).toHaveBeenCalledWith(false);
            });

            it('should call the render function', function() {
                expect(view.render).toHaveBeenCalled();
            });
        });

        describe('when previous navigation button is clicked', function() {
            describe('when element class is previous-fav', function() {
                beforeEach(function() {
                    sinon.stub(jQuery.fn, 'hasClass').withArgs('previous-fav').returns(true);
                    view.onPageNavClicked(evt);
                });

                it('should call loadData with a page Number 1 less than current page', function() {
                    expect(view.loadData).toHaveBeenCalledWith({
                        pageNum: 2
                    });
                });
            });
            describe('when element class is nav-previous', function() {
                beforeEach(function() {
                    sinon.stub(jQuery.fn, 'hasClass').withArgs('nav-previous').returns(true);
                    view.onPageNavClicked(evt);
                });

                afterEach(function() {
                });

                it('should call loadData with a page Number 1 less than current page', function() {
                    expect(view.loadData).toHaveBeenCalledWith({
                        pageNum: 2
                    });
                });
            });
        });
        describe('when next navigation button is clicked', function() {
            var hasClassStub;

            describe('when element class is next-fav', function() {
                beforeEach(function() {
                    hasClassStub = sinon.stub();

                    hasClassStub.withArgs('previous-fav').returns(false)
                        .withArgs('next-fav').returns(true)
                        .withArgs('nav-next').returns(false)
                        .withArgs('nav-previous').returns(false);
                    view.$.restore();
                    sinon.stub(view, '$').callsFake(function() {
                        return {
                            data: function() {
                                return 3;
                            },
                            hasClass: hasClassStub
                        };
                    });
                    view.onPageNavClicked(evt);
                });
                it('should call loadData with a page Number 1 more than current page', function() {

                    expect(view.loadData).toHaveBeenCalledWith({
                        pageNum: 4
                    });
                });
            });
            describe('when element class is nav-next', function() {
                beforeEach(function() {
                    hasClassStub = sinon.stub();

                    hasClassStub.withArgs('previous-fav').returns(false)
                        .withArgs('next-fav').returns(false)
                        .withArgs('nav-next').returns(true)
                        .withArgs('nav-previous').returns(false);

                    view.$.restore();
                    sinon.stub(view, '$').callsFake(function() {
                        return {
                            data: function() {
                                return 3;
                            },
                            hasClass: hasClassStub
                        };
                    });
                    view.onPageNavClicked(evt);
                });
                it('should call loadData with a page Number 1 more than current page', function() {
                    expect(view.loadData).toHaveBeenCalledWith({
                        pageNum: 4
                    });
                });
            });
        });
    });

    describe('onNameClicked', function() {
        var evt;
        var data;
        var oldUserId;
        var newUserId = '1234';

        beforeEach(function() {
            evt = {
                preventDefault: $.noop
            };

            sinon.stub(jQuery.fn, 'data').callsFake(function() {
                return 10;
            });

            sinon.stub(view.recentCollection, 'get').callsFake(function() {
                return new Backbone.Model({
                    id: 10,
                    name: 'asd',
                    my_favorite: 1,
                    created_by: 'testUser     ',
                    modified_user_id: 'testUser2       ',
                    currency_id: '-99               ',
                    pricing_formula: 'my_formula',
                    date_entered: '2021-01-01 12:00:00',
                    date_modified: '2021-01-01 12:00:00',
                    sync_key: 'my_sync_key',
                    team_id: '1',
                    team_set_id: '1',
                    team_count: '1',
                    team_count_link: 'test',
                    team_name: 'test'
                });
            });
            sinon.stub(evt, 'preventDefault').callsFake(function() {});
            sinon.stub(app.controller.context, 'trigger').callsFake(function() {});

            oldUserId = app.user.id;
            app.user.id = newUserId;
            closestComponent.triggerBefore.returns(true);
        });

        afterEach(function() {
            evt = null;
            data = null;
            app.user.id = oldUserId;
        });

        it('should call preventDefault function', function() {
            view.onNameClicked(evt);
            expect(evt.preventDefault).toHaveBeenCalled();
        });

        it('should call app.controller.context.trigger with productCatalogDashlet:add and data', function() {
            view.onNameClicked(evt);

            var viewDetails = view.closestComponent('record') ?
                view.closestComponent('record') :
                view.closestComponent('create');

            if (!_.isUndefined(viewDetails)) {
                expect(app.controller.context.trigger)
                    .toHaveBeenCalledWith(viewDetails.cid + ':productCatalogDashlet:add', {
                        product_template_id: 10,
                        product_template_name: 'asd',
                        created_by: 'testUser',
                        modified_user_id: 'testUser2',
                        currency_id: '-99',
                        assigned_user_id: newUserId,
                        name: 'asd'
                    });
            }
        });

        it('should not call app.controller.context.trigger if disallowed', function() {
            closestComponent.triggerBefore.returns(false);
            view.onNameClicked(evt);

            var viewDetails = view.closestComponent('record') ?
                view.closestComponent('record') :
                view.closestComponent('create');

            if (!_.isUndefined(viewDetails)) {
                expect(app.controller.context.trigger).not.toHaveBeenCalled();
            }
        });
    });

    describe('tabSwitcher()', function() {
        beforeEach(function() {
            sinon.stub(view, '_super').callsFake(function() {});
            sinon.stub(view, 'loadData').callsFake(function() {});
        });

        it('should set dataFetched to False', function() {
            expect(view.dataFetched).toBeFalsy();
        });

        it('should call loadData', function() {
            view.tabSwitcher();

            expect(view.loadData).toHaveBeenCalled();
        });
    });

    describe('toggleLoading()', function() {
        it('should call show if startLoading is true', function() {
            view.toggleLoading(true);

            expect(view.$).toHaveBeenCalledWith('.loading-icon');
            expect(showStub).toHaveBeenCalled();
        });

        it('should call show if startLoading is false', function() {
            view.toggleLoading(false);

            expect(view.$).toHaveBeenCalledWith('.loading-icon');
            expect(hideStub).toHaveBeenCalled();
        });
    });

    describe('_fetchProductTemplate()', function() {
        var url;

        beforeEach(function() {
            url = 'testUrl';

            sinon.stub(app.api, 'buildURL').callsFake(function() {
                return 'testUrl';
            });
            sinon.stub(app.api, 'call').callsFake(function() {});
            view._fetchProductTemplate('test');
        });

        afterEach(function() {
            url = null;
        });

        it('should call app.api.buildURL', function() {
            expect(app.api.buildURL).toHaveBeenCalled();
        });

        it('should call app.api.call with read and url', function() {
            expect(app.api.call).toHaveBeenCalledWith('read', url);
        });
    });

    describe('onIconClicked()', function() {
        var testId;

        beforeEach(function() {
            sinon.stub(jQuery.fn, 'parent').callsFake(function() {
                return {
                    data: function() {
                        return 'some1-Test2-Text3';
                    }
                };
            });
            sinon.stub(view, '_fetchProductTemplate').callsFake(function() {});
            testId = 'some1-Test2-Text3';
        });

        afterEach(function() {
            testId = null;
        });

        it('should call _fetchProductTemplate with the record id of the icon clicked', function() {
            view.onIconClicked({});

            expect(view._fetchProductTemplate).toHaveBeenCalledWith(testId);
        });
    });

    describe('_openItemInDrawer()', function() {
        var oldDrawer;

        beforeEach(function() {
            oldDrawer = app.drawer;
            app.drawer = {
                open: $.noop
            };

            sinon.stub(app.data, 'createBean').callsFake(function(module, response) {
                return response;
            });

            sinon.stub(app.drawer, 'open').callsFake(function() {
            });
        });

        afterEach(function() {
            app.drawer = oldDrawer;
        });

        it('should call app.drawer.open with data', function() {
            view._openItemInDrawer({
                id: 'asd1',
                name: 'test'
            });

            var viewDetails = view.closestComponent('record') ?
                view.closestComponent('record') :
                view.closestComponent('create');

            if (!_.isUndefined(viewDetails)) {
                expect(app.drawer.open).toHaveBeenCalledWith({
                    layout: 'product-catalog-dashlet-drawer-record',
                    context: {
                        module: 'ProductTemplates',
                        model: {
                            id: 'asd1',
                            name: 'test',
                            viewId: viewDetails.cid
                        },
                        closestComponent: viewDetails
                    }
                });
            }
        });
    });
});
