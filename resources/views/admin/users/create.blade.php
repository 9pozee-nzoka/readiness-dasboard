<x-layouts.app>
    <x-slot:title>Create User</x-slot:title>

    <div class="max-w-xl">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('dashboard.index') }}" class="hover:text-indigo-600">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('admin.users.index') }}" class="hover:text-indigo-600">User Management</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Create</span>
        </nav>

        {{-- Create form --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-5">Create new user</h3>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5" id="user-form">
                @csrf

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Full name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                            @error('name') border-red-400 bg-red-50 @enderror">
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                            @error('email') border-red-400 bg-red-50 @enderror">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                            @error('password') border-red-400 bg-red-50 @enderror">
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role --}}
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Assign role <span class="text-red-500">*</span>
                    </label>
                    <select name="role" id="role"
                        onchange="toggleDeptField(this.value)"
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                            @error('role') border-red-400 bg-red-50 @enderror">
                        <option value="">Select a role…</option>
                        @foreach ($assignableRoles as $role)
                            <option value="{{ $role->value }}" @selected(old('role') === $role->value)>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Department --}}
                <div id="dept-field" style="display: none;">
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Department <span class="text-red-500">*</span>
                    </label>
                    <select name="department_id" id="department_id"
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                            @error('department_id') border-red-400 bg-red-50 @enderror">
                        <option value="">Select a department…</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role description hint --}}
                <div id="role-hint" class="hidden rounded-lg border p-3.5 text-xs leading-relaxed"></div>

                <div class="flex items-center gap-3 pt-1">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-5 py-2.5 rounded-lg transition-colors">
                        Create user
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const scopedRoles = ['hod', 'employee'];
        const hints = {
            admin:    { text: 'Full access: manages events, requirements, departments, and users.', cls: 'border-indigo-200 bg-indigo-50 text-indigo-800' },
            director: { text: 'Read-only access to all departments and events across the organisation.', cls: 'border-blue-200 bg-blue-50 text-blue-800' },
            hod:      { text: "Can view and update their department's readiness, and send reminders to their team.", cls: 'border-amber-200 bg-amber-50 text-amber-800' },
            employee: { text: "Can view and update their department's readiness checklist.", cls: 'border-green-200 bg-green-50 text-green-800' },
        };

        function toggleDeptField(role) {
            const deptField  = document.getElementById('dept-field');
            const deptSelect = document.getElementById('department_id');
            const hint       = document.getElementById('role-hint');

            deptField.style.display = scopedRoles.includes(role) ? '' : 'none';
            deptSelect.required     = scopedRoles.includes(role);

            if (hints[role]) {
                hint.className = `rounded-lg border p-3.5 text-xs leading-relaxed ${hints[role].cls}`;
                hint.textContent = hints[role].text;
                hint.classList.remove('hidden');
            } else {
                hint.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const sel = document.getElementById('role');
            if (sel.value) toggleDeptField(sel.value);
        });
    </script>
    @endpush
</x-layouts.app>
