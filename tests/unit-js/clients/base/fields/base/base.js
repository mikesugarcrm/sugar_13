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
describe("Base.Field.Base", function() {
    var app, field, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        app.view.Field.prototype._renderHtml = function() {};
        sinonSandbox = sinon.createSandbox();
        app.start();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
        sinonSandbox.restore();
    });

    it('should initialize events from metadata def', function() {
        var events = {
            'eventType1': 'callback1',
            'eventType2': 'callback2'
        };

        field = SugarTest.createField('base', 'button', 'base', 'list', {
            events: events
        });

        expect(field.events.eventType1).toEqual('callback1');
        expect(field.events.eventType2).toEqual('callback2');
    });

    it('should trim whitespace on unformat', function(){
        field = SugarTest.createField("base","button", "base", "list");
        expect(field.unformat("  ")).toEqual("");
        expect(field.unformat("")).toEqual("");
        expect(field.unformat(" abc   ")).toEqual("abc");
        expect(field.unformat(123)).toEqual(123);
        expect(field.unformat({})).toEqual({});
    });

    it('should create bwc if defined on module', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {isBwcEnabled: true};
        });
        var def = { link: true };
        field = SugarTest.createField('base', 'text', 'base', 'list', def);
        field.model = new Backbone.Model({id: '12345'});
        field.model.module = 'Quotes';
        field._render();
        expect(getModuleStub).toHaveBeenCalled();
        expect(field.href).toEqual('#bwc/index.php?module=Quotes&action=DetailView&record=12345');
    });

    it('should create bwc if defined on def', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {isBwcEnabled: false};
        });
        var def = { link: true, bwcLink: true };
        field = SugarTest.createField('base', 'text', 'base', 'list', def);
        field.model = new Backbone.Model({id: '12345'});
        field.model.module = 'Quotes';
        field._render();
        expect(getModuleStub).toHaveBeenCalled();
        expect(field.href).toEqual('#bwc/index.php?module=Quotes&action=DetailView&record=12345');
    });

    it('should not create bwc if defined false on def', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {isBwcEnabled: false};
        });
        var def = { link: true, bwcLink: false };
        field = SugarTest.createField('base', 'text', 'base', 'list', def);
        field.model = new Backbone.Model({id: '12345'});
        field.model.module = 'Quotes';
        field._render();
        expect(getModuleStub).toHaveBeenCalled();
        expect(field.href).toEqual('#Quotes/12345');
    });

    it('should create normal sidecar if no bwc', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {isBwcEnabled: false};
        });
        var bwcBuildRouteStub = sinonSandbox.stub(app.bwc, 'buildRoute');
        var def = {
            link: true,
            route: {
                action: 'myaction'
            }
        };
        field = SugarTest.createField('base', 'text', 'base', 'list', def);
        field.model = new Backbone.Model({id: '12345'});
        field.model.module = 'Quotes';
        field._render();
        expect(getModuleStub).toHaveBeenCalled();
        expect(field.href).toEqual('#Quotes/12345/myaction');
        expect(bwcBuildRouteStub).not.toHaveBeenCalled();
    });
});
