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

describe("Sugar7 utils", function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.seedMetadata(true);
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) { eval(d) });
    });

    afterEach(function() {
        sinon.restore();
    });

    describe('convertCompToDashletView', function() {
        var comp;
        var resultExpected;

        beforeEach(function() {
            comp = {
                view: {
                    clientFileName: 'client.bundle.js',
                    customConfig: true,
                    dashletConfig: [{
                        'name': 'Sugar Discover',
                        'description': 'Display a Sugar Discover report',
                        'type': 'discover'
                    }],
                    description: 'This is Discover in a Dashlet',
                    env: {
                        SERVER_URL: 'http://localhost:9000'
                    },
                    layouts: [],
                    name: 'Discover',
                    scope: 'openid offline https://apis.sugarcrm.com/auth/crm',
                    src: 'http://localhost:8001/client.bundle.js',
                    srn: 'srn:stage:iam:us-east-2:9999999999:app:web:stargate-react-app-dev',
                    type: 'external-app',
                    version: '1.0.0'
                }
            };
            resultExpected = [{
                description: 'This is Discover in a Dashlet',
                metadata: {
                    component: 'external-app-dashlet',
                    config: {
                        customConfig: true,
                        src: comp.view.src,
                        dashletConfig: comp.view.dashletConfig,
                    },
                    customDashletMeta: comp,
                    description: comp.view.description,
                    label: comp.view.name,
                    dashletType: 'discover',
                    type: 'external-app-dashlet'
                },
                title: 'Discover',
                type: 'external-app-dashlet'
            }];
        });

        afterEach(function() {
            comp = null;
            resultExpected = null;
        });

        it('should build out the view metadata properly', function() {
            var result = app.utils.convertCompToDashletView(comp);

            expect(result).toEqual(resultExpected);
        });
    });

    describe('hideForecastCommitStageField()', function() {
        var options;
        beforeEach(function() {
            options = {
                panels: [
                    {
                        fields: [
                            {
                                name: 'commit_stage',
                                label: 'LBL_COMMIT_STAGE'
                            }
                        ]
                    }
                ]
            };
        });

        afterEach(function() {
            options = undefined;
        });
        it('should replace commit_stage with a spacer', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    is_setup: false
                };
            });
            app.utils.hideForecastCommitStageField(options.panels);
            expect(options.panels[0].fields[0]).toEqual(
                {name: 'spacer', label: 'LBL_COMMIT_STAGE', span: 6, readonly: true}
            );
            app.metadata.getModule.restore();
        });
    });

    describe("getSubpanelCollection()", function() {
        it("should return the proper subpanel collection", function() {
            var ctx = {};
            ctx.children = [];

            var mdl = new Backbone.Model(),
                targetMdl = new Backbone.Model();

            targetMdl.set({id: 'targetMdl'});
            mdl.set({module: 'Test'});

            var col = new Backbone.Collection();
            col.add(targetMdl);

            mdl.set({collection: col});
            ctx.children.push(mdl);

            var targetCol = app.utils.getSubpanelCollection(ctx, 'Test');

            expect(targetCol.models[0].get('id')).toEqual('targetMdl');
        });
    });
    
    describe('Handling iframe URLs', function() {
		
    	it('Add frame mark to URL', function() {
    		var withMark = app.utils.addIframeMark('/sugar7/index.php?module=Administration&action=Home'); 
    		expect(withMark).toBe('/sugar7/index.php?module=Administration&action=Home&bwcFrame=1');
    		withMark = app.utils.addIframeMark('/sugar7/index.php'); 
    		expect(withMark).toBe('/sugar7/index.php?bwcFrame=1');
    		withMark = app.utils.addIframeMark('/sugar7/index.php?bwcFrame=1'); 
    		expect(withMark).toBe('/sugar7/index.php?bwcFrame=1');
    	});
    	
    	it('Remove frame mark from URL', function() {
    		var noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&action=Home&bwcFrame=1');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?bwcFrame=1');
    		expect(noMark).toBe('/sugar7/index.php?'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&bwcFrame=1&action=Home');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&action=Home');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php');
    		expect(noMark).toBe('/sugar7/index.php'); 
    	});
    });

    describe('getRecordName', function() {
        var model;
        beforeEach(function() {
            model = new Backbone.Model();
        });
        it('should get document_name for Documents module', function() {
            model.module = 'Documents';
            model.set({
                document_name: 'Awesome Document',
                name: 'document.zip'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Document');
        });
        it('get full_name when available', function() {
            model.module = 'Contacts';
            model.set({
                full_name: 'Awesome Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Name');
        });
        it('build full name based on first name and last name', function() {
            model.module = 'Contacts';
            model.set({
                first_name: 'Awesome',
                last_name: 'Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Name');
        });
        it('get name otherwise', function() {
            model.module = 'Leads';
            model.set({
                name: 'Simple Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Simple Name');
        });

        it('should return the last name', function() {
            model.module = 'Contacts';
            model.set('last_name', 'Name');
            expect(app.utils.getRecordName(model)).toEqual('Name');
        });
    });

    describe('marking a record name as erased', function() {
        using('name format model combinations ', [
                //Normal case
                {
                    userFormat: 's f l',
                    formatMap: {
                        s: 'salutation',
                        f: 'first_name',
                        l: 'last_name',
                        t: 'title'
                    },
                    erased_fields: ['first_name', 'last_name', 'salutation'],
                    attributes: {},
                    expected: true
                },
                //Not all fields erased, but model is empty
                {
                    userFormat: 's f l',
                    formatMap: {
                        s: 'salutation',
                        f: 'first_name',
                        l: 'last_name',
                        t: 'title'
                    },
                    erased_fields: ['last_name', 'salutation'],
                    attributes: {},
                    expected: true
                },
                //All fields marked erased, but not empty
                {
                    userFormat: 's f l',
                    formatMap: {
                        s: 'salutation',
                        f: 'first_name',
                        l: 'last_name',
                        t: 'title'
                    },
                    erased_fields: ['first_name', 'last_name', 'salutation', 'title'],
                    attributes: {'last_name': 'foo'},
                    expected: false
                },
                //All fields empty, but none erased (title excluded via userFormat)
                {
                    userFormat: 's f l',
                    formatMap: {
                        s: 'salutation',
                        f: 'first_name',
                        l: 'last_name',
                        t: 'title'
                    },
                    erased_fields: ['title'],
                    attributes: {},
                    expected: false
                },
                //remapped field
                {
                    userFormat: 's f l',
                    formatMap: {
                        s: 'other_field',
                        f: 'first_name',
                        l: 'last_name',
                        t: 'title'
                    },
                    erased_fields: ['first_name', 'last_name', 'salutation'],
                    attributes: {other_field: 'something'},
                    expected: false
                },
                //all fields in shorter nameFormat erased
                {
                    userFormat: 'l',
                    formatMap: {
                        s: 'other_field',
                        f: 'first_name',
                        l: 'last_name',
                        t: 'title'
                    },
                    erased_fields: ['last_name'],
                    expected: true
                },
                //components are empty, but the name itself is populated (somehow)
                {
                    userFormat: 'l',
                    formatMap: {
                        s: 'other_field',
                        f: 'first_name',
                        l: 'last_name',
                        t: 'title'
                    },
                    erased_fields: ['last_name'],
                    attributes: {name: 'something'},
                    expected: false
                },
                //Document type record
                {
                    userFormat: 's f l',
                    erased_fields: ['document_name'],
                    fields: {name: {fields: ['document_name']}},
                    expected: true
                },
                //Document with name but not document name erased
                {
                    userFormat: 's f l',
                    erased_fields: ['name'],
                    fields: {name: {fields: ['document_name']}},
                    expected: false
                },
                //Non-person type record
                {
                    userFormat: 's f l',
                    erased_fields: ['name'],
                    fields: {name: {type: 'varchar'}},
                    expected: true
                },
                {
                    userFormat: 's f l',
                    erased_fields: ['not_name'],
                    fields: {name: {type: 'varchar'}},
                    expected: false
                }
            ],
            function(args) {
                it('should leverage the name format to determine what parts have been erased', function() {
                    var model = app.data.createBean('Contacts', args.attributes || {});
                    model.fields = args.fields || {
                        module: 'Contacts',
                        name: {
                            'type': 'fullname'
                        }
                    };
                    if (args.erased_fields) {
                        model.set({_erased_fields: args.erased_fields});
                    }
                    var userMock = sinon.stub(app.user, 'getPreference').callsFake(function() {
                        return args.userFormat;
                    });
                    var metamock = sinon.stub(app.metadata, 'getModule').callsFake(function() {
                        return {nameFormat: args.formatMap};
                    });

                    var result = app.utils.isNameErased(model);
                    expect(result).toEqual(args.expected);

                    metamock.restore();
                    userMock.restore();
                });
            }
        );
    });

    describe('email addresses', function() {
        var combos,
            model;

        combos = {
            primary_valid: {
                email_address: 'primary@valid.com',
                primary_address: true,
                invalid_email: false,
                opt_out: false
            },
            primary_invalid: {
                email_address: 'primary@invalid.com',
                primary_address: true,
                invalid_email: true,
                opt_out: false
            },
            primary_opted_out: {
                email_address: 'primary@optout.com',
                primary_address: true,
                invalid_email: false,
                opt_out: true
            },
            primary_bad: {
                email_address: 'primary@bad.com',
                primary_address: true,
                invalid_email: true,
                opt_out: true
            },
            valid: {
                email_address: 'is@valid.com',
                primary_address: false,
                invalid_email: false,
                opt_out: false
            },
            invalid: {
                email_address: 'is@invalid.com',
                primary_address: false,
                invalid_email: true,
                opt_out: false
            },
            opted_out: {
                email_address: 'is@optout.com',
                primary_address: false,
                invalid_email: false,
                opt_out: true
            },
            bad: {
                email_address: 'is@bad.com',
                primary_address: false,
                invalid_email: true,
                opt_out: true
            }
        };

        beforeEach(function() {
            model = new Backbone.Model();
        });

        using('getEmailAddress', [
            [
                [combos.primary_valid, combos.valid],
                {primary_address: true},
                combos.primary_valid.email_address
            ],
            [
                [combos.valid, combos.primary_valid],
                undefined,
                combos.valid.email_address
            ],
            [
                [combos.primary_invalid, combos.invalid],
                {invalid_email: true},
                combos.primary_invalid.email_address
            ],
            [
                [combos.primary_valid, combos.valid],
                {invalid_email: true},
                ''
            ],
            [
                [combos.primary_valid, combos.valid],
                {opt_out: true},
                ''
            ],
            [
                [combos.valid, combos.invalid],
                {invalid_email: true},
                combos.invalid.email_address
            ],
            [
                [combos.valid, combos.opted_out],
                {opt_out: true},
                combos.opted_out.email_address
            ],
            [
                [combos.valid, combos.invalid, combos.opted_out, combos.bad],
                {invalid_email: true, opt_out: true},
                combos.bad.email_address
            ],
            [
                [combos.bad, combos.valid, combos.invalid, combos.primary_bad, combos.opted_out],
                {primary_address: true, invalid_email: true, opt_out: true},
                combos.primary_bad.email_address
            ]
        ], function(emails, options, expected) {
            it('should return ' + expected, function() {
                model.set('email', emails);
                expect(app.utils.getEmailAddress(model, options)).toEqual(expected);
            });
        });

        using('getPrimaryEmailAddress', [
            [[combos.primary_valid, combos.valid], combos.primary_valid.email_address],
            [[combos.primary_valid, combos.invalid], combos.primary_valid.email_address],
            [[combos.primary_valid, combos.opted_out], combos.primary_valid.email_address],
            [[combos.primary_valid, combos.bad], combos.primary_valid.email_address],
            [[combos.primary_invalid, combos.valid], combos.valid.email_address],
            [[combos.primary_invalid, combos.invalid], ''],
            [[combos.primary_invalid, combos.opted_out], combos.opted_out.email_address],
            [[combos.primary_invalid, combos.bad], ''],
            [[combos.primary_opted_out, combos.valid], combos.primary_opted_out.email_address],
            [[combos.primary_opted_out, combos.invalid], combos.primary_opted_out.email_address],
            [[combos.primary_opted_out, combos.opted_out], combos.primary_opted_out.email_address],
            [[combos.primary_opted_out, combos.bad], combos.primary_opted_out.email_address],
            [[combos.primary_bad, combos.valid], combos.valid.email_address],
            [[combos.primary_bad, combos.invalid], ''],
            [[combos.primary_bad, combos.opted_out], combos.opted_out.email_address],
            [[combos.primary_bad, combos.bad], ''],
            [[combos.valid, combos.invalid], combos.valid.email_address],
            [[combos.valid, combos.opted_out], combos.valid.email_address],
            [[combos.valid, combos.bad], combos.valid.email_address],
            [[combos.invalid, combos.opted_out], combos.opted_out.email_address],
            [[combos.invalid, combos.bad], ''],
            [[combos.opted_out, combos.bad], combos.opted_out.email_address]
        ], function(emails, expected) {
            it('should return ' + expected, function() {
                model.set('email', emails);
                expect(app.utils.getPrimaryEmailAddress(model)).toEqual(expected);
            });
        });
    });

    var name = 'module';
    using('query strings',
        [
            ['?module=asdf', 'asdf'],
            ['?asdf=asdf&module=asdf&module=zxcv', 'zxcv'],
            ['?asdf=asdf&module=zxcv&modtrwer=zxcv', 'zxcv'],
            ['?xcvb=asdf&asdf=asdf&ryuit=zxcv', '']
        ],
        function (value, result) {
            it('should be able to get parameters', function () {
                var testResult = app.utils.getWindowLocationParameterByName(name, value);
                expect(result).toEqual(testResult);
            });
        });

    describe('getSelectedUsersReportees', function() {
        describe('as manager', function() {
            var user;
            beforeEach(function() {
                user = {
                    is_manager: true,
                    id: 'test_id'
                };
            });

            afterEach(function() {
                delete user;
            });

            it('will make an xhr call with status equal to active', function() {
                var post_args = undefined;
                sinon.stub(app.api, 'call').callsFake(function(type, url, args) {
                    post_args = args;
                });
                app.utils.getSelectedUsersReportees(user, {});
                expect(app.api.call).toHaveBeenCalled();
                expect(post_args).not.toBeUndefined();
                expect(post_args.filter[0].status).toEqual('Active');
            });
        });
    });

    describe('getArrowDirectionSpan', function() {
        it('should return a properly styled i tag', function() {
            var expectedHtml = '&nbsp;<i class="sicon sicon-arrow-up font-green"></i>';
            expect(app.utils.getArrowDirectionSpan('LBL_UP')).toEqual(expectedHtml);
            expectedHtml = '&nbsp;<i class="sicon sicon-arrow-down font-red"></i>';
            expect(app.utils.getArrowDirectionSpan('LBL_DOWN')).toEqual(expectedHtml);
            expect(app.utils.getArrowDirectionSpan('anything else')).toEqual('');
        });
    });

    describe('getDifference', function() {
        beforeEach(function() {
            this.newModel = app.data.createBean('MyModule');
            this.oldModel = app.data.createBean('MyModule');
            this.isDifferentWithPrecisionStub = sinon.stub(app.math, 'isDifferentWithPrecision');
            this.getDifferenceStub = sinon.stub(app.math, 'getDifference');
        });

        afterEach(function() {
            this.isDifferentWithPrecisionStub.restore();
            this.getDifferenceStub.restore();
        });

        it('should return the difference in the attributes on the models', function() {
            this.isDifferentWithPrecisionStub.returns(true);
            this.getDifferenceStub.returns('2');
            expect(app.utils.getDifference(this.oldModel, this.newModel, 'myAttr')).toEqual('2');
        });

        it('should return 0 if there is no difference', function() {
            this.isDifferentWithPrecisionStub.returns(false);
            expect(app.utils.getDifference(this.oldModel, this.newModel, 'sameAttr')).toEqual(0);
        });
    });

    describe('getDirection', function() {
        it('should return the proper direction label', function() {
            expect(app.utils.getDirection(5)).toEqual('LBL_UP');
            expect(app.utils.getDirection(-2)).toEqual('LBL_DOWN');
            expect(app.utils.getDirection(0)).toEqual('');
        });
    });

    describe('isTruthy', function() {
        it('should determine if a value is truthy in the SugarCRM sense', function() {
            expect(app.utils.isTruthy(true)).toBeTruthy();
            expect(app.utils.isTruthy('true')).toBeTruthy();
            expect(app.utils.isTruthy(1)).toBeTruthy();
            expect(app.utils.isTruthy('1')).toBeTruthy();
            expect(app.utils.isTruthy('on')).toBeTruthy();
            expect(app.utils.isTruthy('yes')).toBeTruthy();
            expect(app.utils.isTruthy('no')).not.toBeTruthy();
        });

        it('should accept uppercase truthy strings', function() {
            expect(app.utils.isTruthy('YES')).toBeTruthy();
        });
    });

    describe('getReadableFileSize', function() {
        using('file sizes',
            [
                [undefined, '0K'],
                [null, '0K'],
                ['', '0K'],
                [0, '0K'],
                [1, '1K'],
                [999, '1K'],
                [2000, '2K'],
                [999999, '1M'],
                [1000000, '1M'],
                [1500000, '2M'],
                [1073741824, '1G'],
                [1099511627776, '1T'],
                [10000000000000000, '10000T']
            ],
            function(rawSize, readableSize) {
                it('should convert the file size to a readable format', function() {
                    var actual = app.utils.getReadableFileSize(rawSize);
                    expect(actual).toEqual(readableSize);
                });
            });
    });

    describe('creating an email', function() {
        beforeEach(function() {
            var metadata = SugarTest.loadFixture('emails-metadata');

            SugarTest.testMetadata.init();

            _.each(metadata.modules, function(def, module) {
                SugarTest.testMetadata.updateModuleMetadata(module, def);
            });

            SugarTest.testMetadata.set();

            app.data.declareModels();
            app.routing.start();

            app.drawer = {
                open: sinon.stub()
            };
        });

        afterEach(function() {
            delete app.drawer;
        });

        using('layouts', ['create', 'compose-email'], function(layout) {
            it('should load the specified layout when opening the drawer', function() {
                app.utils.openEmailCreateDrawer(layout);

                expect(app.drawer.open).toHaveBeenCalledOnce();
                expect(app.drawer.open.firstCall.args[0].layout).toBe(layout);
            });
        });

        it('should open the drawer with an Emails create context', function() {
            app.utils.openEmailCreateDrawer('compose-email');

            expect(app.drawer.open).toHaveBeenCalledOnce();
            expect(app.drawer.open.firstCall.args[0].context.create).toBe(true);
            expect(app.drawer.open.firstCall.args[0].context.module).toBe('Emails');
            expect(app.drawer.open.firstCall.args[0].context.model.module).toBe('Emails');
        });

        describe('populating the model', function() {
            it('should use the model if one is provided', function() {
                var model;
                var email = app.data.createBean('Emails');

                app.utils.openEmailCreateDrawer('compose-email', {model: email});
                model = app.drawer.open.firstCall.args[0].context.model;

                expect(model).toBe(email);
            });

            using('recipients fields', ['', '_collection'], function(suffix) {
                it('should add recipients if to, cc, or bcc value is passed in', function() {
                    var model;
                    var data = {};

                    data['to' + suffix] = [
                        app.data.createBean('Contacts', {
                            id: _.uniqueId(),
                            email: 'to@foo.com'
                        }),
                        app.data.createBean('Contacts', {
                            id: _.uniqueId(),
                            email: 'too@foo.com'
                        })
                    ];
                    data['cc' + suffix] = [
                        app.data.createBean('Contacts', {
                            id: _.uniqueId(),
                            email: 'cc@foo.com'
                        })
                    ];
                    data['bcc' + suffix] = [
                        app.data.createBean('Contacts', {
                            id: _.uniqueId(),
                            email: 'bcc@foo.com'
                        })
                    ];

                    app.utils.openEmailCreateDrawer('compose-email', data);
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(model.get('to_collection').length).toBe(2);
                    expect(model.get('cc_collection').length).toBe(1);
                    expect(model.get('bcc_collection').length).toBe(1);
                });
            });

            using('attachments fields', ['attachments', 'attachments_collection'], function(fieldName) {
                it('should add attachments from ' + fieldName, function() {
                    var model;
                    var data = {};

                    data[fieldName] = [
                        app.data.createBean('Notes', {
                            id: _.uniqueId(),
                            name: 'attachment 1'
                        }),
                        app.data.createBean('Notes', {
                            id: _.uniqueId(),
                            name: 'attachment 2'
                        })
                    ];

                    app.utils.openEmailCreateDrawer('compose-email', data);
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(model.get('attachments_collection').length).toBe(2);
                });
            });

            using('attributes', ['name', 'description_html', 'reply_to_id'], function(fieldName) {
                it('should set standard attributes', function() {
                    var model;
                    var data = {};

                    data[fieldName] = 'foo';

                    app.utils.openEmailCreateDrawer('compose-email', data);
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(model.get(fieldName)).toBe('foo');
                });
            });

            using('non-fields', [
                [
                    'signature_location',
                    'above'
                ],
                [
                    'foo',
                    'bar'
                ]
            ], function(fieldName, value) {
                it('should pass non-fields to be set as attributes on the context', function() {
                    var context;
                    var data = {};

                    data[fieldName] = value;

                    app.utils.openEmailCreateDrawer('compose-email', data);
                    context = app.drawer.open.firstCall.args[0].context;

                    expect(context.model.get(fieldName)).toBeUndefined();
                    expect(context[fieldName]).toBe(value);
                });
            });

            using('static options', [
                [
                    'create',
                    false,
                    true
                ],
                [
                    'module',
                    'Notes',
                    'Emails'
                ]
            ], function(option, value, expected) {
                it('should not allow some options to be overridden by the caller', function() {
                    var context;
                    var data = {};

                    data[option] = value;

                    app.utils.openEmailCreateDrawer('compose-email', data);
                    context = app.drawer.open.firstCall.args[0].context;

                    expect(context.model.get(option)).toBeUndefined();
                    expect(context[option]).toBe(expected);
                });
            });

            describe('populating the related fields', function() {
                beforeEach(function() {
                    sinon.stub(app.acl, 'hasAccess').withArgs('list').returns(true);
                });

                it('should set the parent attributes without fetching the name of the related record', function() {
                    var model;
                    var contact = app.data.createBean('Contacts', {
                        id: _.uniqueId(),
                        name: 'Bob Tillman'
                    });

                    app.utils.openEmailCreateDrawer('compose-email', {related: contact});
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(model.get('parent_type')).toBe('Contacts');
                    expect(model.get('parent_id')).toBe(contact.get('id'));
                    expect(model.get('parent_name')).toBe('Bob Tillman');
                });

                it('should compute the name and set the parent attributes', function() {
                    var model;
                    var contact = app.data.createBean('Contacts', {
                        id: _.uniqueId(),
                        first_name: 'Bob',
                        last_name: 'Tillman'
                    });

                    app.utils.openEmailCreateDrawer('compose-email', {related: contact});
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(model.get('parent_type')).toBe('Contacts');
                    expect(model.get('parent_id')).toBe(contact.get('id'));
                    expect(model.get('parent_name')).toBe('Bob Tillman');
                });

                it('should set the parent attributes after fetching the name of the related record', function() {
                    var model;
                    var contact = app.data.createBean('Contacts', {id: _.uniqueId()});

                    sinon.stub(contact, 'fetch').callsFake(function(params) {
                        contact.set('name', 'Torry Young');
                        params.success(contact);
                    });

                    app.utils.openEmailCreateDrawer('compose-email', {related: contact});
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(model.get('parent_type')).toBe('Contacts');
                    expect(model.get('parent_id')).toBe(contact.get('id'));
                    expect(model.get('parent_name')).toBe('Torry Young');
                });

                it('should not set the parent attributes when there is no ID for the related record', function() {
                    var model;
                    var contact = app.data.createBean('Contacts', {
                        name: 'Andy Hopkins'
                    });
                    sinon.spy(contact, 'fetch');

                    app.utils.openEmailCreateDrawer('compose-email', {related: contact});
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(contact.fetch).not.toHaveBeenCalled();
                    expect(model.get('parent_type')).toBeUndefined();
                    expect(model.get('parent_id')).toBeUndefined();
                    expect(model.get('parent_name')).toBeUndefined();
                });

                describe('populating for a related case', function() {
                    var aCase;
                    var relatedCollection;

                    beforeEach(function() {
                        sinon.stub(app.metadata, 'getConfig').returns({'inboundEmailCaseSubjectMacro': '[CASE:%1]'});

                        aCase = app.data.createBean('Cases', {
                            id: _.uniqueId(),
                            case_number: '100',
                            name: 'My Case'
                        });

                        relatedCollection = app.data.createBeanCollection('Contacts');
                        sinon.stub(relatedCollection, 'fetch').callsFake(function(params) {
                            params.success(relatedCollection);
                        });

                        sinon.stub(aCase, 'getRelatedCollection').returns(relatedCollection);
                    });

                    it('should set only the subject and when the case does not have any related contacts', function() {
                        var model;

                        app.utils.openEmailCreateDrawer('compose-email', {related: aCase});
                        model = app.drawer.open.firstCall.args[0].context.model;

                        expect(model.get('parent_type')).toBe('Cases');
                        expect(model.get('parent_id')).toBe(aCase.get('id'));
                        expect(model.get('parent_name')).toBe('My Case');
                        expect(model.get('name')).toBe('[CASE:100] My Case');
                        expect(model.get('to_collection').length).toBe(0);
                    });

                    it('should populate the subject and "to" field when the case has related contacts', function() {
                        var model;

                        relatedCollection.add([
                            app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                name: 'Jaime Hammonds'
                            }),
                            app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                name: 'Frank Upton'
                            })
                        ]);

                        app.utils.openEmailCreateDrawer('compose-email', {related: aCase});
                        model = app.drawer.open.firstCall.args[0].context.model;

                        expect(model.get('parent_type')).toBe('Cases');
                        expect(model.get('parent_id')).toBe(aCase.get('id'));
                        expect(model.get('parent_name')).toBe('My Case');
                        expect(model.get('name')).toBe('[CASE:100] My Case');
                        expect(model.get('to_collection').length).toBe(2);
                    });

                    it('should not add to the "to" field when the field already has recipients', function() {
                        var model;
                        var email = app.data.createBean('Emails');

                        relatedCollection.add([
                            app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                name: 'Jaime Hammonds'
                            }),
                            app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                name: 'Frank Upton'
                            })
                        ]);
                        email.get('to_collection').add([
                            app.data.createBean('Leads', {
                                id: _.uniqueId(),
                                name: 'Nancy Rollins'
                            })
                        ]);

                        app.utils.openEmailCreateDrawer('compose-email', {
                            model: email,
                            related: aCase
                        });
                        model = app.drawer.open.firstCall.args[0].context.model;

                        expect(model.get('parent_type')).toBe('Cases');
                        expect(model.get('parent_id')).toBe(aCase.get('id'));
                        expect(model.get('parent_name')).toBe('My Case');
                        expect(model.get('name')).toBe('[CASE:100] My Case');
                        expect(model.get('to_collection').length).toBe(1);
                    });

                    it('should not prepopulate the email with case data', function() {
                        var model;
                        var email = app.data.createBean('Emails');

                        relatedCollection.add([
                            app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                name: 'Jaime Hammonds'
                            })
                        ]);

                        app.utils.openEmailCreateDrawer('compose-email', {
                            related: aCase,
                            skip_prepopulate_with_case: true
                        });
                        model = app.drawer.open.firstCall.args[0].context.model;

                        expect(model.get('parent_type')).toBe('Cases');
                        expect(model.get('parent_id')).toBe(aCase.get('id'));
                        expect(model.get('parent_name')).toBe('My Case');
                        expect(model.get('name')).toBeUndefined();
                        expect(email.get('to_collection').add).not.toHaveBeenCalled();
                    });
                });
            });
        });
    });

    describe('getting the names of all links between two modules', function() {
        beforeEach(function() {
            var metadata = SugarTest.loadFixture('emails-metadata');

            SugarTest.testMetadata.init();

            _.each(metadata.modules, function(def, module) {
                SugarTest.testMetadata.updateModuleMetadata(module, def);
            });

            SugarTest.testMetadata.set();

            app.data.declareModels();
            app.routing.start();
        });

        it('should only return links between Contacts and Emails', function() {
            var links = app.utils.getLinksBetweenModules('Contacts', 'Emails');
            var linkNames = _.pluck(links, 'name');

            expect(links.length).toBe(3);
            expect(linkNames).toEqual([
                'emails',
                'archived_emails',
                'contacts_activities_1_emails'
            ]);
        });
    });

    describe('validating password', function() {
        var oldPasswordSetting;

        beforeEach(function() {
            oldPasswordSetting = app.config.passwordsetting || {};
            app.config.passwordsetting = {
                'minpwdlength': 6,
                'maxpwdlength': 0,
                'oneupper': true,
                'onelower': true,
                'onenumber': true,
                'onespecial': true,
            };
        });

        afterEach(function() {
            app.config.passwordsetting = oldPasswordSetting;
        });

        using('passwords',
            [
                ['asdf', false],
                ['123456', false],
                ['123Abc', false],
                ['Mypass&123', true],
                ['=-123abC', true]
            ],
            function(password, result) {
                it('should be able to get validated', function() {
                    var testResult = app.utils.validatePassword(password, result);
                    expect(result).toEqual(testResult.isValid);
                });
            });
    });
    describe('create User SRN', function() {
        var oldTenant;
        beforeEach(function() {
            oldTenant = app.config.tenant || '';
        });

        afterEach(function() {
            app.config.tenant = oldTenant;
        });

        using('srn',
            [
                [
                    'srn:dev:iam:na:1396243377:tenant',
                    '123',
                    'srn:dev:iam::1396243377:user:123'
                ],
                [
                    'srn:dev:iam:na:1111111111:tenant',
                    '11111111-1111-1111-1111-11111111',
                    'srn:dev:iam::1111111111:user:11111111-1111-1111-1111-11111111'
                ]
            ],
            function(tenant, uId, expectedSRN) {
                it('should be created user SRN', function() {
                    app.config.tenant = tenant;
                    expect(app.utils.createUserSrn(uId)).toEqual(expectedSRN);
                });
            });
    });

    describe('getFieldLabels', function() {
        it('retrieves the labels for the fields that are searchable in the quicksearch', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    fields: {
                        first_name: {
                            vname: 'lbl_first_name'
                        },
                        last_name: {
                            vname: 'lbl_last_name'
                        }
                    }
                };
            });
            expect(app.utils.getFieldLabels('Cases', ['first_name', 'last_name']))
                .toEqual(['lbl_first_name', 'lbl_last_name']);
        });
    });

    describe('setIDMEditableFields', function() {
        let fields;
        let getModule;
        beforeEach(function() {
            fields = [
                {'name': 'foo'},
                {'name': 'bar'},
                {'name': 'email'},
                {'name': 'UserType'},
                {'name': 'title'},
                {'name': 'address', 'idm_mode_disabled': true}
            ];
            getModule = {
                'foo': {'idm_mode_disabled': true},
                'email': {'idm_mode_disabled': true},
                'title': {'idm_mode_disabled': true}
            };
        });

        afterEach(function() {
            options = undefined;
        });

        it('should set editability for fields for IDM enabled and record tpl', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return getModule;
            });
            app.config.idmModeEnabled = true;
            app.utils.setIDMEditableFields(fields, 'record');
            expect(fields).toEqual(
                [
                    {'name': 'foo', 'readonly': true},
                    {'name': 'bar'},
                    {'name': 'email'},
                    {'name': 'UserType', 'readonly': true},
                    {'name': 'title', 'readonly': true},
                    {'name': 'address', 'idm_mode_disabled': true, 'readonly': true}
                ]
            );
        });

        it('should set editability for fields for IDM enabled and recordlist tpl', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return getModule;
            });
            app.config.idmModeEnabled = true;
            app.utils.setIDMEditableFields(fields, 'recordlist');
            expect(fields).toEqual(
                [
                    {'name': 'foo', 'readonly': true},
                    {'name': 'bar'},
                    {'name': 'email', 'readonly': true},
                    {'name': 'UserType', 'readonly': true},
                    {'name': 'title', 'readonly': true},
                    {'name': 'address', 'idm_mode_disabled': true, 'readonly': true}
                ]
            );
        });

        it('should set editability for fields for IDM disabled for Admin & Developer', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return getModule;
            });
            sinon.stub(app.user, 'getAcls').callsFake(function() {
                return {
                    'Metrics': {},
                };
            });
            app.config.idmModeEnabled = false;
            app.utils.setIDMEditableFields(fields, 'record');
            expect(fields).toEqual(
                [
                    {'name': 'foo'},
                    {'name': 'bar'},
                    {'name': 'email'},
                    {'name': 'UserType', 'readonly': true},
                    {'name': 'title'},
                    {'name': 'address', 'idm_mode_disabled': true}
                ]
            );
        });

        it('should set editability for fields for IDM disabled for regular user', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return getModule;
            });
            sinon.stub(app.user, 'getAcls').callsFake(function() {
                return {
                    'Metrics': {'admin': 'foo', 'developer': 'bar'},
                };
            });
            app.config.idmModeEnabled = false;
            app.utils.setIDMEditableFields(fields, 'record');
            expect(fields).toEqual(
                [
                    {'name': 'foo'},
                    {'name': 'bar'},
                    {'name': 'email'},
                    {'name': 'UserType', 'readonly': true},
                    {'name': 'title', 'readonly': true},
                    {'name': 'address', 'idm_mode_disabled': true}
                ]
            );
        });
    });


    describe('isRliFieldValidForCascade', () => {
        using('different values for service', [
            [true, true],
            [false, false],
            ['', false]
        ], (service, expected) => {
            it('should check if service_start_date is valid to cascade', () => {
                let model = {
                    get: () => service
                };

                expect(app.utils.isRliFieldValidForCascade(model, 'service_start_date')).toBe(expected);
            });
        });

        using('different values for service, add_on_to_id, and lock_duration', [
            [
                {
                    service: false,
                    add_on_to_id: '',
                    lock_duration: false
                },
                false
            ],
            [
                {
                    service: true,
                    add_on_to_id: '',
                    lock_duration: false
                },
                true
            ],
            [
                {
                    service: true,
                    add_on_to_id: 'my_add_on_id',
                    lock_duration: false
                },
                false
            ],
            [
                {
                    service: true,
                    add_on_to_id: 'my_add_on_id',
                    lock_duration: true
                },
                false
            ],
            [
                {
                    service: true,
                    add_on_to_id: '',
                    lock_duration: true
                },
                false
            ]
        ], (values, expected) => {
            it('should check if service_duration_value and service_duration_unit are valid to cascade', () => {
                let model = {
                    get: field => values[field]
                };

                expect(app.utils.isRliFieldValidForCascade(model, 'service_duration_value')).toBe(expected);
                expect(app.utils.isRliFieldValidForCascade(model, 'service_duration_unit')).toBe(expected);
            });
        });

        using('different values for sales_stage', [
            [
                {
                    sales_stage: 'Prospecting'
                },
                true,
                true
            ],
            [
                {
                    sales_stage: 'Qualification'
                },
                false,
                false
            ],
            [
                {
                    sales_stage: 'Closed Won'
                },
                true,
                false
            ],
            [
                {
                    sales_stage: 'Closed Lost'
                },
                true,
                false
            ]
        ], (values, hasForecastsAccess, expected) => {
            it('should check if commit_stage is valid to cascade', () => {
                let model = {
                    get: field => values[field]
                };

                sinon.stub(app.metadata, 'getModule').returns({
                    sales_stage_won: ['Closed Won'],
                    sales_stage_lost: ['Closed Lost']
                });
                sinon.stub(app.acl, 'hasAccess').returns(hasForecastsAccess);

                expect(app.utils.isRliFieldValidForCascade(model, 'commit_stage')).toBe(expected);
                if (hasForecastsAccess) {
                    expect(app.metadata.getModule).toHaveBeenCalled();
                } else {
                    expect(app.metadata.getModule).not.toHaveBeenCalled();
                }
            });
        });

        using('cascadable fields with no special requirements', [
            'date_closed', 'sales_stage'
        ], fieldName => {
            it('should allow cascading to the field', () => {
                let model = {
                    get: _.noop
                };

                expect(app.utils.isRliFieldValidForCascade(model, fieldName)).toBe(true);
            });
        });
    });

});
