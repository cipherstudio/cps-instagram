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
                <div class="col-sm-8" style="background:#f3f7f9;padding:8px">
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
<script type="text/javascript" src="{{ URL::asset('js/dist/jquery.photo-tags.js') }}"></script>
<script type="text/javascript">
    // console.log('points...');
    var points = <?php echo json_encode($points);?>;

    var photoTags = $('.photo-tags').photoTags().data('photoTags');
    $.each(points, function(key, point) {
        point.$image = point.imageUrl;
        photoTags.createTag(point);
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

</script>
@stop