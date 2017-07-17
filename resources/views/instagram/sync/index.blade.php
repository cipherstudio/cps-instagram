@extends('voyager::master')

@section('page_title','All '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-cloud-download"></i> Instagram Sync

        <form method="POST" action="{{ route('instagram.sync.import') }}" style="display:inline">
            {{ csrf_field() }}
            <a href="javascript:void" class="btn btn-success instagram-media-import">
                <i class="voyager-cloud-download"></i> Import
            </a>
        </form>
    </h1>
@stop

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6">

                </div>
                <div class="col-sm-6">
                    <div class="pull-right">
                    <a href="javascript:void(0);" class="instagram-media-select-all">Select All</a> / 
                    <a href="javascript:void(0);"  class="instagram-media-deselect-all">Deselect All</a>
                    </div>
                </div>
            </div>
            <instagram ref="instagram"></instagram>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/instagram.css') }}">
@stop

@section('javascript')
<script type="text/javascript" src="{{ URL::asset('js/instagram.js') }}"></script>
<script type="text/javascript">

    $(function() {
        vm.$refs.instagram.init({
            syncUrl: '{{ $syncUrl }}',
            syncData: {!! json_encode($syncData) !!},
            column: 6,
            subtitle: false,
            popup: false
        });
    });

    /* select all / deselect all */

    var selectableClick = function(instance, el) {
        var defaultEvent = {
            target: el,
            currentTarget: el,
            delegateTarget: el,
            ctrlKey: true
        };
        
        var event = $.extend({}, $.Event('mousedown.selectable'), defaultEvent);
        
        var ret = instance._mouseStart.call(instance, event);
        event.type = 'mouseup.selectable';
        var ret = instance._mouseStop.call(instance, event);
    };

    var selectableAllClick = function(previewsContainerSelector, filter) {
        var container = $(previewsContainerSelector),
            instance = container.data('ui-selectable');

        container.selectable('refresh');

        container.find('.ui-selectee' + filter).each(function() {
            selectableClick(instance, this);
        }); 
    };

    $('.instagram-media-select-all').click(function(e) {
        selectableAllClick('.ui-selectable', ':not(.ui-selected)');
    });
    $('.instagram-media-deselect-all').click(function(e) {
        selectableAllClick('.ui-selectable', '.ui-selected');
    });

    /* import */

    $('.instagram-media-import').click(function(e) {
        var self = this;
        e.preventDefault();

        var $el = $(this),
            $container = $('.ui-selectable'),
            $photos = $container.find('.ui-selected');

        if (!$photos.length) {
            console.log('empty selected photos.');
            return;
        };

        var items = [];
        $photos.each(function() {
            // now url is standard resolution
            var url = $('.squared-photo-div', this)
                .css('background-image')
                .replace(/^url\(['"](.+)['"]\)/, '$1');

            // try to use unofficial path for highest resolution
            // data: https://scontent.cdninstagram.com/t51.2885-15/s640x640/sh0.08/e35/12394075_1504607683175422_1353233513_n.jpg path: standard_resolution
            // old changed:  https://igcdn-photos-f-a.akamaihd.net://t51.2885-ak-15/s640x640/e35/12394075_1504607683175422_1353233513_n.jpg path: unofficial ig
            // now changed:  https://igcdn-photos-g-a.akamaihd.net/hphotos-ak-xta1/t51.2885-15/e35/12394075_1504607683175422_1353233513_n.jpg
            var reg = /.+?\:\/\/.+?(\/.+?)(?:#|\?|$)/;
            var path = a = reg.exec(url)[1];

            var host = 'https://igcdn-photos-f-a.akamaihd.net',
                paths = path.split('/');

            var hdUrl = [host, 'hphotos-ak-xta1', paths[1], paths[paths.length - 2], paths[paths.length - 1]].join('/');

            items.push({
                url: url,
                hd_url: hdUrl
            });
        });

        var $form = $el.parent(),
            _token = $form.find('[name=_token]').val(),
            url = $form.attr('action');

        $(self).addClass('disabled');

        $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': _token
            },
            data: {items: items},

            // @todo error handler

            success: function(data) {

                // @todo action after success
                // console.log(data, 'data');
                // return;


                $(self).removeClass('disabled');

                // @fixed 
                window.location.href = "{{ url("admin/instagram-media") }}";
            }
        });

    });

</script>
@stop
