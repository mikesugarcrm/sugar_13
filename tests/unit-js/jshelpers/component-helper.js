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
(function(test) {
    var app = SUGAR.App;

    /**
     * Fetch a component from the file system, then declare it to the app.
     *
     * @param {string} client Client in which to look for the component
     *   (base, portal, etc.).
     * @param {string} type Type of component (layout/view/field).
     * @param {string} name Component name.
     * @param {string} [module] Module in which to look for the component.
     */
    test.loadComponent = function(client, type, name, module) {
        var path = "/clients/" + client + "/" + type + "s/" + name;
        path = (module) ? "../modules/" + module + path : ".." + path;

        SugarTest.loadFile(path, name, "js", function(data) {
            try {
                data = eval(data);
            } catch(e) {
                app.logger.error("Failed to eval view controller for " + name + ": " + e + ":\n" + data);
            }
            test.addComponent(client, type, name, data, module);
        });
    };

    /**
     * Declare a Model, Collection, or Component.
     *
     * @param {string} client Client in which to look for the component
     *   (base, portal, etc.).
     * @param {string} type Type of component
     *   (layout/view/field/model/collection).
     * @param {string} name Component name.
     * @param {string} data Code implementing the component (controller, etc.)
     * @param {string} module Module in which to look for the component.
     */
    test.addComponent = function(client, type, name, data, module) {
        if (type === 'data') {
            if (name === 'model') {
                app.data.declareModelClass(module, null, client, data);
            } else {
                app.data.declareCollectionClass(module, client, data);
            }
        } else {
            app.view.declareComponent(type, name, module, data, true, client);
        }
    };

    /**
     * Load a Sugar plugin, trigger its code, and trigger app:init.
     *
     * @param {string} name Name of the plugin file, excluding extension.
     * @param {string} [subdir=''] Subdirectory within plugins directory
     *   in which to look for the plugin. (Currently not needed as
     *   there are no such subdirectories.)
     */
    test.loadPlugin = function(name, subdir) {
        subdir = subdir ? '/' + subdir : '';
        var path = '../include/javascript/sugar7/plugins' + subdir;
        SugarTest.loadFile(path, name, 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
    };

    /**
     * Load a Handlebars template and add it to SugarTest.testMetadata.
     *
     * @param {string} name Name of the template.
     * @param {string} type Component type (layout/view/field).
     * @param {string} client Client (base/portal/etc.).
     * @param {string} [template] File name of the template (excl. extension).
     *   If unspecified, name parameter is used.
     * @param {string} [module] Module to look in.
     */
    test.loadHandlebarsTemplate = function(name, type, client, template, module) {
        var templateName = template || name;
        var path = "/clients/" + client + "/" + type + "s/" + name;
        path = (module) ? "../modules/" + module + path : ".." + path;
        SugarTest.loadFile(path, templateName, "hbs", function(data) {
            test.testMetadata.addTemplate(name, type, data, templateName, module);
        });
    };

    /**
     * Create a field object of a given type. Can load the relevant
     * controller automatically from source.
     * 
     * @param {string|Object} client Name of client from which to load the
     *   controller. If an Object, all the positional parameters listed below
     *   can also be given as named properties of this argument.
     * @param {boolean} [client.loadJsFile=true] If true, will attempt to load
     *   the controller from source file.
     * @param {string} name Name of the field.
     * @param {string} type Type of the field.
     * @param {string} viewName Name of the view that will host the field.
     * @param {Object} fieldDef Field definition.
     * @param {string} [module] Module to associate with this field. The
     *   controller will be loaded from this module if both loadFromModule
     *   and loadJsFile are set.
     * @param {Data.Bean} [model] Bean to associate with this field.
     * @param {Core.Context} [context] Context to associate with this field.
     * @param {boolean} [loadFromModule=false] If true, will attempt to load
     *   source file from the module directory.
     * @return {Field} The created field.
     */
    test.createField = function(client, name, type, viewName, fieldDef, module, model, context, loadFromModule) {
        var loadJsFile = true;
        // Handle a params object instead of a huge list of params
        if (_.isObject(client)) {
            name = client.name;
            type = client.type;
            viewName = client.viewName;
            fieldDef = client.fieldDef;
            module = client.module;
            model = client.model;
            context = client.context;
            loadFromModule = client.loadFromModule;
            loadJsFile = !_.isUndefined(client.loadJsFile) ? client.loadJsFile : loadJsFile;
            client = client.client || "base";
        }

        if(loadJsFile) {
            if (loadFromModule) {
                test.loadComponent(client, "field", type, module);
            } else {
                test.loadComponent(client, "field", type);
            }
        }

        var view = new app.view.View({ name: viewName, context: context });
        var def = {
            name: name,
            type: type,
            events: (fieldDef) ? fieldDef.events : {},
            fields: (fieldDef) ? fieldDef.fields : undefined
        };
        if (!context) {
            context = app.context.getContext();
            context.set({
                module: module
            });
            context.prepare();
        }

        model = model || new app.data.createBean();

        if (fieldDef) {
            model.fields = model.fields || {};
            model.fields[name] = fieldDef;
        }

        var field = app.view.createField({
            def: def,
            view: view,
            context: context,
            model: model,
            module:module,
            platform: client
        });


        var _origDispose = field._dispose;
        field._dispose = function() {
            if(this.context) {
                SugarTest._events.context.push(this.context._events);
            }
            if(this.model) {
                SugarTest._events.model.push(this.model._events);
            }
            _origDispose.apply(this, arguments);
        };

        SugarTest.components.push(view);
        return field;
    };

    /**
     * Create a view.
     *
     * @param {string} client Client in which to look for the component
     *   (base, portal, etc.).
     * @param {string} module Module to associate with this view.
     * @param {string} viewName Name of the view.
     * @param {Object} [meta] Custom metadata.
     * @param {Core.Context} [context] Context to associate with this view.
     * @param {boolean} [loadFromModule=false] If true, will attempt to load
     *   source file from the module directory.
     * @param {Layout} [layout] The layout to which this view belongs.
     * @param {boolean} [loadComponent=true] If `true`, load the view before
     *   creating it.
     * @return {View} The created view.
     */
    test.createView = function(client, module, viewName, meta, context, loadFromModule, layout, loadComponent) {
        if (_.isUndefined(loadComponent) || loadComponent)
        {
            if (loadFromModule) {
                test.loadComponent(client, "view", viewName, module);
            } else {
                test.loadComponent(client, "view", viewName, null);
            }
        }
        if (!context) {
            context = app.context.getContext();
            context.set({
                module: module
            });
            context.prepare();
        }

        var view = app.view.createView({
            type : viewName,
            context : context,
            module : module,
            meta : meta,
            layout: layout,
            platform: client
        });

        var _origDispose = view._dispose;
        view._dispose = function() {
            if(this.context) {
                SugarTest._events.context.push(this.context._events);
            }
            if(this.model) {
                SugarTest._events.model.push(this.model._events);
            }
            _origDispose.apply(this, arguments);
        };

        SugarTest.components.push(view);
        return view;
    };

    /**
     * Loads and declare a custom Bean and a custom BeanCollection.
     *
     * @param {string} client The platform.
     * @param {string} module The custom Bean module.
     * @param {boolean} [loadModel=true] Set to false to prevent an attempt to
     *   load the model override.
     * @param {boolean} [loadCollection=true] Set to false to prevent an
     *   attempt to load the collection override.
     */
    test.declareData = function(client, module, loadModel, loadCollection) {
        loadModel = (loadModel !== false);
        loadCollection = (loadCollection !== false);

        if (loadModel) {
            test.loadComponent(client, 'data', 'model', module);
        }

        if (loadCollection) {
            test.loadComponent(client, 'data', 'collection', module);
        }

        SugarTest.datas.push(module);
    };

    /**
     * Create a layout.
     *
     * @param {string} client Client in which to look for the component
     *   (base, portal, etc.).
     * @param {string} module The module to associate this layout with.
     * @param {string} layoutName Name of the layout to create.
     * @param {Object} [meta] Custom metadata.
     * @param {Context} [context] Context to associate with this layout.
     * @param {boolean} [loadFromModule=false] If true, load the component
     *   from the specified module.
     * @param {Object} [params] Additional parameters to pass to
     *   App.view.createLayout.
     * @return {Layout} The created layout.
     */
    test.createLayout = function(client, module, layoutName, meta, context, loadFromModule, params) {
        if (loadFromModule) {
            test.loadComponent(client, "layout", layoutName, module);
        } else {
            test.loadComponent(client, "layout", layoutName);
        }
        if (!context) {
            context = app.context.getContext();
            context.set({
                module: module,
                layout: layoutName
            });
            context.prepare();
        }

        var layout = app.view.createLayout(_.extend({
            type: layoutName,
            context: context,
            module: module,
            meta: meta,
            platform: client
        }, params));

        //FIXME: SC-3880 Execution of this line should be contingent on params passed
        layout.initComponents();

        var _origDispose = layout._dispose;
        layout._dispose = function() {
            if(this.context) {
                SugarTest._events.context.push(this.context._events);
            }
            if(this.model) {
                SugarTest._events.model.push(this.model._events);
            }
            _origDispose.apply(this, arguments);
        };
        SugarTest.components.push(layout);
        return layout;
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
        dataType = dataType || 'text';

        var fileContent = null,
            url = path + "/" + file + "." + ext;

        $.ajax({
            async:    false, // must be synchronous to guarantee that a test doesn't run before the fixture is loaded
            cache:    false,
            dataType: dataType,
            url: url,
            success:  function(data) {
                fileContent = parseData(data);
            },
            error: function(error, status, errThrown) {
                console.log(status, errThrown);
                console.log('Failed to load: ' + url);
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
        return test.loadFile(fixturePath || "./fixtures", file, "json", function(data) { return data; }, "json");
    };

    /**
     * Test metadata.
     */
    test.testMetadata = {
        _data: null,

        /**
         * Initialize the test metadata.
         */
        init: function() {
            this._data = $.extend(true, {}, fixtures.metadata);
            this._data.layouts = this._data.layouts || {};
            this._data.views = this._data.views || {};
            this._data.fields = this._data.fields || {};

            // Lang strings are now retrieved in a separate GET, so we need to augment
            // our metadata fake with them here before calling setting metadata.set.
            if (!this.labelsFixture && this._data.labels) {
                this.labelsFixture = SugarTest.loadFixture('labels');
                this._data = $.extend(this._data, this.labelsFixture);
            }
        },

        /**
         * Register a Handlebars template for the specified component.
         *
         * @param {string} name Name of the component.
         * @param {string} type Type of component (singular).
         * @param {string} template Text of the Handlebars template.
         * @param {string} templateName Name of the template.
         * @param {string} [module] Module with which the template is associated.
         */
        addTemplate: function(name, type, template, templateName, module) {
            type = type + 's';
            if (this.isInitialized()) {
                if (module) {
                    type = (type === 'fields') ? 'fieldTemplates' : type;
                    this._initModuleStructure(module, type, name);
                    this._data.modules[module][type][name].templates[templateName] = template;
                } else {
                    this._data[type][name] = this._data[type][name] || {};
                    this._data[type][name].templates = this._data[type][name].templates || {};
                    this._data[type][name].templates[templateName] = template;
                }
            }
        },

        /**
         * Update the specified module with the given module metadata.
         *
         * @param {string} module The module to update.
         * @param {Object} moduleDef The module metadata.
         */
        updateModuleMetadata: function(module, moduleDef) {
            if (this.isInitialized()) {
                this._data.modules[module] = _.extend((this._data.modules[module] || {}), (moduleDef || {}));
            }
        },

        /**
         * Add a view definition.
         *
         * @param {string} name Name of the view.
         * @param {Object} viewDef View definition.
         * @param {string} [module] Module for this component.
         */
        addViewDefinition: function(name, viewDef, module) {
            this._addDefinition(name, 'views', viewDef, module);
        },

        /**
         * Add a layout definition.
         *
         * @param {string} name Name of the layout.
         * @param {Object} layoutDef Layout definition.
         * @param {string} [module] Module for this component.
         */
        addLayoutDefinition: function(name, layoutDef, module) {
            this._addDefinition(name, 'layouts', layoutDef, module);
        },

        /**
         * Initialize the module structure for test metadata for the given
         * module, type, and name.
         *
         * @param {string} module Module name.
         * @param {string} type Type of component
         *   (layouts/views/fields/fieldTemplates). Specified as plural.
         * @param {string} name Name of the component.
         * @private
         */
        _initModuleStructure: function(module, type, name) {
            this._data.modules[module] = this._data.modules[module] || {};
            this._data.modules[module][type] = this._data.modules[module][type] || {};
            this._data.modules[module][type][name] = this._data.modules[module][type][name] || {};
            this._data.modules[module][type][name].templates = this._data.modules[module][type][name].templates || {};
        },

        /**
         * Associate the given metadata with the component specified by name,
         * type, and module.
         *
         * @param {string} name Name of the component.
         * @param {string} type Type of the component (plural).
         *   One of 'layouts', 'views', or 'fields'.
         * @param {Object} def Metadata definition for this component.
         * @param {string} [module] Module for this component.
         * @private
         */
        _addDefinition: function(name, type, def, module) {
            if (this.isInitialized()) {
                if (module) {
                    this._initModuleStructure(module, type, name);
                    this._data.modules[module][type][name].meta = def;
                } else {
                    this._data[type][name] = this._data[type][name] || {};
                    this._data[type][name].meta = def;
                }
            }
        },

        /**
         * Set the test metadata to incorporate changes made by other
         * testMetadata functions.
         */
        set: function() {
            if (this.isInitialized()) {
                this._data._hash = true; //force ignore cache
                _.each(this._data.modules, function(module) {
                    module._patched = false;
                });
                SugarTest.app.metadata.set(this._data, true, true);
            }
        },

        /**
         * Reset the metadata to that found in fixtures.metadata.
         */
        revert: function() {
            if (this.isInitialized()) {
                SugarTest.app.metadata.set(fixtures.metadata, true, true);
            }
        },

        /**
         * Clean up the test metadata.
         */
        dispose: function() {
            this.revert();
            this._data = null;
            this.labelsFixture = null;
        },

        /**
         * Determine if the test metadata has been initialized yet.
         *
         * @return {boolean} true if the metadata has been initialized,
         *   false otherwise.
         */
        isInitialized: function() {
            if (this._data) {
                return true;
            } else {
                return false;
            }
        },

        /**
         * Retrieve the test metadata.
         *
         * @return {Object} The test metadata.
         */
        get: function() {
            return this._data;
        }
    };

}(SugarTest));
