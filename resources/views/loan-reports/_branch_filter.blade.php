@if(auth()->user()?->hasRole('SuperAdmin'))
    <div class="min-w-0 flex-1">
        <label for="branch_id" class="form-label">Cabang</label>

        <select
            id="branch_id"
            name="branch_id"
            class="form-select">

            <option value="">Semua Cabang</option>

            @foreach($branches as $branch)
                <option
                    value="{{ $branch->id }}"
                    @selected((string) request('branch_id') === (string) $branch->id)>
                    {{ $branch->code }} - {{ $branch->name }}
                </option>
            @endforeach
        </select>
    </div>
@endif
