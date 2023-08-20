<!-- BEGIN: Sidebar -->
<div class="sidebar-wrapper group w-0 hidden xl:w-[248px] xl:block">
    <div id="bodyOverlay" class="w-screen h-screen fixed top-0 bg-slate-900 bg-opacity-50 backdrop-blur-sm z-10 hidden">
    </div>
    <div class="logo-segment">

        <!-- Application Logo -->
        <x-application-logo />

        <!-- Sidebar Type Button -->
        <div id="sidebar_type" class="cursor-pointer text-slate-900 dark:text-white text-lg">
            <iconify-icon class="sidebarDotIcon extend-icon text-slate-900 dark:text-slate-200" icon="fa-regular:dot-circle"></iconify-icon>
            <iconify-icon class="sidebarDotIcon collapsed-icon text-slate-900 dark:text-slate-200" icon="material-symbols:circle-outline"></iconify-icon>
        </div>
        <button class="sidebarCloseIcon text-2xl inline-block md:hidden">
            <iconify-icon class="text-slate-900 dark:text-slate-200" icon="clarity:window-close-line"></iconify-icon>
        </button>
    </div>
    <div id="nav_shadow" class="nav_shadow h-[60px] absolute top-[80px] nav-shadow z-[1] w-full transition-all duration-200 pointer-events-none
      opacity-0"></div>
    <div class="sidebar-menus bg-white dark:bg-slate-800 py-2 px-4 h-[calc(100%-80px)] z-50" id="sidebar_menus">
        <ul class="sidebar-menu">
            <li class="sidebar-menu-title">{{ __('MENU') }}</li>
            <li>
                <a href="{{ route('dashboard.index') }}" class="navItem {{ (request()->is('dashboard*')) ? 'active' : '' }}">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="heroicons-outline:home"></iconify-icon>
                        <span>{{ __('Home') }}</span>
                    </span>
                </a>
            </li>

            <!-- Administration -->
            <li class="{{ (\Request::route()->getName() == 'administration*') ? 'active' : '' }}">
                <a href="javascript:void(0)" class="navItem">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="heroicons-outline:shield-check"></iconify-icon>
                        <span>{{ __('Administration') }}</span>
                    </span>
                    <iconify-icon class="icon-arrow" icon="heroicons-outline:chevron-right"></iconify-icon>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('administration.courses') }}" class="{{ (\Request::route()->getName() == 'administration.courses') ? 'active' : '' }}">{{ __('Programs') }}</a>
                    </li>

                    <li>
                        <a href="{{ route('administration.papers') }}" class="{{ (\Request::route()->getName() == 'administration.papers') ? 'active' : '' }}">{{ __('Papers') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('administration.institutions') }}" class="{{ (\Request::route()->getName() == 'administration.institutions') ? 'active' : '' }}">{{ __('Institutions') }}</a>
                    </li>

                    <li>
                        <a href="{{ route('administration.districts') }}" class="{{ (\Request::route()->getName() == 'administration.districts') ? 'active' : '' }}">{{ __('Districts') }}</a>
                    </li>

                    <li>
                        <a href="{{ route('administration.fees') }}" class="{{ (\Request::route()->getName() == 'administration.fees') ? 'active' : '' }}">{{ __('Fees') }}</a>
                    </li>

                    <li>
                        <a href="{{ route('administration.years') }}" class="{{ (\Request::route()->getName() == 'administration.years') ? 'active' : '' }}">{{ __('Years') }}</a>
                    </li>



                </ul>
            </li>

            <!-- Student NSIN -->
            <li class="{{ (\Request::route()->getName() == 'nsin*') ? 'active' : '' }}">
                <a href="javascript:void(0)" class="navItem">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="heroicons-outline:academic-cap"></iconify-icon>
                        <span>{{ __('Students NSIN') }}</span>
                    </span>
                    <iconify-icon class="icon-arrow" icon="heroicons-outline:chevron-right"></iconify-icon>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('nsin-payments') }}" class="{{ (\Request::route()->getName() == 'payments/nsin') ? 'active' : '' }}">{{ __('NSIN Payments') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('nsin-payments') }}" class="{{ (\Request::route()->getName() == 'payments/nsin') ? 'active' : '' }}">{{ __('Incomplete Registration') }}</a>
                    </li>
                </ul>
            </li>

            <!-- NSIN Registration -->
            <li class="{{ (\Request::route()->getName() == 'administration*') ? 'active' : '' }}">
                <a href="javascript:void(0)" class="navItem">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="heroicons-outline:rectangle-group"></iconify-icon>
                        <span>{{ __('NSIN Registration') }}</span>
                    </span>
                    <iconify-icon class="icon-arrow" icon="heroicons-outline:chevron-right"></iconify-icon>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('nsin-payments') }}" class="{{ (\Request::route()->getName() == 'payments/nsin') ? 'active' : '' }}">{{ __('NSIN Payments') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('nsin-payments') }}" class="{{ (\Request::route()->getName() == 'payments/nsin') ? 'active' : '' }}">{{ __('Incomplete Registration') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('nsin-payments') }}" class="{{ (\Request::route()->getName() == 'payments/nsin') ? 'active' : '' }}">{{ __('NSIN Verification') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('nsin-payments') }}" class="{{ (\Request::route()->getName() == 'payments/nsin') ? 'active' : '' }}">{{ __('Accepted Registration') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('nsin-payments') }}" class="{{ (\Request::route()->getName() == 'payments/nsin') ? 'active' : '' }}">{{ __('Rejected Registration') }}</a>
                    </li>
                </ul>
            </li>

            <!-- Administration -->
            <li class="{{ (\Request::route()->getName() == 'exams*') ? 'active' : '' }}">
                <a href="javascript:void(0)" class="navItem">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="heroicons-outline:ticket"></iconify-icon>
                        <span>{{ __('Exam Registraion') }}</span>
                    </span>
                    <iconify-icon class="icon-arrow" icon="heroicons-outline:chevron-right"></iconify-icon>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('exam.payments')}}" class="{{ (\Request::route()->getName() == 'exams.payments') ? 'active' : '' }}">{{ __('Exam Payments') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('exam.registrations') }}" class="{{ (\Request::route()->getName() == 'exams.registration') ? 'active' : '' }}">{{ __('Exam Registrations') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('exam.approval')}}" class="{{ (\Request::route()->getName() == 'exams.approval') ? 'active' : '' }}">{{ __('Approve/Reject') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('exam.approved')}}" class="{{ (\Request::route()->getName() == 'exams.approved') ? 'active' : '' }}">{{ __('Approved Registrations') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('exam.rejected')}}" class="{{ (\Request::route()->getName() == 'exams.rejected') ? 'active' : '' }}">{{ __('Rejected Registrations') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('exam.rejection-reasons')}}" class="{{ (\Request::route()->getName() == 'exams.rejected') ? 'active' : '' }}">{{ __('Reject Reasons') }}</a>
                    </li>


                </ul>
            </li>


            <!-- NSIN Registration Period-->
            <li>
                <a href="{{ route('comments.index') }}" class="navItem {{ (request()->is('comments*')) ? 'active' : '' }}">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="heroicons-outline:view-boards"></iconify-icon>
                        <span>{{ __('Manage Staff') }}</span>
                    </span>
                </a>
            </li>


            <!-- NSIN Registration Period-->
            <li>
                <a href="{{ route('comments.index') }}" class="navItem {{ (request()->is('comments*')) ? 'active' : '' }}">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="heroicons-outline:view-boards"></iconify-icon>
                        <span>{{ __('NSIN Registration Period') }}</span>
                    </span>
                </a>
            </li>

            <!-- Activate Surcharge-->
            <li>
                <a href="{{ route('comments.index') }}" class="navItem {{ (request()->is('comments*')) ? 'active' : '' }}">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="heroicons-outline:view-boards"></iconify-icon>
                        <span>{{ __('Exam Registration Period') }}</span>
                    </span>
                </a>
            </li>

            <!-- Activate Surcharge-->
            <li>
                <a href="{{ route('surcharge.index') }}" class="navItem {{ (request()->is('surcharge*')) ? 'active' : '' }}">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="heroicons-outline:banknotes"></iconify-icon>
                        <span>{{ __('Activate Surcharge') }}</span>
                    </span>
                </a>
            </li>

            <!-- Administration -->
            <li class="{{ (\Request::route()->getName() == 'administration*') ? 'active' : '' }}">
                <a href="javascript:void(0)" class="navItem">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="heroicons-outline:view-boards"></iconify-icon>
                        <span>{{ __('Manage Reports') }}</span>
                    </span>
                    <iconify-icon class="icon-arrow" icon="heroicons-outline:chevron-right"></iconify-icon>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('administration.courses') }}" class="{{ (\Request::route()->getName() == 'administration.courses') ? 'active' : '' }}">{{ __('Programs') }}</a>
                    </li>


                </ul>
            </li>

            <!-- Comments -->
            <li>
                <a href="{{ route('comments.index') }}" class="navItem {{ (request()->is('comments*')) ? 'active' : '' }}">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="heroicons-outline:view-boards"></iconify-icon>
                        <span>{{ __('Comments') }}</span>









































                        < /span>
                </a>

            </li>

            <!-- Settings -->
            <li>
                <a href="{{ route('general-settings.show') }}" class="navItem {{ (request()->is('general-settings*')) || (request()->is('users*')) || (request()->is('roles*')) || (request()->is('profiles*')) || (request()->is('permissions*')) ? 'active' : '' }}">
                    <span class="flex items-center">
                        <iconify-icon class=" nav-icon" icon="material-symbols:settings-outline"></iconify-icon>
                        <span>{{ __('Settings') }}</span>
                    </span>
                </a>
            </li>
        </ul>

    </div>
</div>
<!-- End: Sidebar -->