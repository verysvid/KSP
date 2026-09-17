<?php

namespace App\Http\Controllers;

use App\Models\ShuMemberResult;
use App\Models\ShuPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShuMemberReportController extends Controller
{
    public function index(Request $request): View
    {
        $member = $request->user()?->member()->with('branch:id,code,name')->first();
        abort_unless($member, 403, 'User Anggota belum terhubung dengan data anggota.');

        $years = ShuMemberResult::query()
            ->where('member_id', $member->id)
            ->whereHas('period', fn ($q) => $q->whereIn('status', [ShuPeriod::STATUS_FINALIZED, ShuPeriod::STATUS_PAID]))
            ->with('period:id,year,status')
            ->get()
            ->pluck('period.year')->unique()->sortDesc()->values();

        $selectedYear = $request->filled('year') ? $request->integer('year') : $years->first();

        $result = null;
        if ($selectedYear) {
            $result = ShuMemberResult::query()
                ->where('member_id', $member->id)
                ->whereHas('period', fn ($q) => $q
                    ->where('year', $selectedYear)
                    ->whereIn('status', [ShuPeriod::STATUS_FINALIZED, ShuPeriod::STATUS_PAID]))
                ->with(['period.allocations', 'period.savingTypes:id,code,name'])
                ->first();
        }

        return view('reports.shu.index', compact('member', 'years', 'selectedYear', 'result'));
    }
}
