<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateApplicationSettingRequest;
use App\Models\ApplicationSetting;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ApplicationSettingController extends Controller
{
    public function edit(): View
    {
        $setting = ApplicationSetting::query()->first() ?? ApplicationSetting::defaults();

        return view('application-settings.edit', compact('setting'));
    }

    public function update(UpdateApplicationSettingRequest $request): RedirectResponse
    {
        $setting = ApplicationSetting::query()->first();

        if (! $setting) {
            $setting = ApplicationSetting::create([
                'system_name' => 'Koperasi Simpan Pinjam',
                'title_1' => 'Koperasi Pegawai BPPT Kabupaten Bekasi',
                'title_2' => 'MAHABAH BERSAMA SEJAHTERA',
                'abbreviation' => 'KSP',
                'description' => 'Sistem Informasi Pengelolaan Koperasi Simpan Pinjam',
                'copyright' => '© 2026. All rights reserved.',
            ]);
        }

        $oldValues = $setting->only([
            'system_name', 'title_1', 'title_2', 'abbreviation',
            'description', 'copyright', 'logo_path', 'icon_path',
        ]);

        $data = $request->safe()->only([
            'system_name', 'title_1', 'title_2', 'abbreviation',
            'description', 'copyright',
        ]);

        $oldLogo = $setting->logo_path;
        $oldIcon = $setting->icon_path;
        $deleteOldLogo = false;
        $deleteOldIcon = false;

        if ($request->boolean('remove_logo')) {
            $data['logo_path'] = null;
            $deleteOldLogo = (bool) $oldLogo;
        }

        if ($request->boolean('remove_icon')) {
            $data['icon_path'] = null;
            $deleteOldIcon = (bool) $oldIcon;
        }

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')
                ->store('application-settings/logo', 'public');
            $deleteOldLogo = (bool) $oldLogo;
        }

        if ($request->hasFile('icon')) {
            $data['icon_path'] = $request->file('icon')
                ->store('application-settings/icon', 'public');
            $deleteOldIcon = (bool) $oldIcon;
        }

        $setting->update($data);

        if ($deleteOldLogo && $oldLogo && $oldLogo !== $setting->logo_path) {
            Storage::disk('public')->delete($oldLogo);
        }

        if ($deleteOldIcon && $oldIcon && $oldIcon !== $setting->icon_path) {
            Storage::disk('public')->delete($oldIcon);
        }

        ApplicationSetting::flushCache();

        if (class_exists(AuditLogService::class)) {
            app(AuditLogService::class)->log(
                action: 'UPDATE',
                model: $setting,
                description: 'Memperbarui pengaturan aplikasi',
                oldValues: $oldValues,
                newValues: $setting->fresh()->only(array_keys($oldValues))
            );
        }

        return redirect()
            ->route('application-settings.edit')
            ->with('success', 'Pengaturan aplikasi berhasil diperbarui.');
    }
}
