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
describe('Sugar7 search utils', function() {
    var app;
    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile('../include/javascript/sugar7', 'utils-search', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
    });

    afterEach(function() {
        sinon.restore();
    });

    describe('formatRecords', function() {
        var collection, model;
        beforeEach(function() {
            model = new Backbone.Model(fixtures.search.model1);

            model.fields = fixtures.search.model1_fields;

            collection = new Backbone.Collection(model);
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return fixtures.search.getModule1_return;
            });

            sinon.stub(app.metadata, 'getView').callsFake(function() {
                return fixtures.search.getView1_return;
            });
        });
        it('should set the highlights', function() {
            app.utils.GlobalSearch.formatRecords(collection, false);
            var modelHighlights = model.get('_highlights');
            expect(modelHighlights[0].name).toEqual('alphaField');
            expect(modelHighlights[0].label).toEqual('Alpha');
            expect(modelHighlights[0].value.string).toEqual('highlight1');
            expect(modelHighlights[1].name).toEqual('bravoField');
            expect(modelHighlights[1].label).toEqual('Bravo');
            expect(modelHighlights[1].value.string).toEqual('highlight2');
            expect(modelHighlights[2].name).toEqual('commentlog');
            expect(modelHighlights[2].label).toEqual('Comment Log');
            expect(modelHighlights[2].value.string).toEqual('this is a comment log');
        });
    });

    describe('getFieldsMeta', function() {
        var collection, model;
        beforeEach(function() {
            model = new Backbone.Model(fixtures.search.model1);

            model.fields = fixtures.search.model1_fields;

            collection = new Backbone.Collection(model);
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return fixtures.search.getModule1_return;
            });

            sinon.stub(app.metadata, 'getView').callsFake(function() {
                return fixtures.search.getView1_return;
            });
        });
        it('should patch the field metadata with primary & secondary designations', function() {
            var fieldsMeta = app.utils.GlobalSearch.getFieldsMeta('fakeModule');

            expect(fieldsMeta.primaryFields.name.ellipsis).toBeFalsy();
            expect(fieldsMeta.primaryFields.name.name).toEqual('name');
            expect(fieldsMeta.primaryFields.name.primary).toBeTruthy();

            expect(fieldsMeta.secondaryFields.alphaField.ellipsis).toBeFalsy();
            expect(fieldsMeta.secondaryFields.alphaField.name).toEqual('alphaField');
            expect(fieldsMeta.secondaryFields.alphaField.secondary).toBeTruthy();

            expect(fieldsMeta.secondaryFields.bravoField.ellipsis).toBeFalsy();
            expect(fieldsMeta.secondaryFields.bravoField.name).toEqual('bravoField');
            expect(fieldsMeta.secondaryFields.bravoField.secondary).toBeTruthy();
        });
    });

    describe('highlightFields', function() {
        describe('with just a `name` field', function() {
            var collection, model;
            beforeEach(function() {
                model = new Backbone.Model(fixtures.search.model1);

                model.fields = fixtures.search.model1_fields;

                collection = new Backbone.Collection(model);
                sinon.stub(app.metadata, 'getModule').callsFake(function() {
                    return fixtures.search.getModule1_return;
                });

                sinon.stub(app.metadata, 'getView').callsFake(function() {
                    return fixtures.search.getView1_return;
                });
            });
            it('should highlight the appropriate fields', function() {
                app.utils.GlobalSearch.formatRecords(collection, false);
                var fieldsMeta = app.utils.GlobalSearch.getFieldsMeta('fakeModule');
                var primaryFields = app.utils.GlobalSearch.highlightFields(model, fieldsMeta.primaryFields);
                var secondaryFields = app.utils.GlobalSearch.highlightFields(model, fieldsMeta.secondaryFields, true);

                expect(primaryFields.name.ellipsis).toBeFalsy();
                expect(primaryFields.name.name).toEqual('name');
                expect(primaryFields.name.primary).toBeTruthy();

                expect(secondaryFields.alphaField.ellipsis).toBeFalsy();
                expect(secondaryFields.alphaField.name).toEqual('alphaField');
                expect(secondaryFields.alphaField.secondary).toBeTruthy();
                expect(secondaryFields.alphaField.highlighted).toBeTruthy();

                expect(secondaryFields.bravoField.ellipsis).toBeFalsy();
                expect(secondaryFields.bravoField.name).toEqual('bravoField');
                expect(secondaryFields.bravoField.secondary).toBeTruthy();
                expect(secondaryFields.bravoField.highlighted).toBeTruthy();
            });
        });
        describe('with `first` and `last` name fields', function() {
            var collection, model;
            beforeEach(function() {
                model = new Backbone.Model(fixtures.search.model2);

                model.fields = fixtures.search.model2_fields;

                collection = new Backbone.Collection(model);
                sinon.stub(app.metadata, 'getModule').callsFake(function() {
                    return fixtures.search.getModule2_return;
                });

                sinon.stub(app.metadata, 'getView').callsFake(function() {
                    return fixtures.search.getView2_return;
                });

                app.user.setPreference('default_locale_name_format', 'f l');

            });
            it('should convert to using just a `name` field', function() {
                app.utils.GlobalSearch.formatRecords(collection, false);
                var fieldsMeta = app.utils.GlobalSearch.getFieldsMeta('fakeModule');
                var primaryFields = app.utils.GlobalSearch.highlightFields(model, fieldsMeta.primaryFields);
                var secondaryFields = app.utils.GlobalSearch.highlightFields(model, fieldsMeta.secondaryFields, true);

                expect(primaryFields.name.ellipsis).toBeFalsy();
                expect(primaryFields.name.name).toEqual('name');
                expect(primaryFields.name.primary).toBeTruthy();
                expect(primaryFields.name.highlighted).toBeTruthy();

                expect(secondaryFields.first_name).toBeUndefined();
                expect(secondaryFields.last_name).toBeUndefined();
            });
        });
    });
});
