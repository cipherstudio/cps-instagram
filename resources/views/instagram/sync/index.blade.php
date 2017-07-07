@extends('voyager::master')

@section('page_title','All '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-cloud-download"></i> Instagram Sync
    </h1>
@stop

@section('content')

    <instagram></instagram>
    

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
