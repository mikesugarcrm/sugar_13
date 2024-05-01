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
describe('Base.View.ConfigPanel', function() {
    var app;
    var context;
    var options;
    var view;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        sinon.stub(app.controller.context, 'get').callsFake(function() {
            return 'Opportunities'
        });

        var meta = {
            label: 'testLabel'
        };

        options = {
            context: context,
            meta: meta
        };

        view = SugarTest.createView('base', null, 'config-panel', meta, context);
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('initialize()', function() {
        it('should set `this.titleViewNameTitle`', function() {
            view.initialize(options);
            expect(view.titleViewNameTitle).toBe('testLabel');
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.stub(view, 'on');

            view.bindDataChange();
        });

        it('should set listener for config:panel:hide', function() {
            expect(view.on).toHaveBeenCalledWith('config:panel:hide');
        });

        it('should set listener for config:panel:show', function() {
            expect(view.on).toHaveBeenCalledWith('config:panel:show');
        });
    });

    describe('_render()', function() {
        beforeEach(function() {
            sinon.stub(view, 'updateTitle');
        });

        it('should add the "accordion-group" class to this.$el', function() {
            view._render();

            expect(view.$el.hasClass('accordion-group')).toBeTruthy();
        });

        it('should add the view name + "-group" class to this.$el', function() {
            view.name = 'config-panel';
            view._render();

            expect(view.$el.hasClass('config-panel-group')).toBeTruthy();
        });

        it('should call updateTitle()', function() {
            view._render();
            expect(view.updateTitle).toHaveBeenCalled();
        });
    });

    describe('updateTitle()', function() {
        beforeEach(function() {
            sinon.stub(view, '_updateTitleValues').callsFake(function() {});
            sinon.stub(view, '_updateTitleTemplateVars').callsFake(function() {});
            sinon.stub(view, '$').callsFake(function() {
                return {
                    html: function() {}
                }
            });
            view.toggleTitleTpl = sinon.stub();
        });

        it('should call _updateTitleValues()', function() {
            view.updateTitle();
            expect(view._updateTitleValues).toHaveBeenCalled();
        });

        it('should call _updateTitleTemplateVars()', function() {
            view.updateTitle();
            expect(view._updateTitleTemplateVars).toHaveBeenCalled();
        });

        it('should set the view $el', function() {
            view.updateTitle();
            expect(view.$).toHaveBeenCalled();
        });
    });

    describe('_updateTitleValues()', function() {
        it('should set `this.titleSelectedValues`', function() {
            view.model.set('config-panel', 'testValue');
            view._updateTitleValues();
            expect(view.titleSelectedValues).toBe('testValue');
        });
    });

    describe('_updateTitleTemplateVars()', function() {
        it('should set `this.titleTemplateVars`', function() {
            view.model.set('config-panel', 'testValue');
            view._updateTitleValues();
            view._updateTitleTemplateVars();
            expect(view.titleTemplateVars).toEqual({
                title: 'testLabel',
                selectedValues: 'testValue',
                viewName: 'config-panel'
            });
        });
    });
});
