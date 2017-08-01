ig = {

    first: true,
    limit: 9999,
    size: 12,
    nodes: [],

    json: function(url) {
        var callback = arguments[1] || function() {};
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var data = JSON.parse(this.responseText);
                callback(data);
            }
        };
        xhttp.open("GET", url, true);
        xhttp.send();
    },

    getUrlParameter: function(url, name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(url);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    },

    start: function() {
        var self = this;
        self.nodes = window._sharedData.entry_data.ProfilePage[0].user.media.nodes;
        console.log('start');
        console.log('processing....');
    },

    end: function() {
        var self = this;
        console.log('end');
        // console.log(self.nodes, 'self.nodes');
    },

    run: function(url) {
        var self = this;
            
        if (self.first) {
            self.baseUrl = url.substring(0, url.indexOf('?') );
            self.query_id = self.getUrlParameter(url, 'query_id'),
            self.variables = self.getUrlParameter(url, 'variables');

            self.start();
        };

        self.first = false;

        if (!window._sharedData.entry_data.ProfilePage[0].user.media.page_info.has_next_page) {
            self.end();
            return;
        };


        self.json(url, function(data) {

            if (data.status == 'ok') {

                if (data.data.user === null) {
                    self.end();
                    return;
                };

                var target = data.data.user.edge_owner_to_timeline_media;
                // target.page_info.has_next_page || 
                if (target.page_info.end_cursor.length) {

                    for (var i in target.edges) {
                        self.nodes.push(target.edges[i].node);
                        if (self.nodes.length == self.limit) {
                            self.end();
                            return;
                        };
                    };
                    
                    var v = JSON.parse(self.variables);
                    v.after = target.page_info.end_cursor;
                    v.first = self.size;

                    var nextUrl = self.baseUrl + '?query_id=' + self.query_id + '&variables=' + encodeURIComponent(JSON.stringify(v));

                    self.run(nextUrl);

                } else {
                    self.end();
                }
            } else {
                console.log(data, 'error');
            }
        });


    }
};
