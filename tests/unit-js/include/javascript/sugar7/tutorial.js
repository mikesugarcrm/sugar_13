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
describe('Sugar.Tutorial', function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
    });

    describe('app.tutorial', function() {
        describe('hasTutorial', function() {
            using('different tutorial metadata', [
                {
                    testMeta: null,
                    defaultMeta: null,
                    expected: false
                },
                {
                    testMeta: {
                        views: {
                            tutorial: {
                                meta: {
                                    myLayout: {
                                        some: 'metadata'
                                    }
                                }
                            }
                        }
                    },
                    defaultMeta: null,
                    expected: true
                },
                {
                    testMeta: null,
                    defaultMeta: {
                        myLayout: {
                            some: 'default metadata'
                        }
                    },
                    expected: true
                }
            ], function(value) {
                it('should determine if a given module and layout has an associated tutorial', function() {
                    var oldData = app.tutorial.data;
                    delete app.tutorial.data;

                    var testMeta = value.testMeta;
                    var defaultMeta = value.defaultMeta;
                    sinon.stub(app.metadata, 'getModule').withArgs('MyModule').returns(testMeta);
                    sinon.stub(app.metadata, 'getView').withArgs('', 'tutorial').returns(defaultMeta);
                    expect(app.tutorial.hasTutorial('myLayout', 'MyModule')).toEqual(value.expected);

                    app.tutorial.data = oldData;
                });
            });
        });

        describe('show', function() {
            beforeEach(function() {
                this.oldData = app.tutorial.data;
                delete app.tutorial.data;

                this.tutorialStub = sinon.stub(app.tutorial, 'doTutorial');
                this.getViewStub = sinon.stub(app.metadata, 'getView').withArgs('', 'tutorial');
            });

            afterEach(function() {
                app.tutorial.data = this.oldData;
            });

            it('should do the tutorial if tutorial data exists', function() {
                app.tutorial.data = {
                    tutorial: 'metadata'
                };
                app.tutorial.show('MyName', {some: 'params'});
                expect(this.tutorialStub).toHaveBeenCalledWith('MyName', {some: 'params'});
            });

            it('should cache the tutorial data if it does not exist but default metadata is available', function() {
                this.getViewStub.returns({dummy: 'metadata'});
                app.tutorial.show('MyName', {some: 'params'});
                expect(this.tutorialStub).toHaveBeenCalledWith('MyName', {some: 'params'});
                expect(app.tutorial.data).toEqual({dummy: 'metadata'});
            });

            it('should not do the tutorial if neither tutorial data nor default metadata are available', function() {
                this.getViewStub.returns(null);
                app.tutorial.show('MyName', {some: 'params'});
                expect(this.tutorialStub).not.toHaveBeenCalled();
            });
        });

        describe('getPrefs', function() {
            beforeEach(function() {
                this.cacheStub = sinon.stub(app.cache, 'get');
            });

            it('should retrieve tutorial preferences from cache', function() {
                var tutorialPrefs = {
                    skipVersion: {},
                    viewedVersion: {
                        recordHome: 1
                    }
                };
                this.cacheStub.withArgs('tutorialPrefs').returns(tutorialPrefs);
                expect(app.tutorial.getPrefs()).toEqual(tutorialPrefs);
            });

            it('should fill in skipVersion and viewedVersion if undefined', function() {
                this.cacheStub.withArgs('tutorialPrefs').returns({});
                expect(app.tutorial.getPrefs()).toEqual({
                    skipVersion: {},
                    viewedVersion: {}
                });
            });
        });

        describe('resetPrefs', function() {
            using('different preferences', [
                    null,
                    {
                        skipVersion: {},
                        viewedVersion: {
                            recordHome: 1
                        }
                    }
                ], function(prefs) {
                    it('should reset tutorial preferences in the cache', function() {
                        var cacheStub = sinon.stub(app.cache, 'set');
                        app.tutorial.resetPrefs(prefs);
                        expect(cacheStub).toHaveBeenCalledWith('tutorialPrefs', {
                            skipVersion: {},
                            viewedVersion: {}
                        });
                    });
                }
            );
        });
    });
});
