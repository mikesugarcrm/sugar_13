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

var SugarTest = {};

(function(test) {

    /**
     * Storage object for key-value pairs.
     *
     * @type {Object}
     */
    test.storage = {};

    /**
     * Interface for key-value pair storage engine.
     */
    test.keyValueStore = {
        /**
         * Set a key-value pair in storage.
         *
         * @param {*} key Key.
         * @param {*} value Value.
         */
        set: function(key, value) {
            test.storage[key] = value;
        },

        /**
         * Add the given value to the existing value in storage.
         *
         * @param key Key.
         * @param value Value to add to the existing value.
         */
        add: function(key, value) {
            test.storage[key] += value;
        },

        /**
         * Retrieve a value from storage.
         *
         * @param key The key to look up.
         * @return {*} The corresponding value in storage.
         */
        get: function(key) {
            return test.storage[key];
        },

        /**
         * Check if a truthy value is stored under the given key.
         *
         * @param key The key to look up.
         * @return {boolean} true if a truthy value is stored under the given
         *   key, false otherwise.
         */
        has: function(key) {
            return test.storage[key] ? true : false;
        },

        /**
         * Remove the value associated with the given key from storage.
         *
         * @param key Key.
         */
        cut: function(key) {
            delete test.storage[key];
        },

        /**
         * Clear out all values from storage.
         */
        cutAll: function() {
            test.storage = {};
        },

        /**
         * Retrieve the entire storage object.
         *
         * @return {Object} The entire storage object.
         */
        getAll: function() {
            return test.storage;
        }
    };

    /**
     * Fetch a file via AJAX and return its contents.
     *
     * @param {string} path Path to the directory containing the desired file.
     * @param {string} file Name of the desired file (excluding extension).
     * @param {string} ext File extension (not including the period).
     * @param {Function} parseData Transformation to apply to the file.
     * @param {string} [dataType='text'] Expected data type. See $.ajax
     *   documentation for valid types.
     * @return {*} The contents of the file, as transformed by parseData.
     */
    test.loadFile = function(path, file, ext, parseData, dataType) {
        // FIXME: figure out why there's a near-identical version of this function in component-helper.js
        // It's actually *functionally* identical, there's just a slight difference in where url is defined
        dataType = dataType || 'text';

        var fileContent = null;

        $.ajax({
            async:    false, // must be synchronous to guarantee that a test doesn't run before the fixture is loaded
            cache:    false,
            dataType: dataType,
            url:      path + "/" + file + "." + ext,
            success:  function(data) {
                fileContent = parseData(data);
            },
            failure:  function() {
                console.log('Failed to load file: ' + file);
            }
        });

        return fileContent;
    };

    /**
     * Load a fixture file and return its contents.
     *
     * @param {string} file Name of the fixture file (excl. extension).
     * @param {string} [fixturePath='./fixtures'] Path to the fixture
     *   directory, relative to "unit-js".
     * @return {*} The contents of the fixture file.
     */
    test.loadFixture = function(file, fixturePath) {
        // FIXME: figure out why there's an identical version of this function in component-helper.js
        return test.loadFile(fixturePath || "../fixtures", file, "json", function(data) { return data; }, "json");
    };

    /**
     * Load the metadata fixture into SugarTest.metadata.
     * Only certain tests want seeded metadata so those suites can
     * load this in their respective beforeEach calls.
     *
     * @param {boolean} useJSMetadata If true, use the JS version of the
     *   metadata.
     * @param {string} fixturePath Path to the fixtures directory, relative to
     *   "unit-js".
     */
    test.seedMetadata = function(useJSMetadata, fixturePath) {
        var meta, labels, jssource;

        this.seedApp();
        SugarTest.dm = SUGAR.App.data;
        meta = (useJSMetadata) ? fixtures.metadata : SugarTest.loadFixture("metadata", fixturePath);

        // Lang strings are now retrieved in a separate GET, so we need to augment
        // our metadata fake with them here before calling setting metadata.set.
        if (!this.labelsFixture && meta.labels) {
            this.labelsFixture = SugarTest.loadFixture('labels', fixturePath);
            meta = _.extend(meta, this.labelsFixture);
        }

        SugarTest.app.metadata.set(meta, true, true);

        // Added jssource to simulate our jssource generated component
        jssource = (useJSMetadata) ? fixtures.jssource : null;
        if (jssource) {
            // Same way we do in metadata-manager upon injecting in HEAD
            SugarTest.app.metadata._declareClasses(jssource);
        }

        SugarTest.dm.reset();
        SugarTest.dm.declareModels(meta.modules);
        SugarTest.metadata = meta;
    };

    test._appInitialized = false;

    /**
     * Call SUGAR.App.init if this has not already been done.
     */
    test.seedApp = function() {
        if (this._appInitialized) {
            // Force the clipboard to be reinitialized since it is disposed
            // after each test suite is executed.
            if (this.app.clipboard) {
                this.app.clipboard.init();
            }

            return;
        }
        this.app = SUGAR.App.init({el: "body"});
        this._appInitialized = true;
    };

    /**
     * Create a fake server and assign it to SugarTest.server.
     */
    test.seedFakeServer = function() {
        SugarTest.server = sinon.fakeServer.create();
    };

    test.waitFlag = false;
    test.wait = function() { waitsFor(function() { return test.waitFlag; }); };
    test.resetWaitFlag = function() { this.waitFlag = false; };
    test.setWaitFlag = function() { this.waitFlag = true; };
    test.components = [];

    /**
     * Clean up after test execution.
     */
    test.dispose = function() {
        // TODO: app.destroy works incorrectly
        //if (this.app) this.app.destroy();
        if (this.server && this.server.restore) this.server.restore();
        if (Backbone && Backbone.history) Backbone.history.stop();
        _.each(this.components, function(c) {
            c.dispose();
        });
        this.components = [];

        // Dispose of the clipboard after each test suite is executed to
        // guarantee that all DOM nodes and event listeners are removed
        // correctly.
        if (this.app && this.app.clipboard) {
            this.app.clipboard.dispose();
        }
    };

    /**
     * Create a component of the specified type.
     *
     * @param {string} type Type of component to create (Layout/View/Field).
     *   Make sure to capitalize the first letter.
     * @param {Object} params Parameters to pass the creation function.
     * @return {*} The created component.
     */
    test.createComponent = function(type, params) {
        var c = this.app.view["create" + type](params);
        if (type === 'Layout') {
            c.initComponents();
        }
        this.components.push(c);
        return c;
    };

}(SugarTest));

beforeEach(function(){
    SUGAR.App.config.env = "test";
    SUGAR.App.config.appId = SUGAR.App.config.appId || "portal";
    SUGAR.App.config.maxQueryResult = 20;
    SUGAR.App.config.serverTimeout = 1;
    SUGAR.App.config.cacheMeta = false;
    SUGAR.App.config.minServerVersion = "6.6";
    SUGAR.App.config.supportedServerFlavors = null;
    SUGAR.App.config.alertsEl = "body";
    SUGAR.App.config.alertAutoCloseDelay = "0";

    SugarTest.storage = {};
    SUGAR.App.cache.store = SugarTest.keyValueStore;
    delete SUGAR.App.config.loadCss;

    SugarTest.seedApp();
    SugarTest.resetWaitFlag();
});

/**
 * Data provider code.
 *
 * @see https://github.com/jphpsf/jasmine-data-provider
 */
function using(name, values, func) {
    for (var i = 0, count = values.length; i < count; i++) {
        if (Object.prototype.toString.call(values[i]) !== '[object Array]') {
            values[i] = [values[i]];
        }
        func.apply(this, values[i]);
        jasmine.currentEnv_.currentSpec.description += ' (with "' + name + '" using ' + values[i].join(', ') + ')';
    }
}

var underscoreDelayFunctions = ['throttle', 'debounce'];
var underscoreSetTimeoutFunctions = ['delay', 'defer'];
var _createLayout;
var _createView;
var _createField;
var _components;

beforeEach(function(){
    if (!(SugarTest.clock && SugarTest.clock.restore))
    {
        SugarTest.clock = sinon.useFakeTimers();
    }

    _components = [];
    SugarTest.components = [];
    SugarTest.datas = [];
    SugarTest._events = {
        context: [],
        model: []
    };
    var _wrappedCreateMethod = _.bind(function(orig) {
        var args = Array.prototype.slice.call(arguments, 1);
        var component = orig.apply(this, args);

        _components.push(component);
        return component;
    }, SugarTest.app.view);

    //Mock throttle and debounce to prevent the need to actually wait.
    //(underscore throttle uses dates to enforce waits outside of the normal setTimeout function
    _.each(underscoreDelayFunctions, function(func) {
        if (_[func].restore) {
            return;
        }

        sinon.stub(_, func).callsFake(function(f) {
            return function() {
                f.apply(this, arguments);
            };
        });
    });

    //mock delay and defer to prevent the need to actually wait.
    //we want to invoke the stubbed method right away
    _.each(underscoreSetTimeoutFunctions, function(func) {
        if (_[func].restore) {
            return;
        }

        sinon.stub(_, func).callsFake(function(f) {
            var args = Array.prototype.slice.call(arguments, 1);
            f.apply(null, args);
        });
    });

    // stub out the icon helper so that tests calling templates don't bomb...
    Handlebars.registerHelper('moduleIconLabel', function(module) {
        return module.substring(0, 2);
    });

    _createLayout = SugarTest.app.view.createLayout;
    _createView = SugarTest.app.view.createView;
    _createField = SugarTest.app.view.createField;

    SugarTest.app.view.createView = _.wrap(SugarTest.app.view.createView, _wrappedCreateMethod);
    SugarTest.app.view.createLayout = _.wrap(SugarTest.app.view.createLayout, _wrappedCreateMethod);
    SugarTest.app.view.createField = _.wrap(SugarTest.app.view.createField, _wrappedCreateMethod);
    SugarTest.app.events.on('router:init', function() {
        SugarTest.app.router.stop = _.wrap(SugarTest.app.router.stop, function(orig) {
            var args = Array.prototype.slice.call(arguments, 1);
            SugarTest.app.router.navigate('', {trigger: true});
            return orig.apply(this, args);
        });
    });
});

afterEach(function() {
    SugarTest.dispose();
    SUGAR.App.view.reset();

    _.each(SugarTest.datas, function(module) {
        SugarTest.app.data.resetModel(module);
        SugarTest.app.data.resetCollection(module);
    });
    _.each(SugarTest.components, function(component) {
        component.dispose();
    });
    var suite = this.suite;
    while(suite.parentSuite) {
        suite = suite.parentSuite;
    }
    var suiteDesc = suite.description,
        url = window.location.origin + window.location.pathname + "?spec=" + escape(suiteDesc),
        msgCss = "color:white;background-color:red;";

    _.each(SugarTest._events, function(evts, type) {
        _.each(evts, function(stack, idx) {
            _.each(stack, function(ctx, name) {
                if(!_.isEmpty(ctx)) {
                    if(type == "model") {
                        _.each(ctx, function(cb){
                            if(!(cb.context instanceof Backbone.Model || cb.context instanceof Backbone.Collection)) {
                                if(idx === 0) {
                                    console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".on("  + name + ") - '" + url + "'", msgCss);
                                } else if(idx === 0) {
                                    console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".before("  + name + ") - '" + url + "'", msgCss);
                                }
                            }
                        });
                    } else {
                        if(idx === 0) {
                            console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".on("  + name + ") - '" + url + "'", msgCss);
                        } else if(idx === 0) {
                            console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".before("  + name + ") - '" + url + "'", msgCss);
                        }
                    }
                }
                delete stack[name];
            }, this);
        }, this);
    }, this);

    SugarTest.app.events.off('router:init');
    var type = 'app.routing';
    _.each([SugarTest.app.routing._events, SugarTest.app.routing._before], function(stack, idx) {
        _.each(stack, function(ctx, name) {
            if(!_.isEmpty(ctx)) {
                if(idx === 0) {
                    console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".on("  + name + ") - '" + url + "'", msgCss);
                    delete SugarTest.app.router._events[name];
                } else if(idx === 0) {
                    console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".before("  + name + ") - '" + url + "'", msgCss);
                    delete SugarTest.app.router._before[name];
                }
            }
        }, this);
    }, this);

    if (SugarTest.app.controller.layout) {
        if (SugarTest.app.controller.context) {
            SugarTest.app.controller.context.clear({silent: true});
        }
        SugarTest.app.controller.layout.dispose();
        // We need to empty this so we don't try to re-append the oldLayout in
        // controller.js#loadView
        SugarTest.app.controller.layout = void 0;
    }

    _.each(SugarTest.app.additionalComponents, function(component) {
        component.dispose();
    });
    SugarTest.app.additionalComponents = {};

    _.each(_components, function(component) {
        if (component && !component.disposed) {
            throw new Error('[DISPOSE NEEDED]: ' + component.toString());
        }
    });

    SugarTest.app.view.createLayout = _createLayout;
    SugarTest.app.view.createView = _createView;
    SugarTest.app.view.createField = _createField;

    SugarTest.components = null;
    SugarTest._events = null;

    delete Handlebars.helpers.moduleIconLabel;

    SugarTest.clock.restore();
    _.each(underscoreDelayFunctions, function(func) {
        if (_[func].restore) {
            _[func].restore();
        }
    });
    _.each(underscoreSetTimeoutFunctions, function(func) {
        if (_[func].restore) {
            _[func].restore();
        }
    });
});
