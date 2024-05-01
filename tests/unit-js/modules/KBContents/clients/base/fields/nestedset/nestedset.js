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
describe('modules.KBContents.clients.base.field.nestedset', function() {
    var module = 'KBContents',
        fieldDef = {
            category_root: '76c5ad26-21db-1be5-85ee-54258f68dd4a',
            data_provider: 'Categories'
        },
        app, field, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'nestedset', module);
        SugarTest.loadFile(
            '../modules/Categories/clients/base/plugins',
            'NestedSetCollection',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });
        SugarTest.loadHandlebarsTemplate('nestedset', 'field', 'base', 'edit', module);
        SugarTest.testMetadata.set();

        app.data.declareModels();
        SugarTest.loadFile(
            '../modules/Categories/clients/base/plugins',
            'JSTree',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });

        field = SugarTest.createField('base', 'nestedset', 'nestedset', 'edit', fieldDef, module, null, null, true);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        field.dispose();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
        delete app.plugins.plugins['field']['JSTree'];
        delete app.plugins.plugins['field']['NestedSetCollection'];
        sinonSandbox.restore();
    });

    it('Should render tree and toggle icon on render only in edit mode.', function() {
        var treeRenderSpy = sinonSandbox.stub(field, '_renderTree');
        var toggleSearchIconSpy = sinonSandbox.spy(field, 'toggleSearchIcon');

        field.action = 'record';
        field.render();

        expect(treeRenderSpy).not.toHaveBeenCalled();
        expect(toggleSearchIconSpy).not.toHaveBeenCalled();

        field.action = 'edit';
        field.render();

        expect(treeRenderSpy).toHaveBeenCalled();
        expect(toggleSearchIconSpy).toHaveBeenCalled();
    });

    it('Should show dropdown.', function() {
        var aSel = '[data-action=create-new]',
            iSel = '[data-role=add-item]',
            dSel = '[data-role=treeinput]',
            expected = {
                records: SugarTest.loadFixture('tree', '../tests/modules/Categories/fixtures'),
                next_offset: -1
            },
            clearSelectionSpy = sinonSandbox.spy(field, 'clearSelection');
        expected = JSON.stringify(expected);
        field.action = 'edit';
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith('GET', new RegExp(".*rest\/v10\/Categories\/76c5ad26-21db-1be5-85ee-54258f68dd4a\/tree.*"),
            [200, {'Content-Type': 'application/json'}, expected]);
        field.render();
        field.$treeContainer.jstree = function () {return this;};
        SugarTest.server.respond();

        field.$(dSel).click();
        expect(field.$(field.ddEl).length).not.toBe(0);
        expect(field.$(field.ddEl).hasClass('show')).toBeTruthy();
        $('body').click();
        expect(field.$(field.ddEl).hasClass('show')).toBeFalsy();
        expect(clearSelectionSpy).toHaveBeenCalled();
    });

    it('Should show input for creation.', function() {
        var aSel = '[data-action=create-new]',
            iSel = '[data-role=add-item]',
            dSel = '[data-role=treeinput]',
            oSel = '[data-place=bottom-options]',
            cSel = '[data-place=bottom-create]',
            escKey = $.ui.keyCode.ESCAPE;
        sinonSandbox.stub(field, '_renderTree').callsFake(function() {});
        field.action = 'edit';
        field.render();
        field.$(dSel).click();
        field.$(aSel).click();
        expect(field.$(oSel).hasClass('hide')).toBeFalsy();
        expect(field.$(cSel).hasClass('hide')).toBeTruthy();
        field.switchCreate();
        expect(field.$(oSel).css('display')).not.toBe('none');
        expect(field.$(cSel).css('display')).not.toBe('block');
        field.$(iSel).trigger($.Event('keydown', {keyCode: escKey, which: escKey}));
        expect(field.$(oSel).css('display')).not.toBe('block');
        expect(field.$(cSel).css('display')).not.toBe('none');
    });

});
