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

describe('Data.Base.EmailParticipantsBean', function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.declareData('base', 'EmailParticipants', true, false);
        app.data.declareModels();

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        sandbox.restore();
        SugarTest.testMetadata.dispose();
    });

    describe('setting the name when there is not one', function() {
        it('should set the name', function() {
            var parentId = _.uniqueId();
            var bean = app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    _erased_fields: [],
                    type: 'Contacts',
                    id: parentId,
                    salutation: '',
                    first_name: 'Haley',
                    last_name: 'Rhodes'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: ''
            });
            var parent = bean.getParent();

            expect(bean.get('parent_name')).toBe('Haley Rhodes');
            expect(bean.get('parent').name).toBe('Haley Rhodes');
            expect(parent.get('name')).toBe('Haley Rhodes');
        });

        it('should not set the name', function() {
            var parentId = _.uniqueId();
            var bean = app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    _erased_fields: [
                        'first_name',
                        'last_name'
                    ],
                    type: 'Contacts',
                    id: parentId,
                    name: ''
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: ''
            });
            var parent = bean.getParent();

            expect(bean.get('parent_name')).toBe('');
            expect(bean.get('parent').name).toBe('');
            expect(parent.get('name')).toBe('');
        });
    });

    describe('determining if there is a parent bean', function() {
        it('should return true', function() {
            var parentId = _.uniqueId();
            var emailAddressId = _.uniqueId();
            var bean = app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    _erased_fields: [],
                    type: 'Contacts',
                    id: parentId,
                    name: 'Haley Rhodes'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Haley Rhodes',
                email_addresses: {
                    _acl: {},
                    _erased_fields: [],
                    email_address: 'rhodes@example.com',
                    id: emailAddressId
                },
                email_address_id: emailAddressId,
                email_address: 'rhodes@example.com',
                invalid_email: false,
                opt_out: false
            });

            expect(bean.hasParent()).toBe(true);
        });

        it('should return false', function() {
            var parentId = _.uniqueId();
            var emailAddressId = _.uniqueId();
            var bean = app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    _erased_fields: [],
                    type: 'Contacts',
                    id: parentId,
                    name: ''
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: '',
                email_addresses: {
                    _acl: {},
                    _erased_fields: [],
                    email_address: 'rhodes@example.com',
                    id: emailAddressId
                },
                email_address_id: emailAddressId,
                email_address: 'rhodes@example.com',
                invalid_email: false,
                opt_out: false
            });

            // No parent name and name is not erased. This could happen if the
            // parent record is deleted.
            expect(bean.hasParent()).toBe(false);
        });
    });

    describe('getting the parent bean', function() {
        it('should return the parent bean', function() {
            var parentId = _.uniqueId();
            var bean = app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Haley Rhodes',
                    _erased_fields: []
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Haley Rhodes'
            });
            var parent = bean.getParent();

            expect(parent).not.toBeUndefined();
            expect(parent.module).toBe('Contacts');
            expect(parent.get('id')).toBe(parentId);
            expect(parent.get('name')).toBe('Haley Rhodes');
            expect(parent.get('_acl')).toEqual({});
            expect(parent.get('_erased_fields')).toEqual([]);
        });

        it('should return the parent bean with an erased name', function() {
            var parentId = _.uniqueId();
            var bean = app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: '',
                    _erased_fields: [
                        'first_name',
                        'last_name'
                    ]
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: ''
            });
            var parent = bean.getParent();

            expect(parent).not.toBeUndefined();
            expect(parent.module).toBe('Contacts');
            expect(parent.get('id')).toBe(parentId);
            expect(parent.get('name')).toBe('');
            expect(parent.get('_acl')).toEqual({});
            expect(parent.get('_erased_fields')).toEqual([
                'first_name',
                'last_name'
            ]);
            expect(app.utils.isNameErased(parent)).toBe(true);
        });

        using(
            'parent data',
            [
                // No parent.
                {
                    parent_type: '',
                    parent_id: '',
                    parent_name: ''
                },
                // No parent module.
                {
                    parent: {
                        _acl: {},
                        _erased_fields: [],
                        type: '',
                        id: 'd5611428-2eb0-11e8-9e05-3c15c2d582c6',
                        name: 'Haley Rhodes'
                    },
                    parent_type: '',
                    parent_id: 'd5611428-2eb0-11e8-9e05-3c15c2d582c6',
                    parent_name: 'Haley Rhodes'
                },
                // No parent ID.
                {
                    parent: {
                        _acl: {},
                        _erased_fields: [],
                        type: 'Contacts',
                        id: '',
                        name: 'Haley Rhodes'
                    },
                    parent_type: 'Contacts',
                    parent_id: '',
                    parent_name: 'Haley Rhodes'
                }
            ],
            function(data) {
                it('should return undefined', function() {
                    var emailAddressId = _.uniqueId();
                    var attributes = {
                        _link: 'to',
                        id: _.uniqueId(),
                        email_addresses: {
                            email_address: 'rhodes@example.com',
                            id: emailAddressId,
                            _acl: {},
                            _erased_fields: []
                        },
                        email_address_id: emailAddressId,
                        email_address: 'rhodes@example.com',
                        invalid_email: false,
                        opt_out: false
                    };
                    var bean = app.data.createBean('EmailParticipants', _.extend(attributes, data));
                    var parent = bean.getParent();

                    expect(parent).toBeUndefined();
                });
            }
        );
    });

    describe('formatting a model for email headers', function() {
        describe('participant only has an email address', function() {
            var bean;

            beforeEach(function() {
                var emailAddressId = _.uniqueId();

                bean = app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    email_address_id: emailAddressId,
                    email_address: 'rhodes@example.com',
                    invalid_email: false,
                    opt_out: false,
                    email_addresses: {
                        email_address: 'rhodes@example.com',
                        id: emailAddressId,
                        _acl: {},
                        _erased_fields: []
                    }
                });
            });

            it('should return just an email address', function() {
                var actual = bean.toHeaderString();

                expect(actual).toBe('rhodes@example.com');
            });

            it('should return "Value erased"', function() {
                var actual;
                var link = bean.get('email_addresses');

                // Erase the email address.
                bean.set('email_address', '');
                link.email_address = '';
                link._erased_fields = [
                    'email_address',
                    'email_address_caps'
                ];

                actual = bean.toHeaderString();

                expect(actual).toBe('Value erased');
            });
        });

        describe('participant has a name and email address', function() {
            var bean;

            beforeEach(function() {
                var parentId = _.uniqueId();
                var emailAddressId = _.uniqueId();

                bean = app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId,
                        name: 'Haley Rhodes',
                        _erased_fields: []
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId,
                    parent_name: 'Haley Rhodes',
                    email_address_id: emailAddressId,
                    email_address: 'hrhodes@example.com',
                    invalid_email: false,
                    opt_out: false,
                    email_addresses: {
                        email_address: 'hrhodes@example.com',
                        id: emailAddressId,
                        _acl: {},
                        _erased_fields: []
                    }
                });
            });

            it('should return a name and email address', function() {
                var actual = bean.toHeaderString();

                expect(actual).toBe('Haley Rhodes <hrhodes@example.com>');
            });

            it('should surround the name with quotes', function() {
                var actual = bean.toHeaderString({quote_name: true});

                expect(actual).toBe('"Haley Rhodes" <hrhodes@example.com>');
            });

            it('should use "Value erased" for the name', function() {
                var actual;
                var parent = bean.get('parent');

                // Erase the name.
                bean.set('parent_name', '');
                parent.name = '';
                parent._erased_fields = [
                    'first_name',
                    'last_name'
                ];

                actual = bean.toHeaderString();

                expect(actual).toBe('Value erased <hrhodes@example.com>');
            });

            it('should use "Value erased" for the email address', function() {
                var actual;
                var link = bean.get('email_addresses');

                // Erase the email address.
                bean.set('email_address', '');
                link.email_address = '';
                link._erased_fields = [
                    'email_address',
                    'email_address_caps'
                ];

                actual = bean.toHeaderString();

                expect(actual).toBe('Haley Rhodes <Value erased>');
            });

            it('should use "Value erased" for the name and email address', function() {
                var actual;
                var parent = bean.get('parent');
                var link = bean.get('email_addresses');

                // Erase the name.
                bean.set('parent_name', '');
                parent.name = '';
                parent._erased_fields = [
                    'first_name',
                    'last_name'
                ];

                // Erase the email address.
                bean.set('email_address', '');
                link.email_address = '';
                link._erased_fields = [
                    'email_address',
                    'email_address_caps'
                ];

                actual = bean.toHeaderString();

                expect(actual).toBe('Value erased <Value erased>');
            });
        });

        describe('participant only has a name', function() {
            var bean;

            beforeEach(function() {
                var parentId = _.uniqueId();

                bean = app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId,
                        name: 'Haley Rhodes',
                        _erased_fields: []
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId,
                    parent_name: 'Haley Rhodes'
                });
            });

            using('quotes', [true, false], function(surroundNameWithQuotes) {
                it('should return just a name', function() {
                    var actual = bean.toHeaderString({quote_name: surroundNameWithQuotes});

                    expect(actual).toBe('Haley Rhodes');
                });
            });

            it('should use "Value erased" for the name', function() {
                var actual;
                var parent = bean.get('parent');

                // Erase the name.
                bean.set('parent_name', '');
                parent.name = '';
                parent._erased_fields = [
                    'first_name',
                    'last_name'
                ];

                actual = bean.toHeaderString();

                expect(actual).toBe('Value erased');
            });
        });
    });

    describe('determining if the name has been erased', function() {
        describe('there is a parent', function() {
            var bean;

            beforeEach(function() {
                var parentId = _.uniqueId();

                bean = app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId,
                        name: 'Haley Rhodes',
                        _erased_fields: []
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId,
                    parent_name: 'Haley Rhodes'
                });
            });

            it('should return true', function() {
                var actual;
                var parent = bean.get('parent');

                // Erase the name.
                bean.set('parent_name', '');
                parent.name = '';
                parent._erased_fields = [
                    'first_name',
                    'last_name'
                ];

                actual = bean.isNameErased();

                expect(actual).toBe(true);
            });

            it('should return false', function() {
                var actual = bean.isNameErased();

                expect(actual).toBe(false);
            });
        });

        describe('there is not a parent', function() {
            var bean;

            beforeEach(function() {
                var emailAddressId = _.uniqueId();

                bean = app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    email_address_id: emailAddressId,
                    email_address: 'rhodes@example.com',
                    invalid_email: false,
                    opt_out: false,
                    email_addresses: {
                        email_address: 'rhodes@example.com',
                        id: emailAddressId,
                        _acl: {},
                        _erased_fields: []
                    }
                });
            });

            it('should return false', function() {
                var actual = bean.isNameErased();

                expect(actual).toBe(false);
            });
        });
    });

    describe('determining if the email address has been erased', function() {
        describe('there is an email address', function() {
            var bean;

            beforeEach(function() {
                var parentId = _.uniqueId();
                var emailAddressId = _.uniqueId();

                bean = app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId,
                        name: 'Haley Rhodes',
                        _erased_fields: []
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId,
                    parent_name: 'Haley Rhodes',
                    email_address_id: emailAddressId,
                    email_address: 'hrhodes@example.com',
                    invalid_email: false,
                    opt_out: false,
                    email_addresses: {
                        email_address: 'hrhodes@example.com',
                        id: emailAddressId,
                        _acl: {},
                        _erased_fields: []
                    }
                });
            });

            it('should return true', function() {
                var actual;
                var link = bean.get('email_addresses');

                // Erase the email address.
                bean.set('email_address', '');
                link.email_address = '';
                link._erased_fields = [
                    'email_address',
                    'email_address_caps'
                ];

                actual = bean.isEmailErased();

                expect(actual).toBe(true);
            });

            it('should return false', function() {
                var actual = bean.isEmailErased();

                expect(actual).toBe(false);
            });
        });

        describe('there is not an email address', function() {
            var bean;

            beforeEach(function() {
                var parentId = _.uniqueId();

                bean = app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId,
                        name: 'Haley Rhodes',
                        _erased_fields: []
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId,
                    parent_name: 'Haley Rhodes'
                });
            });

            it('should return false', function() {
                var actual = bean.isEmailErased();

                expect(actual).toBe(false);
            });
        });
    });
});
