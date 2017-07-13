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
    .photo-tags {
        user-drag: none; 
        -webkit-user-drag: none;
    }
    .photo-tags-wrapper {
        z-index: 5;
        position: relative;

        user-select: none;
        -moz-user-select: none;
        -khtml-user-select: none;
        -webkit-user-select: none;
        -o-user-select: none;
    }
    .photo-tags-tag {
        position: absolute;

        text-align: center;
        background-color: #fff;
        width: 28px;
        height: 28px;
        border-radius: 100px;
        padding-top: 2px;

        -webkit-box-shadow: 0 0 5px 0 #3e3e3e;
        -moz-box-shadow: 0 0 5px 0 #3e3e3e;
        box-shadow: 0 0 5px 0 #3e3e3e;
    }
    .photo-tags-tag > span {
        display: inline-block;
        font-size:.8em;
        font-weight: 700;
        cursor: pointer;
        color: #3e3e3e;
    }
    .photo-tags-preview {

    }
    .photo-tags-preview-item {
        float: left;
        padding: 0!important;
        position:relative;

        /*width:50%;*/
        width: calc(50% - 30px);
        margin-left: 15px;
    }
    .photo-tags-preview-item .photo-tags-tag {
        position: absolute;
        top: 28px;
        left: 8px;
    }
    .photo-tags-preview-item .name {
        font-weight: 700;
        font-size: .85em;
        width: 100%;
        display: inline-block;
        overflow: hidden;
        clear: both;
        float: left;
        height: 30px;
        line-height: 30px;
        text-align:center;
    }

    .photo-tags-form-wrapper {
        position: absolute;
        background-color: #22a7f0;
        color: white;
        width: 200;
        z-index: 10;
        cursor: default;
        padding: 10px;
        font-size: .9em;
    }
    .photo-tags-form-wrapper form{

    }
    .photo-tags-form-wrapper a{
        color: #fff;
    }


</style>
@stop

@section('javascript')
<script type="text/javascript">
    // console.log('points...');

    // @todo jquery plugin
    (function ( $ ) {
        $.fn.photoTags = function() {
            var o  = arguments[0] || {};
            $el = this;

            var options = $.extend({
                // predefined
                prefix: 'photo-tags',
                disabled: false,
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
                            name = getFileName(filename);
                            pos.$image = $image;
                        } else {
                            pos.$image = self.$el.attr('src');
                        }

                        if (!name.length) {
                            name = url.split('/').pop();
                        }

                        pos.name = name;
                        pos.url = url;

                        self.hideForm();

                        self.createTag(pos);
                        if (self.$previewContainer) {
                            self.createPreview(pos);
                        }
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

                    $tag = $('<div class="' + self.prefix + '-tag"><span>' + pos.number + '</span></div>')
                        .data('pos', pos)
                        .css({
                            //position: 'absolute',
                            left: (pos.posX - gap),
                            top: (pos.posY - gap)
                        })
                        .appendTo(self.$wrapper);

                    self.$tags.push($tag);
                },
                
                createPreview: function(pos) {
                    var $preview = $([
                        '<div class="' + this.options.prefix + '-preview-item">',
                            '<div class="squared-photo-div"></div>',
                            '<a href="' + pos.url + '" class="name">' + pos.name + '</a>',
                            '<div class="' + this.prefix + '-tag">',
                                '<span>',
                                pos.number,
                                '</span>',
                            '</div>',
                        '</div>'
                    ].join(''))
                        .appendTo(this.$previewContainer);


                    // renderer
                    switch ($.type(pos.$image)) {
                        case 'string':
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

                    $preview.data('pos', pos);
                }
            };
            
            photoTags.init();
            $el.data('photoTags', photoTags);
        };
    } (jQuery));

    $('.photo-tags').photoTags();

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