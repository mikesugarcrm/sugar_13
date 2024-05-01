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

describe('View.Views.Base.SortingDropdownView', function() {
    let app;
    let view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('sorting-dropdown', 'view', 'base');
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', '', 'sorting-dropdown');
        view._render();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        view = null;
        Handlebars.templates = {};
        sinon.restore();
    });

    describe('setDropdownFields', function() {
        it('should check dropdown fields setter', function() {
            let fields = [
                {
                    name: 'name_1',
                    label: 'label_1'
                },
                {
                    name: 'name_2',
                    label: 'label_2'
                }
            ];

            view.setDropdownFields(fields);

            expect(view.dropdownFields).toEqual(fields);
        });
    });

    describe('setState', function() {
        it('should check calling _setCurrentField and _setCurrentDirection methods', function() {
            sinon.stub(view, '_setCurrentField');
            sinon.stub(view, '_setCurrentDirection');

            view.setState('field', 'direction');

            expect(view._setCurrentField).toHaveBeenCalled();
            expect(view._setCurrentDirection).toHaveBeenCalled();
        });
    });

    describe('changeDropdownValue', function() {
        it('should check calling _setCurrentField and _setArrowState and trigger', function() {
            let sinonSandbox = sinon.createSandbox();
            let triggerSpy = sinonSandbox.spy(view.context, 'trigger');
            let event = {val: 'test'};
            sinon.stub(view, '_setCurrentField');
            sinon.stub(view, '_setArrowState');

            view.changeDropdownValue(event);

            expect(view._setCurrentField).toHaveBeenCalledWith(event.val);
            expect(view._setArrowState).toHaveBeenCalled();
            expect(triggerSpy).toHaveBeenCalledWith('app:view:sorting-dropdown:changeDropdownValue');

        });
    });

    describe('clickArrow', function() {
        it('should check calling _toggleCurrentDirection and _setArrowState methods and trigger',function() {
            let sinonSandbox = sinon.createSandbox();
            let triggerSpy = sinonSandbox.spy(view.context, 'trigger');
            let event = {val: 'test'};
            sinon.stub(view, '_toggleCurrentDirection');
            sinon.stub(view, '_setArrowState');

            view.currentField = 'test';

            view.clickArrow();

            expect(view._toggleCurrentDirection).toHaveBeenCalled();
            expect(view._setArrowState).toHaveBeenCalled();
            expect(triggerSpy).toHaveBeenCalledWith('app:view:sorting-dropdown:clickArrow');
        });
    });

    describe('_render', function() {
        it('should render the view', function() {
            var renderStub = sinon.stub(view, '_render');
            view._render();
            expect(renderStub).toHaveBeenCalledOnce();
            renderStub.restore();
        });
    });

    describe('_setCurrentField', function() {
        it('should check currentField setter', function() {
            let value = 'test';
            view._setCurrentField(value);

            expect(view.currentField).toContain(value);
        });
    });

    describe('_setCurrentDirection', function() {
        it('should check currentDirection setter', function() {
            let value = 'test';
            view._setCurrentDirection(value);

            expect(view.currentDirection).toContain(value);
        });
    });

    describe('_isCurrentFieldEmpty', function() {
        using('different data', [
            {
                value: '',
                expected: true
            },
            {
                value: 'test',
                expected: false
            },
        ], function(values) {
            it('should check currentField is empty', function() {
                view.currentField = values.value;
                expect(view._isCurrentFieldEmpty()).toBe(values.expected);
            });
        });
    });

    describe('_enableArrow', function() {
        it('should check arrow enabling', function() {
            view._enableArrow();

            expect(view.$('.sorting-dropdown-arrow').hasClass('disabled')).toBeFalsy();
        });
    });

    describe('_disableArrow', function() {
        it('should check arrow disabling', function() {
            view._disableArrow();

            expect(view.$('.sorting-dropdown-arrow').hasClass('disabled')).toBeTruthy();
        });
    });

    describe('_toggleCurrentDirection', function() {
        using('different data', [
            {
                direction: 'asc',
                expected: 'desc'
            },
            {
                direction: 'desc',
                expected: 'asc'
            },
        ], function(values) {
            it('should check _toggleDirection for toggling value of currentDirection', function() {
                view.currentDirection = values.direction;
                view._toggleCurrentDirection();
                expect(view.currentDirection).toContain(values.expected);
            });
        });
    });

    describe('_setArrowDesc', function() {
        it('should check arrow desc setter', function() {
            let sortingDropdownArrow = view.$('.sorting-dropdown-arrow > i');

            view._setArrowDesc();

            expect(sortingDropdownArrow.hasClass('sicon-arrow-down')).toBeTruthy();
            expect(sortingDropdownArrow.hasClass('sicon-arrow-up')).toBeFalsy();
        });
    });

    describe('_setArrowAsc', function() {
        it('should check arrow asc setter', function() {
            let sortingDropdownArrow = view.$('.sorting-dropdown-arrow > i');

            view._setArrowAsc();

            expect(sortingDropdownArrow.hasClass('sicon-arrow-up')).toBeTruthy();
            expect(sortingDropdownArrow.hasClass('sicon-arrow-down')).toBeFalsy();
        });
    });

    describe('_setArrowState', function() {
        it('should check arrow state setter for disabling', function() {
            sinon.stub(view, '_disableArrow');
            sinon.stub(view, '_setArrowDesc');

            view.currentField = '';
            view._setArrowState();

            expect(view.currentDirection).toContain(view.defaultDirection);
            expect(view._disableArrow).toHaveBeenCalled();
            expect(view._setArrowDesc).toHaveBeenCalled();
        });

        it('should check arrow state setter for enabling and asc', function() {
            sinon.stub(view, '_enableArrow');
            sinon.stub(view, '_setArrowAsc');

            view.currentField = 'test';
            view.currentDirection = 'asc';

            view._setArrowState();

            expect(view._enableArrow).toHaveBeenCalled();
            expect(view._setArrowAsc).toHaveBeenCalled();
        });

        it('should check arrow state setter for enabling and desc', function() {
            sinon.stub(view, '_enableArrow');
            sinon.stub(view, '_setArrowDesc');

            view.currentField = 'test';
            view.currentDirection = 'desc';

            view._setArrowState();

            expect(view._enableArrow).toHaveBeenCalled();
            expect(view._setArrowDesc).toHaveBeenCalled();
        });
    });
});
