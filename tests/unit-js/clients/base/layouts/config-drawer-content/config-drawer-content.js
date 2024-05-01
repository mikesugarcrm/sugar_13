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
describe('Base.Layout.ConfigDrawerContent', function() {
    var app;
    var context;
    var layout;
    var options;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        var meta = {
            components: [{
                view: 'config-panel'
            }, {
                view: 'config-panel'
            }],
        };

        options = {
            context: context,
            meta: meta
        };

        layout = SugarTest.createLayout('base', 'Quotes', 'config-drawer-content', meta, context, false);
        layout._components[1].name = 'config-panel2';
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.stub(layout, '_super');
            sinon.stub(layout, '_initHowTo');
        });

        it('should call _initHowTo', function() {
            layout.initialize(options);

            expect(layout._initHowTo).toHaveBeenCalled();
        });
    });

    describe('_render()', function() {
        var collapseStub;
        var spliceStub;
        var addClassStub;

        beforeEach(function() {
            collapseStub = sinon.stub();
            spliceStub = sinon.stub();
            addClassStub = sinon.stub();

            sinon.stub(layout.$el, 'addClass');
            sinon.stub(layout.$el, 'attr');
            sinon.stub(layout, '_super');
            sinon.stub(layout, 'selectPanel');
            sinon.stub(layout, '$').callsFake(function() {
                return {
                    collapse: collapseStub,
                    splice: spliceStub,
                    addClass: addClassStub
                };
            });
        });

        afterEach(function() {
            collapseStub = null;
            spliceStub = null;
            addClassStub = null;
        });

        it('should add CSS class accordion', function() {
            layout._render();

            expect(layout.$el.addClass).toHaveBeenCalledWith('accordion Quotes-config');
        });

        it('should set id on el', function() {
            layout.collapseDivId = 'test1';
            layout._render();

            expect(layout.$el.attr).toHaveBeenCalledWith('id', 'test1');
        });

        it('should call splice to remove the first toggle item', function() {
            layout._render();

            expect(spliceStub).toHaveBeenCalledWith(0, 1);
        });

        it('should add CSS class collapsed', function() {
            layout._render();

            expect(addClassStub).toHaveBeenCalledWith('collapsed');
        });
    });

    describe('selectPanel()', function() {
        var collapseStub;

        beforeEach(function() {
            collapseStub = sinon.stub();
            sinon.stub(layout, '$').callsFake(function() {
                return {
                    collapse: collapseStub
                };
            });
        });

        afterEach(function() {
            collapseStub = null;
        });

        it('should set selectedPanel to the passed in panelName', function() {
            layout.selectPanel('config-panel');

            expect(layout.selectedPanel).toBe('config-panel');
        });

        it('should call collapse on the panel name id', function() {
            layout.selectPanel('config-panel');

            expect(layout.$).toHaveBeenCalledWith('#config-panelCollapse');
            expect(collapseStub).toHaveBeenCalledWith('show');
        });
    });

    describe('changeHowToData()', function() {
        beforeEach(function() {
            sinon.stub(layout.context, 'trigger');
        });

        afterEach(function() {

        });

        it('should set currentHowToData title', function() {
            layout.changeHowToData('title1', 'text1');

            expect(layout.currentHowToData.title).toBe('title1');
        });

        it('should set currentHowToData text', function() {
            layout.changeHowToData('title1', 'text1');

            expect(layout.currentHowToData.text).toBe('text1');
        });

        it('should trigger config:howtoData:change on the context', function() {
            layout.changeHowToData('title1', 'text1');

            expect(layout.context.trigger).toHaveBeenCalledWith('config:howtoData:change', {
                title: 'title1',
                text: 'text1'
            });
        });
    });
});
