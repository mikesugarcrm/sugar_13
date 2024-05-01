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
describe('View.Views.Base.QuicksearchTaglistView', function() {
    var viewName = 'quicksearch-taglist',
        view, layout, attachKeyDownStub, disposeKeydownStub,
        triggerBeforeStub, triggerSpy,
        tag1, tag2;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        layout = SugarTest.app.view.createLayout({});
        view = SugarTest.createView('base', undefined, viewName, null, null, null, layout);

        attachKeyDownStub = sinon.stub(view, 'attachKeydownEvent');
        disposeKeydownStub = sinon.stub(view, 'disposeKeydownEvent');
        triggerBeforeStub = sinon.stub(view.layout, 'triggerBefore').callsFake(function() {
            return true;
        });
        triggerSpy = sinon.spy(view.layout, 'trigger');

        tag1 = {id: 1, name: 'tag1'};
        tag2 = {id: 2, name: 'tag2'};
        view.selectedTags = [tag1, tag2];
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.restore();
        layout.dispose();
        layout = null;
        view = null;
    });

    describe('navigate:focus:receive', function() {
        it('should set the first element active and attachKeydownEvent', function() {
            view.trigger('navigate:focus:receive', true);
            expect(attachKeyDownStub).toHaveBeenCalled();
            expect(view.activeIndex).toEqual(0);
        });

        it('should set the last element active and attachKeydownEvent', function() {
            view.trigger('navigate:focus:receive', false);
            expect(attachKeyDownStub).toHaveBeenCalled();
            expect(view.activeIndex).toEqual(1);
        });
    });

    describe('navigate:focus:lost', function() {
        it('should clear the activeIndex and disposeKeydownEvent', function() {
            view.trigger('navigate:focus:lost');
            expect(disposeKeydownStub).toHaveBeenCalled();
            expect(view.activeIndex).toBeNull();
        });
    });

    describe('isFocusable', function() {
        it('should be focusable', function() {
            var isFocusable = view.isFocusable();
            expect(isFocusable).toBeTruthy();
        });

        it('should not be focusable', function() {
            view.selectedTags = [];
            var isFocusable = view.isFocusable();
            expect(isFocusable).not.toBeTruthy();
        });
    });

    describe('moveLeft', function() {
        it('should decrement the activeIndex if in bounds', function() {
            view.activeIndex = 1;
            view.moveLeft();
            expect(view.activeIndex).toEqual(0);
        });

        it('should move to the previous element if out of bounds', function() {
            view.activeIndex = 0;
            view.moveLeft();
            expect(view.activeIndex).toBeNull();
            expect(triggerBeforeStub).toHaveBeenCalledOnce();
            expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:previous:component');
            expect(disposeKeydownStub).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledWith('navigate:previous:component');
        });
    });

    describe('moveRight', function() {
        it('should increment the activeIndex if in bounds', function() {
            view.activeIndex = 0;
            view.moveRight();
            expect(view.activeIndex).toEqual(1);
        });

        it('should move to the next element if out of bounds', function() {
            view.activeIndex = 1;
            view.moveRight();
            expect(view.activeIndex).toBeNull();
            expect(triggerBeforeStub).toHaveBeenCalledOnce();
            expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:next:component');
            expect(disposeKeydownStub).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledWith('navigate:next:component');
        });
    });

    describe('handleBackspace', function() {
        beforeEach(function() {
            removeStub = sinon.stub(view, 'removeTag').callsFake(function() {
                view.selectedTags.splice(view.activeIndex, 1);
            });
        });

        it('should remove the tag at activeIndex', function() {
            view.activeIndex = 0;
            view.handleBackspace();
            expect(view.selectedTags.length).toEqual(1);
            expect(view.selectedTags[0].name).toEqual(tag2.name);
        });

        it('should move to the next element if no tags remain', function() {
            view.selectedTags = [tag1];
            view.activeIndex = 0;
            view.handleBackspace();
            expect(view.activeIndex).toBeNull();
            expect(triggerBeforeStub).toHaveBeenCalledOnce();
            expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:next:component');
            expect(disposeKeydownStub).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledWith('navigate:next:component');
        });
    });

    describe('removeTag', function() {
        beforeEach(function() {
            // Stub out jquery so that the search will return tag1's name
            jQueryStub = sinon.stub(view, '$').callsFake(function() {
                return {
                    attr: function() {
                        return tag2.name;
                    },
                    remove: function() {
                    },
                    removeClass: function() {
                    },
                    addClass: function() {
                    }
                }
            });
        });

        it('should call the correct jquery selector if parameter is false', function() {
            view.activeIndex = 0;
            view.removeTag(false);
            expect(jQueryStub).toHaveBeenCalled();
            expect(jQueryStub).toHaveBeenCalledWith('.tag-wrapper:eq(0)');
        });

        it('should remove tag specified by jquery.attr if parameter is false', function() {
            view.activeIndex = 1;
            view.removeTag(false);
            expect(view.selectedTags.length).toEqual(1);
            expect(view.selectedTags[0].name).toEqual(tag1.name);
        });

        it('should remove tag specified by jquery object parameter', function() {
            var jQueryTag = {
                attr: function() {
                    return tag1.name;
                },
                remove: function() {
                }
            };
            view.activeIndex = null;
            view.removeTag(jQueryTag);
            expect(view.selectedTags.length).toEqual(1);
            expect(view.selectedTags[0].name).toEqual(tag2.name);
        });
    });

    describe('removeDropdownTag', function() {
        var evt;
        beforeEach(function() {
            evt = {
                preventDefault: $.noop,
                currentTarget: {
                    parentElement: 'button[name=testBtn]',
                },
                type: 'click',
                stopPropagation: sinon.stub(),
                val: 'test'
            };
            sinon.stub(view, 'removeTagClicked');
            sinon.stub(view, '_render');
        });
        afterEach(function() {
            evt = null;
        });
        it('should remove the selected tab from the dropdown list', function() {
            view.removeDropdownTag(evt);
            expect(view.removeTagClicked).toHaveBeenCalledWith(evt);
            expect(view._render).toHaveBeenCalled();
        });
    });

    describe('render', function() {
        using('various tags', [{
            selectedTags: [
                {id: 1, name: 'tag1'},
                {id: 2, name: 'tag2'}
            ],
            expected: false
        },{
            selectedTags: [
                {id: 1, name: 'tag1'},
                {id: 2, name: 'tag2'},
                {id: 3, name: 'tag3'},
                {id: 4, name: 'tag4'}
            ],
            expected: true
        },{
            selectedTags: [
                {id: 1, name: 'tag1'},
                {id: 2, name: 'tag2'},
                {id: 3, name: 'tag3'},
                {id: 4, name: 'tag4'},
                {id: 5, name: 'tag5'}
            ],
            expected: true
        }], function(value) {
            it('should set multiple to true when more than 3 tags are selected', function() {
                view.selectedTags = value.selectedTags;
                view.render();
                expect(view.dropdownList).toEqual(value.expected);
            });
        });
    });

    describe('removeTagClicked', function() {
        var jQueryStub, removeStub;

        beforeEach(function() {
            jQueryStub = sinon.stub(view, '$').callsFake(function() {
                return {
                    parent: function() {
                        return 'verify';
                    },
                    removeClass: function() {
                    }
                }
            });
            removeStub = sinon.stub(view, 'removeTag');
        });

        it('should call removeTag with the verify parameter', function() {
            var e = {
                stopPropagation: function() {},
                preventDefault: function() {},
                currentTarget: {
                    parentElement: 'button[name=testBtn]',
                },
                target: 1,
            };
            view.removeTagClicked(e);
            expect(removeStub).toHaveBeenCalledOnce();
            expect(removeStub).toHaveBeenCalledWith('verify');
            expect(triggerSpy).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledWith('navigate:to:component');
        });
    });

    describe('removeAllTags', function() {
        it('should remove all tags', function() {
            view.removeAllTags();
            expect(view.selectedTags.length).toEqual(0);
            expect(view.activeIndex).toBeNull();
        });
    });

    describe('highlightTagClicked', function() {
        var jQueryStub;

        beforeEach(function() {
            jQueryStub = sinon.stub(view, '$').callsFake(function() {
                return {
                    removeClass: function() {
                    },
                    parent: function() {
                        return {
                            addClass: function(){
                            },
                            attr: function() {
                                return tag1.name;
                            }
                        };
                    }
                }
            });
        });

        it('should set activeIndex to 0', function() {
            expect(view.activeIndex).toBeNull();
            view.highlightTagClicked({target: 'placeholder'});
            expect(view.activeIndex).toEqual(0);
        });
    });
});
