<template>
<div class="">
    <div>
        <div class="row small-gutter subtitle" v-if="subtitle">
            <div class="col-xs-12">
                <div>
                    <span>Click an image to shop</span>
                </div>
            </div>
        </div>
        <div class="row small-gutter content">
                <div :class="photoClass(item)" v-for="item in items" v-bind:data-id="'photo-' + item.id">
                    <div class="photo-card-box">
                        <div class="photo-card-box-inner">
                            <div class="video-wrapper"></div>
                            <span>
                                <span>
                                    <span>
                                        <a :href="photoLink(item)"> 
                                            <div class="squared-photo-div" :style="{ 'background-image': 'url(' + getHdUrl(item) + ')' }">
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
                subtitle: true,
                popup: true,

                // state
                loading: false,

                // data
                syncUrl: '',
                syncData: {},
                items: [],

                column: 3,

                $popup: null,
            };
        },

        computed: {

            columnClass: function () {
                return {
                    'col-xs-4': (this.column == 3),
                    'col-xs-3': (this.column == 4),
                    'col-xs-2': (this.column == 6)
                }
            }
        },

        mounted: function() {

        },

        methods: {

            photoClass: function(item) {
                var cls = 'photo-card';

                $.each(this.columnClass, function(k, v) {
                    if (v === true) {
                        cls += ' ' + k;
                    }
                });

                var exists = item.exists || false;
                if (exists) {
                    cls += ' x-state-exists';
                };

                cls += ' photo-card-type-' + item.type;

                return cls;
            },

            photoLink: function(item) {
                var url = '';
                if ($.type(item.points) == 'undefined') {
                    url = item.images.standard_resolution.url;
                } else {
                    var points = item.points || [];
                    if (points.length == 1) {
                        url = points[0].url
                    } else {
                        url = window.location.origin + '/items/' + item.id;
                    };
                }

                return url;
            },

            init: function(options) {
                $.extend(this, options);
                this.items = this.syncData.data;

                // pagination
                this.setupInfiniteScroll();

                // selectable for select all | none
                if (this.selectable && ($.type($.fn.selectable) == 'function')) {
                    this.setupSelectable();
                };

                if (this.popup) {
                    this.setupPopup();
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
                    filter: '.photo-card.photo-card-type-image:not(.x-state-exists,.x-state-error)',
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

            getUrl: function(item) {
                var url = item.images.thumbnail.url;
                return url;
            },

            _getHdUrl: function(item) {
                var url = this.getUrl(item);

                // try to use unofficial path for highest resolution
                // data: https://scontent.cdninstagram.com/t51.2885-15/s640x640/sh0.08/e35/12394075_1504607683175422_1353233513_n.jpg path: standard_resolution
                // old changed:  https://igcdn-photos-f-a.akamaihd.net://t51.2885-ak-15/s640x640/e35/12394075_1504607683175422_1353233513_n.jpg path: unofficial ig
                // now changed:  https://igcdn-photos-g-a.akamaihd.net/hphotos-ak-xta1/t51.2885-15/e35/12394075_1504607683175422_1353233513_n.jpg
                var reg = /.+?\:\/\/.+?(\/.+?)(?:#|\?|$)/;
                var path = reg.exec(url)[1];

                var host = 'https://igcdn-photos-f-a.akamaihd.net',
                    paths = path.split('/');

                var hdUrl = [host, 'hphotos-ak-xta1', paths[1], paths[paths.length - 2], paths[paths.length - 1]].join('/');

                return hdUrl;
            },

            getHdUrl: function(item) {
                var url = item.images.standard_resolution.url;
                return url;
            },

            getSelectedItems: function() {
                var self = this,
                    $selectable = $(self.$el).selectable('instance'),
                    items = [];

                $selectable.selectees.each(function() {
                    var $el = $(this),
                        data = $el.data('selectable-item');

                    if (!data.selected) return;

                    var id = $el.data('id').split('-').pop(),
                        item = self.getItem(id);
                    
                    items.push(item);
                });

                return items;
            },

            getItem: function(id) {
                var self = this,
                    found = false,
                    item = null;

                $.each(this.items, function(key, data) {
                    if (found) return;

                    if (data.id == id) {
                        item = data;
                        found = true;
                    }
                });

                return item;
            },

            getPopup: function() {
                var self = this;
                if ($.type(self.$popup) == 'undefined') {
                    var tpl = [

                        // @note ORIGINAL
                        /*
                        '<div class="multiproduct-modal modal fade" tabindex="-1" role="dialog">',
                            '<div class="modal-dialog">',
                                '<div class="modal-content">',
                                    '<div class="modal-header">',
                                        '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span',
                                                    ' aria-hidden="true">&times;</span></button>',
                                        '<h3 class="modal-title">Items to Shop</h3>',
                                    '</div>',
                                    '<div class="modal-body">',

                                        '<div class="multiproduct-view">',
                                            '<div class="multiproduct-view-inner">',
                                                '<div class="row no-inner-gutter container-row">',
                                                    '<div class="multiproduct-photo-container col-xs-12 col-sm-7">',
                                                        '<div class="squared-product-details-image-div">',
                                                            '<div class="spatial-tag-container photo-tags-wrapper">',
                                                            '</div>',
                                                        '</div>',
                                                    '</div>',
                                                    '<div id="multiproduct-list" class="col-sm-5 hidden-xs multiproduct-product-list">',
                                                        '<div class="multiproduct-products">',
                                                        '</div>',
                                                    '</div>',
                                                '</div>',
                                            '</div>',
                                        '</div>',


                                    '</div>',
                                '</div><!-- /.modal-content -->',
                            '</div><!-- /.modal-dialog -->',
                        '</div><!-- /.modal -->'
                        */

                        // @note CIPEHR.CO.TH
                        '<div class="cps multiproduct-modal modal fade" tabindex="-1" role="dialog">',
                            '<div class="modal-dialog">',
                                '<div class="modal-content">',

                                    '<div class="modal-body">',

                                        '<div class="multiproduct-view">',
                                            '<div class="multiproduct-view-inner">',

                                                '<div class="row menu-row">',
                                                    '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span',
                                                        ' aria-hidden="true">&times;</span></button>',
                                                    '<label>Store</label>',
                                                '</div>',

                                                '<div class="row no-inner-gutter container-row">',
                                                    '<div class="multiproduct-photo-container col-xs-12 col-sm-7">',
                                                        '<div class="squared-product-details-image-div">',
                                                            '<div class="spatial-tag-container photo-tags-wrapper">',
                                                                '',
                                                            '</div>',
                                                        '</div>',
                                                    '</div>',

                                                    '<div id="multiproduct-list" class="col-xs-12 col-sm-5 multiproduct-product-list">',

                                                        '<div class="modal-header">',
                                                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span',
                                                                ' aria-hidden="true">&times;</span></button>',
                                                            '<h3 class="modal-title">Items to Shop</h3>',
                                                        '</div>',
                                                        '<div class="multiproduct-products">',
                                                            '',
                                                        '</div>',

                                                    '</div>',

                                                '</div>',
                                            '</div>',
                                        '</div>',


                                    '</div>',
                                '</div><!-- /.modal-content -->',
                            '</div><!-- /.modal-dialog -->',
                        '</div><!-- /.modal -->'

                    ].join('');

                    self.$popup = $(tpl).appendTo('body');
                    self.$popup.on("hidden.bs.modal", function () {
                        $('.modal-backdrop').remove();
                    });
                };

                return self.$popup;
            },

            showPopup: function($el) {
                var self = this,
                    id = $el.data('id').split('-').pop(),
                    item = self.getItem(id);

                // @fixed
                var doAutoName = false;

                if (item.points.length == 1) {
                    window.location.href = $el.find('a').attr('href');
                    return;
                };
                    
                var $popup = self.getPopup(),
                    $photoViewer = $('.multiproduct-photo-container', $popup),
                    $tagWrapper = $('.squared-product-details-image-div', $popup),
                    $tagContainer = $('.spatial-tag-container', $popup),
                    $productList = $('.multiproduct-product-list', $popup),
                    $productContainer = $('.multiproduct-products', $popup);

                // reset 
                $tagWrapper.css('background-image', '');
                $productContainer.html('');
                $tagContainer.html('');

                $popup.one('shown.bs.modal', function () {

                    // calc photo dimension
                    var maxWidth = $photoViewer.outerWidth(),
                        maxHeight = $productList.outerHeight();

                    var url = item.images.standard_resolution.url;

                    // @todo read width and height from server response
                    var image = new Image();
                    image.onload = function(evt) {
                        var width = this.width;
                        var height = this.height;

                        if (width == height) {
                            $tagContainer.width(maxWidth).height(maxWidth);
                        } else if (width > height) {
                            $tagContainer.width(maxWidth).height( (maxWidth * height / width).toFixed(2) );
                        } else {
                            $tagContainer.width( (maxHeight * width / height).toFixed(2) ).height(maxHeight);
                        };

                        $tagContainer.css({
                            marginTop: ($tagContainer.height() / 2 * -1).toFixed(2) + 'px',
                            marginLeft: ($tagContainer.width() / 2 * -1).toFixed(2) + 'px'
                        });

                        // set image
                        $tagWrapper.css('background-image', 'url(' + url + ')');

                        var applyOnClick = function($el, pos) {
                            $el
                                .css('cursor', 'pointer')
                                .click(function(e) {
                                    e.preventDefault;
                                    window.location.href = pos.url;
                            });
                        };

                        // @todo add tags
                        $.each(item.points, function(key, pos) {
                            // console.log(pos, 'pos');
                            // console.log('width: ' + width + ', height: ' + height);
                            // console.log('posX: ' + pos.posX + ', posY: ' + pos.posY);

                            var $el = $([
                                '<div class="photo-tags-tag">',
                                    '<span>' + pos.number + '</span>',
                                '</div>'
                            ].join('')).appendTo($tagContainer);

                            // console.log($el.outerWidth(), '$el.outerWidth()');
                            // console.log($el.outerHeight(), '$el.outerHeight()');

                            var gap = parseInt(Math.max($el.outerWidth(), $el.outerHeight()) / 2);
                            $el.css({
                                left: parseInt($tagContainer.width() * pos.posX / width) - gap,
                                top: parseInt($tagContainer.height() * pos.posY / height) - gap,
                            })

                            applyOnClick($el, pos);
                        });

                        // set preview
                        $.each(item.points, function(key, pos) {
                            var $el = $([
                                '<div class="photo-tags-preview-item">',
                                    '<div class="squared-photo-div">',
                                    '</div>',
                                    '<a href="' + pos.url + '" class="photo-tags-name">',
                                        '<span class="photo-tags-name-text"></span>',
                                    '</a>',
                                    '<div class="photo-tags-tag">',
                                        '<span>' + pos.number + '<span>',
                                    '</div>',
                                '</div>'
                            ].join('')).appendTo($productContainer);

                            var imageUrl = pos.imageUrl || url;
                            $el.find('.squared-photo-div').css('background-image', 'url(' + imageUrl + ')');

                            var name = pos.name || '';
                            if (doAutoName && !name.length) {
                                name = 'Untitled (' + pos.number + ')';
                            };

                            $el.find('.photo-tags-name > span:first').text(name);

                            applyOnClick($el, pos);
                        });

                    };

                    image.src = url; 
                });

                $popup.modal('show');
            },

            setupPopup: function() {
                var self = this;
                $(this.$el).on('click', '.photo-card', function(e) {
                    e.preventDefault();
                    self.showPopup($(this));
                });
            },

            selecetAll: function() {
                // console.log('selecetAll()');
            },
            
            deselectAll: function() {
                // console.log('deselecetAll()');
            }
        }
    }
</script>