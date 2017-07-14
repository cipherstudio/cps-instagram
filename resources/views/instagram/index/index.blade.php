@extends('instagram.index')

@section('content')
    <div class="content-container featured desktop">
        <div class="content-container-inner">
            <instagram ref="instagram"></instagram>
        </div>
    </div>
@endsection

@section('javascript')
<script type="text/javascript">
    $(function() {
        vm.$refs.instagram.init({
            syncUrl: '{{ $syncUrl }}',
            syncData: {!! json_encode($syncData) !!}
        });
    });
</script>
@endsection