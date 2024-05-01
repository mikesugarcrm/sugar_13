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
describe('favorite field', function() {

    var app;
    var model;
    var field;

    var moduleName;
    var metadata;

    beforeEach(function() {

        moduleName = 'Accounts';
        metadata = {
            fields: {
                name: {
                    name: 'name',
                    vname: 'LBL_NAME',
                    type: 'varchar',
                    len: 255,
                    comment: 'Name of this bean'
                }
            },
            favoritesEnabled: true,
            views: [],
            layouts: [],
            _hash: 'bc6fc50d9d0d3064f5d522d9e15968fa'
        };

        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.updateModuleMetadata(moduleName, metadata);
        SugarTest.testMetadata.set();
        app.data.declareModel(moduleName, metadata);

        model = app.data.createBean(moduleName, {
            id:'123test',
            name: 'Lórem ipsum dolor sit àmêt, ut úsu ómnés tatión imperdiet.'
        });

        field = SugarTest.createField('base', 'favorite', 'favorite', 'detail', null, null, model);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;

        moduleName = null;
        metadata = null;
    });

    it('should re-render if the value change on the model', function() {
        field.model.set('my_favorite', true);
        sinon.spy(field, 'render');

        field.model.set('my_favorite', false);
        expect(field.render).toHaveBeenCalled();

        field.model.set('my_favorite', true);
        expect(field.render).toHaveBeenCalledTwice();
    });

    it('should not render and log error if the module has no favorites enabled', function() {

        var error = sinon.spy(app.logger, 'error');

        var loadTemplate = sinon.stub(field, '_loadTemplate').callsFake(function() {
            this.template = function() {
                return '<button type="button" class="btn btn-invisible">' +
                    '<i class="sicon sicon-star-outline"></i></button>';
            };
        });

        metadata.favoritesEnabled = false;
        SugarTest.testMetadata.updateModuleMetadata(moduleName, metadata);
        app.data.declareModel(moduleName, metadata);

        field.model = model;
        field.render();
        expect(loadTemplate.called).toBeFalsy();
        expect(error.calledOnce).toBeTruthy();

        error.restore();
        loadTemplate.restore();
    });

    it('should not render doesnt not have id', function() {

        var loadTemplate = sinon.stub(field, '_loadTemplate').callsFake(function() {
            this.template = function() {
                return '<button type="button" class="btn btn-invisible">' +
                    '<i class="sicon sicon-star-outline"></i></button>';
            };
        });

        app.data.declareModel(moduleName, metadata);
        delete model.attributes.id;
        field.model = model;
        field.render();
        expect(loadTemplate.called).toBeFalsy();

        loadTemplate.restore();
    });

    describe('toggle favorite', function() {

        let templateFavoriteIsActive = '<button type="button" class="btn btn-invisible active">' +
            '<i class="sicon sicon-star-outline"></i></button>';
        let templateFavoriteIsInactive = '<button type="button" class="btn btn-invisible">' +
            '<i class="sicon sicon-star-fill"></i></button>';
        let loadTemplateStub = null;
        let isFavStub = null;
        let favStub = null;

        beforeEach(function() {
            isFavStub = sinon.stub(field.model, 'isFavorite').callsFake(function() {
                return this.fav;
            });

            favStub = sinon.stub(field.model, 'favorite').callsFake(function() {
                this.fav = !this.fav;
                return true;
            });
        });

        afterEach(function() {
            loadTemplateStub.restore();
            favStub.restore();
            isFavStub.restore();
        });

        it('should favorite an unfavorite record', function() {
            loadTemplateStub = sinon.stub(field, '_loadTemplate').callsFake(function() {
                this.template = function() {
                    return templateFavoriteIsInactive;
                };
            });

            model.fav   = false;
            field.model = model;
            field.render();

            field.$('.btn').trigger('click');
            expect(favStub.calledOnce);
            expect(isFavStub.calledOnce);
            expect(field.$('.btn').hasClass('active')).toBeTruthy();
        });

        it('should unfavorite a favorite record', function() {
            loadTemplateStub = sinon.stub(field, '_loadTemplate').callsFake(function() {
                this.template = function() {
                    return templateFavoriteIsActive;
                };
            });

            model.fav   = true;
            field.model = model;
            field.render();

            field.$('.btn').trigger('click');
            expect(favStub.calledOnce);
            expect(isFavStub.calledOnce);
            expect(field.$('.btn').hasClass('active')).toBeFalsy();
        });

        it('should log error if unable to favorite or unfavorite record', function() {
            var errorSpy = sinon.spy(app.logger, 'error');

            loadTemplateStub = sinon.stub(field, '_loadTemplate').callsFake(function() {
                this.template = function() {
                    return templateFavoriteIsInactive;
                };
            });

            isFavStub.restore();
            isFavStub = sinon.stub(field.model, 'isFavorite').callsFake(function() {
                return false;
            });

            favStub.restore();
            favStub = sinon.stub(field.model, 'favorite').callsFake(function() {
                return false;
            });

            field.model = model;
            field.render();

            field.$('.btn').trigger('click');
            expect(favStub.calledOnce);
            expect(isFavStub.calledOnce);
            expect(errorSpy.calledOnce);

            errorSpy.restore();
        });

        describe('trigger `favorite:active` on context', function() {
            var triggerSpy;

            beforeEach(function() {
                triggerSpy = sinon.spy(field.model, 'trigger');
            });

            afterEach(function() {
                triggerSpy.restore();
            });

            it('Should trigger the favorite:active event on the context when favorite an unfavorite record.', function() {
                loadTemplateStub = sinon.stub(field, '_loadTemplate').callsFake(function() {
                    this.template = function() {
                        return templateFavoriteIsInactive;
                    };
                });

                model.fav   = false;
                field.model = model;
                field.render();

                field.$('.btn').trigger('click');
                expect(triggerSpy.calledWithExactly('favorite:active')).toBeTruthy();
            });

            it('Should not trigger the favorite:active event on the context when unfavorite a favorite record.', function() {
                loadTemplateStub = sinon.stub(field, '_loadTemplate').callsFake(function() {
                    this.template = function() {
                        return templateFavoriteIsActive;
                    };
                });

                model.fav   = true;
                field.model = model;
                field.render();

                field.$('.btn').trigger('click');
                expect(triggerSpy.neverCalledWith('favorite:active')).toBeTruthy();
            });
        });
    });

    it('should format accordingly with favorite status on bean', function() {

        field.model = model;
        var isFavStub = sinon.stub(field.model, 'isFavorite').callsFake(function() {
            return this.fav;
        });

        field.model.fav = false;
        expect(field.format()).toBeFalsy();
        expect(isFavStub.calledOnce);

        field.model.fav = true;
        expect(field.format()).toBeTruthy();
        expect(isFavStub.calledOnce);

        isFavStub.restore();
    });

    it('should be able to trigger filtering to the filterpanel layout.', function() {
        var applyLastFilterStub,
            getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function(module) {
                return {activityStreamEnabled:true};
            });
        //Fake layouts
        field.view = app.view.createView({type: 'base'});
        field.view.layout = app.view.createLayout({type: 'base'});
        field.view.layout.layout = SugarTest.createLayout('base', 'Accounts', 'filterpanel', {});
        field.view.layout.layout.name = 'filterpanel';
        applyLastFilterStub = sinon.stub(field.view.layout.layout, 'applyLastFilter');

        //Call the method
        field._refreshListView();

        expect(applyLastFilterStub).toHaveBeenCalled();
        expect(applyLastFilterStub).toHaveBeenCalledWith(field.collection, 'favorite');

        field.view.dispose();
        field.view.layout.dispose();
        field.view.layout.layout.dispose();

        getModuleStub.restore();
    });
});
