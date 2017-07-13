@extends('voyager::master')

@section('page_title','All '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-anchor"></i> Instagram Media Points

        <form method="POST" action="{{ route('instagram.sync.import') }}" style="display:inline;float:right">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $id }}" />
            <a href="javascript:void(0);" class="btn btn-success instagram-point-points">
                <i class="voyager-cloud-download"></i> Save
            </a>
        </form>

        <a href="{{ route('voyager.'.$dataType->slug.'.index') }}" class="btn btn-warning">
            <span class="glyphicon glyphicon-list"></span>&nbsp;
            Return to List
        </a>  

    </h1>
@stop

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered">

        <div class="panel-heading" style="border-bottom:0;">
            <h3 class="panel-title"> Instagram Media Points </h3>
        </div>
        <div class="panel-body" style="padding-top:0;">


            <div class="row">
                <div class="col-sm-8" style="background:#46be8a;padding:8px">
                    <img class="photo-tags" src="{{ Voyager::image($dataTypeContent->url) }}">
                </div>
                <div class="col-sm-4">

                    <form class="" role="form" method="POST" action="" enctype="multipart/form-data">

                        {{--
                        <div class="form-group">
                            <label for="">URL</label>
                            <input type="text" class="form-control" name="url" id="" value="" placeholder="" />
                        </div>

                        <div class="form-group">
                            <label for="">Pointing</label>
                            <img src="" class="hidden" />
                            <input type="file" name="" />
                        </div>
                        --}}
                        <div class="photo-tags-preview">

                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/instagram.css') }}">

<style>


</style>
@stop

@section('javascript')
<script type="text/javascript">
    // console.log('points...');
    var points = <?php echo json_encode($points);?>;

    // @todo jquery plugin
    (function ( $ ) {
        $.fn.photoTags = function() {
            var o  = arguments[0] || {};
            $el = this;

            var options = $.extend({
                // predefined
                prefix: 'photo-tags',
                disabled: false,
                readonly: false,
                previewContainer: '',
                size: 30,
                radius: '50%',
                backgroundColor: '#fff'

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

                options: options,

                init: function() {
                    var self = this;

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
                        size = self.options.size,
                        gap = parseInt(size / 2);

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
                                '<div class="form-group">',
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

                        // @todo validate form
                        if (!url.length) {
                            console.log('error: URL is empty');
                            return;
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
                            if (!name.length) {
                                name = getFileName(filename);
                            };
                        } else {
                            pos.$image = self.$el.attr('src');
                        };

                        if (!name.length) {
                            name = url.split('/').pop();
                        }

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
                        size = self.options.size,
                        gap = parseInt(size / 2);

                    var $tag = $('<div class="' + self.prefix + '-tag"><span>' + pos.number + '</span></div>')
                        .data('pos', pos)
                        .css({
                            //position: 'absolute',
                            left: (pos.posX - gap),
                            top: (pos.posY - gap)
                        })
                        .appendTo(self.$wrapper);

                    
                    if (self.$previewContainer) {
                        $tag.$preview = self.createPreview(pos);
                    };

                    self.$tags.push($tag);
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
                        $tag.data('pos', pos);
                        $tag.find('> span').text(number);

                        if ($.type($tag.$preview) !== 'undefined') {
                            $tag.$preview.data('pos', pos);
                            $tag.$preview.find('.' + self.prefix + '-tag > span').text(number);
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
                    }

                    $('[type="submit"]', self.$deleteModal)
                        .unbind('click')
                        .bind('click', function(e) {
                            self.$deleteModal.modal('hide');
                            callback();
                        });

                    self.$deleteModal.modal('show');
                    return self.$deleteModal;
                },
                
                createPreview: function(pos) {
                    var self = this;
                    var name = pos.name || ('Untitled (' + pos.number + ')');

                    var deleteButtonTpl = this.readonly ? '' : '<span class="' + this.prefix + '-delete' + (self.readonly ? 'hidden' : '') + '"></span>';

                    var tpl = [
                        '<div class="' + this.prefix + '-preview-item">',
                            '<div class="squared-photo-div"></div>',
                            '<a href="' + pos.url + '" class="' + this.prefix + '-name">' + name,
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

                    // renderer
                    switch ($.type(pos.$image)) {
                        case 'string':
                            pos.$image.length && $preview.find('.squared-photo-div').css('background-image', 'url("' + pos.$image + '")');
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

                    $preview.data('pos', pos);

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

    

    var photoTags = $('.photo-tags').photoTags().data('photoTags');
    $.each(points, function(key, point) {
        point.$image = point.imageUrl;
        photoTags.createTag(point);
    });

    $('.instagram-point-points').click(function(e) {
        e.preventDefault();

        var $items = $('.photo-tags-preview-item');

        if (!$items.length) {
            console.log('$items is empty');
            return;
        };


        var mediaId = $(this).siblings('[name="id"]').val();
        if (!mediaId.length) {
            console.log('error: mediaId is empty');
            return;
        }

        var data = new FormData();

        $items.each(function(key, $preview) {
            var pos = $(this).data('pos');

            // @todo files is option
            try {
                if ($.type(pos.$image) == 'object') {
                    var file = pos.$image[0].files[0];
                    data.append(key, file);
                };
            } catch (e) {
                console.log(e, 'error');
            };

            data.append('items[' + key + '][mediaId]', mediaId);

            delete pos.$image;
            $.each(pos, function(k, v) {
                data.append('items[' + key + '][' + k + ']', v);
            });
        });

        $.ajax({
            url: '{{ route('instagram.point.save-points') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('[name=_token]').val()
            },
            data: data,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function(data, textStatus, jqXHR)
            {
                console.log(data, 'data');
                return;


                if(typeof data.error === 'undefined')
                {
                    // Success so call function to process the form
                    submitForm(event, data);
                }
                else
                {
                    // Handle errors here
                    console.log('ERRORS: ' + data.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Handle errors here
                console.log('ERRORS: ' + textStatus);
                // STOP LOADING SPINNER
            }
        });



    });

</script>
@stop