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
describe('Base.View.Pagination', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;
        var context = new app.Context();
        view = SugarTest.createView('base', null, 'pagination', {}, context);
        sinon.stub(view, '$').callsFake(function() {
            return {
                data: function() {
                    return 3;
                },
                hasClass: function() {
                    return true;
                }
            };
        });
        sinon.stub(view, 'render');
    });

    afterEach(function() {
        view.dispose();
        view = null;
        sinon.restore();
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

    describe('show', function() {
        var result;

        it('should reset pagination when no records are present in the result', function() {
            result = {
                currentPage: 0,
                records: [],
                totalPages: 0
            };
            var hideStub = sinon.stub(view, 'hide');
            view.show(result);
            expect(view.pageNumList).toEqual([]);
            expect(view.paginationLength).toBe(0);
            expect(hideStub).toHaveBeenCalled();
        });

        describe('when records are present in the result', function() {
            beforeEach(function() {
                result = {
                    currentPage: 2,
                    records: [
                        {
                            url: 'url1',
                            name: 'name1',
                            description: 'desc1'
                        },
                        {
                            url: 'url2',
                            name: 'name2',
                            description: 'desc2'
                        },
                        {
                            url: 'url3',
                            name: 'name3',
                            description: 'desc3'
                        }
                    ],
                    totalPages: 3
                };
            });

            afterEach(function() {
                result = null;
            });

            it('should assign the totalPages to view.paginationLength', function() {
                view.show(result);
                expect(view.paginationLength).toBe(3);
                expect(view.pageNumList.length).toBe(result.totalPages);
            });

            it('should update view.pageNumClicked', function() {
                view.pageNumClicked = 3;
                view.show(result);
                expect(view.pageNumClicked).toBe(2);
            });

            it('should set view.isNextDisabled to true', function() {
                result.currentPage = 3;
                view.show(result);
                expect(view.isNextDisabled).toBeTruthy();
            });

            it('should disable pagination', function() {
                result.currentPage = 1;
                result.totalPages = 1;
                view.show(result);
                expect(view.isNextDisabled).toBeTruthy();
                expect(view.isPrevDisabled).toBeTruthy();
                expect(view.isPageNumDisabled).toBeTruthy();
            });

            it('should set view.isPageNumDisabled to false', function() {
                result.currentPage = 3;
                view.show(result);
                expect(view.isPageNumDisabled).toBeFalsy();
            });

            it('should have all the hbs classes assigned to each pageNumList object', function() {
                view.show(result);
                expect(view.pageNumList[1].isIcon).toBeFalsy();
                expect(view.pageNumList[1].listClass).toEqual('pagination-li');
                expect(view.pageNumList[1].subListClass)
                    .toEqual('paginate-num-button btn btn-link btn-invisible');
                expect(view.pageNumList[1].pageNum).toBe(2);
            });

            it('should set view.pageNumList isActive to true', function() {
                view.show(result);
                expect(view.pageNumList[1].isActive).toBeTruthy();
            });

            it('should set view.pageNumList isActive to false', function() {
                result.currentPage = 1;
                view.show(result);
                expect(view.pageNumList[1].isActive).toBeFalsy();
            });

            it('should set view.pageNumList isActive to false', function() {
                result.currentPage = 1;
                result.totalPages = 1;
                view.show(result);
                expect(view.pageNumList[0].isActive).toBeFalsy();
            });
        });

        describe('when totalPages are more than 4', function() {
            beforeEach(function() {
                result = {
                    currentPage: 3,
                    records: [
                        {
                            url: 'url1',
                            name: 'name1',
                            description: 'desc1'
                        },
                        {
                            url: 'url2',
                            name: 'name2',
                            description: 'desc2'
                        },
                        {
                            url: 'url3',
                            name: 'name3',
                            description: 'desc3'
                        }
                    ],
                    totalPages: 5
                };
            });

            it('should populate the view.pageNumList and should have length equal to 5', function() {
                view.show(result);
                expect(view.pageNumList.length).toBe(5);
            });

            it('should have all the hbs classes assigned to each pageNumList object', function() {
                view.show(result);
                expect(view.pageNumList[2].isIcon).toBeFalsy();
                expect(view.pageNumList[2].listClass).toEqual('pagination-li');
                expect(view.pageNumList[2].subListClass)
                    .toEqual('paginate-num-button btn btn-link btn-invisible');
                expect(view.pageNumList[2].pageNum).toBe(3);
            });

            it('should Add ellipsis to the front of the view.pageNumList', function() {
                view.show(result);
                expect(view.pageNumList[0].isIcon).toBeTruthy();
                expect(view.pageNumList[0].listClass).toEqual('pagination-li');
                expect(view.pageNumList[0].subListClass).toEqual('left-ellipsis-icon sicon sicon-ellipsis');
            });

            it('should Add ellipsis to the end of the view.pageNumList', function() {
                view.show(result);
                expect(view.pageNumList[4].isIcon).toBeTruthy();
                expect(view.pageNumList[4].listClass).toEqual('pagination-li');
                expect(view.pageNumList[4].subListClass).toEqual('right-ellipsis-icon sicon sicon-ellipsis');
            });
        });
    });

    describe('getPageNumClicked()', function() {
        var evt;
        var triggerStub;

        beforeEach(function() {
            evt = {
                preventDefault: sinon.stub(),
                stopPropagation: sinon.stub()
            };
            triggerStub = sinon.stub(view.context, 'trigger');
            view.pageNumClicked = 5;
        });

        afterEach(function() {
            evt = null;
            triggerStub = null;
        });

        it('event page.clicked should be triggered', function() {
            view.getPageNumClicked(evt);
            expect(evt.preventDefault).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('page:clicked', {pageNum: 3});
        });

        it('when the same page is clicked twice, event page.clicked should not be triggered', function() {
            view.pageNumClicked = 3;
            view.getPageNumClicked(evt);
            expect(triggerStub).not.toHaveBeenCalled();
        });
    });

    describe('onPageNavClicked()', function() {
        var evt;
        var triggerStub;

        beforeEach(function() {
            evt = {
                preventDefault: sinon.stub(),
                stopPropagation: sinon.stub()
            };
            triggerStub = sinon.stub(view.context, 'trigger');
        });

        afterEach(function() {
            evt = null;
            triggerStub = null;
        });

        it('should call preventDefault function', function() {
            view.onPageNavClicked(evt);
            expect(evt.preventDefault).toHaveBeenCalled();
        });

        it('should trigger event page.clicked when previous navigation button is clicked', function() {
            sinon.stub(jQuery.fn, 'hasClass').withArgs('previous-fav').returns(true);
            view.onPageNavClicked(evt);
            expect(triggerStub).toHaveBeenCalledWith('page:clicked', {pageNum: 2});
        });

        it('should trigger event page.clicked when element class is nav-previous', function() {
            sinon.stub(jQuery.fn, 'hasClass').withArgs('nav-previous').returns(true);
            view.onPageNavClicked(evt);
            expect(triggerStub).toHaveBeenCalledWith('page:clicked', {pageNum: 2});
        });

        it('should trigger event page.clicked when next navigation button is clicked', function() {
            var hasClassStub = sinon.stub();
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
            expect(triggerStub).toHaveBeenCalledWith('page:clicked', {pageNum: 4});
        });

        it('should trigger event page.clicked when element class is nav-next', function() {
            var hasClassStub = sinon.stub();
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
            expect(triggerStub).toHaveBeenCalledWith('page:clicked', {pageNum: 4});
        });
    });
});
