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
describe('Plugins.ClickToEdit', function() {
    var app,
        plugin,
        component;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadPlugin('ClickToEdit');
        plugin = app.plugins.plugins.view.ClickToEdit;
    });

    afterEach(function() {
        sinon.restore();
        if(component) {
            component.dispose();
            component = null;
        }
        app.cache.cutAll();
        app = null;
    });

    describe('handleHideDatePicker', function() {
        beforeEach(function() {
            plugin.$ = function() {
                return {
                    val: function() {}
                };
            };
        });

        afterEach(function() {
            plugin.$ = null;
        });

        it('should return true if isHiding is true', function() {
            //simulate multiple handleHideDatePicker calls
            plugin.isHiding = true;
            expect(plugin.handleHideDatePicker()).toBeTruthy();
        });
    });

    describe('onAttach', function() {
        beforeEach(function() {
            sinon.stub(plugin, '_fieldOnAttach').callsFake(function() {});
            sinon.stub(plugin, '_viewOnAttach').callsFake(function() {});
        });

        it('should call _fieldOnAttach when called with a Field component', function() {
            component = SugarTest.createField('base', 'enum', 'enum');
            plugin.onAttach(component);

            expect(plugin._fieldOnAttach).toHaveBeenCalled();
            expect(plugin._viewOnAttach).not.toHaveBeenCalled();
            expect(plugin.isField).toBeTruthy();
            expect(plugin.isView).toBeFalsy();
        });

        it('should call _viewOnAttach when called with a View component', function() {
            component = SugarTest.createView('base', 'ForecastWorksheets', 'recordlist');
            plugin.onAttach(component);

            expect(plugin._fieldOnAttach).not.toHaveBeenCalled();
            expect(plugin._viewOnAttach).toHaveBeenCalled();
            expect(plugin.isField).toBeFalsy();
            expect(plugin.isView).toBeTruthy();
        });
    });

    describe('_fieldOnAttach', function() {
        it('should add events on field attach', function() {
            plugin.events = {};
            plugin.once = function() {};
            plugin._fieldOnAttach({});

            expect(plugin.events['mouseenter div.clickToEdit']).toBe('_fieldShowClickToEdit');
            expect(plugin.events['mouseleave div.clickToEdit']).toBe('_fieldHideClickToEdit');
            expect(plugin.events['click div.clickToEdit']).toBe('_fieldHandleFieldClick');
        });
    });

    describe('onDetach', function() {
        beforeEach(function() {
            sinon.stub(plugin, '_fieldOnDetach').callsFake(function() {});
            sinon.stub(plugin, '_viewOnDetach').callsFake(function() {});
        });

        it('should call _fieldOnDetach when called with a Field component', function() {
            plugin.isField = true;
            plugin.onDetach(component);

            expect(plugin._fieldOnDetach).toHaveBeenCalled();
            expect(plugin._viewOnDetach).not.toHaveBeenCalled();
        });

        it('should call _viewOnDetach when called with a View component', function() {
            plugin.isView = true;
            plugin.onDetach(component);

            expect(plugin._fieldOnDetach).not.toHaveBeenCalled();
            expect(plugin._viewOnDetach).toHaveBeenCalled();
        });
    });

    describe('_viewHandleKeyDown', function() {
        var keyDownEvent,
            clickObj;
        beforeEach(function() {
            keyDownEvent = {
                which: -1,
                preventDefault: function() {},
                shiftKey: false,
                target: '<input />'
            };
            sinon.stub(keyDownEvent, 'preventDefault').callsFake(function() {});
            plugin.$ = function() {};
            plugin._viewCurrentCTEList = [];
            clickObj = {
                click: function() {}
            };

            sinon.stub(plugin, '$').callsFake(function() {
                return {
                    find: function() {
                        return clickObj;
                    }
                };
            });
        });

        it('should do nothing if the key is not Tab', function() {
            plugin._viewHandleKeyDown(keyDownEvent);

            expect(keyDownEvent.preventDefault).not.toHaveBeenCalled();
        });

        it('should prevent default tabbing if the key is Tab', function() {
            keyDownEvent.which = 9;
            plugin.fields = [];
            plugin._viewHandleKeyDown(keyDownEvent);

            expect(keyDownEvent.preventDefault).toHaveBeenCalled();
        });

        it('will tab to the first record', function() {
            sinon.stub(clickObj, 'click');
            plugin.fields = [];
            plugin._viewCurrentCTEList = ['<input />'];
            keyDownEvent.which = 9;
            plugin._viewHandleKeyDown(keyDownEvent);

            expect(clickObj.click).toHaveBeenCalled();
        });

        it('move to the second record', function() {
            sinon.stub(clickObj, 'click');
            plugin.fields = [];
            plugin._viewCurrentCTEList = ['<input />', '<input />'];
            plugin._viewCurrentIndex = 0;
            keyDownEvent.which = 9;
            sinon.stub(plugin, '_fieldDoValidate').callsFake(function() {
                return true;
            });
            plugin._viewHandleKeyDown(keyDownEvent);

            expect(plugin._viewCurrentIndex).toEqual(1);
        });

        it('it will cycle to back to the start', function() {
            sinon.stub(clickObj, 'click');
            plugin.fields = [];
            plugin._viewCurrentCTEList = ['<input />', '<input />'];
            plugin._viewCurrentIndex = 1;
            keyDownEvent.which = 9;
            sinon.stub(plugin, '_fieldDoValidate').callsFake(function() {
                return true;
            });
            plugin._viewHandleKeyDown(keyDownEvent);

            expect(plugin._viewCurrentIndex).toEqual(0);
        });

        it('it will cycle to back to the end when shift is pressed', function() {
            sinon.stub(clickObj, 'click');
            plugin.fields = [];
            plugin._viewCurrentCTEList = ['<input />', '<input />'];
            plugin._viewCurrentIndex = 0;
            keyDownEvent.which = 9;
            keyDownEvent.shiftKey = true;
            sinon.stub(plugin, '_fieldDoValidate').callsFake(function() {
                return true;
            });
            plugin._viewHandleKeyDown(keyDownEvent);

            expect(plugin._viewCurrentIndex).toEqual(1);
        });

        it('it will move backwards from 1 to 0 when shift key is pressed', function() {
            sinon.stub(clickObj, 'click');
            plugin.fields = [];
            plugin._viewCurrentCTEList = ['<input />', '<input />'];
            plugin._viewCurrentIndex = 1;
            keyDownEvent.which = 9;
            keyDownEvent.shiftKey = true;
            sinon.stub(plugin, '_fieldDoValidate').callsFake(function() {
                return true;
            });
            plugin._viewHandleKeyDown(keyDownEvent);

            expect(plugin._viewCurrentIndex).toEqual(0);
        });

        it('will find the select2 value when field type is enum', function() {
            var select2Obj = {
                    select2: function() {}
                },
                field = {
                    type: 'enum',
                    fieldTag: '.select2',
                    $: function() {
                        return select2Obj;
                    }
                };
            sinon.stub(select2Obj, 'select2');
            plugin._viewCurrentIndex = 0;
            plugin.fields = [field];
            plugin._viewCurrentCTEList = ['<input sfuuid="0" />'];
            keyDownEvent.which = 9;
            plugin._viewHandleKeyDown(keyDownEvent);
            expect(select2Obj.select2).toHaveBeenCalled();
        });
    });

    describe('_fieldOnKeyDown', function() {
        beforeEach(function() {
            sinon.stub(plugin, '_fieldHandleKeyDown').callsFake(function() {});
        });

        it('should call _fieldHandleKeyDown on field key down', function() {
            plugin._fieldOnKeyDown({
                data: {
                    field: ''
                }
            });

            expect(plugin._fieldHandleKeyDown).toHaveBeenCalled();
        });
    });

    describe('_fieldHandleKeyDown', function() {
        var keyDownEvent,
            field;

        beforeEach(function() {
            sinon.stub(plugin, 'setMode').callsFake(function() {});
            keyDownEvent = {
                which: -1
            };
            field = {
                disposed: false
            };
        });

        it('should return if the field is disposed', function() {
            field.disposed = true;
            keyDownEvent.which = 27;
            plugin._fieldHandleKeyDown(keyDownEvent, field);

            expect(plugin.setMode).not.toHaveBeenCalled();
        });

        it('should setMode to list if Escape is pressed', function() {
            keyDownEvent.which = 27;
            plugin._fieldHandleKeyDown(keyDownEvent, field);

            expect(plugin.setMode).toHaveBeenCalledWith('list');
        });

        it('should add model change listener if Enter is pressed and the field value has changed', function() {
            keyDownEvent.which = 13;
            plugin.model = {
                once: function() {}
            };
            sinon.stub(plugin, '_fieldValueChanged').callsFake(function() { return true; });
            sinon.stub(plugin.model, 'once').callsFake(function() {});
            plugin._fieldHandleKeyDown(keyDownEvent, field);

            expect(plugin.model.once).toHaveBeenCalled();
        });

        it('should setMode to list if Enter is pressed and the field value has not changed', function() {
            keyDownEvent.which = 13;
            sinon.stub(plugin, '_fieldValueChanged').callsFake(function() { return false; });
            plugin._fieldHandleKeyDown(keyDownEvent, field);

            expect(plugin.setMode).toHaveBeenCalledWith('list');
        });
    });

    describe('_fieldValueChanged', function() {
        var field,
            testVal = '100',
            fieldVal = '25',
            result;

        beforeEach(function() {
            field = {
                fieldTag: '',
                type: '',
                value: fieldVal,
                $: function() {
                    return {
                        val: function() {
                            return testVal;
                        },
                        html: function() {
                            return testVal;
                        }
                    }
                }
            };

            plugin.unformat = function(val) {
                return val;
            };
            sinon.stub(plugin, '_fieldParsePercentage').callsFake(function(val) { return val; });
        });

        it('should call _fieldParsePercentage on currency field types', function() {
            field.type = 'currency';
            plugin._fieldValueChanged(field);

            expect(plugin._fieldParsePercentage).toHaveBeenCalledWith(testVal);
        });

        it('should call _fieldParsePercentage on int field types', function() {
            field.type = 'int';
            plugin._fieldValueChanged(field);

            expect(plugin._fieldParsePercentage).toHaveBeenCalledWith(testVal);
        });

        it('should not call _fieldParsePercentage on non currency/int field types', function() {
            field.type = 'potato';
            plugin._fieldValueChanged(field);

            expect(plugin._fieldParsePercentage).not.toHaveBeenCalled();
        });

        describe('for currency field types', function() {
            it('should return true if the currency value changed', function() {
                field.type = 'currency';
                result = plugin._fieldValueChanged(field);

                expect(result).toBeTruthy();
            });

            it('should return false if the currency value has not changed', function() {
                field.type = 'currency';
                field.value = testVal;
                result = plugin._fieldValueChanged(field);

                expect(result).toBeFalsy();
            });
        });

        describe('for date field types', function() {
            it('should return true if the date value changed', function() {
                field.type = 'date';
                result = plugin._fieldValueChanged(field);

                expect(result).toBeTruthy();
            });

            it('should return false if the date value has not changed', function() {
                field.type = 'date';
                field.value = testVal;
                result = plugin._fieldValueChanged(field);

                expect(result).toBeFalsy();
            });
        });

        describe('for non-currency and non-date field types', function() {
            it('should return true if the currency values have changed', function() {
                field.type = 'potato';
                result = plugin._fieldValueChanged(field);

                expect(result).toBeTruthy();
            });

            it('should return false if the currency values have not changed', function() {
                field.type = 'potato';
                field.value = testVal;
                result = plugin._fieldValueChanged(field);

                expect(result).toBeFalsy();
            });
        });
    });

    describe('_fieldDoValidate', function() {
        var field,
            fieldType = '',
            testVal = '100',
            fieldVal = '25',
            newVal,
            result;

        beforeEach(function() {
            field = {
                type: fieldType,
                data: function() {
                    return fieldType;
                },
                value: fieldVal,
                $: function() {
                    return {
                        val: function() {
                            return testVal;
                        },
                        html: function() {
                            return testVal;
                        }
                    }
                }
            };

            plugin.$ = function() {
                return {
                    val: function() {
                        return 55;
                    }
                }
            };
        });

        afterEach(function() {
            fieldType = '';
        });

        it('should return false when new value and fieldTag is undefined', function() {
            newVal = undefined;
            plugin.fieldTag = undefined;
            result = plugin._fieldDoValidate(field, newVal);

            expect(result).toBeFalsy();
        });
        it('should return false when new value is empty and fieldTag is undefined', function() {
            newVal = [];
            plugin.fieldTag = undefined;
            result = plugin._fieldDoValidate(field, newVal);

            expect(result).toBeFalsy();
        });

        it('should get the newValue if the newValue param passed in is undefined', function() {
            newVal = undefined;
            plugin.fieldTag = 'a';
            fieldType = 'int';
            sinon.stub(plugin, '_fieldParsePercentage').callsFake(function(val) { return val; });
            sinon.stub(plugin, '_fieldVerifyIntValue').callsFake(function(val) { return val; });
            result = plugin._fieldDoValidate(field, newVal);

            expect(result).toBe(55);
        });

        describe('for int field types', function() {
            beforeEach(function() {
                sinon.stub(plugin, '_fieldParsePercentage').callsFake(function(val) { return val; });
                fieldType = field.type = 'int';
            });

            it('should call _fieldParsePercentage and _fieldVerifyIntValue', function() {
                sinon.stub(plugin, '_fieldVerifyIntValue').callsFake(function(val) { return val; });
                plugin.fieldTag = 'a';
                plugin._fieldDoValidate(field, newVal);

                expect(plugin._fieldParsePercentage).toHaveBeenCalledWith(55, 0);
                expect(plugin._fieldVerifyIntValue).toHaveBeenCalled();
            });

            it('should return true if int is valid', function() {
                sinon.stub(plugin, '_fieldVerifyIntValue').callsFake(function() { return true; });
                plugin.fieldTag = 'a';
                result = plugin._fieldDoValidate(field, newVal);

                expect(result).toBeTruthy();
            });

            it('should return false if int is invalid', function() {
                sinon.stub(plugin, '_fieldVerifyIntValue').callsFake(function() { return false; });
                plugin.fieldTag = 'a';
                result = plugin._fieldDoValidate(field, newVal);

                expect(result).toBeFalsy();
            });
        });

        describe('for currency field types', function() {
            beforeEach(function() {
                sinon.stub(plugin, '_fieldParsePercentage').callsFake(function(val) { return val; });
                fieldType = field.type = 'currency';
            });

            it('should call _fieldParsePercentage and _fieldVerifyCurrencyValue', function() {
                sinon.stub(plugin, '_fieldVerifyCurrencyValue').callsFake(function(val) { return val; });
                plugin.fieldTag = 'a';
                plugin._fieldDoValidate(field, newVal);

                expect(plugin._fieldParsePercentage).toHaveBeenCalledWith(55);
                expect(plugin._fieldVerifyCurrencyValue).toHaveBeenCalled();
            });

            it('should return true if currency is valid', function() {
                sinon.stub(plugin, '_fieldVerifyCurrencyValue').callsFake(function() { return true; });
                plugin.fieldTag = 'a';
                result = plugin._fieldDoValidate(field, newVal);

                expect(result).toBeTruthy();
            });

            it('should return false if currency is invalid', function() {
                sinon.stub(plugin, '_fieldVerifyCurrencyValue').callsFake(function() { return false; });
                plugin.fieldTag = 'a';
                result = plugin._fieldDoValidate(field, newVal);

                expect(result).toBeFalsy();
            });
        });

        describe('for date field types', function() {
            var dateFormat = '',
                isDateValid = false;
            beforeEach(function() {
                fieldType = field.type = 'date';
                sinon.stub(app.user, 'getPreference').callsFake(function() {});
                sinon.stub(app, 'date').callsFake(function(date) {
                    return {
                        convertFormat: function() {
                            return dateFormat;
                        },
                        isValid: function() { return isDateValid; }
                    };
                });
                sinon.stub(app.date, 'convertFormat').callsFake(function() { return dateFormat; });
            });

            it('should return the date with proper date format and valid date', function() {
                dateFormat = 'DD/MM/YYYY';
                newVal = '01/14/1981';
                plugin.fieldTag = 'a';
                isDateValid = true;
                result = plugin._fieldDoValidate(field, newVal);

                expect(result).toBe(newVal);
            });

            it('should return false with proper date format and invalid date', function() {
                dateFormat = 'DD/MM/YYYY';
                newVal = '99/99/1981';
                plugin.fieldTag = 'a';
                isDateValid = false;
                result = plugin._fieldDoValidate(field, newVal);

                expect(result).toBeFalsy();
            });

            it('should return the date without dateFormat if date is valid', function() {
                newVal = '01/14/1981';
                plugin.fieldTag = 'a';
                isDateValid = true;
                result = plugin._fieldDoValidate(field, newVal);

                expect(result).toBe(newVal);
            });

            it('should return false without dateFormat if date is invalid', function() {
                dateFormat = 'DD/MM/YYYY';
                newVal = '99/99/1981';
                plugin.fieldTag = 'a';
                isDateValid = false;
                result = plugin._fieldDoValidate(field, newVal);

                expect(result).toBeFalsy();
            });
        });

        describe('for any other field types', function() {
            it('should return the value passed in', function() {
                fieldType = field.type = 'potato';
                result = plugin._fieldDoValidate(field, newVal);

                expect(result).toBe(newVal);
            });
        });
    });

    describe('_fieldVerifyCurrencyValue', function() {
        var result;
        beforeEach(function() {
            sinon.stub(app.metadata, 'getConfig').callsFake(function() {
                return {};
            });
            sinon.stub(app.user, 'getPreference')
                .withArgs('decimal_separator').returns('.')
                .withArgs('number_grouping_separator').returns(',')
                .withArgs('currency_id').returns('-99');

            sinon.stub(app.currency, 'getBaseCurrencyId').callsFake(function() {
                return '-99';
            });
            sinon.stub(app.currency, 'getCurrencySymbol').callsFake(function() {
                return '$';
            });
        });

        it('should be true with a valid currency value', function() {
            result = plugin._fieldVerifyCurrencyValue('1,234.56');

            expect(result).toBeTruthy();
        });

        it('should be false with invalid currency value', function() {
            result = plugin._fieldVerifyCurrencyValue('derp');

            expect(result).toBeFalsy();
        });
    });

    describe('_fieldVerifyIntValue', function() {
        var result;
        beforeEach(function() {
            plugin.def = {};
        });

        it('should be false if value is not an int', function() {
            result = plugin._fieldVerifyIntValue('derp');

            expect(result).toBeFalsy();
        });

        it('should be true with valid int value but no field def validation in metadata', function() {
            result = plugin._fieldVerifyIntValue(10);

            expect(result).toBeTruthy();
        });

        describe('with validation in metadata', function() {
            beforeEach(function() {
                plugin.def.validation = {};
                plugin.def.validation.type = 'range';
            });

            it('should be true with valid int value but no min/max range', function() {
                result = plugin._fieldVerifyIntValue(10);

                expect(result).toBeTruthy();
            });

            it('should be true with valid int value but only min range', function() {
                plugin.def.validation.min = '0';
                result = plugin._fieldVerifyIntValue(10);

                expect(result).toBeTruthy();
            });

            it('should be true with valid int value but only max range', function() {
                plugin.def.validation.max = '100';
                result = plugin._fieldVerifyIntValue(10);

                expect(result).toBeTruthy();
            });

            it('should be true with valid int value in the correct range', function() {
                plugin.def.validation.min = '0';
                plugin.def.validation.max = '100';
                result = plugin._fieldVerifyIntValue(10);

                expect(result).toBeTruthy();
            });

            it('should be false with valid int value but not in the correct range', function() {
                plugin.def.validation.min = '0';
                plugin.def.validation.max = '100';
                result = plugin._fieldVerifyIntValue(-10);

                expect(result).toBeFalsy();
            });
        });
    });

    describe('_fieldParsePercentage', function() {
        var result;
        beforeEach(function() {
            sinon.stub(app.metadata, 'getConfig').callsFake(function() {
                return {};
            });
            sinon.stub(app.user, 'getPreference')
                .withArgs('decimal_separator').returns('.')
                .withArgs('number_grouping_separator').returns(',');

            plugin.model = {
                get: function() {
                    return 100;
                }
            };
            plugin.unformat = function(val) {
                return val;
            };
            plugin.format = function(val) {
                return val;
            };
        });

        it('should return the value passed in if not a valid percent', function() {
            result = plugin._fieldParsePercentage('1,234.56');

            expect(result).toBe('1,234.56');
        });

        it('should return the value passed in if it does not have a + or -', function() {
            result = plugin._fieldParsePercentage('5%');

            expect(result).toBe('5%');
        });

        it('should return the percent modified value on +', function() {
            result = plugin._fieldParsePercentage('+5%');

            expect(result).toBe('105');
        });

        it('should return the percent modified value on -', function() {
            result = plugin._fieldParsePercentage('-5%');

            expect(result).toBe('95');
        });
    });
});
