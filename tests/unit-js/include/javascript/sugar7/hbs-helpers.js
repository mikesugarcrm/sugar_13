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
describe('Sugar7.View.Handlebars.helpers', function() {
    var app, savedHelpers;

    beforeEach(function () {
        app = SugarTest.app;
        savedHelpers = Handlebars.helpers;
        SugarTest.loadFile('../include/javascript/sugar7', 'hbs-helpers', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        Handlebars.helpers = savedHelpers;
        app = null;
        SugarTest.testMetadata.dispose();
        sinon.restore();
    });

    describe('sub-template helpers', function() {
        var data, options, spy, stub;

        beforeEach(function() {
            spy = sinon.spy();
            data = {name: 'Jack'};
            options = {};
            options.hash = {hashArg1: 'foo', hashArg2: 'bar'};
        });

        afterEach(function() {
            stub.restore();
        });

        describe('subViewTemplate helper', function() {
            it('should make private @variables out of the hash arguments', function() {
                stub = sinon.stub(app.template, 'getView').returns(spy);
                Handlebars.helpers.subViewTemplate('key', data, options);
                expect(spy.args[0][1].data.hashArg1).toEqual('foo');
                expect(spy.args[0][1].data.hashArg2).toEqual('bar');
            });
        });

        describe('subFieldTemplate helper', function() {
            it('should make private @variables out of the hash arguments', function() {
                stub = sinon.stub(app.template, 'getField').returns(spy);
                Handlebars.helpers.subFieldTemplate('fieldName', 'view', data, options);
                expect(spy.args[0][1].data.hashArg1).toEqual('foo');
                expect(spy.args[0][1].data.hashArg2).toEqual('bar');
            });
        });

        describe('subLayoutTemplate helper', function() {
            it('should make private @variables out of the hash arguments', function() {
                stub = sinon.stub(app.template, 'getLayout').returns(spy);
                Handlebars.helpers.subLayoutTemplate('key', data, options);
                expect(spy.args[0][1].data.hashArg1).toEqual('foo');
                expect(spy.args[0][1].data.hashArg2).toEqual('bar');
            });
        });
    });

    describe('loading', function() {

        it('should translate the string passed and escape it if needed', function() {
            sinon.stub(app.lang, 'get').withArgs('LBL_LOADING').returns('Loading Text');

            var tpl = Handlebars.compile('{{loading "LBL_LOADING"}}');
            var result = tpl();

            expect($(result).text()).toEqual('Loading Text...');
        });

        it('should escape the string passed', function() {
            sinon.stub(app.lang, 'get').withArgs('LBL_HTML').returns('<script>alert()</script>');

            var tpl = Handlebars.compile('{{loading "LBL_HTML"}}');
            var result = tpl();

            expect($(result).text()).toEqual('<script>alert()</script>...');
        });

        it('should allow classes to be passed to the helper', function() {
            sinon.stub(app.lang, 'get').withArgs('LBL_LOADING').returns('Loading Text');

            var tpl = Handlebars.compile('{{loading "LBL_HTML" cssClass="my-class other-class"}}');
            var result = tpl();

            var $el = $(result);
            expect($el).toHaveClass('my-class');
            expect($el).toHaveClass('other-class');
        });
    });

    describe('moduleLabel', function() {
        let module;
        let moduleMeta;
        let size;
        let options;

        beforeEach(function() {
            module = 'Accounts';
            moduleMeta = {
                color: 'green',
                icon: 'sicon-accounts-lg',
                display_type: 'icon',
            };
            size = 'lg';
            options = {
                hash: {}
            };

            sinon.stub(app.metadata, 'getModule').returns(moduleMeta);
            sinon.stub(app.lang, 'getModuleIconLabel').returns('ZZ');
        });

        it('should add the necessary general label classes', function() {
            let result = Handlebars.helpers.moduleLabel(module, size, options);
            let $el = $(result.string);
            expect($el).toHaveClass('label-module');
        });

        it('should add the correct size class', function() {
            let result = Handlebars.helpers.moduleLabel(module, 'sm', options);
            let $el = $(result.string);
            expect($el).toHaveClass('label-module-size-sm');

            result = Handlebars.helpers.moduleLabel(module, 'lg', options);
            $el = $(result.string);
            expect($el).toHaveClass('label-module-size-lg');

            result = Handlebars.helpers.moduleLabel(module, 'notARealSize', options);
            $el = $(result.string);
            expect($el).toHaveClass('label-module-size-sm');
        });

        it('should add the correct color class based on module metadata', function() {
            let result = Handlebars.helpers.moduleLabel(module, size, options);
            let $el = $(result.string);
            expect($el).toHaveClass('label-module-color-green');
        });

        it('should add the correct icon class when the module is using icons', function() {
            let result = Handlebars.helpers.moduleLabel(module, size, options);
            let $el = $(result.string);
            expect($el).toHaveClass('sicon');
            expect($el).toHaveClass('sicon-accounts-lg');
        });

        it('should add the correct text content when the module is using abbreviations', function() {
            moduleMeta.display_type = 'abbreviation';
            let result = Handlebars.helpers.moduleLabel(module, size, options);
            let $el = $(result.string);
            expect($el).toHaveText('ZZ');
        });

        it('should add any extra classes provided through the class option', function() {
            options.hash = {
                class: 'bg-transparent text-white'
            };

            let result = Handlebars.helpers.moduleLabel(module, size, options);
            let $el = $(result.string);
            expect($el).toHaveClass('bg-transparent');
            expect($el).toHaveClass('text-white');
        });

        it('should use the color option in the label class if one is provided', function() {
            options.hash = {
                color: 'sidebar',
            };

            let results = Handlebars.helpers.moduleLabel(module, size, options);
            let $el = $(results.string);
            expect($el).toHaveClass('label-module-color-sidebar');
        });

        it('should apply any extra options as attributes', function() {
            options.hash = {
                rel: 'tooltip',
                title: 'test title'
            };

            let result = Handlebars.helpers.moduleLabel(module, size, options);
            let $el = $(result.string);
            expect($el.attr('rel')).toEqual('tooltip');
            expect($el.attr('title')).toEqual('test title');
        });
    });
});
