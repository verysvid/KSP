<x-app-layout>
    <x-slot name="title">Permission Role</x-slot>

    <x-page-header
        title="Permission Role"
        description="Atur permission untuk role {{ $role->name }}.">

        <x-slot name="actions">
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Kembali</a>
        </x-slot>
    </x-page-header>

    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            @foreach($permissions as $group => $items)
                <x-card :title="strtoupper($group)">
                    <div class="space-y-3">
                        @foreach($items as $permission)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
                                <input type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permission->name }}"
                                       @checked(in_array($permission->name, old('permissions', $rolePermissionNames)))
                                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                    {{ $permission->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="btn btn-primary">
                Simpan Permission
            </button>
        </div>
    </form>
</x-app-layout>
