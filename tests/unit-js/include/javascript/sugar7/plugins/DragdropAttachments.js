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
describe("DragdropAttachments Plugin", function() {
    var plugin;

    beforeEach(function() {
        SugarTest.loadPlugin('DragdropAttachments');
        plugin = SugarTest.app.plugins.plugins.view.DragdropAttachments;
    });

    describe("_mapNoteParentAttributes", function() {
        var dataProvider = [
            {
                message: "Should add parent if parentId and parentType specified",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                        id: '123',
                        module: 'Accounts'
                    }
                },
                expectedAttributes: {
                    parent_id: '123',
                    parent_type: 'Accounts'
                }
            },
            {
                message: "Should add parent if parentType specified",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                        module: 'Accounts'
                    }
                },
                expectedAttributes: {
                    parent_type: 'Accounts'
                }
            },
            {
                message: "Should not add parent if parentType not specified",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                        id: '123'
                    }
                },
                expectedAttributes: {}
            },
            {
                message: "Should not add parent if neither parentId or parentType specified",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                    }
                },
                expectedAttributes: {}
            },
            {
                message: "Should add parent if parentType is specified as Home",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                        module: 'Home',
                        id: '123'
                    }
                },
                expectedAttributes: {
                    parent_type: 'Home'
                }
            },
            {
                message: "Should add parent if parentType is specified as Activities",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                        module: 'Activities'
                    }
                },
                expectedAttributes: {
                    parent_type: 'Activities'
                }
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                plugin.context = {};
                plugin.context.parent = SugarTest.app.context.getContext();
                plugin.context.parent.set(data.attributes);
                var actual = plugin._mapNoteParentAttributes();
                expect(actual).toEqual(data.expectedAttributes);
            });
        }, this);
    });
});
