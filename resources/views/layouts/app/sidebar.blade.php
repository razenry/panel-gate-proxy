<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 font-sans">
        <flux:sidebar.header>
            <flux:brand href="{{ route('dashboard') }}" name="Raznar Hosting" class="px-2">
                <x-slot name="logo">
                    <img src="{{ asset('Raznar1.png') }}" class="h-8 w-auto" alt="Raznar Hosting" />
                </x-slot>
            </flux:brand>
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:navlist variant="minimal">
            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:navlist.item>
            @if(auth()->user()?->is_admin == false)
                <flux:navlist.item icon="server" :href="route('servers.index')" :current="request()->routeIs('servers.*')" wire:navigate>
                    {{ __('My Servers') }}
                </flux:navlist.item>
                <flux:navlist.item icon="ticket" :href="route('my-plan')" :current="request()->routeIs('my-plan')" wire:navigate>
                    {{ __('My Plans') }}
                </flux:navlist.item>
                <flux:navlist.item icon="banknotes" :href="route('client.credits')" :current="request()->routeIs('client.credits')" wire:navigate>
                    {{ __('Credits') }}
                </flux:navlist.item>
            @endif

            @if(auth()->user()?->is_admin)
                <flux:navlist.group heading="Administration" class="mt-8">
                    <flux:navlist.item icon="cpu-chip" :href="route('admin.nodes')" :current="request()->routeIs('admin.nodes')" wire:navigate>
                        {{ __('Nodes') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="credit-card" :href="route('admin.plans')" :current="request()->routeIs('admin.plans')" wire:navigate>
                        {{ __('Plans') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="adjustments-horizontal" :href="route('admin.servers')" :current="request()->routeIs('admin.servers')" wire:navigate>
                        {{ __('Manage Servers') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>
                        {{ __('Users') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="ticket" :href="route('admin.subscriptions')" :current="request()->routeIs('admin.subscriptions')" wire:navigate>
                        {{ __('Subscriptions') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="key" :href="route('admin.api-keys')" :current="request()->routeIs('admin.api-keys')" wire:navigate>
                        {{ __('API Keys') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="banknotes" :href="route('admin.credits')" :current="request()->routeIs('admin.credits')" wire:navigate>
                        {{ __('Credit History') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="cog-8-tooth" :href="route('admin.settings')" :current="request()->routeIs('admin.settings')" wire:navigate>
                        {{ __('Expired Settings') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            @endif
        </flux:navlist>

        <flux:spacer />

        @auth
            <form id="impersonate-stop-form" action="{{ route('admin.impersonate.stop') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <form id="sso-return-form" action="{{ route('sso.return') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>

            <flux:navlist variant="minimal">
                <flux:navlist.item icon="cog-6-tooth" :href="route('profile.edit')" wire:navigate>{{ __('Settings') }}</flux:navlist.item>
            </flux:navlist>

            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()" icon-trailing="chevron-up-down" />

                <flux:menu>
                    @if(session()->has('impersonated_user_id'))
                        <flux:menu.item icon="arrow-left-end-on-rectangle" href="#" onclick="event.preventDefault(); document.getElementById('impersonate-stop-form').submit();" class="text-amber-600 dark:text-amber-400">
                            {{ __('Exit Client Mode') }}
                        </flux:menu.item>
                        <flux:menu.separator />
                    @endif

                    @if(session()->has('sso_admin_id'))
                        <flux:menu.item icon="arrow-uturn-left" href="#" onclick="event.preventDefault(); document.getElementById('sso-return-form').submit();" class="text-orange-600 dark:text-orange-400">
                            {{ __('Return to Admin') }}
                        </flux:menu.item>
                        <flux:menu.separator />
                    @endif

                    @if(!session()->has('impersonated_user_id') && auth()->user()->is_admin)
                        <flux:menu.item icon="arrow-path-rounded-square" href="{{ route('admin.impersonate.start', auth()->user()->id) }}">
                            {{ __('Switch to Client Mode') }}
                        </flux:menu.item>
                        <flux:menu.separator />
                    @endif

                    <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        {{ __('Log out') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        @endauth
    </flux:sidebar>

    <flux:header class="lg:hidden border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:brand href="{{ route('dashboard') }}" name="Raznar Hosting">
            <x-slot name="logo">
                <img src="{{ asset('Raznar1.png') }}" class="h-8 w-auto" alt="Raznar Hosting" />
            </x-slot>
        </flux:brand>
        <flux:spacer />
        @auth
            <flux:dropdown position="bottom" align="end">
                <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                <flux:menu>
                    <flux:menu.item icon="user" :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:menu.item>
                    <flux:menu.separator />
                    
                    @if(session()->has('impersonated_user_id'))
                        <flux:menu.item icon="arrow-left-end-on-rectangle" href="#" onclick="event.preventDefault(); document.getElementById('impersonate-stop-form').submit();" class="text-amber-600 dark:text-amber-400">
                            {{ __('Exit Client Mode') }}
                        </flux:menu.item>
                        <flux:menu.separator />
                    @endif

                    @if(session()->has('sso_admin_id'))
                        <flux:menu.item icon="arrow-uturn-left" href="#" onclick="event.preventDefault(); document.getElementById('sso-return-form').submit();" class="text-orange-600 dark:text-orange-400">
                            {{ __('Return to Admin') }}
                        </flux:menu.item>
                        <flux:menu.separator />
                    @endif

                    @if(!session()->has('impersonated_user_id') && auth()->user()->is_admin)
                        <flux:menu.item icon="arrow-path-rounded-square" href="{{ route('admin.impersonate.start', auth()->user()->id) }}">
                            {{ __('Switch to Client Mode') }}
                        </flux:menu.item>
                        <flux:menu.separator />
                    @endif

                    <flux:menu.item
                        icon="arrow-right-start-on-rectangle"
                        href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    >
                        {{ __('Log out') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        @endauth
    </flux:header>

    {{-- The $slot will be wrapped in flux:main by layouts/app.blade.php --}}
    {{ $slot }}

    @fluxScripts
    @stack('scripts')
</body>

</html>
