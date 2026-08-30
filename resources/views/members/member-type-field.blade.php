<div>
    <label for="member_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Jenis Anggota <span class="text-red-500">*</span>
    </label>
    <select id="member_type_id" name="member_type_id"
        class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
        <option value="">Pilih Jenis Anggota</option>
        @foreach($memberTypes as $memberType)
            <option value="{{ $memberType->id }}" @selected((string) old('member_type_id',$member->member_type_id ?? '') === (string) $memberType->id)>{{ $memberType->name }}</option>
        @endforeach
    </select>
    @error('member_type_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
</div>
