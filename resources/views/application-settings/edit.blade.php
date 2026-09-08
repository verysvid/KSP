<x-app-layout>
    <x-slot name="title">Pengaturan Aplikasi</x-slot>

    <x-page-header
        title="Pengaturan Aplikasi"
        description="Atur identitas, branding, logo, icon, judul, dan informasi aplikasi." />

    <div class="mx-auto max-w-5xl">
        <form method="POST"
              action="{{ route('application-settings.update') }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            @method('PUT')

            <x-card
                title="Logo & Icon"
                description="Logo digunakan pada halaman depan, login, dan sidebar. Icon digunakan sebagai favicon browser.">

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div>
                        <label class="form-label">Logo</label>
                        <div class="mb-3 flex min-h-36 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                            <img id="logo-preview"
                                 src="{{ $setting->logo_url ?: '' }}"
                                 alt="Preview Logo"
                                 class="{{ $setting->logo_url ? '' : 'hidden' }} max-h-28 max-w-full object-contain">
                            <div id="logo-placeholder"
                                 class="{{ $setting->logo_url ? 'hidden' : '' }} text-center text-sm text-slate-400">
                                Belum ada logo
                            </div>
                        </div>
                        <input type="file" name="logo" id="logo" accept="image/png,image/jpeg,image/webp" class="form-control">
                        <p class="mt-2 text-xs text-slate-500">PNG/JPG/WEBP, maksimal 4 MB. Disarankan background transparan.</p>
                        @if($setting->logo_path)
                            <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300">
                                Hapus logo saat disimpan
                            </label>
                        @endif
                        @error('logo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Icon / Favicon</label>
                        <div class="mb-3 flex min-h-36 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                            <img id="icon-preview"
                                 src="{{ $setting->icon_url ?: '' }}"
                                 alt="Preview Icon"
                                 class="{{ $setting->icon_url ? '' : 'hidden' }} h-20 w-20 object-contain">
                            <div id="icon-placeholder"
                                 class="{{ $setting->icon_url ? 'hidden' : '' }} text-center text-sm text-slate-400">
                                Belum ada icon
                            </div>
                        </div>
                        <input type="file" name="icon" id="icon" accept="image/png,image/jpeg,image/webp,image/x-icon,.ico" class="form-control">
                        <p class="mt-2 text-xs text-slate-500">PNG/JPG/WEBP/ICO, maksimal 2 MB. Disarankan ukuran persegi.</p>
                        @if($setting->icon_path)
                            <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                <input type="checkbox" name="remove_icon" value="1" class="rounded border-slate-300">
                                Hapus icon saat disimpan
                            </label>
                        @endif
                        @error('icon')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </x-card>

            <x-card
                title="Identitas Aplikasi"
                description="Nilai di bawah ini otomatis menjadi variable global pada seluruh tampilan aplikasi.">

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="system_name" class="form-label">Nama Sistem <span class="text-red-500">*</span></label>
                        <input id="system_name" name="system_name" type="text"
                               value="{{ old('system_name', $setting->system_name) }}" class="form-control" required>
                        @error('system_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="abbreviation" class="form-label">Singkatan <span class="text-red-500">*</span></label>
                        <input id="abbreviation" name="abbreviation" type="text"
                               value="{{ old('abbreviation', $setting->abbreviation) }}" class="form-control" required>
                        @error('abbreviation')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="title_1" class="form-label">Judul 1 <span class="text-red-500">*</span></label>
                        <input id="title_1" name="title_1" type="text"
                               value="{{ old('title_1', $setting->title_1) }}" class="form-control" required>
                        @error('title_1')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="title_2" class="form-label">Judul 2</label>
                        <input id="title_2" name="title_2" type="text"
                               value="{{ old('title_2', $setting->title_2) }}" class="form-control">
                        @error('title_2')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $setting->description) }}</textarea>
                        @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="copyright" class="form-label">Copyright</label>
                        <input id="copyright" name="copyright" type="text"
                               value="{{ old('copyright', $setting->copyright) }}" class="form-control"
                               placeholder="© 2026. All rights reserved.">
                        @error('copyright')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </x-card>

            <x-card
                title="Preview Branding"
                description="Gambaran sederhana penggunaan identitas pada halaman depan dan login.">
                <div class="rounded-2xl bg-gradient-to-br from-sky-50 via-white to-blue-100 p-6 text-center dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
                    <div class="flex flex-col items-center justify-center gap-4 lg:flex-row">
                        @if($setting->logo_url)
                            <img src="{{ $setting->logo_url }}" alt="Logo" class="h-20 w-20 object-contain">
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-xl border-2 border-dashed border-slate-300 text-xl font-black text-slate-400">{{ $setting->abbreviation ?: 'LOGO' }}</div>
                        @endif
                        <div>
                            <div class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $setting->title_1 }}</div>
                            <div class="mt-1 text-xl font-bold text-blue-600">{{ $setting->title_2 }}</div>
                            <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $setting->description }}</div>
                        </div>
                    </div>
                </div>
            </x-card>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function bindImagePreview(inputId, imageId, placeholderId) {
            const input = document.getElementById(inputId);
            const image = document.getElementById(imageId);
            const placeholder = document.getElementById(placeholderId);
            if (!input || !image) return;

            input.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (!file) return;
                image.src = URL.createObjectURL(file);
                image.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            });
        }

        bindImagePreview('logo', 'logo-preview', 'logo-placeholder');
        bindImagePreview('icon', 'icon-preview', 'icon-placeholder');
    </script>
    @endpush
</x-app-layout>
