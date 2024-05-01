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
describe('Base.Field.CollectionCount', function() {
    var app, field, template,
        module = 'Bugs',
        fieldName = 'foo';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate('collection-count', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();
        var fieldDef = {};
        field = SugarTest.createField('base', fieldName, 'collection-count', 'detail', fieldDef, module);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
        sinon.restore();
    });

    describe('fetchCount', function() {
        beforeEach(function() {
            sinon.stub(app.alert, 'show');
            sinon.stub(app.lang, 'get');
            sinon.stub(field, 'updateCount');
            sinon.stub(field.collection, 'fetchTotal');
            sinon.stub(field.collection, 'trigger').callsFake(function() {});
        });

        it('should show loading alert message when total is null', function() {
            field.collection.total = null;
            field.fetchCount();

            expect(app.alert.show).toHaveBeenCalledWith('fetch_count', {
                level: 'process',
                title: app.lang.get('LBL_LOADING'),
                autoClose: false
            });
        });

        it('should trigger list:page-total:fetching on collection', function() {
            field.isLoadingCount = false;
            field.fetchCount();

            expect(field.collection.trigger).toHaveBeenCalledWith('list:page-total:fetching');
            expect(field.isLoadingCount).toBeTruthy();
            expect(field.updateCount).toHaveBeenCalled();
        });

        it('should call fetchTotal method on collection', function() {
            field.fetchCount();

            expect(field.collection.fetchTotal).toHaveBeenCalled();
        });
    });

    describe('updateCount', function() {

        beforeEach(function() {
            sinon.stub(app.lang, 'get').callsFake(function(key) {
                return key;
            });
            field.collection = app.data.createBeanCollection(module);
        });

        using('different collection properties', [
            {
                length: 0,
                next_offset: -1,
                expected: '',
                dataFetched: false
            },
            {
                length: 20,
                next_offset: -1,
                expected: 'TPL_LIST_HEADER_COUNT',
                dataFetched: false
            },
            {
                length: 0,
                next_offset: -1,
                expected: '',
                dataFetched: true
            },
            {
                length: 5,
                next_offset: -1,
                expected: 'TPL_LIST_HEADER_COUNT',
                dataFetched: true
            },
            {
                length: 20,
                next_offset: 20,
                expected: 'TPL_LIST_HEADER_COUNT_TOTAL',
                dataFetched: true
            },
            // If options are passed to updateCount, they will take precedence
            // over the collection's properties.
            {
                length: 20,
                next_offset: -1,
                expected: 'TPL_LIST_HEADER_COUNT_TOTAL',
                options: {
                    length: 50,
                    hasMore: true
                },
                dataFetched: true
            },
            {
                length: 20,
                total: 50,
                next_offset: -1,
                expected: 'TPL_LIST_HEADER_COUNT',
                options: {
                    length: 50,
                    hasMore: false
                },
                dataFetched: true
            }
        ], function(provider) {
            it('should display a proper count representation', function() {
                provider = provider || {};
                field.collection.total = provider.total || 0;
                field.collection.length = provider.length;
                field.collection.next_offset = provider.next_offset;
                field.collection.dataFetched = provider.dataFetched;

                field.updateCount(provider.options);
                expect(field.countLabel.toString()).toBe(provider.expected);
            });
        });

        it('should display the total cached count', function() {
            field.collection.length = 20;
            field.collection.total = 500;
            field.collection.dataFetched = true;

            field.updateCount();
            expect(field.countLabel.toString()).toBe('TPL_LIST_HEADER_COUNT_TOTAL');
        });

        it('should trigger list:page-total:fetched on the collection', function() {
            field.disposed = false;
            sinon.stub(field, '_setCountLabel');
            sinon.stub(field, 'render');
            sinon.stub(field.collection, 'trigger').callsFake(function() {});
            var options = {
                total: 5
            };

            field.updateCount(options);

            expect(field._setCountLabel).toHaveBeenCalledWith(options);
            expect(field.collection.trigger).toHaveBeenCalled();
            expect(field.render).toHaveBeenCalled();
        });
    });

    describe('paginate', function() {
        it('should fetch the total count when paginating', function() {
            sinon.stub(app.BeanCollection.prototype, 'fetchTotal');
            sinon.stub(app.alert);

            field.context.trigger('paginate');
            expect(app.BeanCollection.prototype.fetchTotal).toHaveBeenCalled();
        });
    });

    describe('checkListPagination', function() {
        it('should return false if field.view is not defined', function() {
            field.view = {};

            expect(field.checkListPagination()).toBeFalsy();
        });

        it('should return false if field.view.layout is not defined', function() {
            field.view = {
                layout: {}
            };

            expect(field.checkListPagination()).toBeFalsy();
        });

        it('should return false if field.view.layout does not have list-pagination', function() {
            field.view = {
                layout: {
                    getComponent: sinon.stub().returns({})
                }
            };

            expect(field.checkListPagination()).toBeFalsy();
        });

        it('should return true if field.view.layout has list-pagination', function() {
            field.view = {
                layout: {
                    getComponent: sinon.stub().returns({
                        'test': 'test'
                    })
                }
            };

            expect(field.checkListPagination()).toBeTruthy();
        });
    });

    describe('getRecordsNumCurrent', function() {
        using('different collection properties and options', [
            {
                start: 1,
                length: 10,
                page: 1,
                options: {
                    hasMore: false,
                },
                layout: 'search',
                isUsingListPagination: false,
                expected: 10,
            },
            {
                start: 1,
                length: 10,
                page: 1,
                options: {
                    hasMore: true,
                },
                layout: 'record',
                isUsingListPagination: false,
                expected: 10,
            },
            {
                start: 1,
                length: 10,
                page: 1,
                options: {
                    hasMore: true,
                },
                layout: 'records',
                isUsingListPagination: true,
                expected: '1-10',
            },
            {
                start: 11,
                length: 10,
                page: 2,
                options: {},
                layout: 'search',
                isUsingListPagination: true,
                expected: '11-20',
            },
        ], function(p) {
            it('should return correct numeration of current records', function() {
                sinon.stub(field, 'checkListPagination');
                field.context.get = sinon.stub()
                    .withArgs('layout').returns(p.layout)
                    .withArgs('isUsingListPagination').returns(p.isUsingListPagination);
                field.collection.page = p.page;

                const res = field.getRecordsNumCurrent(p.start, p.length, p.options);
                expect(res).toEqual(p.expected);
                expect(field.context.get).toHaveBeenCalledWith('isUsingListPagination');
            });
        });
    });

    describe('getRecordsNumTotal', function() {
        using('different collection properties and options', [
            {
                start: 1,
                length: 10,
                total: 10,
                options: {
                    hasMore: false,
                },
                expected: 10,
            },
            {
                start: 1,
                length: 10,
                total: null,
                options: {
                    hasMore: true,
                },
                expected: 11,
            },
            {
                start: 1,
                length: 10,
                total: null,
                options: {
                    hasMore: false,
                },
                expected: 10,
            },
        ], function(p) {
            it('should return correct total number of records', function() {
                field.collection.total = p.total;

                const res = field.getRecordsNumTotal(p.start, p.length, p.options);
                expect(res).toEqual(p.expected);
            });
        });
    });

    describe('getRecordsNum', function() {
        using('different collection properties and options', [
            {
                total: 10,
                page: 1,
                limit: 20,
                length: 10,
                layout: 'search',
                isUsingListPagination: false,
                expected: {
                    current: 10,
                    total: 10,
                },
            },
            {
                total: 25,
                page: 2,
                limit: 20,
                layout: 'search',
                isUsingListPagination: true,
                expected: {
                    current: '21-25',
                    total: 25,
                },
            },
            {
                total: 0,
                page: 2,
                limit: 5,
                layout: 'records',
                isUsingListPagination: true,
                expected: {
                    current: '6-10',
                    total: 10,
                },
            },
        ], function(provider) {
            it('should return correct result', function() {
                field.collection.total = provider.total;
                field.collection.page = provider.page;
                field.collection.setOption('limit', provider.limit);
                field.context.get = sinon.stub().returns(provider.isUsingListPagination);

                const res = field.getRecordsNum({length: provider.length || 5});
                expect(res).toEqual(provider.expected);
            });
        });
    });

    describe('reset', function() {
        it('should keep the counts in sync with the collection', function() {
            sinon.spy(field, 'updateCount');

            field.collection.length = 20;
            field.collection.total = 500;
            field.cachedCount = 500;
            field.collection.dataFetched = true;

            field.collection.trigger('reset');

            expect(field.updateCount.calledOnce).toBe(true);
            expect(field.countLabel.toString()).toBe('TPL_LIST_HEADER_COUNT_TOTAL');

            field.collection.length = 20;
            field.collection.total = null;
            field.cachedCount = undefined;
            field.collection.next_offset = -1;

            field.collection.trigger('reset');

            expect(field.updateCount.calledTwice).toBe(true);
            expect(field.countLabel.toString()).toBe('TPL_LIST_HEADER_COUNT');
        });
    });

    describe('refresh:count', function() {
        using('different collection properties and options', [
            {
                length: 20,
                next_offset: -1,
                expected: 'TPL_LIST_HEADER_COUNT_TOTAL',
                hasAmount: true,
                options: {
                    length: 50,
                    hasMore: true
                }
            },
            {
                length: 20,
                next_offset: 20,
                expected: 'TPL_LIST_HEADER_COUNT',
                hasAmount: true,
                options: {
                    length: 50,
                    hasMore: false
                }
            }
        ], function(provider) {
            it('should update the count field with passed-in options, not collection properties', function() {
                sinon.spy(field, 'updateCount');

                field.collection.length = provider.length;
                field.collection.next_offset = provider.next_offset;
                field.collection.dataFetched = true;

                field.context.trigger('refresh:count', provider.hasAmount, provider.options);

                expect(field.updateCount.called).toBe(true);
                expect(field.countLabel.toString()).toBe(provider.expected);
            });
        });
    });
});
