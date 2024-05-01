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

describe('OutboundEmail.BaseEmailAddressField', function() {
    var app;
    var context;
    var field;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.declareData('base', 'OutboundEmail', true, false);
        SugarTest.declareData('base', 'Filters');
        SugarTest.loadHandlebarsTemplate('relate', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('relate', 'field', 'base', 'pill');
        SugarTest.loadComponent('base', 'field', 'relate');
        SugarTest.loadComponent('base', 'field', 'email-address', 'OutboundEmail');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'OutboundEmail'});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.createSandbox();

        field = SugarTest.createField({
            name: 'email_address',
            type: 'email-address',
            viewName: 'edit',
            fieldDef: {
                name: 'email_address',
                id_name: 'email_address_id',
                link: 'email_addresses',
                module: 'EmailAddresses',
                required: true,
                rname: 'email_address',
                source: 'non-db',
                table: 'email_addresses',
                type: 'relate'
            },
            module: 'OutboundEmail',
            model: model,
            context: context,
            loadFromModule: true
        });

        field.filters = app.data.createBeanCollection('Filters');
        field.filters.setModuleName('EmailAddresses');

        sandbox.stub(app.user, 'getPreference').withArgs('default_locale_name_format').returns('s f l');
        sandbox.stub(app.acl, 'hasAccess').returns(true);
        sandbox.stub(app.metadata, 'getModuleNames').returns(['EmailAddresses']);
        sandbox.stub(field, 'buildFilterDefinition').returns([]);
    });

    afterEach(function() {
        sandbox.restore();

        field.dispose();
        app.cache.cutAll();
        app.view.reset();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('initialization', function() {
        it('should be a single-select', function() {
            expect(field.def.isMultiSelect).toBe(false);
        });

        it('should use the RelateField templates', function() {
            expect(field.def.type).toBe('email-address');
            expect(field.type).toBe('relate');
        });

        it('should use : as the separator', function() {
            expect(field._separator).toBe(':');
        });
    });

    describe('searching for an email address', function() {
        it('should not add a new search choice when the term is not a valid email address', function() {
            var $el;
            var term = 'foo';
            var filterDef = {
                email_address: {
                    '$starts': term
                }
            };
            var addresses = app.data.createBeanCollection('EmailAddresses');

            addresses.next_offset = -1;
            addresses.add(app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: 'food@example.com'
            }));
            addresses.add(app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: 'fool@example.com'
            }));

            field.buildFilterDefinition.withArgs(term).returns(filterDef);
            sandbox.stub(field.searchCollection, 'fetch').callsFake(function(options) {
                options.success(addresses);
            });
            sandbox.spy(field, '_createSearchChoice');

            field.render();
            $el = field.$(field.fieldTag);

            $el.select2('search', term);
            expect(field._createSearchChoice).toHaveBeenCalledOnce();
            expect(field._createSearchChoice.alwaysReturned(null)).toBe(true);
            expect($el.data('select2').context.length).toBe(2);
        });

        it('should not add a new search choice when the term matches an existing email address', function() {
            var $el;
            var term = 'food@example.com';
            var filterDef = {
                email_address: {
                    '$starts': term
                }
            };
            var addresses = app.data.createBeanCollection('EmailAddresses');

            addresses.next_offset = -1;
            addresses.add(app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: term
            }));

            field.buildFilterDefinition.withArgs(term).returns(filterDef);
            sandbox.stub(field.searchCollection, 'fetch').callsFake(function(options) {
                options.success(addresses);
            });
            sandbox.spy(field, '_createSearchChoice');

            field.render();
            $el = field.$(field.fieldTag);

            $el.select2('search', term);
            expect(field._createSearchChoice).toHaveBeenCalledOnce();
            expect(field._createSearchChoice.alwaysReturned(null)).toBe(true);
            expect($el.data('select2').context.length).toBe(1);
        });

        it('should add a new search choice when the term is a new valid email address', function() {
            var $el;
            var term = 'foosball@example.com';
            var filterDef = {
                email_address: {
                    '$starts': term
                }
            };
            var addresses = app.data.createBeanCollection('EmailAddresses');

            addresses.next_offset = -1;

            field.buildFilterDefinition.withArgs(term).returns(filterDef);
            sandbox.stub(field.searchCollection, 'fetch').callsFake(function(options) {
                options.success(addresses);
            });
            sandbox.spy(field, '_createSearchChoice');

            field.render();
            $el = field.$(field.fieldTag);

            $el.select2('search', term);
            expect(field._createSearchChoice).toHaveBeenCalledOnce();
            expect(field._createSearchChoice.alwaysReturned({id: term, text: term})).toBe(true);
            expect($el.data('select2').context.length).toBe(1);
        });
    });

    describe('selecting an email address', function() {
        describe('the email address already exists', function() {
            it('should update the model', function() {
                var $el;
                var match;
                var term = 'foo';
                var filterDef = {
                    email_address: {
                        '$starts': term
                    }
                };
                var addresses = app.data.createBeanCollection('EmailAddresses');

                addresses.next_offset = -1;
                addresses.add(app.data.createBean('EmailAddresses', {
                    id: _.uniqueId(),
                    email_address: 'food@example.com'
                }));
                addresses.add(app.data.createBean('EmailAddresses', {
                    id: _.uniqueId(),
                    email_address: 'fool@example.com'
                }));

                field.buildFilterDefinition.withArgs(term).returns(filterDef);
                sandbox.stub(field.searchCollection, 'fetch').callsFake(function(options) {
                    options.success(addresses);
                });
                sandbox.spy(field, '_onFormatSelection');

                field.render();
                $el = field.$(field.fieldTag);

                // Search for the term.
                $el.select2('search', term);

                // Select the first match.
                match = addresses.at(0);
                $el.select2('data', {id: match.get('id'), text: match.get('email_address')}, true);

                expect(field._onFormatSelection).toHaveBeenCalledOnce();
                expect(model.get('email_address_id')).toBe(match.get('id'));
                expect(model.get('email_address')).toBe(match.get('email_address'));
                expect($el.data('select2').context.length).toBe(2);
            });
        });

        describe('the email address does not already exist', function() {
            var clock;
            var $el;
            var term;
            var filterDef;
            var addresses;

            beforeEach(function() {
                clock = sinon.useFakeTimers();

                term = 'foosball@example.com';
                filterDef = {
                    email_address: {
                        '$starts': term
                    }
                };
                field.buildFilterDefinition.withArgs(term).returns(filterDef);

                addresses = app.data.createBeanCollection('EmailAddresses');
                addresses.next_offset = -1;

                sandbox.stub(field.searchCollection, 'fetch').callsFake(function(options) {
                    options.success(addresses);
                });
                field.view.toggleButtons = sandbox.stub();
                sandbox.spy(field, '_onFormatSelection');
                sandbox.spy(field, 'setValue');

                field.render();
                $el = field.$(field.fieldTag);
            });

            afterEach(function() {
                clock.restore();
            });

            it('should update the model after the email address is created', function() {
                var match;
                var jsonResponse = {
                    id: _.uniqueId(),
                    email_address: term,
                    email_address_caps: term.toUpperCase(),
                    invalid_email: false,
                    opt_out: false,
                    deleted: false
                };

                sandbox.stub(app.api, 'records').callsFake(function(method, module, data, params, callbacks, options) {
                    setTimeout(function() {
                        callbacks.success(jsonResponse);
                        callbacks.complete();

                        // The action buttons should be enabled.
                        expect(field.view.toggleButtons.args[1][0]).toBe(true);

                        // The model is updated with the ID of the new email
                        // address.
                        expect(field.setValue.args[1][0].id).toBe(jsonResponse.id);
                        expect(field.setValue.args[1][0].value).toBe(jsonResponse.email_address);
                        expect(model.get('email_address_id')).toBe(jsonResponse.id);
                        expect(model.get('email_address')).toBe(jsonResponse.email_address);

                        // The temporary model should be removed from the search
                        // collection once the email address has been created.
                        expect($el.data('select2').context.length).toBe(0);
                    }, 100);
                });

                // Search for the term.
                $el.select2('search', term);

                // The temporary model should be added to the search collection
                // while the email address is being created.
                expect($el.data('select2').context.length).toBe(1);

                // Select the new email address and trigger the change event.
                match = $el.data('select2').context.at(0);
                $el.select2('data', {id: match.get('id'), text: match.get('email_address')}, true);

                expect(field._onFormatSelection).toHaveBeenCalledOnce();

                // The action buttons should be disabled.
                expect(field.view.toggleButtons.args[0][0]).toBe(false);

                // The model should be temporarily updated with the selected
                // email address. The ID is not yet known.
                expect(field.setValue.args[0][0].id).toBe(term);
                expect(field.setValue.args[0][0].value).toBe(term);

                // Tick ahead until after the email address has been created.
                clock.tick(100);
            });

            it('should update the model after creating the email address fails', function() {
                var match;

                sandbox.stub(app.error, 'handleHttpError');
                sandbox.stub(app.api, 'records').callsFake(function(method, module, data, params, callbacks, options) {
                    setTimeout(function() {
                        var error = {
                            request: {
                                aborted: false
                            }
                        };

                        callbacks.error(error);
                        callbacks.complete();

                        // The action buttons should be enabled.
                        expect(field.view.toggleButtons.args[1][0]).toBe(true);

                        // The model is updated with empty data.
                        expect(field.setValue.args[1][0].id).toBe('');
                        expect(field.setValue.args[1][0].value).toBe('');
                        expect(model.get('email_address_id')).toBe('');
                        expect(model.get('email_address')).toBe('');

                        // The temporary model should be removed from the search
                        // collection once the email address has been created.
                        expect($el.data('select2').context.length).toBe(0);
                    }, 100);
                });

                // Search for the term.
                $el.select2('search', term);

                // The temporary model should be added to the search collection
                // while the email address is being created.
                expect($el.data('select2').context.length).toBe(1);

                // Select the new email address and trigger the change event.
                match = $el.data('select2').context.at(0);
                $el.select2('data', {id: match.get('id'), text: match.get('email_address')}, true);

                expect(field._onFormatSelection).toHaveBeenCalledOnce();

                // The action buttons should be disabled.
                expect(field.view.toggleButtons.args[0][0]).toBe(false);

                // The model should be temporarily updated with the selected
                // email address. The ID is not yet known.
                expect(field.setValue.args[0][0].id).toBe(term);
                expect(field.setValue.args[0][0].value).toBe(term);

                // Tick ahead until after the email address has been created.
                clock.tick(100);
            });
        });
    });
});
