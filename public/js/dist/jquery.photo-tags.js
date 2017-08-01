(function ( $ ) {
    $.fn.photoTags = function() {
        var o  = arguments[0] || {};
        $el = this;

        var options = $.extend({
            // predefined
            prefix: 'photo-tags',
            disabled: false,
            readonly: false,
            editable: true,
            previewContainer: '',
            size: 28,
            radius: '50%',
            backgroundColor: '#fff',

            width: 0,
            height: 0,
            maxWidth: 640,
            maxHeight: 640

        }, o );

        

        if (!options.previewContainer.length) {
            options.previewContainer = '.' + options.prefix + '-preview'
        };

        var photoTags = {
            
            prefix: options.prefix,

            // elements
            $el: $el,
            $form: null,
            $wrapper: null,
            $previewContainer: null,
            $tags: [],

            doAutoName: false,

            options: options,

            init: function() {
                var self = this;

                // @todo resize image
                // console.log(self.options, 'self.options');
                var width = self.options.width,
                    height = self.options.hieght,
                    maxWidth = self.options.maxWidth,
                    maxHeight = self.options.maxHeight;
                if ((maxWidth <= width) || (maxHeight <= height)) {

                    if (width == height) {
                        $el.width(maxWidth).height(maxWidth);
                    } else if (width > height) {
                        $el.width(maxWidth).height( (maxWidth * height / width).toFixed(2) );
                    } else {
                        $el.width( (maxHeight * width / height).toFixed(2) ).height(maxHeight);
                    };

                };

                //$el.attr('draggable', false);
                    
                $el.wrap('<div class="' + self.prefix + '-wrapper"></div>');
                self.$wrapper = $el.parent();

                self.$previewContainer = $(this.options.previewContainer);

                if (!this.options.diabled) {
                    $el.click(function(e) {
                        self.onClick.apply(self, arguments);
                    });
                };
            },

            getGap: function() {
                var self = this,
                    size = self.options.size,
                    gap = parseInt(size / 2);

                return gap;
            },

            onClick: function(e) {
                var offset = $el.offset(),
                    pos = {
                        posX: e.pageX - offset.left,
                        posY: e.pageY - offset.top,
                        number: this.$tags.length + 1
                    };

                if (this.$form) {
                    this.hideForm();
                };

                this.createForm(pos);
                
                
            },

            createForm: function(pos) {
                var self = this,
                    gap = this.getGap();

                // console.log('createForm()');
                // console.log(pos, 'pos');

                var tpl = [
                    '<div class="photo-tags-form-wrapper">',
                        '<form>',
                            '<div class="form-group">',
                                '<label>Name</label>',
                                '<input type="text" name="name" class="form-control">',
                            '</div>',
                            '<div class="form-group">',
                                '<label>URL</label>',
                                '<input type="text" name="url" class="form-control">',
                            '</div>',
                            '<div class="form-group">',
                                '<label>Photo</label>',
                                '<input type="file" name="photo" accept="image/*" />',
                            '</div>',
                            '<div class="form-group error hidden">',
                                '<label></label>',
                            '</div>',
                            '<div class="form-group pull-right">',
                                '<button type="submit" class="btn btn-primary">Submit</button>',
                                ' or ',
                                '<a href="" class="x-close">Close</a>',
                            '</div>',
                        '</form>',
                    '</div>'
                ].join('');

                this.$form = $(tpl)
                    .css({left: pos.posX, top: pos.posY})
                    .appendTo(this.$wrapper);

                this.$form.find('.x-close').click(function(e) {
                    e.preventDefault();
                    self.hideForm();
                });

                this.$form.find('[type="submit"]').click(function(e) {
                    e.preventDefault();

                    var name = self.$form.find('[name="name"]').val() || '',
                        url = self.$form.find('[name="url"]').val() || '',
                        $image = self.$form.find('[name="photo"]');

                    // @todo not allowed in valid url
                    var isValidUrl = function(s) {
                        var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
                        return regexp.test(s);    
                    };

                    if (true !== isValidUrl(url)) {
                        var errorMsg = 'Error: Invalid URL';
                        $(this).parents('form').find('.form-group.error')
                            .removeClass('hidden')
                            .find('> label')
                            .text(errorMsg);
                        return false;
                    };

                    var getFileName = function(path) {
                        var filename = path.replace(/^.*[\\\/]/, '');
                        let tmp = filename.split('.');
                        tmp.pop();
                        return tmp.join('');
                    };

                    // check image
                    var filename = $image.val() || '';
                    if (filename.length) {
                        pos.$image = $image;
                    };

                    pos.name = name;
                    pos.url = url;

                    self.hideForm();

                    self.createTag(pos);
                    
                });

                this.$form.find('[name="name"]').focus();

                // @todo hide menu when outside click
            },

            hideForm: function() {
                this.$form.remove();
                delete this.$form;
            },

            createTag: function(pos) {
                var self = this,
                    doNotRePos = arguments[1] || false;

                var $tag = $('<div class="' + this.prefix + '-tag"><span>' + pos.number + '</span></div>')
                    .appendTo(this.$wrapper);

                this.updateTag(pos, $tag, doNotRePos);

                if (this.options.editable) {
                    var cursor = 'move';
                    $tag.draggable({
                        containment: this.$wrapper
                    })
                        .css('cursor', cursor)
                        .find('>span')
                            .css('cursor', cursor)
                        .end()
                        .on('dragstop', function(event, ui) {
                            var $el = $(this)
                                gap = self.getGap();

                            var pos = $el.data('pos');
                            pos.posX = parseInt($el.css('left')) + gap;
                            pos.posY = parseInt($el.css('top')) + gap;

                            self.updateTag(pos, $el);
                        });
                };

                if (this.$previewContainer) {
                    $tag.$preview = this.createPreview(pos);
                };

                this.$tags.push($tag);
            },

            updateTag: function(pos, $tag) {
                var self = this,
                    doNotRePos = arguments[2] || false;
                    gap = this.getGap();

                console.log('width: ' + this.options.width + ', height: ' + this.options.height 
                    + ', maxWidth: ' + this.options.maxWidth + ', maxHeight: ' + this.options.maxHeight);

                console.log('posX: ' + pos.posX + ', posY: ' + pos.posY);

                // calc new pos for image ration
                var _posX = pos.posX,
                    _posY = pos.posY;

                console.log(doNotRePos, 'doNotRePos');

                if (doNotRePos) {
                    _posX = ($el.width() * pos.posX / this.options.width).toFixed(2);
                    _posY = ($el.height() * pos.posY / this.options.height).toFixed(2);
                } else {
                    pos.posX = (this.options.width * pos.posX / $el.width()).toFixed(2),
                    pos.posY = (this.options.height * pos.posY / $el.height()).toFixed(2);
                };

                console.log('posX: ' + _posX + ', posY: ' + _posY);

                $tag
                    .find('> span:first')
                        .text(pos.number)
                    .end()
                    .css({
                        // left: (pos.posX - gap),
                        // top: (pos.posY - gap)
                        left: (_posX - gap),
                        top: (_posY - gap)
                    })
                    .data('pos', pos);
            },

            removeTag: function(pos) {
                var self = this;
                var $tags = [];
                $.each(self.$tags, function(key, $tag) {
                    var number = $tag.data('pos').number;
                    if (number == pos.number) {
                        if ($.type($tag.$preview) !== 'undefined') {
                            $tag.$preview.remove();
                        };
                        $tag.remove();
                    } else {
                        $tags.push($tag);
                    };
                });

                this.$tags = $tags;

                this.update();
            },

            update: function() {
                var self = this;
                $.each(self.$tags, function(key, $tag) {
                    var number = key + 1;

                    var pos = $tag.data('pos');

                    // update tag
                    pos.number = number;
                    self.updateTag(pos, $tag, true);

                    if ($.type($tag.$preview) !== 'undefined') {
                        self.updatePreview(pos, $tag.$preview);
                    };
                });
            },

            confirmDelete: function(callback) {
                var self = this;

                if ($.type(self.$deleteModal) == 'undefined') {
                    var tpl = [
                        '<div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">',
                            '<div class="modal-dialog">',
                                '<div class="modal-content">',
                                    '<div class="modal-header">',
                                        '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span',
                                                    ' aria-hidden="true">&times;</span></button>',
                                        '<h4 class="modal-title"><i class="voyager-trash"></i> Are you sure you want to delete',
                                            ' this point?</h4>',
                                    '</div>',
                                    '<div class="modal-footer">',
                                        '<input type="submit" class="btn btn-danger pull-right delete-confirm"',
                                                'value="Yes, delete this point">',
                                        '<button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancel</button>',
                                    '</div>',
                                '</div><!-- /.modal-content -->',
                            '</div><!-- /.modal-dialog -->',
                        '</div><!-- /.modal -->'
                    ].join('');

                    self.$deleteModal = $(tpl).appendTo('body');
                    self.$deleteModal.on("hidden.bs.modal", function () {
                        $('.modal-backdrop').remove();
                    });
                };

                $('[type="submit"]', self.$deleteModal)
                    .unbind('click')
                    .bind('click', function(e) {
                        self.$deleteModal.modal('hide');
                        callback();
                    });

                self.$deleteModal.modal('show');
                return self.$deleteModal;
            },

            updatePreview: function(pos, $preview) {
                var self = this;
                var name = pos.name || '';

                var doAutoName = self.options.doAutoName;
                if (doAutoName && !name.length) {
                    name = ('Untitled (' + pos.number + ')');
                };

                // renderer
                switch ($.type(pos.$image)) {
                    case 'undefined':
                        pos.$image = '';

                    case 'string':
                        if (!pos.$image.length) {
                            pos.$image = self.$el.attr('src');
                        };

                        $preview.find('.squared-photo-div').css('background-image', 'url("' + pos.$image + '")');
                        break;

                    case 'object':
                        var file    = pos.$image[0].files[0];
                        var reader  = new FileReader();
                        reader.addEventListener("load", function () {
                            $preview.find('.squared-photo-div').css('background-image', 'url("' + reader.result + '")');
                        }, false);
                        reader.readAsDataURL(file);
                        break;
                };

                $preview.find('.' + this.prefix + '-tag > span:first').text(pos.number);
                $preview.find('.' + this.prefix + '-name > span:first').text(name);

                $preview.data('pos', pos);
            },
            
            createPreview: function(pos) {
                var self = this;

                // element
                
                var deleteButtonTpl = this.readonly ? '' : '<span class="' + this.prefix + '-delete' + (self.readonly ? 'hidden' : '') + '"></span>';

                var tpl = [
                    '<div class="' + this.prefix + '-preview-item">',
                        '<div class="squared-photo-div"></div>',
                        '<a href="' + pos.url + '" class="' + this.prefix + '-name">',
                            '<span class="' + this.prefix + '-name-text">' + pos.name + '</span>',
                            deleteButtonTpl,
                        '</a>',
                        '<div class="' + this.prefix + '-tag">',
                            '<span>',
                            pos.number,
                            '</span>',
                        '</div>',
                    '</div>'
                ].join('');

                var $preview = $(tpl).appendTo(this.$previewContainer);

                self.updatePreview(pos, $preview);

                /* event handler */

                // delelete button
                if (!self.readonly) {
                    $preview.find('.' + this.prefix + '-name').click(function(e) {
                        e.preventDefault();
                    });
                    $preview.find('.' + this.prefix + '-delete').click(function(e) {
                        self.confirmDelete(function() {
                            self.removeTag(pos);
                        });
                    });
                };
                
                return $preview;
            }
        };
        
        photoTags.init();
        $el.data('photoTags', photoTags);

        return this;
    };
} (jQuery));