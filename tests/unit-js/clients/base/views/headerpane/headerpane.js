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
describe('Headerpane View', function() {
    var app;
    var viewName = 'headerpane';
    var testModule = 'Home';
    var testLayout = 'record';
    var sinonSandbox;
    var view;
    var layout;

    beforeEach(function() {
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'headerpane');
        SugarTest.loadComponent('base', 'layout', 'base');
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'view', viewName);

        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();

        var meta = {
            'buttons': [
                {
                    'name': 'button1',
                    'type': 'myButton'
                },
                {
                    'name': 'button2',
                    'type': 'myButton'
                }
            ]
        };
        var context = app.context.getContext({
            module: testModule,
            layout: testLayout
        });
        context.prepare();

        layout = app.view.createLayout({
            name: testLayout,
            context: context
        });
        view = app.view.createView({
            name: viewName,
            context: context,
            meta: meta,
            layout: layout
        });
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.app.view.reset();
        app.data.reset();
        layout.dispose();
        view.dispose();
        view = null;
        layout = null;
    });

    describe('setButtonStates', function() {
        var toggleButtonsMock;

        beforeEach(function() {
            view.buttons = [
                {
                    'id': 1,
                    'def': {
                        'showOn': 'view'
                    },
                    'show': sinonSandbox.spy(),
                    'hide': sinonSandbox.spy()
                },
                {
                    'id': 2,
                    'def': {
                        'showOn': 'edit'
                    },
                    'show': sinonSandbox.spy(),
                    'hide': sinonSandbox.spy()
                },
                {
                    'id': 3,
                    'def': {},
                    'show': sinonSandbox.spy(),
                    'hide': sinonSandbox.spy()
                }
            ];

            toggleButtonsMock = sinonSandbox.mock(view, '_toggleButtons');
            toggleButtonsMock.expects('_toggleButtons').once().withArgs(true);
        });

        using('different states', ['view', 'edit'], function(state) {
            it('should show buttons whose `showOn` property matches `state` or is `undefined`', function() {
                view.setButtonStates(state);

                _.each(view.buttons, function(button) {
                    if (button.def.showOn === state || _.isUndefined(button.def.showOn)) {
                        expect(button.show).toHaveBeenCalled();
                    }
                });
                toggleButtonsMock.verify();
            });

            it('should hide buttons whose `showOn` property is opposite to `state`', function() {
                view.setButtonStates(state);

                _.each(view.buttons, function(button) {
                    if (button.def.showOn && button.def.showOn !== state) {
                        expect(button.hide).toHaveBeenCalled();
                    }
                });
                toggleButtonsMock.verify();
            });
        });
    });

    describe('setEditableFields', function() {
        it('should call getEditableFields from `editable` plugin', function() {
            sinonSandbox.stub(view, 'getEditableFields');
            view.setEditableFields();
            expect(view.getEditableFields).toHaveBeenCalled();
        });
    });
});
