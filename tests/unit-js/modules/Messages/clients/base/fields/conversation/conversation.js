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
describe('View.Fields.Base.Messages.ConversationField', function() {
    var app;
    var field;
    var createFieldProperties;
    var module = 'Messages';

    beforeEach(function() {
        app = SugarTest.app;
        createFieldProperties = {
            client: 'base',
            name: 'conversation',
            type: 'conversation',
            viewName: 'detail',
            fieldDef: {host: false},
            module: module,
            loadFromModule: true,
        };
    });

    afterEach(function() {
        if (field) {
            field.dispose();
        }
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('conversation field', function() {
        var messagesList = [
            {
                author: 'CUSTOMER',
                message: 'Text of Customer message 1',
            },
            {
                author: 'AGENT',
                message: 'Text of Agent message 1',
            },
            {
                author: 'AGENT',
                message: 'Text of Agent message 2',
            },
        ];

        beforeEach(function() {
            field = SugarTest.createField(createFieldProperties);
        });

        it('should parse transcript correctly', function() {
            var conversation = `
                [SYSTEM SYSTEM_MESSAGE] 10:33
                Text of System message

                [CUSTOMER Test Customer] 10:34
                Text of Customer message 1

                [AGENT Sugar] 10:35
                Text of Agent message 1

                [AGENT Sugar] 10:36
                Text of Agent message 2`;

            field.parseMessages(conversation);

            expect(field.messagesList).toEqual(messagesList);
        });

        it('should format transcript message', function() {
            field.messagesList = messagesList;
            var result = field.format('');

            expect(result).toEqual([
                {
                    author: 'CUSTOMER',
                    messagesList: [
                        'Text of Customer message 1',
                    ],
                },
                {
                    author: 'AGENT',
                    messagesList: [
                        'Text of Agent message 1',
                        'Text of Agent message 2',
                    ],
                },
            ]);
        });
    });
});
