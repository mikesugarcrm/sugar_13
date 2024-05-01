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
describe('Quotes.Base.Fields.ConvertToOpportunityField', function() {
    var app;
    var layout;
    var view;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'record');

        var def = {
            type: 'convert-to-opportunity',
            event: 'button:convert_to_quote:click',
            name: 'convert_to_opportunity_button',
            label: 'LBL_CONVERT_TO_OPPORTUNITY_LABEL',
            acl_module: 'Quotes'
        };

        layout = SugarTest.createLayout('base', 'Quotes', 'record', {});
        view = SugarTest.createView('base', 'Quotes', 'record', null, null, true, layout);
        field = SugarTest.createField({
            name: 'convert-to-opportunity',
            type: 'convert-to-opportunity',
            viewName: 'detail',
            fieldDef: def,
            module: 'Quotes',
            model: view.model,
            loadFromModule: true
        });
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();
        field.dispose();
        view = null;
        layout = null;
        app = null;
    });

    describe('_onCreateOppFromQuoteClicked', function() {

        beforeEach(function() {
            sinon.stub(app.alert, 'show');
            sinon.stub(app.api, 'call');
        });

        it('should call app.alert with an info window', function() {
            field._onCreateOppFromQuoteClicked();
            expect(app.alert.show).toHaveBeenCalledWith(
                'convert_to_opp', {
                    level: 'info',
                    title: app.lang.get('LBL_QUOTE_TO_OPPORTUNITY_STATUS'),
                    messages: ['']
                }
            );
        });

        it('should call app.alert with an info window', function() {
            var id = view.model.get('id');
            var url = app.api.buildURL('Quotes/' + id + '/opportunity');
            field._onCreateOppFromQuoteClicked();
            expect(app.api.call).toHaveBeenCalledWith(
                'create',
                url,
                null,
                {
                    success: field._onCreateOppFromQuoteCallback,
                    error: field._onCreateOppFromQuoteError
                }
            );
        });
    });

    describe('_onCreateOppFromQuoteCallback', function() {
        var data;
        beforeEach(function() {
            if (_.isUndefined(app.router)) {
                app.router = {
                    navigate: function() {}
                };
            }

            sinon.stub(app.alert, 'dismiss');
            sinon.stub(app.router, 'navigate');
            data = {
                record: {
                    id: 'foo'
                }
            };
        });

        it('should call app.alert.dismiss', function() {
            field._onCreateOppFromQuoteCallback(data);
            expect(app.alert.dismiss).toHaveBeenCalledWith('convert_to_opp');
        });

        it('should call app.router.navigate', function() {
            field._onCreateOppFromQuoteCallback(data);
            expect(app.router.navigate).toHaveBeenCalledWith('Opportunities/foo', {trigger: true});
        });
    });

    describe('_onCreateOppFromQuoteError', function() {
        beforeEach(function() {
            sinon.stub(app.alert, 'show');
            sinon.stub(app.alert, 'dismiss');
            data = {
                message: 'foo'
            };
        });

        it('should try to dismiss the "Creating" message', function() {
            field._onCreateOppFromQuoteError(data);
            expect(app.alert.dismiss).toHaveBeenCalledWith('convert_to_opp');
        });

        it('should try display the error from the endpoint', function() {
            field._onCreateOppFromQuoteError(data);
            expect(app.alert.show).toHaveBeenCalledWith('error_convert', {
                level: 'error',
                title: app.lang.get('LBL_ERROR'),
                messages: [data.message]
            });
        });
    });

    describe('_toggleDisable', function() {
        beforeEach(function() {
            field.render();
        });

        it('will set disabled class on the field element', function() {
            field.model.set('opportunity_id', 'my_new_opportunity', {silent: true});
            field._toggleDisable();
            expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();
        });

        it('will remove disabled class when opportunity_id changes to empty', function() {
            field.model.set('opportunity_id', 'my_new_opportunity', {silent: true});
            field._toggleDisable();
            expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();

            field.model.set('opportunity_id', '');
            expect(field.getFieldElement().hasClass('disabled')).toBeFalsy();
        });

        it('will remove disabled class when opportunity_id is unset', function() {
            field.model.set('opportunity_id', 'my_new_opportunity', {silent: true});
            field._toggleDisable();
            expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();

            field.model.unset('opportunity_id');
            expect(field.getFieldElement().hasClass('disabled')).toBeFalsy();
        });
    });
});
