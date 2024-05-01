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
describe('SugarLive.View.ConfigHeaderButtons', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base', 'SugarLive', 'config-header-buttons', null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('save related methods', function() {
        it('should call build selected list method before saving', function() {
            var buildSpy = sinon.spy(view, 'buildSelectedList');
            view._beforeSaveConfig();
            expect(buildSpy).toHaveBeenCalled();
        });

        it('should set the save button disabled on save', function() {
            var field = {
                setDisabled: function(param) { }
            };
            sinon.stub(view, 'getField').withArgs('save_button').returns(field);
            var fieldSpy = sinon.spy(field, 'setDisabled');
            view._saveConfig();
            expect(fieldSpy).toHaveBeenCalledWith(true);
        });
    });

    describe('cancelConfig', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'get').callsFake(function() { });
        });

        describe('when triggerBefore is true', function() {
            it('should call app.drawer.close method', function() {
                sinon.stub(view, 'triggerBefore').callsFake(function() {return true;});
                app.drawer = {
                    close: $.noop,
                    count: function() {
                        return 1;
                    }
                };
                sinon.spy(app.drawer, 'close');
                view.cancelConfig();

                expect(app.drawer.close).toHaveBeenCalledWith(view.context, view.context.get());
                delete app.drawer;
            });

            it('should not call app.drawer.close method', function() {
                sinon.stub(view, 'triggerBefore').callsFake(function() {return true;});
                app.drawer = {
                    close: $.noop,
                    count: function() {
                        return 0;
                    }
                };
                sinon.spy(app.drawer, 'close');
                view.cancelConfig();

                expect(app.drawer.close).not.toHaveBeenCalled();
                delete app.drawer;
            });
        });

        describe('when triggerBefore is false', function() {
            it('should not call app.router.navigate', function() {
                sinon.stub(view, 'triggerBefore').callsFake(function() {return false;});
                app.drawer = {
                    close: $.noop,
                    count: function() {
                        return 1;
                    }
                };
                sinon.spy(app.drawer, 'count');
                view.cancelConfig();

                expect(app.drawer.count).not.toHaveBeenCalled();
                delete app.drawer;
            });
        });
    });

    describe('buildSelectedList', function() {
        var ul;

        beforeEach(function() {
            ul = document.createElement('ul');
            ul.setAttribute('module_name', 'Calls');
            var li1 = document.createElement('li');
            li1.setAttribute('fieldname', 'name');
            li1.setAttribute('fieldlabel', 'LBL_SUBJECT');
            var li2 = document.createElement('li');
            li2.setAttribute('fieldname', 'description');
            li2.setAttribute('fieldlabel', 'LBL_DESC');
            ul.appendChild(li1);
            ul.appendChild(li2);

            sinon.stub(app.metadata, '_patchFields');
            sinon.stub(app.metadata, 'getField').callsFake(function(param) {
                return {
                    type: 'text',
                    name: param.name
                };
            });
        });

        it('should build a metadata object out of dom elements', function() {
            sinon.stub(document, 'querySelectorAll')
                .withArgs('.drawer.active .columns .field-list').returns([ul]);

            var results = view.buildSelectedList();
            expect(results.Calls.base.view['omnichannel-detail'].fields.length).toEqual(2);
        });

        it('should return an empty metadata object if it does not find the list', function() {
            var results = view.buildSelectedList();
            expect(results).toEqual({});
        });
    });
});
