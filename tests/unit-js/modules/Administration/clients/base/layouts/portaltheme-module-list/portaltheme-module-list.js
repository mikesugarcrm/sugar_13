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
describe('Base.Layout.PortalThemeModuleList', function() {
    var app;
    var layout;
    var layoutName = 'portaltheme-module-list';
    var parentName = 'module-list';
    var module = 'Administration';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', parentName);
        SugarTest.loadComponent('base', 'layout', layoutName, module);
        SugarTest.loadHandlebarsTemplate(parentName, 'layout', 'base');
        SugarTest.loadHandlebarsTemplate(parentName, 'layout', 'base', 'list');
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout('base', module, layoutName, {}, null, true);
    });

    afterEach(function(){
        layout.dispose();
        SugarTest.testMetadata.dispose();
        layout = null;
        sinon.restore();
    });

    describe('_addDefaultMenus', function() {
        it('should call api utils with expected args', function() {
            sinon.stub(app.api, 'buildURL').returns('test_url');
            sinon.stub(_, 'bind');
            sinon.stub(app.api, 'call');
            layout._addDefaultMenus();
            expect(app.api.buildURL).toHaveBeenCalledWith('Administration/portalmodules', 'read');
            expect(_.bind).toHaveBeenCalledWith(layout._addMenus, layout);
            expect(app.api.call).toHaveBeenCalledWith('read', 'test_url', null);
        });
    });

    describe('_addMenus', function() {
        using('different module lists', [
            {modules: ['Home', 'Cases', 'Bugs'], expected: 3},
            {modules: [], expected: 0},
            {modules: ['Home', 'Cases', 'Bugs', 'KBContents'], expected: 4},
        ], function(values) {
            it('should call _adMenu once per module passed in, and render once at the end', function() {
                sinon.stub(layout, '_addMenu');
                sinon.stub(layout, 'render');
                layout._addMenus(values.modules);
                expect(layout._addMenu.callCount).toBe(values.expected);
                _.each(values.modules, function(module) {
                    expect(layout._addMenu).toHaveBeenCalledWith(module, true);
                });
                expect(layout.render).toHaveBeenCalledOnce();
            });
        });
    });
});
