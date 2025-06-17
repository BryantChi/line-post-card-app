<!-- need to remove -->
{{-- <li class="nav-item">
    <a href="{{ route('home') }}" class="nav-link {{ Request::is('home') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Home</p>
    </a>
</li>

<li class="nav-item mb-4">
    <a href="{{ route('home') }}" target="_blank" class="nav-link">
        <span class="h5 mr-2 brand-image"><i class="fas fa-external-link-alt"></i></span>
        <p class="h5"> 瀏覽網站</p>
    </a>
</li> --}}

<li class="nav-item {{ Auth::user()->isSuperAdmin() ? '' : 'd-none' }}">
    <a href="{{ route('admin.adminUsers.index') }}"
        class="nav-link {{ Request::is('admin/adminUsers*') ? 'active' : '' }}">
        <span class="mr-2 brand-image"><i class="fas fa-users-cog"></i></span>
        <p> 超級管理員</p>
    </a>
</li>

<li class="nav-item {{ Auth::user()->isSuperAdmin() || Auth::user()->isMainUser() ? '' : 'd-none' }}">
    <a href="{{ route('super_admin.mainUsers.index') }}"
        class="nav-link {{ Request::is('admin/main-users*') ? 'active' : '' }}">
        <span class="mr-2 brand-image"><i class="fas fa-users-cog"></i></span>
        <p> 管理員</p>
    </a>
</li>

<li class="nav-item {{ Auth::user()->isSuperAdmin() || Auth::user()->isMainUser() ? '' : 'd-none' }}">
    <a href="{{ route('sub-users.index') }}"
        class="nav-link {{ Request::is('admin/sub-users*') ? 'active' : '' }}">
        <span class="mr-2 brand-image"><i class="fas fa-users-cog"></i></span>
        <p> 會員</p>
    </a>
</li>

<li class="nav-item {{ Auth::user()->isSuperAdmin() || Auth::user()->isMainUser() ? '' : 'd-none' }}">
    <a href="{{ route('admin.cardTemplates.index') }}" class="nav-link {{ Request::is('admin/card-templates*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-id-card-alt"></i>
        <p>名片模板</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('admin.businessCards.index') }}" class="nav-link {{ Request::is('admin/business-cards*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-id-card"></i>
        <p>數位名片</p>
    </a>
</li>

{{-- Profile --}}
<li class="nav-item">
    <a href="{{ route('admin.profile.edit') }}"
       class="nav-link {{ Request::is('admin/profile*') ? 'active' : '' }}">
       <span class="mr-2 brand-image"><i class="fas fa-user"></i></span>
        <p>個人資料管理</p>
    </a>
</li>

{{--

<li class="nav-item">
    <a href="{{ route('admin.seoSettings.index') }}"
       class="nav-link {{ Request::is('admin/seoSettings*') ? 'active' : '' }}">
       <span class="mr-2 brand-image"><i class="fas fa-search"></i></span>
        <p>Seo設定</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('admin.marqueeInfos.index') }}"
       class="nav-link {{ Request::is('admin/marqueeInfos*') ? 'active' : '' }}">
       <span class="mr-2 brand-image"><i class="fas fa-bullhorn"></i></span>
        <p>跑馬燈資訊</p>
    </a>
</li>


<li class="nav-item">
    <a href="{{ route('admin.newsInfos.index') }}"
       class="nav-link {{ Request::is('admin/newsInfos*') ? 'active' : '' }}">
       <span class="mr-2 brand-image"><i class="fas fa-newspaper"></i></span>
        <p>最新消息</p>
    </a>
</li> --}}


