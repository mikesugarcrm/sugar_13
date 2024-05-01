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
// Test project.js
describe('includes.javascript.pmse.project', function() {
    var app;
    var oldApp;
    var mockProject;
    var mockCanvas;

    beforeEach(function() {

        // Setting temporary globals in case they are altered during any tests
        app = SugarTest.app;
        oldApp = App;
        App = app;

        // Mocking an AdamProject
        mockProject = new AdamProject();
        mockCanvas = new AdamCanvas();
        mockCanvas.initObject();
        mockProject.setCanvas(mockCanvas);
    });

    afterEach(function() {
        // Restore the local variables and stubs
        mockProject = null;
        mockCanvas = null;
        sinon.restore();

        // Restore the global variables
        app = null;
        App = oldApp;
    });

    // Test the init functionality
    describe('Adamproject.prototype.init', function() {
        var fakeSetInterval;
        var mockTimer;

        beforeEach(function() {

            // Stub the save function since we don't need to save on a test
            sinon.stub(mockProject, 'save');

            // Stub the process traversal, as it is not needed
            sinon.stub(window, 'traverseProcess');

            // Create a fake commandStack, since it is called as part of
            // Stube the setInterval function since we don't need to actually set it on a test
            fakeSetInterval = sinon.stub(window, 'setInterval');

            // Set the mock project to loaded so that the autosave timer code will be checked
            mockProject.loaded = true;

            // Stub out the commandStack attribute, since it causes problems when init() is called
            // and these tests don't depend on the canvas
            mockProject.canvas.commandStack = {
                setHandler: function() {
                }
            };
        });

        afterEach(function() {

            // Restore the stubbed functions
            sinon.restore();
        });

        it('should set the autosave timer if project.saveInterval is a number n = 30000', function() {
            mockProject.setSaveInterval(30000);
            mockProject.init();
            expect(fakeSetInterval).toHaveBeenCalledWith(jasmine.any(Function), 30000);
        });

        it('should set the autosave timer if project.saveInterval is a number n > 30000', function() {
            mockProject.setSaveInterval(60000);
            mockProject.init();
            expect(fakeSetInterval).toHaveBeenCalledWith(jasmine.any(Function), 60000);
        });

        it('should set the autosave timer to 30000 if project.saveInterval is a number n < 0', function() {
            mockProject.setSaveInterval(-1000);
            mockProject.init();
            expect(fakeSetInterval).toHaveBeenCalledWith(jasmine.any(Function), 30000);
        });

        it('should set the autosave timer to 30000 if project.saveInterval is a number 0 < n < 30000', function() {
            mockProject.setSaveInterval(29999);
            mockProject.init();
            expect(fakeSetInterval).toHaveBeenCalledWith(jasmine.any(Function), 30000);
        });

        it('shoud not set the autosave timer if project.saveInterval is a number n = 0', function() {
            mockProject.setSaveInterval(0);
            mockProject.init();
            expect(fakeSetInterval).not.toHaveBeenCalled();
        });

        it('should not set the autosave timer if project.saveInterval is not a number', function() {
            mockProject.setSaveInterval('30000');
            mockProject.init();
            expect(fakeSetInterval).not.toHaveBeenCalled();
        });

        it('should include validation during autosave if auto-validate on auto-save is enabled', function() {

            // Set a fake timer to mock time passing (overwrites some important window globals
            // like setInterval, setTimeout, Date, etc.)
            mockTimer = sinon.useFakeTimers();
            app.config.autoValidateProcessesOnAutosave = true;
            mockProject.setSaveInterval(30000);
            mockProject.init();

            // Interval should not be triggered at 29999ms
            mockTimer.tick(29999);
            expect(traverseProcess).not.toHaveBeenCalled();

            // Interval should have been triggered by 30000ms
            mockTimer.tick(1);
            expect(traverseProcess).toHaveBeenCalled();

            // Restore the global functions altered by the mock timer object
            mockTimer.restore();
        });

        it('should not include validation during autosave if auto-validate on auto-save is not enabled', function() {

            // Set a fake timer to mock time passing (overwrites some important window globals
            // like setInterval, setTimeout, Date, etc.)
            mockTimer = sinon.useFakeTimers();

            // Make sure auto-validate on autosave is enabled
            app.config.autoValidateProcessesOnAutosave = false;

            // Set the save interval to 30000
            mockProject.setSaveInterval(30000);
            mockProject.init();

            // Project should be saved, but validation should not be triggered, after the 30000ms
            mockTimer.tick(30000);
            expect(mockProject.save).toHaveBeenCalled();
            expect(traverseProcess).not.toHaveBeenCalled();

            // Restore the global functions altered by the mock timer object
            mockTimer.restore();
        });
    });
});
