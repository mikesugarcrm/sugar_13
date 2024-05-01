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

const User = require('../../../src/core/user');

describe('Core/ACL', function () {

    beforeEach(function () {
        SugarTest.seedMetadata(true);

        this.app = SUGAR.App;

        this.data = this.app.data;
        this.metadata = this.app.metadata;
        this.acl = require('../../../src/core/acl');

        this.sandbox = sinon.createSandbox();
    });

    afterEach(function () {
        this.data.reset();
        this.sandbox.restore();
    });

    describe('Module level ACLs', function () {

        beforeEach(function () {
            this.sandbox.stub(User, 'getAcls').returns({
                Module1: {
                    access: 'yes',
                    create: 'yes',
                    view: 'yes',
                    edit: 'yes',
                    delete: 'yes',
                    list: 'yes',
                    export: 'yes',
                    import: 'yes',
                },
                Module2: {
                    access: 'yes',
                    create: 'no',
                    view: 'no',
                    edit: 'no',
                    delete: 'no',
                    list: 'no',
                    export: 'no',
                    import: 'no',
                },
                Module3: {
                    access: 'no',
                    create: 'yes',
                    view: 'yes',
                    edit: 'yes',
                    delete: 'yes',
                    list: 'yes',
                    export: 'yes',
                    import: 'yes',
                },
            });
        });

        it('should check for module access', function () {
            expect(this.acl.hasAccess('create', 'Module1')).toBeTruthy();
            expect(this.acl.hasAccess('view', 'Module1')).toBeTruthy();
            expect(this.acl.hasAccess('edit', 'Module1')).toBeTruthy();
            expect(this.acl.hasAccess('delete', 'Module1')).toBeTruthy();
            expect(this.acl.hasAccess('list', 'Module1')).toBeTruthy();
            expect(this.acl.hasAccess('export', 'Module1')).toBeTruthy();
            expect(this.acl.hasAccess('import', 'Module1')).toBeTruthy();

            expect(this.acl.hasAccess('create', 'Module2')).toBeFalsy();
            expect(this.acl.hasAccess('view', 'Module2')).toBeFalsy();
            expect(this.acl.hasAccess('edit', 'Module2')).toBeFalsy();
            expect(this.acl.hasAccess('delete', 'Module2')).toBeFalsy();
            expect(this.acl.hasAccess('list', 'Module2')).toBeFalsy();
            expect(this.acl.hasAccess('export', 'Module2')).toBeFalsy();
            expect(this.acl.hasAccess('import', 'Module2')).toBeFalsy();
        });

        it('should check for module global access first', function () {
            expect(this.acl.hasAccess('create', 'Module3')).toBeFalsy();
            expect(this.acl.hasAccess('view', 'Module3')).toBeFalsy();
            expect(this.acl.hasAccess('edit', 'Module3')).toBeFalsy();
            expect(this.acl.hasAccess('delete', 'Module3')).toBeFalsy();
            expect(this.acl.hasAccess('list', 'Module3')).toBeFalsy();
            expect(this.acl.hasAccess('export', 'Module3')).toBeFalsy();
            expect(this.acl.hasAccess('import', 'Module3')).toBeFalsy();
        });

        it('should return true by default', function () {
            expect(this.acl.hasAccess('undefinedAction', 'Module1')).toBeTruthy();
            expect(this.acl.hasAccess('create', 'UndefinedModule')).toBeTruthy();
        });
    });

    describe('Model level ACLs', function () {

        beforeEach(function () {

            this.sandbox.stub(User, 'getAcls').returns({
                Contacts: {
                    fields: {
                        field1: {
                            read: 'yes',
                            write: 'no',
                        },
                        field2: {
                            read: 'no',
                            write: 'no',
                        },
                    },
                    access: 'yes',
                    create: 'yes',
                    view: 'yes',
                    list: 'no',
                    edit: 'yes',
                    delete: 'yes',
                    import: 'yes',
                    export: 'yes',
                },
            });

            this.model = this.data.createBean('Contacts', { id: '1234' });
        });

        it('should respect model ACLs even when there is no Module ACLs', function () {
            let model = this.data.createBean('UndefinedModule', { id: '1234' });
            this.sandbox.stub(model, 'get').withArgs('_acl').returns({
                fields: {
                    field1: {
                        read: 'yes',
                        write: 'no',
                    },
                },
                access: 'yes',
                view: 'yes',
                edit: 'no',
            });

            expect(this.acl.hasAccessToModel('view', model)).toBeTruthy();
            expect(this.acl.hasAccessToModel('edit', model)).toBeFalsy();
            expect(this.acl.hasAccessToModel('view', model, 'field1')).toBeTruthy();
            expect(this.acl.hasAccessToModel('edit', model, 'field1')).toBeFalsy();
        });

        it('should respect model access overrides', function () {
            this.sandbox.stub(this.model, 'get').withArgs('_acl').returns({
                create: 'yes',
                view: 'yes',
                list: 'yes',
                edit: 'no',
                delete: 'yes',
                import: 'yes',
                export: 'yes',
            });

            expect(this.acl.hasAccessToModel('list', this.model)).toBeTruthy();
            expect(this.acl.hasAccessToModel('edit', this.model)).toBeFalsy();
        });

        it('should allow `edit` action when using a new (unsaved) model (same as create)', function () {
            let model = this.data.createBean('Contacts');

            expect(this.acl.hasAccessToModel('edit', model)).toBeTruthy();
            expect(this.acl.hasAccessToModel('edit', model, 'field1')).toBeTruthy();
        });

        it('should check for field access', function () {

            expect(this.acl.hasAccessToModel('edit', this.model, 'undefinedAccess')).toBeTruthy();
            expect(this.acl.hasAccessToModel('undefinedAction', this.model, 'email')).toBeTruthy();

            expect(this.acl.hasAccessToModel('read', this.model, 'field1')).toBeTruthy();
            expect(this.acl.hasAccessToModel('write', this.model, 'field1')).toBeFalsy();

            // action mapping from fields
            expect(this.acl.hasAccessToModel('detail', this.model, 'field1')).toBeTruthy();
            expect(this.acl.hasAccessToModel('view', this.model, 'field1')).toBeTruthy();
            expect(this.acl.hasAccessToModel('edit', this.model, 'field1')).toBeFalsy();
            expect(this.acl.hasAccessToModel('list', this.model, 'field1')).toBeFalsy();
            expect(this.acl.hasAccessToModel('readonly', this.model, 'field1')).toBeTruthy();
            expect(this.acl.hasAccessToModel('disabled', this.model, 'field1')).toBeTruthy();

            expect(this.acl.hasAccessToModel('read', this.model, 'field2')).toBeFalsy();
            expect(this.acl.hasAccessToModel('write', this.model, 'field2')).toBeFalsy();

            // action mapping from fields
            expect(this.acl.hasAccessToModel('detail', this.model, 'field2')).toBeFalsy();
            expect(this.acl.hasAccessToModel('view', this.model, 'field2')).toBeFalsy();
            expect(this.acl.hasAccessToModel('edit', this.model, 'field2')).toBeFalsy();
            expect(this.acl.hasAccessToModel('list', this.model, 'field2')).toBeFalsy();
            expect(this.acl.hasAccessToModel('readonly', this.model, 'field2')).toBeFalsy();
            expect(this.acl.hasAccessToModel('disabled', this.model, 'field2')).toBeFalsy();
        });

        it('should check for field access respecting model overrides', function () {

            this.sandbox.stub(this.model, 'get').withArgs('_acl').returns({
                fields: {
                    field1: { write: 'no' },
                },
            });

            expect(this.acl.hasAccessToModel('read', this.model, 'field1')).toBeTruthy();
            expect(this.acl.hasAccessToModel('write', this.model, 'field1')).toBeFalsy();

            expect(this.acl.hasAccessToModel('detail', this.model, 'field1')).toBeTruthy();
            expect(this.acl.hasAccessToModel('view', this.model, 'field1')).toBeTruthy();
            expect(this.acl.hasAccessToModel('edit', this.model, 'field1')).toBeFalsy();
            expect(this.acl.hasAccessToModel('list', this.model, 'field1')).toBeFalsy();
            expect(this.acl.hasAccessToModel('readonly', this.model, 'field1')).toBeTruthy();
            expect(this.acl.hasAccessToModel('disabled', this.model, 'field1')).toBeTruthy();
        });

        it('should check for field group access', function () {

            this.sandbox.stub(this.metadata, 'getModule').withArgs('Contacts').returns({
                fields: {
                    groupField1: {
                        group: 'field1',
                    },
                    groupField2: {
                        group: 'field1',
                    },
                },
            });

            expect(this.acl.hasAccessToModel('view', this.model, 'groupField1')).toBeTruthy();
            expect(this.acl.hasAccessToModel('view', this.model, 'groupField2')).toBeTruthy();
            expect(this.acl.hasAccessToModel('edit', this.model, 'groupField1')).toBeFalsy();
            expect(this.acl.hasAccessToModel('edit', this.model, 'groupField2')).toBeFalsy();
        });
    });

    describe('Admin', function () {

        beforeEach(function () {

            this.sandbox.stub(User, 'getAcls').returns({
                Module1: {
                    admin: 'yes',
                    access: 'no',
                    create: 'no',
                    view: 'no',
                    list: 'no',
                    edit: 'no',
                    delete: 'no',
                    import: 'no',
                    export: 'no',
                },
                Module2: {
                    fields: {
                        field1: {
                            read: 'no',
                            write: 'no',
                        },
                    },
                    admin: 'yes',
                    access: 'yes',
                    create: 'no',
                    view: 'no',
                    list: 'no',
                    edit: 'no',
                    delete: 'no',
                    import: 'no',
                    export: 'no',
                },
            });
        });

        it('should not treat admin property as special property', function () {
            expect(this.acl.hasAccess('admin', 'Module1')).toBeFalsy();

            expect(this.acl.hasAccess('admin', 'Module2')).toBeTruthy();
            expect(this.acl.hasAccess('admin', 'Module2', { field: 'field1' })).toBeTruthy();
            expect(this.acl.hasAccess('edit', 'Module2')).toBeFalsy();
            expect(this.acl.hasAccess('edit', 'Module2', { field: 'field1' })).toBeFalsy();
        });
    });

    describe('hasAccessToAny', function () {

        it('should return `false` when action is forbidden in all modules', function () {
            this.sandbox.stub(User, 'getAcls').returns({
                Module1: {
                    admin: 'no',
                    developer: 'no',
                },
                Module2: {
                    admin: 'no',
                    developer: 'no',
                },
            });

            expect(this.acl.hasAccessToAny('admin')).toBeFalsy();
            expect(this.acl.hasAccessToAny('developer')).toBeFalsy();
        });

        it('should return `true` when action is allowed in some modules', function () {
            this.sandbox.stub(User, 'getAcls').returns({
                Module1: {
                    admin: 'no',
                    developer: 'yes',
                },
                Module2: {
                    admin: 'yes',
                    developer: 'no',
                },
            });

            expect(this.acl.hasAccessToAny('admin')).toBeTruthy();
            expect(this.acl.hasAccessToAny('developer')).toBeTruthy();
        });

        it('should return `true` when action is allowed in all modules', function () {
            this.sandbox.stub(User, 'getAcls').returns({
                Module1: {},
                Module2: {},
            });

            expect(this.acl.hasAccessToAny('admin')).toBeTruthy();
            expect(this.acl.hasAccessToAny('developer')).toBeTruthy();
        });
    });

    // FIXME: these test deprecated code
    describe('deprecated stuff', function() {
        describe('_accessToAny', function () {
            it('should default to an empty object', function () {
                expect(this.acl._accessToAny).toEqual({});
            });

            it('should let you set values', function () {
                let obj = {dummy: 'value'};
                this.acl._accessToAny = obj;
                expect(this.acl._accessToAny).toEqual(obj);

                this.acl.clearCache();
                expect(this.acl._accessToAny).toEqual({});
            });
        });

        describe('action2permission', function () {
            it('should let you get and set values', function() {
                let obj = {'dummy-action': 'edit'};
                let originalAction2Permission = _.clone(this.acl.action2permission);
                this.acl.action2permission = obj;
                expect(this.acl.action2permission).toEqual(obj);
                this.acl.action2permission = originalAction2Permission;
            });
        });
    });
});
