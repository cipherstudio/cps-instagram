@extends('voyager::master')

@section('page_title','All '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-cloud-download"></i> Instagram Sync

        <label style="margin-left:15px">Advanced: 
            <input type="checkbox" name="advanced" value="" @if($advanced)checked="checked"@endif />
        </label>
        

        <a href="{{ route('voyager.'.$dataType->slug.'.index') }}" class="btn btn-warning" style="float:right;margin-left:15px;">
            <span class="glyphicon glyphicon-list"></span>&nbsp;
            Return to List
        </a>  

        <form id="normalForm" method="POST" action="{{ route('instagram.sync.import') }}" @if($advanced)class="hidden"@endif>
            {{ csrf_field() }}
            <a href="javascript:void(0)" class="btn btn-success instagram-media-import">
                <i class="voyager-cloud-download"></i> 
                <i class="glyphicon glyphicon-refresh hidden"></i> Import
            </a>
        </form>

        <form id="advancedForm" method="POST" action="{{ route('instagram.sync.sync') }}" @if(!$advanced)class="hidden"@endif style="margin-left:15px;">
            {{ csrf_field() }}
            <select name="interval">
                <option value="1 day" @if($intervalDefault == "1 day")selected="selected"@endif>1 day</option>
                <option value="3 days" @if($intervalDefault == "3 days")selected="selected"@endif>3 days</option>
                <option value="1 week" @if($intervalDefault == "1 week")selected="selected"@endif>1 week</option>
                <option value="2 weeks" @if($intervalDefault == "2 weeks")selected="selected"@endif>2 weeks</option>
                <option value="1 month" @if($intervalDefault == "1 month")selected="selected"@endif>1 month</option>
                <option value="2 months" @if($intervalDefault == "2 months")selected="selected"@endif>2 months</option>
                <option value="6 months" @if($intervalDefault == "6 months")selected="selected"@endif>6 months</option>
                <option value="1 year" @if($intervalDefault == "1 year")selected="selected"@endif>1 year</option>
            </select>
            <a href="javascript:void(0)" class="btn btn-success instagram-media-import-auto">
                <i class="voyager-cloud-download"></i> 
                <i class="glyphicon glyphicon-refresh hidden"></i> Import
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
<style>
.glyphicon-refresh {
    -webkit-animation: spin 0.6s infinite linear;
    -moz-animation: spin 0.6s infinite linear;
    animation: spin 0.6s infinite linear;
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

    // loading and block screen
    var loadingOn = function(el) {
        $(el)
            .addClass('disabled')
            .find('i').toggleClass('hidden');

        $('<div class="modal-backdrop"></div>')
            .css({
                opacity: 0.2,
                cursor: 'wait'
            })
            .appendTo(document.body);

        // @see voyager : master.blade.php
    };

    var loadingOff = function(el) {
        $(el)
            .removeClass('disabled')
            .find('i').toggleClass('hidden');

        $('.modal-backdrop').remove();
    };

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

        var instagram = vm.$refs.instagram;
        var items = [];
        $.each(instagram.getSelectedItems(), function(key, value) {
            items.push({
                // url: instagram.getUrl(value),
                // hd_url: instagram.getHdUrl(value),
                data: value
            });
        });

        var $form = $el.parent(),
            _token = $form.find('[name=_token]').val(),
            url = $form.attr('action');

        loadingOn(self);

        $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': _token
            },
            data: {items: items},

            success: function(data) {
                loadingOff(self);
                window.location.href = "{{ url("admin/instagram-media") }}";
            },

            // @todo error handler
            error: function(jqXHR, textStatus, errorThrow) {
                var errorMsg = errorThrow.message;
                console.log(errorMsg, 'errorMsg');
            }
        });

    });

    /* advanced import */
    $('[name="advanced"]').on('change', function() {
        var checked = $(this).is(':checked'),
            $normalForm = $('#normalForm'),
            $advancedForm = $('#advancedForm');
        if (checked) {
            $normalForm.addClass('hidden');
            $advancedForm.removeClass('hidden');
        } else {
            $normalForm.removeClass('hidden');
            $advancedForm.addClass('hidden');
        };
    });

    $('.instagram-media-import-auto').click(function(e) {
        var self = this;
        e.preventDefault();

        var $el = $(this),
            $form = $el.parent(),
            token = $form.find('[name=_token]').val(),
            syncUrl = $form.attr('action'),
            interval = $('[name="interval"]').val();

        var total = 0;
        var $toastr = null;
        var title = 'Instagram Sync';

        var start = function() {
            loadingOn(self);
        };

        var end = function() {
            loadingOff(self);
            $toastr.addClass('hidden');
            $toastr = toastr.success('Imported Media has been successfully.', title);
            setTimeout(function() {
                window.location.href = "{{ url("admin/instagram-media") }}";
            }, 1500);
        };

        var sync = function() {
            var url = arguments[0] || '';
                data = {interval: interval};

            if (url.length) {
                data.url = url;
            } else {
                start();
            };

            $.ajax({
                type: "POST",
                url: syncUrl,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: data,

                success: function(data) {
                    try {
                        var nextUrl = data.pagination.next_url || '';

                        // simple progressbar
                        var _total = total;
                        total += data.added.length;
                        if (total > _total) {
                            var msg = 'Imported ' + total + ' items.';
                            if ($.type($toastr) == 'null') {
                                $toastr = toastr.info(msg, title, {timeOut: 86400 * 1000});
                            } else {
                                $toastr.find('.toast-message').text(msg);
                            };
                        };

                        if (nextUrl.length) {
                            sync(nextUrl);
                        } else {
                            end();
                        }
                    } catch (e) {
                        console.log(e, 'error');
                    }
                },

                // @todo error handler
                error: function(jqXHR, textStatus, errorThrow) {
                    var errorMsg = errorThrow.message;
                    console.log(errorMsg, 'error');
                }
            });
        };

        sync();
    });

</script>
@stop