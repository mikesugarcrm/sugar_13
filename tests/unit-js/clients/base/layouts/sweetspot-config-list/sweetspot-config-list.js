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
describe('Base.Layout.SweetspotConfigList', function() {
    var layout, app, module = 'Accounts';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'sweetspot-config-list');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        layout = SugarTest.createLayout('base', module, 'sweetspot-config-list');
    });

    afterEach(function() {
        layout.dispose();
        layout = null;
        SugarTest.testMetadata.dispose();
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('_formatData', function() {
        using('different sweetspot configurations', [
            {
                message: 'preparing the payload for the User Preferences API',
                config: [
                    {action: '#Bugs', keyword: 'b1'},
                    {action: '#bwc/index.php?module=Reports', keyword: 'b2'},
                    {action: '#Accounts/create', keyword: 'b3'},
                ],
                expectedConfig: {
                    'hotkeys': [
                        {action: '#Bugs', keyword: ['b1']},
                        {action: '#bwc/index.php?module=Reports', keyword: ['b2']},
                        {action: '#Accounts/create', keyword: ['b3']},
                    ],
                }
            },
            {
                message: 'merging multiple keywords for a single action',
                config: [
                    {action: '#Accounts/create', keyword: 'b1'},
                    {action: '#Bugs', keyword: 'b1'},
                    {action: '#Leads/create', keyword: 'b1'},
                    {action: '#Bugs', keyword: 'b2'},
                    {action: '#bwc/index.php?module=Reports', keyword: 'b1'},
                    {action: '#Bugs', keyword: 'b2'},
                ],
                expectedConfig: {
                    'hotkeys': [
                        {action: '#Accounts/create', keyword: ['b1']},
                        {action: '#Bugs', keyword: ['b1', 'b2']},
                        {action: '#Leads/create', keyword: ['b1']},
                        {action: '#bwc/index.php?module=Reports', keyword: ['b1']},
                    ]
                }
            },
            {
                message: 'sanitizing the config from empty/falsy values',
                config: [
                    {action: '#Bugs', keyword: ''},
                    {action: '#Accounts', keyword: 'b2'},
                    {action: '', keyword: 'b3'},
                    {action: '', keyword: ''},
                    {action: '#Tasks/create', keyword: 'b5'}
                ],
                expectedConfig: {
                    'hotkeys': [
                        {action: '#Accounts', keyword: ['b2']},
                        {action: '#Tasks/create', keyword: ['b5']}
                    ]
                }
            },
            {
                message: 'sanitizing the config from duplicate values',
                config: [
                    {action: '#Bugs', keyword: 'b1'},
                    {action: '#Accounts', keyword: 'b2'},
                    {action: '#Bugs', keyword: 'b1'}
                ],
                expectedConfig: {
                    'hotkeys': [
                        {action: '#Bugs', keyword: ['b1']},
                        {action: '#Accounts', keyword: ['b2']}
                    ]
                }
            }
        ], function(options) {
            it('should format by ' + options.message, function() {
                var result = layout._formatData(options.config);
                expect(result).toEqual(options.expectedConfig);
            });
        });
    });
});
