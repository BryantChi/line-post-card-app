<!-- need to remove -->
{{-- <li class="nav-item">
    <a href="{{ route('home') }}" class="nav-link {{ Request::is('home') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Home</p>
    </a>
</li> --}}

<li class="nav-item mb-4 {{ Auth::user()->isSuperAdmin() ? '' : 'd-none' }}">
    <a href="{{ route('index') }}" target="_blank" class="nav-link">
        <span class="h5 mr-2 brand-image"><i class="fas fa-external-link-alt"></i></span>
        <p class="h5"> 瀏覽網站</p>
    </a>
</li>

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

<li class="nav-item {{ Auth::user()->isSuperAdmin() ? '' : 'd-none' }}">
    <a href="{{ route('admin.login-logs.index') }}"
        class="nav-link {{ Request::is('admin/login-logs*') ? 'active' : '' }}">
        <span class="mr-2 brand-image"><i class="fas fa-history"></i></span>
        <p> 登入紀錄</p>
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
        <p>AI數位名片</p>
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


@if (Auth::user()->isSuperAdmin())

<li class="my-4">
    <div class="mx-auto divider-line"></div>
</li>

<li class="mt-3 nav-item has-treeview {{ Request::is('admin/seoSettings*') || Request::is('admin/caseInfos*') || Request::is('admin/lessonInfos*') ? 'menu-open' : '' }}">
    {{-- 子選單 --}}
    <a href="#" class="nav-link {{ Request::is('admin/seoSettings*') ? 'active' : '' }}" role="button">
        <span class="mr-2 brand-image"><i class="fas fa-cogs"></i></span>
        <p>前台設定<i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.seoSettings.index') }}"
            class="nav-link {{ Request::is('admin/seoSettings*') ? 'active' : '' }}">
            <span class="mr-2 brand-image"><i class="fas fa-search"></i></span>
                <p>Seo設定</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.caseInfos.index') }}" class="nav-link {{ Request::is('admin/caseInfos*') ? 'active' : '' }}">
                <span class="mr-2 brand-image"><i class="fas fa-images"></i></span>
                <p>成功案例</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.lessonInfos.index') }}" class="nav-link {{ Request::is('admin/lessonInfos*') ? 'active' : '' }}">
                <span class="mr-2 brand-image"><i class="fas fa-chalkboard-teacher"></i></span>
                <p>學習中心</p>
            </a>
        </li>
    </ul>
</li>

@endif


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
