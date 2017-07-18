<div class="side-menu sidebar-inverse">
    <nav class="navbar navbar-default" role="navigation">
        <div class="side-menu-container">
            <div class="navbar-header">
                <a class="navbar-brand" href="{{ route('voyager.dashboard') }}">
                    <div class="logo-icon-container">
                        <?php $admin_logo_img = Voyager::setting('admin_icon_image', ''); ?>
                        @if($admin_logo_img == '')
                            <img src="{{ voyager_asset('images/logo-icon-light.png') }}" alt="Logo Icon">
                        @else
                            <img src="{{ Voyager::image($admin_logo_img) }}" alt="Logo Icon">
                        @endif
                    </div>
                    <div class="title">{{Voyager::setting('admin_title', 'VOYAGER')}}</div>
                </a>
            </div><!-- .navbar-header -->

            <div class="panel widget center bgimage"
                 style="background-image:url({{ Voyager::image( Voyager::setting('admin_bg_image'), config('voyager.assets_path') . '/images/bg.jpg' ) }});">
                <div class="dimmer"></div>
                <div class="panel-content">
                    <img src="{{ $user_avatar }}" class="avatar" alt="{{ Auth::user()->name }} avatar">
                    <h4>{{ ucwords(Auth::user()->name) }}</h4>
                    <p>{{ Auth::user()->email }}</p>

                    <a href="{{ route('voyager.profile') }}" class="btn btn-primary">Profile</a>
                    <div style="clear:both"></div>
                </div>
            </div>

        </div>

        <?php
            $role_name = '';
            $roles = \TCG\Voyager\Models\Role::all();
            $found = false;
            foreach ($roles as $role) {
                if ($role->id == auth()->user()->role_id) {
                    $role_name = $role->name;
                    $found = true;
                }
            }
            if (!$role_name) {
                $role_name = 'admin';
            }
        ?>

        {!! menu($role_name, 'admin_menu') !!}
    </nav>
</div>
