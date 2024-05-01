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
describe('Data.Base.FiltersBeanCollection', function() {
    var app, filters, filterModuleName = 'Accounts', fetchStub, metadata,
        fixturePath = '../tests/modules/Filters/clients/base/datas/fixtures';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        SugarTest.declareData('base', 'Filters');

        metadata = SugarTest.loadFixture('metadata', fixturePath);
        fetchStub = sinon.stub(app.BeanCollection.prototype, 'fetch').callsFake(function(options) {
            options.success();
        });

        filters = app.data.createBeanCollection('Filters');
        filters.setModuleName(filterModuleName);
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
    });

    it('should set the `moduleName` property on the collection', function() {
        expect(filters.moduleName).toEqual(filterModuleName);
    });

    describe('collection events', function() {

        beforeEach(function() {
            sinon.stub(app.metadata, 'getModule')
                .withArgs(filterModuleName)
                .returns(metadata);

            fetchStub.restore();
            fetchStub = sinon.stub(filters._getPrototype(), 'fetch').callsFake(function(options) {
                var models = SugarTest.loadFixture('user', fixturePath);
                options.success(models);
            });

            filters.load();
        });

        it('should save/remove the filter to memory if explicitly added/removed to the collection', function() {
            expect(filters.collection.get('test')).toBeUndefined();

            filters.collection.add({id: 'test'});

            var filters2 = app.data.createBeanCollection('Filters');
            filters2.setModuleName(filterModuleName);
            filters2.load();
            filters.load();

            // verify all the collections have it
            expect(filters.collection.get('test')).toBeDefined();
            expect(filters2.collection.get('test')).toBeDefined();

            filters2.collection.remove('test');

            var filters3 = app.data.createBeanCollection('Filters');
            filters3.setModuleName(filterModuleName);
            filters3.load();
            filters2.load();
            filters.load();

            // verify no collections have it
            expect(filters.collection.get('test')).toBeUndefined();
            expect(filters2.collection.get('test')).toBeUndefined();
            expect(filters3.collection.get('test')).toBeUndefined();
        });

        it('should refresh the filters in memory on "cache:update" event', function() {
            var filter = filters.collection.get('user-filter-id-0');
            expect(filter).toBeDefined();

            filter.set('name', 'another name');

            var filters2 = app.data.createBeanCollection('Filters');
            filters2.setModuleName(filterModuleName);
            filters2.load();

            // verify the name is not changed in the new collection
            filter = filters2.collection.get('user-filter-id-0');
            expect(filter.get('name')).not.toEqual('another name');

            filter.set('name', 'another name');
            filter.trigger('cache:update', filter);

            var filters3 = app.data.createBeanCollection('Filters');
            filters3.setModuleName(filterModuleName);
            filters3.load();
            filters2.load();
            filters.load();

            // verify the name has changed in all the collections
            expect(filters.collection.get('user-filter-id-0').get('name')).toEqual('another name');
            expect(filters2.collection.get('user-filter-id-0').get('name')).toEqual('another name');
            expect(filters3.collection.get('user-filter-id-0').get('name')).toEqual('another name');
        });
    });

    describe('load', function() {
        var previousUserId;

        beforeEach(function() {
            sinon.stub(app.metadata, 'getModule')
                .withArgs(filterModuleName)
                .returns(metadata);

            fetchStub.restore();

            previousUserId = app.user.id;
            app.user.id = 'testId';
            fetchStub = sinon.stub(filters._getPrototype(), 'fetch').callsFake(function(options) {
                var models,
                    filter = [
                        {'created_by': 'testId'},
                        {'module_name': filterModuleName}
                    ];
                if (_.isEqual(options.filter, filter)) {
                    models = SugarTest.loadFixture('user', fixturePath);
                }
                options.success(models);
            });

        });

        afterEach(function() {
            // dispose
            app.user.id = previousUserId;
        });

        it('should load the collection of filters sorted by type, and alphabetically', function() {
            sinon.stub(app.lang, 'get')
                .withArgs('LBL_TEST1').returns('A')
                .withArgs('LBL_TEST2', [filterModuleName, 'Filters']).returns('C')
                .withArgs('LBL_TEST3', [filterModuleName, 'Filters']).returns('Ab');

            filters.load();

            expect(filters.collection.pluck('id')).toEqual([
                // `editable` first (i.e. users' filters).
                'user-filter-id-0', // My Awesome Filter
                'user-filter-id-1', // My Favorite Filter
                // non `editable` second (i.e. predefined filters).
                'test1', //A
                'test3', //Ab
                'test2' //C
            ]);
        });

        it('should make only one request for multiple collections', function() {

            var filters2 = app.data.createBeanCollection('Filters');
            filters2.setModuleName(filterModuleName);

            var filters3 = app.data.createBeanCollection('Filters');
            filters3.setModuleName(filterModuleName);

            // reset existing `fetch` stub
            fetchStub.restore();
            var fetchSpy = sinon.spy(app.BeanCollection.prototype, 'fetch');

            // mock the server response to fake existing user defined filters
            var fakeUserFilters = SugarTest.loadFixture('user', fixturePath);
            var server = sinon.fakeServer.create();
            server.respondWith("GET", /.*\/rest\/v10\/Filters.*/,
                [200, {"Content-Type": "application/json"},
                    JSON.stringify({records: fakeUserFilters})]);

            // run load on multiple filters
            filters.load();
            filters2.load();

            // make server respond with fake data
            server.respond();

            // load after server responded
            filters3.load();

            server.restore();

            expect(fetchSpy).toHaveBeenCalledOnce();

            // verify that all filters have user-defined data
            expect(filters.collection.get('user-filter-id-0')).toBeDefined();
            expect(filters2.collection.get('user-filter-id-0')).toBeDefined();
            expect(filters3.collection.get('user-filter-id-0')).toBeDefined();
        });

        it('should load the template filter when initial_filter is defined in the filter options', function() {
            filters.load();

            // verify template filters are not in the collection
            expect(filters.collection.get('test4')).toBeUndefined();

            // verify the expected template filter is in the collection
            filters.setFilterOptions({initial_filter: 'test4'});
            filters.load();
            expect(filters.collection.get('test4')).toBeDefined();

            filters.clearFilterOptions();
            filters.load();
            expect(filters.collection.get('test4')).toBeUndefined();
        });

        it('should create a relate template/initial filter and add it to the collection', function() {
            var filterOptions = {
                initial_filter: '$relate',
                filter_populate: {
                    'assigned_user_id': 'testUserId'
                }
            };
            filters.setFilterOptions(filterOptions);
            filters.load();

            var filter = filters.collection.get('$relate');
            expect(filter).toBeDefined();
            expect(filter.get('editable')).toBe(true);
            expect(filter.get('is_template')).toBe(true);
            expect(filter.get('filter_definition')).toEqual([
                {'assigned_user_id': ''}
            ]);
        });
    });
});
