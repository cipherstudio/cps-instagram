@extends('voyager::master')

@section('page_title','All '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-cloud-download"></i> Instagram Sync
    </h1>
@stop

@section('content')

    <div infinite-scroll="" infinite-scroll-immediate-check="false" infinite-scroll-distance="0.5">
        <div class="row small-gutter content">
            @foreach ($items as $item)
                <div class="photo-card col-xs-2">
                    <div class="photo-card-box">
                        <div class="photo-card-box-inner">
                            <div class="video-wrapper"></div>
                            <span>
                                <span>
                                    <span>
                                        <a href="">
                                            <div class="squared-photo-div" style="background-image: url({{ $item['images']['standard_resolution']['url'] }});">

                                            </div>
                                        </a>
                                    </span>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@stop

@section('css')
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/instagram.css') }}">
@stop

@section('javascript')

@stop
