@extends('admin.layouts.app')

@section('title', 'Log Activity - Maroon Sneat Style')
@section('page_title', 'Log Activity')
@section('page_desc', 'Riwayat aktivitas create/update/delete dari seluruh user')

@section('content')
    <div class="space-y-4 max-w-full">
        {{-- Header card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(360px,1fr)_auto] lg:items-start">
                {{-- Left: Title + desc --}}
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-slate-900">Audit Trail</h2>
                    <p class="mt-1 text-sm text-slate-600 max-w-2xl">
                        Melacak perubahan data untuk keamanan & troubleshooting.
                    </p>
                </div>

                {{-- Right: Filters --}}
                <form method="GET"
                    class="w-full lg:w-auto flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                    {{-- Search --}}
                    <div class="relative w-full sm:w-72 lg:w-80">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Zm11 3-6-6" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </span>
                        <input type="text" name="q" value="{{ $q }}"
                            placeholder="Cari user, model, id..."
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 pl-11 text-sm
                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition" />
                    </div>

                    {{-- Action --}}
                    <select name="action"
                        class="w-full sm:w-44 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                   focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                        <option value="">Semua Aksi</option>
                        <option value="created" {{ $action === 'created' ? 'selected' : '' }}>created</option>
                        <option value="updated" {{ $action === 'updated' ? 'selected' : '' }}>updated</option>
                        <option value="deleted" {{ $action === 'deleted' ? 'selected' : '' }}>deleted</option>
                    </select>

                    {{-- Type --}}
                    <select name="type"
                        class="w-full sm:w-72 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                   focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                        <option value="">Semua Model</option>
                        @foreach ($types as $t)
                            <option value="{{ $t }}" {{ $type === $t ? 'selected' : '' }}>
                                {{ $t }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Buttons --}}
                    <div class="flex gap-2">
                        <button
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white
                       hover:bg-slate-800 transition">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path d="M21 21l-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                <path d="M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" stroke="currentColor" stroke-width="2" />
                            </svg>
                            Filter
                        </button>

                        <a href="{{ route('admin.activity_logs.index') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm
                       hover:bg-slate-50 transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>


            {{-- Quick stats --}}
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs text-slate-500">Total log (halaman ini)</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ $logs->count() }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs text-slate-500">Page</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ $logs->currentPage() }} /
                        {{ $logs->lastPage() }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs text-slate-500">Total semua log</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ $logs->total() }}</div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft">
            <div class="max-w-full min-w-0 overflow-x-auto">
                <table class="min-w-[1100px] w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Aksi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Target</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Detail</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse($logs as $log)
                            @php
                                $actionBadge = match ($log->action) {
                                    'created' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                                    'updated' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-200',
                                    'deleted' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                                    default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
                                };

                                $shortType = class_basename($log->subject_type);
                                $hasDiff = !empty($log->before) || !empty($log->after);
                            @endphp

                            <tr class="hover:bg-maroon-50/40 transition-colors">
                                <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                                    <div class="font-medium text-slate-900">
                                        {{ optional($log->created_at)->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ optional($log->created_at)->format('H:i:s') }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-700">
                                    <div class="font-semibold text-slate-900 whitespace-nowrap">
                                        {{ $log->user?->nama_lengkap ?? 'System/Unknown' }}
                                    </div>
                                    <div class="text-xs text-slate-500 whitespace-nowrap">
                                        {{ $log->user?->email ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $actionBadge }}">
                                        {{ $log->action }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-700">
                                    <div class="font-semibold text-slate-900">
                                        {{ $shortType }}
                                        <span class="text-slate-400">#</span>{{ $log->subject_id }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ $log->subject_type }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2 whitespace-nowrap">
                                        <button type="button"
                                            onclick="document.getElementById('log-{{ $log->id }}').showModal()"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-sm
                                                   hover:bg-slate-50 transition">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path d="M12 5c5.5 0 10 7 10 7s-4.5 7-10 7S2 12 2 12s4.5-7 10-7Z"
                                                    stroke="currentColor" stroke-width="1.8" />
                                                <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor"
                                                    stroke-width="1.8" />
                                            </svg>
                                            Lihat
                                        </button>
                                    </div>

                                    {{-- Modal detail --}}
                                    <dialog id="log-{{ $log->id }}" class="rounded-2xl p-0 backdrop:bg-black/30">
                                        <div class="w-full max-w-3xl bg-white rounded-2xl border border-slate-200 p-5">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $actionBadge }}">
                                                            {{ $log->action }}
                                                        </span>
                                                        <div class="text-sm font-semibold text-slate-900 truncate">
                                                            {{ $shortType }} #{{ $log->subject_id }}
                                                        </div>
                                                    </div>

                                                    <p class="mt-1 text-sm text-slate-600">
                                                        Oleh <span
                                                            class="font-semibold text-slate-900">{{ $log->user?->nama_lengkap ?? 'System/Unknown' }}</span>
                                                        • {{ optional($log->created_at)->format('d M Y H:i:s') }}
                                                    </p>
                                                </div>

                                                <button type="button"
                                                    onclick="document.getElementById('log-{{ $log->id }}').close()"
                                                    class="h-10 w-10 rounded-2xl hover:bg-slate-100 transition grid place-items-center">
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                                        <path d="M6 6l12 12M18 6 6 18" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                    <div class="text-xs text-slate-500">IP</div>
                                                    <div class="mt-1 text-sm font-semibold text-slate-900">
                                                        {{ $log->ip_address ?? '-' }}</div>
                                                </div>
                                                <div
                                                    class="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                    <div class="text-xs text-slate-500">User Agent</div>
                                                    <div class="mt-1 text-sm text-slate-900 break-words">
                                                        {{ $log->user_agent ?? '-' }}</div>
                                                </div>
                                            </div>

                                            <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-3">
                                                {{-- BEFORE --}}
                                                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                                                    <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                                                        <div class="text-sm font-semibold text-slate-900">Before</div>
                                                        <div class="text-xs text-slate-500">Data sebelum perubahan</div>
                                                    </div>
                                                    <pre class="p-4 text-xs text-slate-800 overflow-auto max-h-80 bg-white">@php
                                                        echo $log->before
                                                            ? json_encode(
                                                                $log->before,
                                                                JSON_PRETTY_PRINT |
                                                                    JSON_UNESCAPED_SLASHES |
                                                                    JSON_UNESCAPED_UNICODE,
                                                            )
                                                            : '-';
                                                    @endphp</pre>
                                                </div>

                                                {{-- AFTER --}}
                                                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                                                    <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                                                        <div class="text-sm font-semibold text-slate-900">After</div>
                                                        <div class="text-xs text-slate-500">Data setelah perubahan</div>
                                                    </div>
                                                    <pre class="p-4 text-xs text-slate-800 overflow-auto max-h-80 bg-white">@php
                                                        echo $log->after
                                                            ? json_encode(
                                                                $log->after,
                                                                JSON_PRETTY_PRINT |
                                                                    JSON_UNESCAPED_SLASHES |
                                                                    JSON_UNESCAPED_UNICODE,
                                                            )
                                                            : '-';
                                                    @endphp</pre>
                                                </div>
                                            </div>

                                            <div class="mt-5 flex justify-end">
                                                <button type="button"
                                                    onclick="document.getElementById('log-{{ $log->id }}').close()"
                                                    class="rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-800 transition">
                                                    Tutup
                                                </button>
                                            </div>
                                        </div>
                                    </dialog>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Belum ada data log.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div>
            {{ $logs->links() }}
        </div>
    </div>
@endsection
