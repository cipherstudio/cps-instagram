@extends('voyager::master')

@section('page_title','All '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-anchor"></i> Instagram Media Points

        {{--
        <form method="POST" action="{{ route('instagram.sync.import') }}" style="display:inline">
            {{ csrf_field() }}
            <a href="javascript:void" class="btn btn-success instagram-media-import">
                <i class="voyager-cloud-download"></i> Save
            </a>
        </form>
        --}}

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
                <div class="col-sm-6">
                    <img class="" src="{{ Voyager::image($dataTypeContent->url) }}">
                </div>
                <div class="col-sm-6">
                    xxx yyy zzz
                </div>
            </div>

        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/instagram.css') }}">
@stop

@section('javascript')
<script type="text/javascript">
    console.log('points...');
</script>
@stop