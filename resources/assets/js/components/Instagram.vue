<template>
<div>
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
            // console.log('mounted()');
            // console.log( $('body') , 'body');
            // console.log ( $('#instagram .content')[0], 'target');
            // console.log( $('body').data('instagramSyncData') , 'instagramSyncData');

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
                $(this.$el).selectable({
                    filter: '.photo-card'
                });
            }
        }
    }
</script>