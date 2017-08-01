@extends('voyager::master')

@section('page_title','All '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-anchor"></i> Instagram Media Points

        <a href="{{ route('voyager.'.$dataType->slug.'.index') }}" class="btn btn-warning" style="float:right;margin-left:15px">
            <span class="glyphicon glyphicon-list"></span>&nbsp;
            Return to List
        </a>  
        <form method="POST" action="{{ route('instagram.sync.import') }}">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $id }}" />
            <a href="javascript:void(0);" class="btn btn-success instagram-point-points">
                <i class="voyager-cloud-download"></i> Save
            </a>
        </form>

    </h1>
@stop

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered">

        <div class="panel-heading" style="border-bottom:0;">
            <h3 class="panel-title"> Instagram Media Points </h3>
        </div>
        <div class="panel-body" style="padding-top:0;">

            <!-- Standard Resolution is 640px -->
            <div class="row" style="margin: auto; max-width:1020px">
                <div class="col-sm-8" style="padding:0">
                    <img class="photo-tags" data-src="{{ Voyager::image($dataTypeContent->url) }}">
                </div>
                <div class="col-sm-4" style="padding:0">

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
    .photo-tags-wrapper{
        border: 10px solid #f3f7f9;
    }
    .page-title form{
        float:right;
        margin-top:1px;
    }
    .page-title form .btn {
        margin-right: 15px;
        min-width: 95px;
    }
</style>
@stop

@section('javascript')
<script type="text/javascript" src="{{ URL::asset('js/dist/jquery.photo-tags.js') }}"></script>
<script type="text/javascript">
    // console.log('points...');
    var points = <?php echo json_encode($points);?>;

    var $el = $('.photo-tags');
    var image = new $('.photo-tags')[0];
    image.onload = function(evt) {

        // console.log('width: ' + this.width + ', height: ' + this.height);

        // if (this.width == 640 && this.height == 640) {
        //     $el.parents('.row:first').css('max-width', '1080px');
        // };

        var photoTags = $el.photoTags({width: this.width, height: this.height}).data('photoTags');
        $.each(points, function(key, point) {
            point.$image = point.imageUrl;
            photoTags.createTag(point, true);
        });

        $('.instagram-point-points').click(function(e) {
            e.preventDefault();

            var $items = $('.photo-tags-preview-item');

            if (!$items.length) {
                // do not anythig
                // edit can empty mode for clear all
            };

            var mediaId = $(this).siblings('[name="id"]').val();
            if (!mediaId.length) {
                console.log('error: mediaId is empty');
                return;
            }

            var data = new FormData();

            data.append('mediaId', mediaId);

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
                    window.location.href = "{{ route('voyager.instagram-media.index') }}"
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
    };

    image.src = $el.data('src');

</script>
@stop