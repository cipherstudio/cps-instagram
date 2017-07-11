@extends('voyager::master')

@section('page_title','All '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-cloud-download"></i> Instagram Sync
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
                    <a href="#" class="instagram-media-select-all">Select All</a> / <a href="#"  class="instagram-media-deselect-all">Deselect All</a>
                    </div>
                </div>
            </div>
            <instagram></instagram>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/instagram.css') }}">
@stop

@section('javascript')
<script type="text/javascript">
    $('body')
        .data('syncUrl', '{{ route('instagram.sync.load') }}')
        .data('syncData', {!! json_encode($syncData) !!});
</script>
<script type="text/javascript" src="{{ URL::asset('js/instagram.js') }}"></script>
@stop
