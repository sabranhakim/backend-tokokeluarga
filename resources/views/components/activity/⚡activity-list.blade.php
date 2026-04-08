<?php

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function with()
    {
        return [
            'activities' => Activity::with('causer')
                ->where('description', 'like', '%' . $this->search . '%')
                ->orWhereHas('causer', function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(20),
        ];
    }

    public function getModelName($subjectType)
    {
        if (!$subjectType) return '-';
        $parts = explode('\\', $subjectType);
        return end($parts);
    }

    public function getEventBadge($event)
    {
        return match($event) {
            'created' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'updated' => 'bg-blue-100 text-blue-700 border-blue-200',
            'deleted' => 'bg-red-100 text-red-700 border-red-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200'
        };
    }
};
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Riwayat Aktivitas</h3>
            <p class="text-sm text-slate-500">Monitor setiap perubahan data dalam sistem</p>
        </div>
        <div class="relative w-full md:w-64">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari aktivitas atau user...">
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Objek</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Detail Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($activities as $activity)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            {{ $activity->created_at->format('d/m/Y H:i') }}
                            <div class="text-[10px] text-slate-400">{{ $activity->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 mr-2">
                                    {{ substr($activity->causer->name ?? 'S', 0, 1) }}
                                </div>
                                <span class="text-sm font-medium text-slate-900">{{ $activity->causer->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $this->getEventBadge($activity->event) }}">
                                {{ strtoupper($activity->event) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-slate-700">{{ $this->getModelName($activity->subject_type) }}</span>
                            <span class="text-xs text-slate-400 font-mono">#{{ $activity->subject_id }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs text-slate-600 space-y-1">
                                @if($activity->event == 'updated')
                                    @php
                                        $old = $activity->changes['old'] ?? [];
                                        $new = $activity->changes['attributes'] ?? [];
                                    @endphp
                                    @foreach($new as $key => $value)
                                        @if(isset($old[$key]) && $old[$key] != $value)
                                            <div>
                                                <span class="font-bold">{{ str_replace('_', ' ', $key) }}:</span>
                                                <span class="text-red-500 line-through">{{ is_array($old[$key]) ? json_encode($old[$key]) : $old[$key] }}</span>
                                                <svg class="w-3 h-3 inline text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                                <span class="text-emerald-600 font-bold">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                @elseif($activity->event == 'created')
                                    <p class="italic text-slate-400 text-[10px]">Data baru ditambahkan</p>
                                @else
                                    <p class="text-slate-500">{{ $activity->description }}</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-500 italic text-sm">Belum ada catatan aktivitas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $activities->links() }}
        </div>
    </div>
</div>
