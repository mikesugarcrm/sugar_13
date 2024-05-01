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
describe('Forecasts.Base.Views.CommitmentHeaderpane', function() {
    let view;
    let layout;
    let context;
    let moduleName = 'Forecasts';
    let sbox = sinon.createSandbox();

    beforeEach(function() {
        sinon.stub(SugarTest.app.metadata, 'getModule').withArgs('Opportunities', 'config').returns({
            opps_view_by: 'Opportunities'
        });

        context = SugarTest.app.context.getContext();
        layout = {
            context: context,
            on: function() {},
            off: function() {}
        };
        view = SugarTest.createView('base', moduleName, 'commitment-headerpane', null, null, true, layout, true);
        view.saveDraftBtnField = {
            hide: _.noop,
            show: _.noop,
        };
        view.commitBtnField = {
            setDisabled: function(disabled) {
                return disabled;
            },
            $: function() {
                return {
                    tooltip: function() {}
                };
            }
        };
        view.cancelBtnField = {
            hide: _.noop,
            show: _.noop,
        };
    });

    afterEach(function() {
        view.saveDraftBtnField = null;
        view.commitBtnField = null;
        view.cancelBtnField = null;
        view.dispose();
        view = null;
        sinon.restore();
        sbox.restore();
    });

    describe('setButtonStates()', function() {
        beforeEach(function() {
            sinon.spy(view.saveDraftBtnField, 'hide');
            sinon.spy(view.saveDraftBtnField, 'show');
            sinon.spy(view.commitBtnField, 'setDisabled');
            sinon.spy(view.cancelBtnField, 'hide');
            sinon.spy(view.cancelBtnField, 'show');
            view.forecastSyncComplete = false;
            view.fieldHasErrorState = false;
            view.saveBtnDisabled = false;
            view.commitBtnDisabled = false;
            view.cancelBtnHidden = false;
        });

        it('should disable if forecastSyncComplete is true and fieldHasErrorState is true', function() {
            view.forecastSyncComplete = true;
            view.fieldHasErrorState = true;
            view.setButtonStates();
            expect(view.saveDraftBtnField.hide).toHaveBeenCalled();
            expect(view.commitBtnField.setDisabled).toHaveBeenCalledWith(true);
            expect(view.cancelBtnField.hide).toHaveBeenCalled();
        });

        it('should disable if forecastSyncComplete is false', function() {
            view.setButtonStates();
            expect(view.saveDraftBtnField.hide).toHaveBeenCalled();
            expect(view.commitBtnField.setDisabled).toHaveBeenCalledWith(true);
            expect(view.cancelBtnField.hide).toHaveBeenCalled();
        });

        it('should set disabled state if forecastSyncComplete is true and fieldHasErrorState is false', function() {
            view.forecastSyncComplete = true;
            view.saveBtnDisabled = true;
            view.commitBtnDisabled = false;
            view.cancelBtnHidden = true;
            view.setButtonStates();
            expect(view.saveDraftBtnField.hide).toHaveBeenCalled();
            expect(view.commitBtnField.setDisabled).toHaveBeenCalledWith(false);
            expect(view.cancelBtnField.hide).toHaveBeenCalled();
        });

        it('should set disabled state if forecastSyncComplete is true and fieldHasErrorState is false', function() {
            view.forecastSyncComplete = true;
            view.saveBtnDisabled = false;
            view.commitBtnDisabled = true;
            view.cancelBtnHidden = false;
            view.setButtonStates();
            expect(view.saveDraftBtnField.show).toHaveBeenCalled();
            expect(view.commitBtnField.setDisabled).toHaveBeenCalledWith(true);
            expect(view.cancelBtnField.show).toHaveBeenCalled();
        });
    });
});
