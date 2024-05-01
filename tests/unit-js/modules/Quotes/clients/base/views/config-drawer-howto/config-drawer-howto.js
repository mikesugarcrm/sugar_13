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
describe('Quotes.View.ConfigDrawerHowto', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base', 'Quotes', 'config-drawer-howto', null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should initialize fieldsListLeft', function() {
            expect(view.fieldsListLeft).toEqual([]);
        });

        it('should initialize fieldsListRight', function() {
            expect(view.fieldsListRight).toEqual([]);
        });

        it('should initialize hiddenFields', function() {
            expect(view.hiddenFields).toEqual([]);
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'on');
            sinon.stub(view, '_super');

            view.bindDataChange();
        });

        it('should set listener on context for config:fields:change', function() {
            expect(view.context.on).toHaveBeenCalledWith('config:fields:change', view.onFieldsChange, view);
        });
    });

    describe('onFieldsChange()', function() {
        var fieldList;

        beforeEach(function() {
            sinon.stub(view, 'render');
            fieldList = [{
                name: 'a1'
            }, {
                name: 'b2'
            }, {
                name: 'c3'
            }];
            view.onFieldsChange('test', fieldList);
        });

        afterEach(function() {
            fieldList = null;
        });

        it('should set fieldsListLeft', function() {
            expect(view.fieldsListLeft).toEqual([{
                name: 'a1'
            }]);
        });

        it('should set fieldsListRight', function() {
            expect(view.fieldsListRight).toEqual([{
                name: 'b2'
            }, {
                name: 'c3'
            }]);
        });

        it('should call render', function() {
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('onSearchFilterChanged()', function() {
        var event;
        var field1;
        var field2;
        var field1ShowStub;
        var field1HideStub;
        var field2HideStub;
        var field2ShowStub;

        beforeEach(function() {
            field1ShowStub = sinon.stub();
            field1HideStub = sinon.stub();
            field2HideStub = sinon.stub();
            field2ShowStub = sinon.stub();
            field1 = {
                name: 'aaa',
                label: 'test 1',
                hide: field1HideStub,
                show: field1ShowStub,
                dispose: $.noop
            };
            field2 = {
                name: 'bbb',
                label: 'test 2',
                hide: field2HideStub,
                show: field2ShowStub,
                dispose: $.noop
            };
            view.hiddenFields = [
                field1,
                field2
            ];
            view.fields = [
                field1,
                field2
            ];
        });

        afterEach(function() {
            event = null;
            field1ShowStub = null;
            field1HideStub = null;
            field2ShowStub = null;
            field2HideStub = null;
            field1 = null;
            field2 = null;
        });

        it('should show any hidden fields', function() {
            event = {
                currentTarget: $('<input value="test" />')
            };
            view.onSearchFilterChanged(event);

            expect(field1ShowStub).toHaveBeenCalled();
            expect(field2ShowStub).toHaveBeenCalled();
        });

        it('should hide fields that do not meet search term in name search', function() {
            event = {
                currentTarget: $('<input value="aa" />')
            };
            view.onSearchFilterChanged(event);

            expect(view.hiddenFields.length).toBe(1);
            expect(view.hiddenFields[0]).toBe(field2);
            expect(field2HideStub).toHaveBeenCalled();
        });

        it('should hide fields that do not meet search term in label search', function() {
            event = {
                currentTarget: $('<input value="1" />')
            };
            view.onSearchFilterChanged(event);

            expect(view.hiddenFields.length).toBe(1);
            expect(view.hiddenFields[0]).toBe(field2);
            expect(field2HideStub).toHaveBeenCalled();
        });

        it('should leave fields visible that pass search term', function() {
            event = {
                currentTarget: $('<input value="test" />')
            };
            view.onSearchFilterChanged(event);

            expect(view.hiddenFields.length).toBe(0);
            expect(field1HideStub).not.toHaveBeenCalled();
            expect(field2HideStub).not.toHaveBeenCalled();
        });
    });

    describe('render()', function() {
        var propStub;

        beforeEach(function() {
            propStub = sinon.stub();
            sinon.stub(view, '$').callsFake(function() {
                return {
                    prop: propStub
                };
            });
            sinon.stub(view, '_super');

            view.render();
        });

        afterEach(function() {
            propStub = null;
        });

        it('should call $ with .indeterminate', function() {
            expect(view.$).toHaveBeenCalledWith('.indeterminate');
        });

        it('should call $.prop indeterminate true', function() {
            expect(propStub).toHaveBeenCalledWith('indeterminate', true);
        });
    });

    describe('_dispose()', function() {
        beforeEach(function() {
            sinon.stub(view, '_super');

            view._dispose();
        });

        it('should empty fieldsListLeft', function() {
            expect(view.fieldsListLeft).toEqual([]);
        });

        it('should empty fieldsListRight', function() {
            expect(view.fieldsListRight).toEqual([]);
        });

        it('should empty hiddenFields', function() {
            expect(view.hiddenFields).toEqual([]);
        });
    });
});
