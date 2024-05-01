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
describe('Quotes.Base.Fields.QuoteFooterCurrency', function() {
    var app;
    var layout;
    var view;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'record');

        var def = {
            name: 'shipping',
            type: 'quote-footer-currency'
        };

        layout = SugarTest.createLayout('base', 'Quotes', 'record', {});
        view = SugarTest.createView('base', 'Quotes', 'record', null, null, true, layout);
        field = SugarTest.createField({
            name: 'shipping',
            type: 'quote-footer-currency',
            viewName: 'detail',
            fieldDef: def,
            module: 'Quotes',
            model: view.model,
            loadFromModule: true
        });
        sinon.stub(field, '_super');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();
        field.dispose();
        view = null;
        layout = null;
        app = null;
    });

    describe('initialize', function() {
        var initOptions;

        beforeEach(function() {
            initOptions = {
                context: {
                    isCreate: function() {
                        return false;
                    }
                }
            };
            sinon.stub(field.model, 'addValidationTask').callsFake(function() {});
        });

        it('should add model validation task', function() {
            field.initialize(initOptions);

            expect(field.model.addValidationTask).toHaveBeenCalled();
        });

        it('should trigger quotes:editableFields:add to add this field to the record', function() {
            sinon.stub(field.context, 'trigger').callsFake(function() {});
            field.initialize(initOptions);

            expect(field.context.trigger).toHaveBeenCalledWith('quotes:editableFields:add', field);
        });

        describe('on create view', function() {
            beforeEach(function() {
                initOptions = {
                    context: {
                        isCreate: function() {
                            return true;
                        }
                    }
                };

                field.context = {
                    trigger: sinon.spy()
                };
            });

            afterEach(function() {
                delete field.context;
            });

            it('should not add click events', function() {
                field.events = {};
                field.initialize(initOptions);

                expect(field.events['click .currency-field']).toBeUndefined();
            });

            it('should set options viewName to edit', function() {
                field.initialize(initOptions);

                expect(initOptions.viewName).toBe('edit');
            });

            it('should set action to edit', function() {
                field.initialize(initOptions);

                expect(field.action).toBe('edit');
            });
        });

        describe('on record view', function() {
            beforeEach(function() {
                initOptions = {
                    context: {
                        isCreate: function() {
                            return false;
                        }
                    }
                };

                sinon.stub(field.context, 'trigger').callsFake(function() {});
            });

            afterEach(function() {
                delete field.context;
            });

            it('should add click events', function() {
                field.events = {};
                field.initialize(initOptions);

                expect(field.events['click .currency-field']).toBeDefined();
            });

            it('should set options viewName to detail', function() {
                field.initialize(initOptions);

                expect(initOptions.viewName).toBe('detail');
            });

            it('should set action to edit', function() {
                field.initialize(initOptions);

                expect(field.action).toBe('detail');
            });
        });
    });

    describe('_loadTemplate()', function() {
        it('should get noaccess template when user has no access', function() {
            sinon.stub(field, '_checkAccessToAction').callsFake(function() {
                return false;
            });
            sinon.spy(app.template, 'getField');
            field._loadTemplate();

            expect(app.template.getField).toHaveBeenCalledWith(
                'quote-footer-currency',
                'noaccess',
                'Quotes'
            );
        });

        it('should use regular _loadTemplate when user has access', function() {
            sinon.stub(field, '_checkAccessToAction').callsFake(function() {
                return true;
            });
            sinon.spy(app.template, 'getField');
            field._loadTemplate();

            expect(field._super).toHaveBeenCalledWith('_loadTemplate');
        });
    });

    describe('_toggleFieldToEdit', function() {
        var record;
        var recordContextTriggerSpy;

        beforeEach(function() {
            recordContextTriggerSpy = sinon.spy();
            record = {
                context: {
                    trigger: recordContextTriggerSpy
                }
            };
            sinon.stub(field, 'closestComponent').callsFake(function() {
                return record;
            });
        });

        describe('when $el is in edit', function() {

            beforeEach(function() {
                field.$el = $('<div class="edit"></div>');
            });

            it('should not trigger the handleEdit event', function() {
                field._toggleFieldToEdit({});

                expect(recordContextTriggerSpy).not.toHaveBeenCalledWith('editable:handleEdit');
            });
        });

        describe('when $el is in detail', function() {
            beforeEach(function() {
                field.$el = $('<div class="detail"></div>');
            });

            it('should trigger the handleEdit event', function() {
                field._toggleFieldToEdit({});

                expect(recordContextTriggerSpy).toHaveBeenCalledWith('editable:handleEdit');
            });
        });
    });

    describe('_doValidateIsNumeric', function() {
        var callback = sinon.stub();
        var errors = [];

        beforeEach(function() {
            sinon.stub(app.lang, 'get').callsFake(function() {
                return 'foo';
            });
        });

        it('should call the callback without errors ', function() {
            sinon.stub(field.model, 'get').callsFake(function() {
                return 1;
            });
            field._doValidateIsNumeric([], [], callback);

            expect(errors.shipping).toBeUndefined();
        });

        it('should call the callback with one error', function() {
            sinon.stub(field.model, 'get').callsFake(function() {
                return 'foo';
            });
            errors[field.name] = 'foo';
            field._doValidateIsNumeric([], [], callback);

            expect(errors.shipping).toBeDefined();
        });
    });

    describe('_dispose', function() {
        it('should call app.utils.formatNumberLocale ', function() {
            sinon.stub(field.model, 'removeValidationTask').callsFake(function() {});
            field._dispose();

            expect(field.model.removeValidationTask).toHaveBeenCalled();
        });
    });
});
