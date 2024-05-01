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
describe("Activity Stream Omnibar View", function() {
    var app;
    var view;
    var activityStreamsEnabledBefore;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('activitystream-omnibar', 'view', 'base');
        SugarTest.testMetadata.set();

        activityStreamsEnabledBefore = app.config.activityStreamsEnabled;
        app.config.activityStreamsEnabled = true;

        view = SugarTest.createView('base', 'Cases', 'activitystream-omnibar');
        view.render();
    });

    afterEach(function() {
        app.config.activityStreamsEnabled = activityStreamsEnabledBefore;
        sinon.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe("toggleSubmitButton()", function() {
        var attachments = {};

        beforeEach(function() {
            view.getAttachments = function() {
                return attachments;
            }
        });

        afterEach(function() {
            view.getAttachments = null;
        });

        it('Should disable Submit button by default', function() {
            expect(view.$('.addPost').hasClass('disabled')).toBe(true);
        });

        it('Should enable Submit button when there is text inside the input area', function() {
            view.$('.sayit').text('foo bar');
            view.toggleSubmitButton();
            expect(view.$('.addPost').hasClass('disabled')).toBe(false);
        });

        it('Should disable Submit button when there are only spaces inside the input area', function() {
            view.$('.sayit').text('       ');
            view.toggleSubmitButton();
            expect(view.$('.addPost').hasClass('disabled')).toBe(true);
        });

        it('Should enable Submit button when an attachment is added', function() {
            view.toggleSubmitButton();

            attachments = {one:1};
            view.trigger('attachments:add');

            expect(view.$('.addPost').hasClass('disabled')).toBe(false);
            attachments = {};
        });

        it('Should disable Submit button when an existing attachment is removed', function() {
            attachments = {one:1};
            view.toggleSubmitButton();

            attachments = {};
            view.trigger('attachments:remove');

            expect(view.$('.addPost').hasClass('disabled')).toBe(true);
        });

        describe('_handleContentChange calls toggleSubmitButton', function() {
            var evt,
                stubGetPost;

            beforeEach(function() {
                evt = {
                    currentTarget: {
                        setAttribute: function(attr, val) {},
                        removeAttribute: function(attr) {}
                    }
                };
                stubGetPost = sinon.stub(view, 'getPost');
            });

            var dataProvider = [
                {
                    message: 'should enable Submit button when _handleContentChange receives an event with content',
                    content: 'foo',
                    expected: false
                },
                {
                    message: 'should disable Submit button when _handleContentChange receives an event without content',
                    content: '',
                    expected: true
                }
            ];

            _.each(dataProvider, function(data) {
                it(data.message, function() {
                    evt.currentTarget.textContent = data.content;
                    stubGetPost.returns({value: data.content});
                    view._handleContentChange(evt);
                    expect(view.$('.addPost').hasClass('disabled')).toBe(data.expected);
                });
            });
        });
    });

    it('should hide the view when activity streams is disabled', function() {
        app.config.activityStreamsEnabled = false;
        view.render();

        expect(view.$el.hasClass('hide')).toBe(true);
    });
});
