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
describe('View.Layouts.Base.SearchLayout', function() {
    var app, layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        app.hint = {
            isHintUser: function() {},
        };
        layout = SugarTest.createLayout('base', null, 'search');
        layout.initialize(layout.options);
        layout.initComponents();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        layout.context.clear();
        layout.dispose();
        layout = null;
        sinon.restore();
    });

    describe('tags filters', function() {
        var sampleTag;

        beforeEach(function() {
            sampleTag = {name: 'tag1', id: '1234'};
            layout.context.set('tags', [sampleTag]);
            sinon.stub(layout, '_super');

            sinon.spy(layout.collection, 'fetch');
        });

        it('adds tag_filters to search collection', function() {
            layout.search();
            var args = layout.collection.fetch.args[0][0];

            expect(args.apiOptions.data.tag_filters).toEqual([sampleTag.id]);
            expect(args.module_list).toEqual([]);
        });

        it('adds tag_filters to filter collection', function() {
            layout.filter();
            var args = layout.collection.fetch.args[0][0];

            expect(args.apiOptions.data.tag_filters).toEqual([sampleTag.id]);
            expect(args.module_list).toEqual([]);
        });
    });

    describe('loadData', function() {
        var sampleTag;

        beforeEach(function() {
            sampleTag = {name: 'tag1', id: '1234'};
            layout.context.set('tags', [sampleTag]);
            sinon.stub(layout, '_super');
        });

        it('sets the right options', function() {
            var options = {};
            layout.loadData(options);

            expect(options.apiOptions).toEqual({data: {tag_filters: [sampleTag.id]},
                fetchWithPost: true, useNewApi: true});
            expect(options.module_list).toEqual([]);
        });
    });
});
