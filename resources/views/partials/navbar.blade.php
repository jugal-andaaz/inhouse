<?php use Vanguard\Models\Searchbox; ?>
<nav class="navbar fixed-top align-items-start navbar-expand-lg pl-0 pr-0 py-0" >

    <div class="navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand mr-0" href="{{ url('/') }}">
            <x-logo class="logo-lg" height="35" />
            <x-logo variant="no-text" class="logo-sm" height="35" />
        </a>
    </div>

    <div>
        @if (app('impersonate')->isImpersonating())
            <a href="{{ route('impersonate.leave') }}" class="navbar-toggler text-danger hidden-md">
                <i class="fas fa-user-secret"></i>
            </a>
        @endif

        <button class="navbar-toggler" type="button" id="sidebar-toggle">
            <i class="fas fa-align-right text-muted"></i>
        </button>

        <button class="navbar-toggler mr-3"
                type="button"
                data-toggle="collapse"
                data-target="#top-navigation"
                aria-controls="top-navigation"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <i class="fas fa-bars text-muted"></i>
        </button>
    </div>

    <div class="collapse navbar-collapse" id="top-navigation">
        <div class="row ml-2">
            <div class="col-lg-12 d-flex align-items-left align-items-md-center flex-column flex-md-row py-3">
                <h4 class="page-header mb-0">
                    @yield('page-heading')
                </h4>

                <ol class="breadcrumb mb-0 font-weight-light">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/') }}" class="text-muted">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>

                    @yield('breadcrumbs')
                </ol>
            </div>
        </div>
        <ul class="navbar-nav m-auto flex-row" style="margin: 0 auto;">
            <li class="nav-item d-flex align-items-center visible-lg">
                 
                    <div class="row flex-md-row flex-column-reverse">
                        <div class="col-md-6 mt-md-0 mt-2 pr-1">
                            <div class="input-group custom-search-form">
                                <input type="text" id="searchform"
                                       class="form-control input-solid"
                                       name="search"
                                       value="{{ Request::get('search') }}"
                                       placeholder="@lang('Search for orders...')">

                                <span class="input-group-append">
                                    @if (Request::has('search') && Request::get('search') != '')
                                    <a href="/orders"
                                       class="btn btn-light d-flex align-items-center text-muted"
                                       role="button">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 mt-2 mt-md-0 pr-1">
                            <select name="searchopt" id="searchopt" class="form-control input-solid">
                                <?php // Get list of order statuses
                                $searches = ['' => __('Please Select')] + Searchbox::searchTopLists();
                                ?>
                                @foreach($searches as $key => $value)
                                <option value="{{ $key }}" {{ Request::get('oid') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 mt-md-0 pr-1">
                            <a id="searchmainbtn" class="py-2" href="#">
                                <button class="btn btn-light" type="button" id="search-users-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </a>
                        </div>
                    </div> 
            </li>
        </ul>

        <ul class="navbar-nav ml-auto pr-3 flex-row">
            @if (app('impersonate')->isImpersonating())
                <li class="nav-item d-flex align-items-center visible-lg">
                    <a href="{{ route('impersonate.leave') }}" class="btn text-danger">
                        <i class="fas fa-user-secret mr-2"></i>
                        @lang('Stop Impersonating')
                    </a>
                </li>
            @endif

            @hook('navbar:items')

            @include('partials.locale-dropdown')

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle"
                   href="#"
                   id="navbarDropdown"
                   role="button"
                   data-toggle="dropdown"
                   aria-haspopup="true"
                   aria-expanded="false">
                    <img src="{{ auth()->user()->present()->avatar }}"
                         width="50"
                         height="50"
                         class="rounded-circle img-thumbnail img-responsive">
                </a>
                <div class="dropdown-menu dropdown-menu-right position-absolute p-0" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item py-2" href="{{ route('profile') }}">
                        <i class="fas fa-user text-muted mr-2"></i>
                        @lang('My Profile')
                    </a>

                    @if (config('session.driver') == 'database')
                        <a href="{{ route('profile.sessions') }}" class="dropdown-item py-2">
                            <i class="fas fa-list text-muted mr-2"></i>
                            @lang('Active Sessions')
                        </a>
                    @endif

                    @hook('navbar:dropdown')

                    <div class="dropdown-divider m-0"></div>

                    <a class="dropdown-item py-2" href="{{ route('auth.logout') }}">
                        <i class="fas fa-sign-out-alt text-muted mr-2"></i>
                        @lang('Logout')
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>
 
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchOpt = document.getElementById('searchopt');
        const searchForm = document.getElementById('searchmainbtn');
        const searchInput = document.querySelector('input[name="search"]');

        searchOpt.addEventListener('change', function () {
            const selected = this.value;
            const searchValue = encodeURIComponent(searchInput.value.trim());

            let baseUrl = '{{ url("/orders") }}';

            if (selected === 'icode') {
                baseUrl = '{{ url("/skuorderlist") }}' + '?sku=' + searchValue;
            } else if (selected === 'oid') {
                baseUrl = '{{ url("/orders") }}' + '?search=' + searchValue;
            } else if (selected === 'alicode') {
                baseUrl = '{{ url("/orders/search-all-item-code") }}' + '?search=' + searchValue;
            }

            searchForm.href = baseUrl;
        });
    });
</script>
<style type="text/css">.phpdebugbar.phpdebugbar-minimized {display: none;}</style>