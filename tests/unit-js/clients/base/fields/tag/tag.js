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

describe("tag field", function() {
    var app,
        field,
        model,
        module = 'Contacts',
        fieldName = 'tag',
        appGetStub;

    beforeEach(function() {
        Handlebars.templates = {};
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('tag', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('tag', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('tag', 'field', 'base', 'list');
        SugarTest.testMetadata.set();

        SugarTest.app.data.declareModels();
        model = app.data.createBean(module);
        appGetStub = sinon.stub(app.lang, 'get').callsFake(function(label) {
            return '(New Tag)';
        });
    });

    afterEach(function() {
        sinon.restore();
        if (field) {
            field.dispose();
        }
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
        SugarTest.testMetadata.dispose();
        appGetStub.restore();
    });

    it('should populate tagList with comma-space separated tags', function() {
        field = SugarTest.createField('base', fieldName, 'tag', 'list');
        field.initializeSelect2();
        var sampleTag = [{name: 'aaa'}, {name: 'bbb'}];
        var expectedTagList = 'aaa, bbb';

        field.model.set(fieldName, sampleTag);
        field.render();

        expect(field.tagList).toEqual(expectedTagList);
    });

    it('handleNewSelection should remove the newTag string from tag names', function() {
        field = SugarTest.createField('base', fieldName, 'tag', 'edit');
        var sampleText = 'asdf';
        var sampleEvent = {
            object: {
                newTag: true,
                text: sampleText + ' ' + app.lang.get('LBL_NEW_TAG')
            }
        };

        field.handleNewSelection(sampleEvent);
        expect(sampleEvent.object.text).toEqual(sampleText);
    });

    it('parseRecords should translate a tag to a select2 friendly format', function() {
        var name0 = 'asdf', name1 = 'zxcv';
        var sampleTags = [{name: name0} , {name: name1}];
        field = SugarTest.createField('base', fieldName, 'tag', 'detail');

        var resultTags = field.parseRecords(sampleTags);

        expect(resultTags[0].name).toBeUndefined();
        expect(resultTags[1].name).toBeUndefined();
        expect(resultTags[0].text).toEqual(name0);
        expect(resultTags[1].text).toEqual(name1);
        expect(resultTags[0].id).toEqual(name0);
        expect(resultTags[1].id).toEqual(name1);
    });

    describe('storeValues', function() {
        var name0 = 'asdf', name1 = 'zxcv', name2 = 'qwer';
        var initialTags = [{id: name0, name: name0}, {id: name1, name: name1}];

        beforeEach(function() {
            field = SugarTest.createField('base', fieldName, 'tag', 'edit');
            field.initializeSelect2();
        });

        afterEach(function() {
            field = null;
        });

        it('storeValues should add to the field model', function() {
            var newTagEvent = {
                added: {
                    text: name2,
                    id: name2
                }
            };

            field.value = initialTags;
            field.storeValues(newTagEvent);

            //model's tag property should include an extra tag
            expect(field.model.get('tag').length).toEqual(initialTags.length + 1);
            //model's tag property should now include name2
            expect(_.find(field.model.get('tag'), function(tag) {
                return tag === name2;
            })).toBeTruthy();
        });

        it('storeValues should remove from the field model', function() {
            var newTagEvent = {
                removed: {
                    text: name1,
                    id: name1
                }
            };

            field.value = initialTags;
            field.storeValues(newTagEvent);

            //model's tag property should have removed a tag
            expect(field.model.get('tag').length).toEqual(initialTags.length - 1);
            //model's tag property should no longer include name1
            expect(_.find(field.model.get('tag'), function(tag) {
                return tag.name === name1;
            })).toBeUndefined();
        });
    });

    describe('select2 function tests', function() {
        var fakeSelect2, select2Stub, superStub, jQueryStub, name = 'asdf';

        beforeEach(function() {
            field = SugarTest.createField('base', fieldName, 'tag', 'edit');
            field.view.action = 'placeholder';
            superStub = sinon.stub(field, '_super').callsFake(function() {});
            fakeSelect2 = { select2: function() {}, on: function() {} };
            select2Stub = sinon.stub(fakeSelect2, 'select2').callsFake(function(properties, record) {
                    _.extend(properties, properties, { on: function() {} }, fakeSelect2);
                    return properties;
                }
            );
            jQueryStub = sinon.stub(field, '$').callsFake(function() {
                return fakeSelect2;
            });
        });

        afterEach(function() {
            superStub.restore();
            jQueryStub.restore();
            select2Stub.restore();
            field = null;
        });

        it('createSearchChoice should return an object representing a new choice', function() {
            field._render();
            var returnedObj = field.$select2.createSearchChoice(name);

            expect(returnedObj.id).toEqual(name);
            expect(returnedObj.text).toEqual(name + ' (New Tag)');
        });

        it('createSearchChoice should return false on filter views', function() {
            field.view.action = 'filter-rows';
            field._render();

            expect(field.$select2.createSearchChoice(name)).toBe(false);
        });

        it('existing tags that contain special characters as the first character should be handled specially', function() {
            var name = '\'asdf';
            var getFormattedValueStub = sinon.stub(field, 'getFormattedValue').callsFake(function() {
                return [{name: name}];
            });
            field._render();

            expect(select2Stub.callCount).toBeGreaterThan(1);
            // Get the arg that is pushed to the select2 function at the end of the initializeSelect2 function
            var returnedRecord = _.find(select2Stub.args, function(arg) {
                return arg[0] === 'val';
            });
            expect(returnedRecord[1][0]).toEqual('\\\\' + name);

            getFormattedValueStub.restore();
        });

        it('existing tags that contain special characters but not as the first character should come out as is', function() {
            var name = 'as\'df';
            var getFormattedValueStub = sinon.stub(field, 'getFormattedValue').callsFake(function() {
                return [{name: name}];
            });
            field._render();

            expect(select2Stub.callCount).toBeGreaterThan(1);
            // Get the arg that is pushed to the select2 function at the end of the initializeSelect2 function
            var returnedRecord = _.find(select2Stub.args, function(arg) {
                return arg[0] === 'val';
            });
            expect(returnedRecord[1][0]).toEqual(name);

            getFormattedValueStub.restore();
        });
    });
});
