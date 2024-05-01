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
describe("Base.Field.Button", function() {
    var app, field, Address;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'button');
        app.routing.start();
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
        Address = null;
        app.router.stop();
    });

    it("should setDisabled with CSS 'disabled'", function() {
        var def = {
            'events' : {
                'click .btn' : 'function() { this.callback = "stuff excuted"; }',
                'blur .btn' : 'function() { this.callback = "blur excuted"; }'
            }
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        field._loadTemplate = function() {  this.template = function(){ return '<a class="btn" href="javascript:void(0);"></a>'}; };

        expect(field.getFieldElement().hasClass("disabled")).toBeFalsy();
        field.render();
        field.setDisabled(true);
        expect(field.getFieldElement().hasClass("disabled")).toBeTruthy();
        field.setDisabled(false);
        expect(field.getFieldElement().hasClass("disabled")).toBeFalsy();
        field.setDisabled();
        expect(field.getFieldElement().hasClass("disabled")).toBeTruthy();
    });

    it('css_class should contain disable after calling setDisabled(true) and not after setDisabled(false)', function() {
        var def = {
            css_class: 'btn'
        };
        field = SugarTest.createField('base', 'button', 'button', 'edit', def);

        // make sure it doesn't start with it.
        expect(field.def.css_class).not.toContain('disabled');
        field.setDisabled(true);
        // make sure it's added
        expect(field.def.css_class).toContain('disabled');
        field.setDisabled(false);
        // make sure it's removed
        expect(field.def.css_class).not.toContain('disabled');
    });

    it("should show and hide functions must trigger hide and show events, and it should change the isHidden property", function() {

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'edit');
        SugarTest.testMetadata.set();

        var def = {
            'events' : {
                'click .btn' : 'function() { this.callback = "stuff excuted"; }',
                'blur .btn' : 'function() { this.callback = "blur excuted"; }'
            }
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        field.render();

        // we need to hide first, since the render() does the show
        var triggers2 = sinon.spy(field, 'trigger');
        field.hide();
        expect(triggers2.calledOnce).toBe(true);
        expect(triggers2.calledWithExactly('hide')).toBe(true);
        expect(field.isHidden).toBe(true);
        expect(field.isVisible()).toBe(false);
        triggers2.restore();

        // now try and show it
        var triggers = sinon.spy(field, 'trigger');
        field.show();
        expect(triggers.calledOnce).toBe(true);
        expect(triggers.calledWithExactly('show')).toBe(true);
        expect(field.isHidden).toBe(false);
        expect(field.isVisible()).toBe(true);
        triggers.restore();

        SugarTest.testMetadata.dispose();

    });

    it('should re-render the field if the field has access after the model is loaded', function() {
        field = SugarTest.createField('base', 'button', 'button', 'edit');

        sinon.stub(field, 'hasAccess').returns(false);
        sinon.stub(field, '_render');
        field.render();
        expect(field._render).not.toHaveBeenCalled();

        field.hasAccess.returns(true);
        field.model.trigger('sync');
        expect(field._render).toHaveBeenCalled();
    });

    it('should not show buttons for BWC modules if allow_bwc is false', function(){
        sinon.stub(app.metadata, 'getModule').returns({
            isBwcEnabled: true
        });
        var def = {
            'acl_action' : 'edit',
            'allow_bwc' : false
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var stubHasAccess = sinon.stub(app.acl, "hasAccess").returns(true);
        var stubHasAccessToModel = sinon.stub(app.acl, "hasAccessToModel").returns(true);

        var access = field.triggerBefore('render');
        expect(access).toBeFalsy();

        stubHasAccess.restore();
        stubHasAccessToModel.restore();
    });

    it('should show buttons for BWC modules if allow_bwc is true', function(){
        var bwcStub = sinon.stub(app.metadata, 'getModule').returns({
            isBwcEnabled: true
        });
        var def = {
            'acl_action' : 'edit',
            'allow_bwc' : true
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var stubHasAccess = sinon.stub(app.acl, "hasAccess").returns(true);
        var stubHasAccessToModel = sinon.stub(app.acl, "hasAccessToModel").returns(true);

        var access = field.triggerBefore('render');
        expect(access).toBeTruthy();

        stubHasAccess.restore();
        stubHasAccessToModel.restore();
        bwcStub.restore();
    });

    it('should call app.acl.hasAccessToModel if acl_module is not specified', function() {
        var def = {
            'acl_action' : 'edit'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var stubHasAccess = sinon.stub(app.acl, "hasAccess").returns(true);
        var stubHasAccessToModel = sinon.stub(app.acl, "hasAccessToModel").returns(false);

        var access = field.triggerBefore('render');
        expect(stubHasAccess).not.toHaveBeenCalled();
        expect(stubHasAccessToModel).toHaveBeenCalled();
        expect(access).toBeFalsy();

        stubHasAccess.restore();
        stubHasAccessToModel.restore();
    });

    it('should call app.acl.hasAccess if acl_module is specified', function() {
        var def = {
            'acl_module' : 'Contacts',
            'acl_action' : 'edit'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var stubHasAccess = sinon.stub(app.acl, "hasAccess").returns(true);
        var stubHasAccessToModel = sinon.stub(app.acl, "hasAccessToModel").returns(false);

        var access = field.triggerBefore('render');
        expect(stubHasAccess).toHaveBeenCalled();
        expect(stubHasAccessToModel).not.toHaveBeenCalled();
        expect(access).toBeTruthy();

        stubHasAccess.restore();
        stubHasAccessToModel.restore();

    });

    it('should update isHidden if show is called and hasAccess returns false', function() {
        var def = {
            'acl_module' : 'Contacts',
            'acl_action' : 'edit'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        sinon.stub(field,'hasAccess').returns(false);

        field.show();

        expect(field.isHidden).toBeTruthy();
        expect(field.isVisible()).toBeFalsy();
    });

    it('should update visibility once it triggers rendering', function() {
        var def = {
            'acl_module' : 'Contacts',
            'acl_action' : 'edit'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        sinon.stub(field,'hasAccess').returns(true);
        field.render();
        expect(field.isVisible()).toBe(true);

        field.hasAccess.returns(false);
        var renderStub = sinon.stub(field, '_render');
        field.render();
        expect(field.isVisible()).toBe(false);
        expect(renderStub).not.toHaveBeenCalled();
        renderStub.restore();
    });

    it("should differentiate string routes from sidecar route object", function() {
        var def = {
            'route' : {
                'action' : 'edit'
            }
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        field.render();
        expect(field.fullRoute).toBeNull();

        def = {
            'route' : 'custom/route'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        field.render();
        expect(field.fullRoute).toEqual('custom/route');
    });

    it('should create accessibility label from icon class name', function() {
        const icons = [
            {
                'icon': 'settings',
                'expectedLabel': 'settings'
            }, {
                'icon': 'sicon-settings',
                'expectedLabel': 'settings'
            }, {
                'icon': 'sicon-warning-circle',
                'expectedLabel': 'warning circle'
            },
        ];

        _.each(icons, function(data) {
            field = SugarTest.createField('base', null, 'button', 'edit', data);
            field.render();

            expect(field.ariaLabel).toEqual(data.expectedLabel);
        });
    });

    it("should test hasAccess control before it is rendered", function() {
        field = SugarTest.createField("base","button", "button", "edit");
        var hasAccessStub = sinon.stub(field, 'hasAccess');
        field.triggerBefore("render");
        expect(hasAccessStub).toHaveBeenCalled();
        hasAccessStub.restore();
    });

    it("should update visibility simultaneously once it triggers show and hide", function() {
        field = SugarTest.createField("base","button", "button", "edit");
        field.on("hide", function() {
            expect(this.isVisible()).toBe(false);
        }, field);
        field.on("show", function() {
            expect(this.isVisible()).toBe(true);
        }, field);
        field.show();
        field.hide();
        field.off();
    });

    it('should prevent click when disabled', function() {
        var called = false,
            def = {
                events: {
                    // In the events hash, Backbone is always checking for
                    // _.isFunction, and since sinon stubs are objects, we can't
                    // use one here, so we just use a flag instead.
                    'click .btn': function() {
                        called = true;
                    }
                }
            };

        field = SugarTest.createField('base', 'button', 'button', 'edit', def);
        loadTemplateStub = sinon.stub(field, '_loadTemplate').callsFake(function() {
            this.template = function() {
                return '<a class="btn" href="javascript:void(0);"></a>'
            };
        });

        field.render();

        field.setDisabled(true);
        field.$('.btn').click();
        expect(called).toBe(false);

        field.setDisabled(false);
        field.$('.btn').click();
        expect(called).toBe(true);
    });

    describe('_isOnLayout', function() {
        var closestComponentStub;

        beforeEach(function() {
            field = SugarTest.createField('base', 'button', 'button', 'edit', {});
            closestComponentStub = sinon.stub(field, 'closestComponent');
            closestComponentStub.withArgs('dashboard').returns({
                model: {
                    get: function() {return {'type': 'good-type'};}
                }
            });
        });

        afterEach(function() {
            closestComponentStub.restore();
        });

        it('should be truthy if it is on a layout', function() {
            var layout = {'name': 'dashboard', 'type': 'good-type'};
            expect(field._isOnLayout(layout)).toBeTruthy();
        });

        it('should be falsy if it is not on a layout', function() {
            var layout = {'name': 'dashboard', 'type': 'bad-type'};
            expect(field._isOnLayout(layout)).toBeFalsy();
        });
    });

    describe('isOnForbiddenLayout', function() {
        beforeEach(function() {
            field = SugarTest.createField('base', 'button', 'button', 'edit', {});
        });

        it('should be falsy if there are no forbidden layouts defined', function() {
            field.def = null;
            expect(field.isOnForbiddenLayout()).toBeFalsy();
            field.def = {};
            expect(field.isOnForbiddenLayout()).toBeFalsy();
        });

        it('should be truthy if it is on a forbidden layout', function() {
            field.def.disallowed_layouts = [{'name': 'bad-layout'}];
            var closestComponentStub = sinon.stub(field, 'closestComponent');
            closestComponentStub.withArgs('bad-layout').returns({fake: 'component'});
            expect(field.isOnForbiddenLayout()).toBeTruthy();
            closestComponentStub.restore();
        });

        it('should be truthy if it is on a forbidden dashboard', function() {
            field.def.disallowed_layouts = [{'name': 'dashboard', 'id': 'bad-id'}];
            var closestComponentStub = sinon.stub(field, 'closestComponent');
            closestComponentStub.withArgs('dashboard').returns({
                model: {
                    get: function() {return 'bad-id';}
                }
            });
            expect(field.isOnForbiddenLayout()).toBeTruthy();
            closestComponentStub.restore();
        });

        it('should be falsy if it is not on a forbidden dashboard', function() {
            field.def.disallowed_layouts = [{'name': 'dashboard', 'id': 'bad-id'}];
            var closestComponentStub = sinon.stub(field, 'closestComponent');
            closestComponentStub.withArgs('dashboard').returns({
                model: {
                    get: function() {return 'good-id';}
                }
            });
            expect(field.isOnForbiddenLayout()).toBeFalsy();
            closestComponentStub.restore();
        });

        it('should be truthy if it is on a forbidden dashboard', function() {
            field.def.disallowed_layouts = [{name: 'dashboard', type: 'bad'}];
            var closestComponentStub = sinon.stub(field, 'closestComponent');
            closestComponentStub.withArgs('dashboard').returns({
                model: {
                    get: function() {return {type: 'bad'};}
                }
            });
            expect(field.isOnForbiddenLayout()).toBeTruthy();
            closestComponentStub.restore();
        });

        it('should be falsy if it is not on a forbidden dashboard', function() {
            field.def.disallowed_layouts = [{name: 'dashboard', type: 'bad'}];
            var closestComponentStub = sinon.stub(field, 'closestComponent');
            closestComponentStub.withArgs('dashboard').returns({
                model: {
                    get: function() {return {type: 'good'};}
                }
            });
            expect(field.isOnForbiddenLayout()).toBeFalsy();
            closestComponentStub.restore();
        });

        it('should be falsy if it is not on a forbidden layout', function() {
            var disallowedLayouts = [{'name': 'bad-layout-1'}, {'name': 'bad-layout-2'}];
            var closestComponentStub = sinon.stub(field, 'closestComponent');
            closestComponentStub.withArgs('bad-layout-1').returns(void 0);
            closestComponentStub.withArgs('bad-layout-2').returns(void 0);
            expect(field.isOnForbiddenLayout()).toBeFalsy();
            closestComponentStub.restore();
        });

        it('should be truthy if it is not on an allowed dashboard', function() {
            field.def.allowed_layouts = [{name: 'dashboard', type: 'good'}];
            var closestComponentStub = sinon.stub(field, 'closestComponent');
            closestComponentStub.withArgs('dashboard').returns({
                model: {
                    get: function() {return {type: 'bad'};}
                }
            });
            expect(field.isOnForbiddenLayout()).toBeTruthy();
            closestComponentStub.restore();
        });

        it('should be falsy if it is on an allowed dashboard', function() {
            field.def.allowed_layouts = [{name: 'dashboard', type: 'good'}];
            var closestComponentStub = sinon.stub(field, 'closestComponent');
            closestComponentStub.withArgs('dashboard').returns({
                model: {
                    get: function() {return {type: 'good'};}
                }
            });
            expect(field.isOnForbiddenLayout()).toBeFalsy();
            closestComponentStub.restore();
        });
    });
});
