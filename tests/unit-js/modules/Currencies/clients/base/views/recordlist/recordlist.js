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

describe('Currencies.Base.Views.Recordlist', function() {
    var view;

    beforeEach(function() {
        view = SugarTest.createView('base', 'Currencies', 'recordlist', null, null, true, null);
    });

    describe('_render', function() {
        it('should remove dropdown for system currency row', function() {
            view.$el.html('<tr name="Currencies_-99">' +
                    '<td>' +
                        '<a data-bs-toggle="dropdown"></a>' +
                    '</td>' +
                '</tr>');
            view._render();

            expect(view.$el.find('[data-bs-toggle="dropdown"]').length).toEqual(0);
        });
    });
});
