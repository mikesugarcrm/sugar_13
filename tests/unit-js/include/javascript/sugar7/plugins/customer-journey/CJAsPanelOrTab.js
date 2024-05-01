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

describe('SUGAR.CJAsPanelOrTab', function() {
    let app;
    let view;
    let pluginsBefore;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();

        view = SugarTest.createView('base', 'Accounts', 'record');
        pluginsBefore = view.plugins;
        view.plugins = ['CJAsPanelOrTab'];
        SugarTest.loadPlugin('CJAsPanelOrTab', 'customer-journey');

        SugarTest.app.plugins.attach(view, 'view');
        view.trigger('init');
    });

    afterEach(function() {
        sinon.restore();
        view.plugins = pluginsBefore;
        view.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();

        Handlebars.templates = {};
        view = null;
    });

    describe('_isRecordViewHasTabEnabled', function() {
        using('input', [
            {
                meta: {
                    panels: [],
                },
                options: {},
                result: false,
            },
            {
                meta: {
                    panels: [],
                },
                options: {
                    meta: {
                        panels: [],
                    },
                },
                result: false,
            },
            {
                meta: {
                    panels: [],
                },
                options: {
                    meta: {
                        panels: [
                            {
                                newTab: true,
                                header: true,
                            },
                            {
                                newTab: true,
                            },
                        ],
                    },
                },
                result: true,
            },
        ],

        function(input) {
            it('should return true if panels have tab enabled else false', function() {
                view.meta = input.meta;
                view.options = input.options;

                expect(view._isRecordViewHasTabEnabled()).toBe(input.result);
            });
        });
    });

    describe('_isPanelMetaExists', function() {
        it('should return true if meta has panels', function() {
            view.options = {
                meta: {
                    panels: [
                        {
                            newTab: true,
                        },
                    ],
                },
            };

            expect(view._isPanelMetaExists()).toBe(true);
        });
    });

    describe('_getCurrentModule', function() {
        it('should return current module as Contacts', function() {
            view.module = 'Contacts';
            view.model = new Backbone.Model();

            expect(view._getCurrentModule()).toBe('Contacts');
        });
    });

    describe('_preRequisite', function() {
        beforeEach(function() {
            sinon.stub(app.CJBaseHelper, 'getCJEnabledModules').returns([
                'Accounts', 'Contacts', 'Leads', 'Opportunities',
            ]);
            sinon.stub(app.logger, 'debug');
            sinon.stub(app.utils, 'formatString');
            sinon.stub(app.lang, 'get');
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should return true and should not call debug, formatString and get functions', function() {
            expect(view._preRequisite('Leads')).toBe(true);
            expect(app.CJBaseHelper.getCJEnabledModules).toHaveBeenCalled();
            expect(app.logger.debug).not.toHaveBeenCalled();
            expect(app.utils.formatString).not.toHaveBeenCalled();
            expect(app.lang.get).not.toHaveBeenCalled();
        });

        it('should return false and call debug, formatString and get functions', function() {
            expect(view._preRequisite('Notes')).toBe(false);
            expect(app.CJBaseHelper.getCJEnabledModules).toHaveBeenCalled();
            expect(app.logger.debug).toHaveBeenCalled();
            expect(app.utils.formatString).toHaveBeenCalled();
            expect(app.lang.get).toHaveBeenCalled();
        });
    });

    describe('_loadCJLayout', function() {
        beforeEach(function() {
            view.type = 'cj-as-a-panel';
            sinon.stub(view, '_preRequisite').returns(true);
            sinon.stub(view, '_getCurrentModule');
            sinon.stub(view, '_isRecordViewHasTabEnabled').returns(true);
            sinon.stub(view, '_isModelSynced').returns(true);
            sinon.stub(view, '_loadCjAsPanel');
            sinon.stub(view, '_loadCjAsTab');
            sinon.stub(view, 'render');
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should _loadCjAsPanel function and should not call _loadCjAsTab and render functions', function() {
            sinon.stub(app.CJBaseHelper, 'getCJRecordViewSettings').returns('panel_top');
            view._loadCJLayout();

            expect(app.CJBaseHelper.getCJRecordViewSettings).toHaveBeenCalled();
            expect(view._loadCjAsPanel).toHaveBeenCalled();
            expect(view._loadCjAsTab).not.toHaveBeenCalled();
            expect(view.render).not.toHaveBeenCalled();
        });

        it('should _loadCjAsTab and render functions and should not call _loadCjAsPanel function', function() {
            sinon.stub(app.CJBaseHelper, 'getCJRecordViewSettings').returns('tab_first');
            view._loadCJLayout();

            expect(app.CJBaseHelper.getCJRecordViewSettings).toHaveBeenCalled();
            expect(view._loadCjAsPanel).not.toHaveBeenCalled();
            expect(view._loadCjAsTab).toHaveBeenCalled();
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('_loadCjAsDashlet', function() {
        it('should call _preRequisite and createLayout functions', function() {
            let element = {
                length: 1,
                append: function() {},
            };
            let cj = {
                initComponents: function() {},
                loadData: function() {},
                context: new Backbone.Model(),
                render: sinon.stub(),
            };
            sinon.stub(app.user, 'hasAutomateLicense').returns(true);
            sinon.stub(app.view, 'createLayout').returns(cj);
            sinon.stub(view, '$').returns(element);
            sinon.stub(view, '_prepareContextForCJTab');
            sinon.stub(view, '_preRequisite').returns(true);
            sinon.stub(view, '_getCurrentModule');
            view._loadCjAsDashlet();

            expect(app.view.createLayout).toHaveBeenCalled();
            expect(view._preRequisite).toHaveBeenCalled();
            expect(view._CJ.render).toHaveBeenCalled();
            expect(view._CJ.context.get('moreLess')).toBe('more');
        });
    });

    describe('_isModelSynced', function() {
        it('should return true as dataFetched is set and inSync is unset', function() {
            view.model = new Backbone.Model();
            view.model.dataFetched = true;
            view.model.inSync = false;

            expect(view._isModelSynced()).toBe(true);
        });
    });

    describe('_isCJPanelMetaExists', function() {
        it('should call _isPanelMetaExists and return true', function() {
            sinon.stub(view, '_isPanelMetaExists').returns(true);
            view.options = {
                meta: {
                    panels: [
                        {
                            name: 'customer_journey_tab',
                        },
                    ],
                },
            };

            expect(view._isCJPanelMetaExists()).toBe(true);
            expect(view._isPanelMetaExists).toHaveBeenCalled();
        });
    });

    describe('_isCJPanelMetaExistsInExtraInfoLayout', function() {
        it('should return true as componenet has dri-workflow layout', function() {
            let components = [
                {
                    layout: 'dri-workflows',
                    view: 'dri-workflow',
                },
            ];

            expect(view._isCJPanelMetaExistsInExtraInfoLayout(components)).toBe(true);
        });
    });

    describe('_loadCjAsPanel', function() {
        beforeEach(function() {
            sinon.stub(view, '_loadCjAsPanelBottom');
            sinon.stub(view, '_loadCjAsPanelTop');
        });

        it('should call _loadCjAsPanelBottom and should not call _loadCjAsPanelTop function', function() {
            view.displaySetting = 'panel_bottom';

            view._loadCjAsPanel();
            expect(view._loadCjAsPanelTop).not.toHaveBeenCalled();
            expect(view._loadCjAsPanelBottom).toHaveBeenCalled();
        });

        it('should call _loadCjAsPanelTop and should not call _loadCjAsPanelBottom function', function() {
            view.displaySetting = 'panel_top';

            view._loadCjAsPanel();
            expect(view._loadCjAsPanelTop).toHaveBeenCalled();
            expect(view._loadCjAsPanelBottom).not.toHaveBeenCalled();

        });

        it('should not call _loadCjAsPanelTop and _loadCjAsPanelBottom functions', function() {
            view.disposed = true;

            view._loadCjAsPanel();
            expect(view._loadCjAsPanelBottom).not.toHaveBeenCalled();
            expect(view._loadCjAsPanelTop).not.toHaveBeenCalled();
        });
    });

    describe('_loadCjAsPanelBottom', function() {
        it('should call _getLayout, _getCJPanelLayoutMeta and _addPanelMetaInExtraInfoLayout functions', function() {
            let layout = {
                meta: {
                    components: [
                        {
                            layout: {
                                type: 'extra-info',
                            },
                        },
                    ],
                },
                getComponent: function() {
                    return {
                        render: function() {},
                    };
                }
            };
            sinon.stub(view, '_getLayout').returns(layout);
            sinon.stub(view, '_isCJPanelMetaExistsInExtraInfoLayout').returns(false);
            sinon.stub(view, '_getCJPanelLayoutMeta');
            sinon.stub(view, '_addPanelMetaInExtraInfoLayout');
            view._loadCjAsPanelBottom();

            expect(view._getLayout).toHaveBeenCalled();
            expect(view._getCJPanelLayoutMeta).toHaveBeenCalled();
            expect(view._addPanelMetaInExtraInfoLayout).toHaveBeenCalled();
        });
    });

    describe('_loadCjAsPanelTop', function() {
        it('should call render, _loadCJP, _appendCJEleInView and _removePanelHeaderDiv functions', function() {
            sinon.stub(view, 'render');
            sinon.stub(view, '_loadCJP');
            sinon.stub(view, '_appendCJEleInView');
            sinon.stub(view, '_removePanelHeaderDiv');
            view._loadCjAsPanelTop();

            expect(view.render).toHaveBeenCalled();
            expect(view._loadCJP).toHaveBeenCalled();
            expect(view._appendCJEleInView).toHaveBeenCalled();
            expect(view._removePanelHeaderDiv).toHaveBeenCalled();
        });
    });

    describe('_addPanelMetaInExtraInfoLayout', function() {
        it('should call _getCJPanelLayoutMeta and _getCurrentModule functions', function() {
            sinon.stub(view, '_getCJPanelLayoutMeta').returns([]);
            sinon.stub(view, '_getCurrentModule');
            let extraInfo = {
                meta: {
                    components: [
                        {
                            layout: 'dri-workflows',
                            view: 'dri-workflow',
                        },
                    ],
                },
                initComponents: function() {},
            };
            view._addPanelMetaInExtraInfoLayout(extraInfo, {});

            expect(view._getCJPanelLayoutMeta).toHaveBeenCalled();
            expect(view._getCurrentModule).toHaveBeenCalled();
        });
    });

    describe('_getCJPanelLayoutMeta', function() {
        it('returned object context should have dri_workflows link and true activeArchivedTrigger', function() {
            let response = view._getCJPanelLayoutMeta();

            expect(response.context.link).toEqual('dri_workflows');
            expect(response.context.activeArchivedTrigger).toEqual(true);
        });
    });

    describe('_getLayout', function() {
        it('should return dri-workflows layout', function() {
            view.options.layout = {
                name: 'dri-workflows',
                view: 'dri-workflow',
            };

            expect(view._getLayout()).toEqual({name: 'dri-workflows', view: 'dri-workflow'});
        });
    });

    describe('_loadCJP', function() {
        beforeEach(function() {
            view.model = new Backbone.Model();
            sinon.stub(view, '_prepareContextForCJTab');
            sinon.stub(view, '_getCurrentModule');
        });

        it('should call _prepareContextForCJTab, _getCurrentModule and createLayout functions', function() {
            let cj = {
                initComponents: function() {},
                loadData: function() {},
            };
            sinon.stub(app.view, 'createLayout').returns(cj);
            view._loadCJP();

            expect(view._prepareContextForCJTab).toHaveBeenCalled();
            expect(view._getCurrentModule).toHaveBeenCalled();
            expect(app.view.createLayout).toHaveBeenCalled();
        });

        it('should not call _prepareContextForCJTab and _getCurrentModule functions', function() {
            view._CJ = {view: 'dri-workflow'};
            view._loadCJP();

            expect(view._prepareContextForCJTab).not.toHaveBeenCalled();
            expect(view._getCurrentModule).not.toHaveBeenCalled();
        });
    });

    describe('_appendCJEleInView', function() {
        it('should call _getCJEleInView and _CJ render functions', function() {
            sinon.stub(view, '_getCJEleInView').callsFake(function() {
                return {
                    length: 1,
                    append: function() {},
                };
            });
            view._CJ = {
                context: {
                    set: function() {},
                },
                render: sinon.stub(),
            };
            view._appendCJEleInView();

            expect(view._getCJEleInView).toHaveBeenCalled();
            expect(view._CJ.render).toHaveBeenCalled();
        });
    });

    describe('_getCJEleInView', function() {
        beforeEach(function() {
            sinon.stub(view, '$').callsFake(function() {
                return {
                    find: function() {
                        return '<div></div>';
                    },
                };
            });
        });

        afterEach(function() {
            sinon.restore();
        });

        it('response should not be "<div></div>"', function() {
            view.displaySetting = 'tab-last';

            expect(view._getCJEleInView()).not.toEqual('<div></div>');
            expect(view.$).toHaveBeenCalled();
        });

        it('response should be "<div></div>"', function() {
            view.displaySetting = 'panel-top';

            expect(view._getCJEleInView()).toEqual('<div></div>');
            expect(view.$).toHaveBeenCalled();
        });
    });

    describe('_removePanelHeaderDiv', function() {
        it('should call _getCJEleInView and remove functions', function() {
            let panelHeader = {
                length: 1,
                parent: function() {
                    return {
                        find: function() {
                            return {
                                remove: function() {},
                            };
                        },
                    };
                },
            };
            sinon.stub(view, '_getCJEleInView').returns(panelHeader);
            view._removePanelHeaderDiv();

            expect(view._getCJEleInView).toHaveBeenCalled();
        });
    });

    describe('_loadCjAsTab', function() {
        it('should call _loadCJP and _appendCJEleInView functions', function() {
            sinon.stub(view, '_loadCJP');
            sinon.stub(view, '_appendCJEleInView');
            view._loadCjAsTab();

            expect(view._loadCJP).toHaveBeenCalled();
            expect(view._appendCJEleInView).toHaveBeenCalled();
        });
    });

    describe('_prepareContextForCJTab', function() {
        it('should call createBean, createRelatedBean and createRelatedCollection functions', function() {
            view.model = new Backbone.Model();
            sinon.stub(app.data, 'createBean');
            sinon.stub(app.data, 'createRelatedBean');
            sinon.stub(app.data, 'createRelatedCollection');
            sinon.stub(view.context, 'getChildContext').returns(new Backbone.Model());
            let response = view._prepareContextForCJTab();

            expect(app.data.createBean).toHaveBeenCalled();
            expect(app.data.createRelatedBean).toHaveBeenCalled();
            expect(app.data.createRelatedCollection).toHaveBeenCalled();
            expect(response.get('cjAsTab')).toBe(true);
            expect(response.get('moreLess')).toBe('more');
        });
    });

    describe('disposePlugin', function() {
        it('should call _CJ dispose function and _CJ and _CJTabName should be null', function() {
            view._CJ = {
                dispose: sinon.stub(),
            };
            view.disposePlugin();

            expect(view._CJ).toBe(null);
            expect(view._CJTabName).toBe(null);
        });
    });
});
