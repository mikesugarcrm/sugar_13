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
describe('FocusDrawer plugin', function() {
    var addFocusDrawerIconStub;
    var app;
    var field;
    var focusIconContainer;
    var module;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadPlugin('FocusDrawer');
        field = SugarTest.createField('base', 'relate', 'relate');
        field.fieldTag = 'relate';
        field.plugins.push('FocusDrawer');
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
        field = null;
        module = null;
        addFocusDrawerIconStub = null;
    });

    describe('onAttach', function() {
        using('different focus drawer availabilities', [true, false], function(focusIsAvailable) {
            it('should correctly determine if it should initialize focus icon and link', function() {
                app.plugins.attach(field, 'field');
                sinon.stub(field, 'checkFocusAvailability').callsFake(function() {
                    return focusIsAvailable;
                });
                sinon.stub(field, 'initFocusIcon');
                sinon.stub(field, 'handleRecordTitleDrag');
                sinon.stub(field, 'initFocusLink');

                field.trigger('render');

                if (focusIsAvailable) {
                    expect(field.initFocusIcon).toHaveBeenCalled();
                } else {
                    expect(field.initFocusIcon).not.toHaveBeenCalled();
                }
                expect(field.initFocusLink).toHaveBeenCalled();
                expect(field.handleRecordTitleDrag).toHaveBeenCalled();
            });
        });
    });

    describe('checkFocusAvailability', function() {
        var userLicenses;
        var moduleIsBwc;

        beforeEach(function() {
            // Default to conditions valid for focus drawer
            moduleIsBwc = false;
            app.config.enableLinkToDrawer = true;

            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    isBwcEnabled: moduleIsBwc
                };
            });

            app.sideDrawer = {
                isOpen: () => false
            };
        });

        afterEach(() => {
            app.sideDrawer = null;
        });

        it('should return true if all focus drawer conditions are met', function() {
            expect(field.checkFocusAvailability()).toBe(true);
        });

        it('should return false if there is no focus drawer container', function() {
            app.sideDrawer = null;
            expect(field.checkFocusAvailability()).toBe(false);
        });
    });

    describe('handleFocusClick', function() {
        var mockFocusDrawer;

        beforeEach(function() {
            mockFocusDrawer = {
                isOpen: function() {},
                open: function() {}
            };
            sinon.stub(mockFocusDrawer, 'isOpen').callsFake(function() {
                return false;
            });
            sinon.stub(mockFocusDrawer, 'open');

            sinon.spy(field, 'openFocusDrawer');

            app.sideDrawer = mockFocusDrawer;

            sinon.stub(field, 'getFocusContextModule').callsFake(function() {
                return 'Accounts';
            });
            sinon.stub(field, 'getFocusContextModelId').callsFake(function() {
                return '12345';
            });
            sinon.stub(field, 'getDataTitle').callsFake(function() {
                return {
                    module: 'Account',
                    view: 'Record',
                    name: 'recordName',
                    labelColor: 'label-Accounts'
                };
            });
        });

        afterEach(() => {
            app.sideDrawer = null;
        });

        it('should open the focus drawer with the proper context when clicked', function() {
            field.focusIconEnabled = true;
            field.value = 'recordName';
            field.handleFocusClick('dashboard', 'icon');
            expect(field.openFocusDrawer).toHaveBeenCalled();
            expect(mockFocusDrawer.open).toHaveBeenCalledWith({
                layout: 'row-model-data',
                dashboardName: 'recordName',
                context: {
                    layout: 'focus',
                    contentType: 'dashboard',
                    module: 'Accounts',
                    modelId: '12345',
                    dataTitle: {
                        module: 'Account',
                        view: 'Record',
                        name: 'recordName',
                        labelColor: 'label-Accounts'
                    },
                    parentContext: field.context,
                    fieldDefs: field.fieldDefs,
                    baseModelId: field.model.get('id'),
                    evtSource: 'icon',
                    disableRecordSwitching: false
                }
            });
        });
    });

    describe('handleRecordTitleDrag', function() {
        it('should do nothing if it is not in dashlet header', function() {
            let closestStub = sinon.stub(field.$el, 'closest')
                .withArgs('.dashlet-header').returns(false);
            field.handleRecordTitleDrag();
            expect(closestStub).not.toHaveBeenCalledWith('.grid-stack-item.ui-draggable');
        });

        it('should handle drag if it is in a dashlet header', function() {
            let draggable = {
                on: sinon.stub(),
                off: $.noop
            };
            sinon.stub(field.$el, 'closest')
                .withArgs('.dashlet-header').returns(true)
                .withArgs('.grid-stack-item.ui-draggable').returns(draggable);
            field.handleRecordTitleDrag();
            expect(draggable.on.firstCall.args[0]).toBe('mousedown.link');
            expect(draggable.on.secondCall.args[0]).toBe('dragstart.link');
        });
    });

    describe('_checkLayoutFocusDrawerAccess', function() {
        var layout;
        var parentLayout;

        beforeEach(function() {
            parentLayout = app.view.createLayout({
                name: 'test-layout-2'
            });
            layout = app.view.createLayout({
                name: 'test-layout-1',
                layout: parentLayout
            });
            field.view.layout = layout;
        });

        afterEach(function() {
            layout.dispose();
            parentLayout.dispose();
        });

        it('should return true if no ancestor layouts disable Focus Drawer icons', function() {
            expect(field._checkLayoutFocusDrawerAccess()).toEqual(true);
        });

        it('should return false if the field layout disables Focus Drawer icons', function() {
            field.view.layout.disableFocusDrawer = true;
            expect(field._checkLayoutFocusDrawerAccess()).toEqual(false);
        });

        it('should return false if the field\'s parent layout disables Focus Drawer icons', function() {
            field.view.layout.layout.disableFocusDrawer = true;
            expect(field._checkLayoutFocusDrawerAccess()).toEqual(false);
        });
    });
});
