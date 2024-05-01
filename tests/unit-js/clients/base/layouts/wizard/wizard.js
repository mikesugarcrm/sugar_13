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
describe("Wizard layout", function() {
    var layout, app, sinonSandbox;

    beforeEach(function() {
        sinonSandbox = sinon.createSandbox();
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'wizard');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Users', 'wizard');
    });

    afterEach(function() {
        sinonSandbox.restore();
        layout.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe("finished()", function(){

        it("should dispose the layout", function(){
            let disposeStub = sinonSandbox.stub(layout, 'dispose').callsFake($.noop());
            layout.finished();
            expect(disposeStub.calledOnce).toBe(true);
        });

        it("should trigger the complete callback if one is registered on context", function(){
            var disposeSpy = sinonSandbox.spy(layout, "dispose");
            var callbacks = {
                complete: function(){}
            };
            var callbackSpy = sinonSandbox.spy(callbacks, "complete");
            layout.context.set("callbacks", callbacks);
            layout.finished();
            expect(disposeSpy.calledOnce).toBe(true, "Should still call dispose.");
            expect(callbackSpy.calledOnce).toBe(true);
        });
    });

    describe("addComponent()", function(){
        it("should only add wizard-page components where showPage() is true", function(){
            var show = true;
            var component = {showPage: function(){ return show; }};
            var parentStub = sinonSandbox.stub(app.view.Layout.prototype, 'addComponent').callsFake($.noop());
            var addButtonsForComponentStub = sinonSandbox.stub(layout, '_addButtonsForComponent').callsFake(
                function(c) {
                    return c;
                }
            );
            layout.addComponent(component);
            expect(parentStub.calledOnce).toBe(true);
            parentStub.resetHistory();

            show = false;
            layout.addComponent(component);
            expect(parentStub.called).toBe(false);
        });
    });
});
