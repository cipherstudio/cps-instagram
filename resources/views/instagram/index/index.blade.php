@extends('instagram.index')

@section('content')
    <div class="content-container featured desktop">
        <div class="content-container-inner">

            {{-- switch loading --}}
            <div class="loading-view"></div>
            <div class="">
                <div class="row small-gutter subtitle">
                    <div class="col-xs-12">
                        <div>
                            <span>Click an image to shop</span>
                        </div>
                        <hr />
                    </div>
                </div>
                <div infinite-scroll="" infinite-scroll-immediate-check="false" infinite-scroll-distance="0.5">
                    <div class="row small-gutter content">
                        @for ($i = 0; $i < 20; $i++)
                            <div class="photo-card col-xs-3">
                                <div class="photo-card-box">
                                    <div class="photo-card-box-inner">
                                        <div class="video-wrapper"></div>
                                        <span>
                                            <span>
                                                <span>
                                                    <a href="">
                                                        <div class="squared-photo-div" style="background-image: url(https://instagram.fbkk5-7.fna.fbcdn.net/t51.2885-15/s640x640/sh0.08/e35/17076128_619774714876000_9147438196177502208_n.jpg);">

                                                        </div>
                                                    </a>
                                                </span>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection