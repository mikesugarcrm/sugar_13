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
describe('Administration.Views.AwsConfig', function() {
    var app;
    var view;
    var viewName = 'aws-config';
    var moduleName = 'Administration';

    beforeEach(function() {
        app = SugarTest.app;
        sinon.stub(app.template, 'getView').returns(function() {
            return 'name1: text1';
        });
        sinon.stub(app.utils, 'updatePendoMetadata');
        var model = app.data.createBean(moduleName);
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName, moduleName);
        SugarTest.loadComponent('base', 'view', 'config', moduleName);
        SugarTest.testMetadata.set();
        app.data.declareModels();
        var endpointFieldMeta = {
            name: 'aws_login_url'
        };
        var apiGatewayFieldMeta = {
            name: 'aws_connect_api_gateway_url'
        };
        var contactFlowIdFieldMeta = {
            name: 'aws_connect_contact_flow_id'
        };
        var instanceIdFieldMeta = {
            name: 'aws_connect_instance_id'
        };
        var meta = {
            panels: [{
                fields: [endpointFieldMeta],
                name: 'panel_1'
            }, {
                fields: [apiGatewayFieldMeta, contactFlowIdFieldMeta, instanceIdFieldMeta],
                name: 'panel_2'
            }]
        };

        var context = app.context.getContext();
        context.set('model', model);
        context.set('category', 'aws');
        view = SugarTest.createView('base', moduleName, viewName, meta, context, true);
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        sinon.restore();
    });

    describe('general behaviour', function() {
        it('copy the settings to the model', function() {
            var settings = {
                aws_connect_region: 'us-west-1',
            };
            expect(view.model.get('aws_connect_region')).toEqual(undefined);
            view.copySettingsToModel(settings);
            expect(view.model.get('aws_connect_region')).toEqual('us-west-1');
        });

        it('should init a call for loading aws settings', function() {
            var callStub = sinon.stub(app.api, 'call');
            var buildStub = sinon.stub(app.api, 'buildURL');
            view.loadSettings();
            expect(callStub).toHaveBeenCalled();
            expect(buildStub).toHaveBeenCalledWith('Administration', 'config/aws');
        });

        it('should update app config with the saved values', function() {
            var settings = {
                aws_connect_region: 'us-west-1',
                aws_connect_instance_name: 'my_connect_instance_name'
            };
            var toggleStub = sinon.stub(view, 'toggleHeaderButton');
            var closeStub = sinon.stub(view, 'closeView');
            view.saveSuccessHandler(settings);
            expect(toggleStub).toHaveBeenCalled();
            expect(closeStub).toHaveBeenCalled();
            expect(app.config.awsConnectRegion).toEqual('us-west-1');
            expect(app.config.awsConnectInstanceName).toEqual('my_connect_instance_name');
        });
    });

    describe('_toggleEndpointField', function() {
        using('different identity providers', ['Connect', 'SAML'], function(provider) {
            it('should call _toggleFieldVisibility with appropriate arguments', function() {

                var idField = {
                    getFormattedValue: function() { return provider; }
                };
                var epField = {
                    def: {},
                    render: sinon.stub()
                };

                sinon.stub(view, 'getField')
                    .withArgs('aws_connect_identity_provider').returns(idField)
                    .withArgs('aws_login_url').returns(epField);
                sinon.stub(_, 'findWhere').returns({});
                sinon.stub(view, '_toggleFieldVisibility');

                view._toggleEndpointField();

                expect(epField.render).toHaveBeenCalled();
                expect(view._toggleFieldVisibility).toHaveBeenCalledWith(epField, provider !== 'Connect');
            });
        });
    });

    describe('_toggleChatSettings', function() {
        using('different identity providers', [false, true], function(chatEnabled) {
            it('should call _toggleChatSettings with appropriate arguments', function() {
                var toggleStub = sinon.stub();
                var findStub = sinon.stub().returns({
                    toggle: toggleStub
                });
                var closestStub = sinon.stub().returns({
                    find: findStub
                });

                var chatEnabledField = {
                    getFormattedValue: function() { return chatEnabled; },
                    $el: {
                        closest: closestStub
                    }
                };
                var apiGatewayField = {
                    def: {},
                    render: sinon.stub()
                };
                var ContactFlowIdField = {
                    def: {},
                    render: sinon.stub()
                };
                var instanceIdField = {
                    def: {},
                    render: sinon.stub()
                };

                sinon.stub(view, 'getField')
                    .withArgs('aws_connect_enable_portal_chat').returns(chatEnabledField)
                    .withArgs('aws_connect_api_gateway_url').returns(apiGatewayField)
                    .withArgs('aws_connect_contact_flow_id').returns(ContactFlowIdField)
                    .withArgs('aws_connect_instance_id').returns(instanceIdField);
                sinon.stub(_, 'findWhere').returns({});
                sinon.stub(view, '_toggleFieldVisibility');

                view._toggleChatSettings();

                expect(apiGatewayField.render).toHaveBeenCalled();
                expect(ContactFlowIdField.render).toHaveBeenCalled();
                expect(instanceIdField.render).toHaveBeenCalled();
                expect(view._toggleFieldVisibility).toHaveBeenCalledWith(apiGatewayField, chatEnabled);
                expect(view._toggleFieldVisibility).toHaveBeenCalledWith(ContactFlowIdField, chatEnabled);
                expect(view._toggleFieldVisibility).toHaveBeenCalledWith(instanceIdField, chatEnabled);
            });
        });
    });

    describe('help block', function() {
        var helpString;

        beforeEach(function() {
            helpString = 'name1: text1';
            view.meta.panels[0].helpLabels = [{
                name: 'name1',
                text: 'text1'
            }];
        });

        afterEach(function() {
            sinon.restore();
            view.helpBlock = {};
            view.meta.panels[0].helpLabels = [];
        });

        it('should generate help block', function() {
            sinon.stub(app.lang, 'get').returns('');
            expect(view.generateHelpBlock()).toEqual({panel_1: helpString});
        });

        it('should render help block', function() {
            var appendStub = sinon.stub();
            sinon.stub(view, '$').returns({
                append: appendStub
            });
            view.helpBlock = {
                panel_1: helpString
            };
            view.renderHelpBlock();
            expect(appendStub).toHaveBeenCalledWith(helpString);
        });
    });

    describe('updatePendoMetadata', function() {
        using('various settings', [
            {
                settings: {
                    aws_connect_instance_name: 'test-instance'
                },
                expected: {
                    aws_connect_instance_name: 'test-instance'
                }
            },{
                settings: {
                    aws_connect_url: 'test-url'
                },
                expected: {
                    aws_connect_url: 'test-url'
                }
            },{
                settings: {
                    aws_connect_instance_name: 'test-instance',
                    aws_connect_url: 'test-url',
                    something_else: 'this should be filtered out'
                },
                expected: {
                    aws_connect_instance_name: 'test-instance',
                    aws_connect_url: 'test-url'
                }
            },{
                settings: {
                    something_else: 'this should be filtered out',
                    another_nonessential_property: 'this should also be filtered out'
                },
                expected: {}
            },
        ], function(values) {
            it('should filter provided settings before updating pendo', function() {
                view.updatePendoMetadata(values.settings);
                expect(app.utils.updatePendoMetadata).toHaveBeenCalledWith({}, values.expected);
            });
        });

    });
});
