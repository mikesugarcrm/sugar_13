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

describe('pmse.ui.decision-table', function() {
    var _PMSE;
    beforeEach(function() {
        // Get the global PMSE classes variable.
        _PMSE = PMSE || {};
    });

    describe('removePrevIndexOnDeleteRow', function() {
        var decisionTable;
        var div;
        beforeEach(function() {
            div = $('<div id="decision-table-body">' +
                        '<tr class="decision-table-row" data-previndex="0"></tr>' +
                    '</div>');
        });
        it('should remove data attribute prev-index', function() {
            decisionTable = new DecisionTable(new PMSE.Element());
            decisionTable.removePrevIndexOnDeleteRow();
            expect(div).not.toHaveAttr('data-previndex', '0');
        });
    });
});
