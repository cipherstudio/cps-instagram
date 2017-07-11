<template>
<div class="">
    <div infinite-scroll="" infinite-scroll-immediate-check="false" infinite-scroll-distance="0.5">
        <div class="row small-gutter content">
                <div class="photo-card col-xs-2" v-for="item in items" v-bind:data-id="'photo-' + item.id">
                    <div class="photo-card-box">
                        <div class="photo-card-box-inner">
                            <div class="video-wrapper"></div>
                            <span>
                                <span>
                                    <span>
                                        <a :href="item.images.standard_resolution.url">
                                            <div class="squared-photo-div" :style="{ 'background-image': 'url(' + item.images.standard_resolution.url + ')' }">
                                            </div>
                                        </a>
                                    </span>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
        </div>
    </div>
    <div class="loading-view" v-show="loading">
        <div class='uil-spin-css' style='-webkit-transform:scale(0.18)'><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div></div>
    </div>
</div>
</template>

<script>
    export default {

        data: function() {
            return {
                // props
                selectable: true,

                // state
                loading: false,

                // data
                syncUrl: '',
                syncData: {},
                items: []
            };
        },

        mounted: function() {
            try {
                var app = $('body');
                this.init(app.data('syncUrl'), app.data('syncData'));
            } catch (e) {
                console.log(e, 'error');
            };

        },

        methods: {
            init: function(syncUrl, syncData) {
                this.syncUrl = syncUrl;
                this.syncData = syncData;
                this.items = syncData.data;

                // pagination
                this.setupInfiniteScroll();

                // selectable for select all | none
                if (this.selectable) {
                    this.setupSelectable();
                };
            },

            hasMore: function() {
                return this.syncData.pagination.next_max_id !== undefined;
            },

            load: function() {
                var self = this;
                // console.log('loading');
                // console.log(self.syncData, 'syncData');

                var url = this.syncData.pagination.next_url,
                    data = {url: encodeURIComponent(url)};

                // CURL proxy
                $.getJSON(this.syncUrl, data, function(syncData) {
                    // console.log(syncData, 'syncData');

                    // @todo check meta code

                    self.syncData.meta = syncData.meta;
                    self.syncData.pagination = syncData.pagination;

                    if (syncData.data.length) {
                        for (var key in syncData.data) {
                            self.items.push(syncData.data[key]);
                        }
                    }

                    self.loading = false;

                    if (self.selectable) {
                        
                    }
                });
            },

            setupInfiniteScroll: function() {
                var self = this;
                var win = $(window);
                //var loading = $('.loading-view');

                win.scroll(function() {

                    if (self.loading) return;
                    if (!self.hasMore()) return;

                    var done = $(document).height() - win.height() == win.scrollTop();
                    if (done) {

                        self.loading = true;
                        self.load();
                    };
                });
            },

            setupSelectable: function() {
                var SELECTABLE_MODE_CLICK_NORMAL = 1;
                var SELECTABLE_MODE_CLICK_ONLY = 2;
                var SELECTABLE_MODE_CLICK_ONE = 3;
                var SELECTABLE_MODE_CLICK_ADVANCED = 4;

                var mode = SELECTABLE_MODE_CLICK_ADVANCED;

                var container = $(this.$el);
                var options = {
                    filter: '.photo-card:not(.x-state-exists,.x-state-error)',
                    cancel: 'input,textarea,button,select,option'
                };
                var events = {};

                // here we will store index of previous selection
                var prev = -1; 
                
                var fnClickOnly = function() {
                    var selectee = function(event) {
                        var el = $(event.originalEvent.toElement);
                        if (!el.hasClass('ui-selectee')) {
                            el = el.parents('.ui-selectee:first');
                        };
                        return el;
                    };
                    
                    events.selectablestart = function (event) {
                        // for select
                        event.originalEvent.ctrlKey = true;
                        
                        // for unselect
                        var el = selectee(event);
                        if (el.length) {
                            if (el.hasClass('ui-selected')) {
                                container.one('selectablestop', function() {
                                    var self = container.data('ui-selectable');
                                    el.removeClass('ui-selected');
                                    self._trigger("unselected", event, {                                                                               
                                        unselected: el                                                                                     
                                    });  
                                });
                            };
                        }
                        
                    };
                    
                };

                // features: click and keyboard enabled both ctrl and shift buttons
                // @see http://stackoverflow.com/questions/9374743/enable-shift-multiselect-in-jquery-ui-selectable
                // @see http://jsfiddle.net/mac2000/DJFaL/1/light/
                var fnClickNormal = function() {
                    options.selecting = function(e, ui) {
                        var curr = $(ui.selecting.tagName, e.target).index(ui.selecting);
                        if(e.shiftKey && prev > -1) {
                            var self = $(this).data('ui-selectable');
                            var $elements = $(ui.selecting.tagName, e.target).slice(Math.min(prev, curr), 1 + Math.max(prev, curr))
                                    .filter(self.options.filter);

                            $elements.addClass('ui-selected');
                            $elements.each(function(key, element) {
                                self._trigger("selected", e, {                                                                                 
                                    selected: element                                                                                      
                                });
                            });
                            
                            prev = -1;
                        } else {
                            prev = curr;
                        }
                    };  
                };

                
                // @see naram/gallery
                switch (mode) {
                    case SELECTABLE_MODE_CLICK_ADVANCED:
                        fnClickOnly();
                        fnClickNormal();
                        break;
                        
                    /**
                     * toggle click for selected state
                     */
                    case SELECTABLE_MODE_CLICK_ONLY:
                        fnClickOnly();
                        break;

                    /**
                     * features:
                     *   - click (select, reset)
                     *   - ctrl + click
                     *   - shift + click
                     *   - crop (mouse drag area and drop over items)
                     */
                    case SELECTABLE_MODE_CLICK_NORMAL:
                        fnClickNormal();
                        break;

                    case SELECTABLE_MODE_CLICK_ONE:
                        options.selecting = function(e, ui) {
                            if( $(".ui-selected, .ui-selecting").length > 1){
                                $(ui.selecting).removeClass("ui-selecting");
                            };
                        };
                        break;

                };

                // apply optional events
                $.each(events, function(eventName, eventFn) {
                    container.on(eventName, eventFn);
                });

                container.selectable(options);
            },

            selecetAll: function() {
                console.log('selecetAll()');
            },
            
            deselectAll: function() {
                console.log('deselecetAll()');
            }
        }
    }
</script>