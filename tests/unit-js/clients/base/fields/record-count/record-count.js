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
describe('Base.Field.RecordCountField', function() {
    var app;
    var field;
    var fieldDef;
    var module;
    var superStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'record-count');
        SugarTest.testMetadata.set();
        module = 'Cases';
        fieldDef = {
            client: 'base',
            name: 'record-count',
            type: 'record-count',
            viewName: 'detail',
            module: module,
            filter: [
                {
                    'status': {
                        '$not_in': ['Closed', 'Rejected', 'Duplicate']
                    }
                },
            ],
        };
        field = SugarTest.createField(fieldDef);
        superStub = sinon.stub(field, '_super');
    });

    afterEach(function() {
        sinon.restore();
        if (field) {
            field.dispose();
        }
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('_getCount', function() {
        it('should not call api if count is cached', function() {
            var apiCallStub = sinon.stub(app.api, 'call');
            var buildURLStub = sinon.stub(app.api, 'buildURL').callsFake(function() {
                return 'url';
            });
            field.context.set('recordCounts', {url: 1});
            field._getCount();
            expect(apiCallStub).not.toHaveBeenCalled();
        });

        it('should display if count > 0', function() {
            field.count = 1;
            field.render();
            expect(superStub).toHaveBeenCalled();
        });

        it('should not display if count == 0', function() {
            field.count = 0;
            field.render();
            expect(superStub).not.toHaveBeenCalled();
        });
    });
});
