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
// Test utils.js
describe('includes.javascript.pmse.utils', function() {

    // Tests that setDatetimeFieldsBCOptions correctly alters an array of fields data
    describe('setDatetimeFieldsBCOptions', function() {
        var fieldsArrayMock;

        beforeEach(function() {
            // Mock an array of fields
            fieldsArrayMock = [
                {
                    optionItem: 'none',
                    required: true,
                    text: 'Assigned to',
                    type: 'user',
                    value: 'assigned_user_id'
                },
                {
                    optionItem: 'none',
                    text: 'Description',
                    type: 'TextArea',
                    value: 'description'
                },
                {
                    optionItem: 'none',
                    text: 'Follow Up Date',
                    type: 'Datetime',
                    value: 'follow_up_datetime'
                },
                {
                    optionItem: {P1: 'High', P2: 'Medium', P3: 'Low'},
                    text: 'Priority',
                    type: 'DropDown',
                    value: 'priority'
                }
            ];
            sinon.stub(window, 'isRelatedToBusinessCenters').callsFake(function() { return true; });
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should set the targetModuleBC flag and selectedModuleBC string if flags to do so are true', function() {
            expect(setDatetimeFieldsBCOptions({
                targetModule: 'Accounts',
                selectedModule: 'Cases',
                fields: fieldsArrayMock,
                showTargetModuleOption: true,
                showSelectedModuleOption: true
            })[2].optionItem).toEqual({
                businessHours: {
                    show: true,
                    targetModuleBC: true,
                    selectedModuleBC: 'Cases'
                }
            });
        });

        it('should not set the targetModuleBC flag if the flag to do so is false', function() {
            expect(setDatetimeFieldsBCOptions({
                targetModule: 'Accounts',
                selectedModule: 'Cases',
                fields: fieldsArrayMock,
                showTargetModuleOption: false,
                showSelectedModuleOption: true
            })[2].optionItem).toEqual({
                businessHours: {
                    show: true,
                    targetModuleBC: false,
                    selectedModuleBC: 'Cases'
                }
            });
        });

        it('should not set the selectedModuleBC string if the flag to do so is false', function() {
            expect(setDatetimeFieldsBCOptions({
                targetModule: 'Accounts',
                selectedModule: 'Cases',
                fields: fieldsArrayMock,
                showTargetModuleOption: true,
                showSelectedModuleOption: false
            })[2].optionItem).toEqual({
                businessHours: {
                    show: true,
                    targetModuleBC: true,
                    selectedModuleBC: ''
                }
            });
        });

        it('should only affect Datetime fields in the fields array', function() {
            setDatetimeFieldsBCOptions('Accounts', 'Cases', fieldsArrayMock, true, true);
            expect(fieldsArrayMock[0].optionItem).toBe('none');
            expect(fieldsArrayMock[1].optionItem).toBe('none');
            expect(fieldsArrayMock[3].optionItem).toEqual({P1: 'High', P2: 'Medium', P3: 'Low'});
        });
    });

    describe('decodeHtmlEntities', function() {
        it('should decode htmlentities', function() {
            var label = '&quotTest&quot';
            expect(decodeHtmlEntities(label)).toEqual('"Test"');
        });
    });
});
