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
describe('Base.Fields.Source', function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField({
            name: 'source',
            type: 'source',
        });
    });

    afterEach(function() {
        sinon.restore();
    });

    describe('format', function() {
        it('should return a blank string if no trusted source available', function() {
            // Return empty string on having source null
            expect(field.format(null)).toEqual('');
            // Return empty string on having source empty
            expect(field.format({})).toEqual('');
        });

        it('should return the id if no label and no name are found', function() {
            sinon.stub(app.lang, 'get')
                .withArgs('LBL_AUDIT_SUBJECT_DUMMYTESTCASE')
                .returns('LBL_AUDIT_SUBJECT_DUMMYTESTCASE');
            expect(field.format({
                subject: {
                    first_name: 'Michael',
                    id: '123',
                    last_name: 'Sphinx',
                    _module: 'Users',
                    _type: 'DummyTestCase'
                }
            })).toEqual('123');
        });

        it('should return the name if no label is found', function() {
            expect(field.format({
                subject: {
                    first_name: 'Michael',
                    id: '123',
                    name: 'Michael Sphinx',
                    last_name: 'Sphinx',
                    _module: 'Users',
                    _type: 'DummyTestCase'
                }
            })).toEqual('Michael Sphinx');
        });

        it('should return the label value if no special case', function() {
            sinon.stub(app.lang, 'get')
                .withArgs('LBL_AUDIT_SUBJECT_DUMMYTESTCASE')
                .returns('Dummy Test Case');
            expect(field.format({
                subject: {
                    first_name: 'Michael',
                    name: 'Michael Sphinx',
                    last_name: 'Sphinx',
                    _module: 'Users',
                    _type: 'DummyTestCase'
                }
            })).toEqual('Dummy Test Case');
        });

        it('should return the full name if the type is user', function() {
            expect(field.format({
                subject: {
                    first_name: 'Michael',
                    id: '123',
                    last_name: 'Sphinx',
                    name: 'Michael Sphinx',
                    _module: 'Users',
                    _type: 'user'
                }
            })).toEqual('Michael Sphinx');
        });

        it('should return "SugarBPM" if the type is sugar-bpm and no source name is set', function() {
            sinon.stub(app.lang, 'get')
                .withArgs('LBL_AUDIT_SUBJECT_SUGAR-BPM')
                .returns('SugarBPM');
            expect(field.format({
                subject: {
                    id: '1a74ba10-5c46-11ee-84a9-0242c0a88004',
                    _module: 'pmse_Project',
                    _type: 'sugar-bpm',
                }
            })).toEqual('SugarBPM');
        });

        it('should return "Logic Hook" when no source name is set', function() {
            sinon.stub(app.lang, 'get')
                .withArgs('LBL_AUDIT_SUBJECT_LOGIC-HOOK')
                .returns('Logic Hook');
            expect(field.format({
                subject: {
                    class: 'DummyClass',
                    method: 'performDummyAction',
                    _type: 'logic-hook'
                }
            })).toEqual('Logic Hook');
        });

        it('should return "Logic Hook" with source name when provided', function() {
            sinon.stub(app.lang, 'get')
                .withArgs('LBL_AUDIT_SUBJECT_LOGIC-HOOK')
                .returns('Logic Hook');
            expect(field.format({
                subject: {
                    label: 'Before Demo Action',
                    class: 'DummyClass',
                    method: 'performDummyAction',
                    _type: 'logic-hook'
                }
            })).toEqual('Logic Hook Before Demo Action');
        });

        it('should return the identityAwareDataSource value if it is set from API', function() {
            sinon.stub(field, '_getValidLabelValue')
                .returns(null);
            expect(field.format({
                subject: {
                    id: 'srn:stage:iam:ap-southeast-2:9999999999:sa:module-sync',
                    _type: 'identity-aware-sa',
                    client: {
                        type: 'rest-api',
                    },
                },
                attributes: {
                    platform: 'base',
                    identityAwareDataSource: 'SugarPredict',
                }
            })).toEqual('SugarPredict');
        });
    });
});
