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

describe('Base.Layout.Dashlet', function() {
    let parentLayout;
    let layout;

    beforeEach(function() {
        parentLayout = SugarTest.createLayout('base', 'Home', 'dashboard-grid');
        layout = SugarTest.createLayout('base', 'Home', 'dashlet', {empty: true},
            null, null, {layout: parentLayout});
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        layout = null;
        parentLayout.dispose();
        parentLayout = null;
        app.cache.cutAll();
        app.view.reset();
    });

    describe('getComponentsFromMetadata', function() {
        it('should return component from current tab', function() {
            var currentTab = 0;
            var tab0 = {name: 'tab0', components: [{rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}]};
            var tab1 = {name: 'tab1', components: [{view: 'multi-line-list'}]};
            var metadata = {tabs: [tab0, tab1]};
            layout.context = {
                get: sinon.stub().returns(currentTab),
                off: $.noop
            };
            expect(layout.getComponentsFromMetadata(metadata)).toEqual(metadata.tabs [currentTab].components);
        });
    });

    describe('_setDashletContens', function() {
        using('different combinations of empty metadata and contents', [
            {emptyMeta: true, hasContent: false},
            {emptyMeta: false, hasContent: false},
            {emptyMeta: true, hasContent: true},
            {emptyMeta: false, hasContent: true},
        ], function(values) {
            it('should set content with appropriate method based on content', function() {
                layout.meta.empty = values.emptyMeta;
                var emptyContent = 'emptyMeta';
                var nonEmptyContent = 'nonEmptyMeta';
                var expectedContent = values.emptyMeta ? emptyContent : nonEmptyContent;
                layout.template = sinon.stub().returns(nonEmptyContent);
                sinon.stub(app.template, 'empty').callsFake(function() {
                    return emptyContent;
                });
                var replaceStub = sinon.stub();
                sinon.stub(layout.$el, 'children').callsFake(function() {
                    return {
                        first: function() {
                            return values.hasContent ? {replaceWith: replaceStub} : {};
                        }
                    };
                });
                sinon.stub(layout.$el, 'html');
                layout._setDashletContents();
                if (values.hasContent) {
                    expect(replaceStub).toHaveBeenCalledWith(expectedContent);
                } else {
                    expect(layout.$el.html).toHaveBeenCalledWith(expectedContent);
                }
            });
        });
    });

    describe('collapse', function() {
        beforeEach(function() {
            sinon.stub(layout.layout, 'toggleCollapseDashlet');
        });

        using('different collapsed values', [true, false], function(collapsed) {
            it('should set the isCollapsed flag on the dashlet', function() {
                layout.collapse(collapsed);
                expect(layout.isCollapsed).toEqual(collapsed);
            });

            it('should notify the layout that the dashlet was collapsed', function() {
                layout.collapse(collapsed);
                expect(layout.layout.toggleCollapseDashlet).toHaveBeenCalledWith(layout, collapsed);
            });
        });
    });
});
