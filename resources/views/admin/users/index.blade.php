<x-layouts.app>
    <x-slot:title>User Management</x-slot:title>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="text-sm text-gray-500 mt-1">Approve registrations and assign roles</p>
        </div>

        @if ($pendingCount > 0)
            <span class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-800 text-sm font-medium px-3 py-1.5 rounded-full">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                {{ $pendingCount }} pending approval{{ $pendingCount !== 1 ? 's' : '' }}
            </span>
        @endif
    </div>

    {{-- Filter tabs --}}
    <div class="flex gap-1 bg-gray-100 rounded-lg p-1 w-fit mb-6">
        @foreach (['all' => 'All Users', 'pending' => 'Pending', 'approved' => 'Approved'] as $value => $label)
            <a href="{{ route('admin.users.index', ['filter' => $value]) }}"
                class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors
                    {{ $filter->value() === $value
                        ? 'bg-white text-gray-900 shadow-sm'
                        : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Users table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if ($users->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <svg class="mx-auto w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-sm font-medium">No users found</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-6 py-3 text-left">User</th>
                        <th class="px-6 py-3 text-left">Department</th>
                        <th class="px-6 py-3 text-left">Declared Level</th>
                        <th class="px-6 py-3 text-left">Assigned Role</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Registered</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors {{ $user->isPending() ? 'bg-amber-50/40' : '' }}">
                            {{-- User --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-xs shrink-0">
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Department --}}
                            <td class="px-6 py-4 text-gray-600">
                                @if ($user->department)
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $user->department->color }}"></div>
                                        {{ $user->department->name }}
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Declared level --}}
                            <td class="px-6 py-4">
                                @if ($user->declared_level)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $user->declared_level === 'hod' ? 'Head of Department' : 'Staff' }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">System account</span>
                                @endif
                            </td>

                            {{-- Assigned role --}}
                            <td class="px-6 py-4">
                                @php
                                    $roleColors = [
                                        'admin'    => 'bg-indigo-100 text-indigo-700',
                                        'director' => 'bg-blue-100 text-blue-700',
                                        'hod'      => 'bg-amber-100 text-amber-700',
                                        'employee' => 'bg-green-100 text-green-700',
                                    ];
                                    $roleColor = $roleColors[$user->role->value] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $roleColor }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                @if ($user->isPending())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Approved
                                    </span>
                                @endif
                            </td>

                            {{-- Registered --}}
                            <td class="px-6 py-4 text-gray-500 text-xs">
                                {{ $user->created_at->format('d M Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($user->isPending())
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Approve
                                        </a>
                                    @else
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                                            Edit role
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.revoke', $user) }}"
                                                onsubmit="return confirm('Revoke access for {{ $user->name }}?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-xs text-amber-600 hover:text-amber-700 font-medium">
                                                    Revoke
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            onsubmit="return confirm('Permanently delete {{ $user->name }}\'s account?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
