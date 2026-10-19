@extends('layouts.app')

@section('title', 'Kelola Akun Resepsionis')

@section('content')
    <div class="mx-auto max-w-7xl space-y-7">
        <header class="relative isolate overflow-hidden rounded-3xl bg-kazain-primary px-6 py-7 text-white shadow-[0_20px_50px_-32px_rgba(29,48,39,0.65)] sm:px-8 sm:py-9">
            <div aria-hidden="true" class="absolute -right-16 -top-28 -z-10 h-72 w-72 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -right-2 -top-14 -z-10 h-52 w-52 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -bottom-28 right-36 -z-10 h-52 w-52 rounded-full bg-kazain-accent/10 blur-3xl"></div>

            <div class="flex flex-col gap-7 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#d9c99d]"></span>
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </p>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Kelola akun resepsionis</h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/75">Atur akses akun bagi petugas resepsionis Kazain Hotel.</p>
                </div>

                <button
                    type="button"
                    onclick="openCreateModal()"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#d8c89c] px-4 text-sm font-semibold text-[#26332c] shadow-sm transition hover:bg-[#e3d6b5] focus:outline-none focus-visible:ring-4 focus-visible:ring-white/30"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
                    </svg>
                    Tambah resepsionis
                </button>
            </div>
        </header>

        @if (session('success'))
            <div role="status" class="flex items-start gap-3 rounded-2xl border border-[#c6dfcf] bg-[#f0f7f2] px-4 py-3.5 text-sm text-[#285c3d]">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25 4.5-4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-2xl border border-[#e8c9c2] bg-[#fbf3f1] px-4 py-3.5 text-sm text-[#844a40]">
                <p class="font-semibold">Periksa kembali data yang dimasukkan.</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-kazain-border bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-kazain-border px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-base font-semibold text-kazain-text">Daftar resepsionis</h2>
                        <span class="rounded-full bg-[#edf1ec] px-2.5 py-1 text-xs font-medium text-kazain-primary">{{ number_format($receptionists->count(), 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-1 text-sm text-kazain-muted">Kelola informasi dan akses akun petugas.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-left text-sm">
                    <thead class="bg-[#f7f8f5]">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">No.</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Nama lengkap</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Username</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Dibuat pada</th>
                            <th scope="col" class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-kazain-border">
                        @forelse ($receptionists as $index => $user)
                            <tr class="transition hover:bg-[#f7f8f5]">
                                <td class="whitespace-nowrap px-6 py-4 text-kazain-muted">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#edf1ec] text-sm font-semibold text-kazain-primary">
                                            {{ mb_strtoupper(mb_substr($user->user_name, 0, 1)) }}
                                        </span>
                                        <span class="font-semibold text-kazain-text">{{ $user->user_name }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="rounded-lg bg-[#f4f5f1] px-2.5 py-1 font-mono text-xs text-kazain-muted">{{ $user->username }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-kazain-muted">{{ $user->created_at->format('d M Y, H:i') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button
                                            type="button"
                                            onclick="openEditModal(@js($user->user_id), @js($user->user_name), @js($user->username))"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-kazain-primary transition hover:bg-[#edf1ec] focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            onclick="confirmDelete(@js($user->user_id), @js($user->user_name))"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-[#975b50] transition hover:bg-[#f4eae7] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#975b50]/15"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#edf1ec] text-kazain-primary">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 1 2.625.372 9.337 9.337 0 0 1 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
                                        </svg>
                                    </span>
                                    <p class="mt-3 font-semibold text-kazain-text">Belum ada akun resepsionis</p>
                                    <p class="mt-1 text-sm text-kazain-muted">Tambahkan akun petugas agar dapat mengakses sistem.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div id="userModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <button type="button" class="absolute inset-0 cursor-default bg-[#1d3027]/35 backdrop-blur-[2px]" onclick="closeUserModal()" aria-label="Tutup formulir"></button>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl border border-kazain-border bg-kazain-surface p-6 shadow-[0_24px_70px_-30px_rgba(29,48,39,0.45)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#edf1ec] text-kazain-primary">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 1 2.625.372 9.337 9.337 0 0 1 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
                    </svg>
                </span>
                <h2 id="modalTitle" class="mt-4 text-lg font-semibold text-kazain-text">Tambah resepsionis</h2>
                <p class="mt-1 text-sm text-kazain-muted">Isi informasi akun petugas resepsionis.</p>

                <form id="userForm" method="POST" action="{{ route('admin.users.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <div id="methodField"></div>
                    <div class="space-y-1.5">
                        <label for="inputName" class="block text-xs font-semibold text-kazain-muted">Nama lengkap <span class="text-[#975b50]">*</span></label>
                        <input type="text" name="user_name" id="inputName" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-white px-3 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                    <div class="space-y-1.5">
                        <label for="inputUsername" class="block text-xs font-semibold text-kazain-muted">Username <span class="text-[#975b50]">*</span></label>
                        <input type="text" name="username" id="inputUsername" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-white px-3 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                    <div class="space-y-1.5">
                        <label for="inputPassword" class="block text-xs font-semibold text-kazain-muted">Password <span id="passwordHint" class="font-normal text-kazain-muted">(Minimal 6 karakter)</span></label>
                        <input type="password" name="password" id="inputPassword" class="min-h-11 w-full rounded-xl border border-kazain-border bg-white px-3 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeUserModal()" class="min-h-11 flex-1 rounded-xl border border-kazain-border bg-white px-4 text-sm font-medium text-kazain-text transition hover:bg-[#f4f5f1] focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15">
                            Batal
                        </button>
                        <button type="submit" class="min-h-11 flex-1 rounded-xl bg-kazain-primary px-4 text-sm font-semibold text-white transition hover:bg-kazain-primary-hover focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
        <button type="button" class="absolute inset-0 cursor-default bg-[#1d3027]/35 backdrop-blur-[2px]" onclick="closeDeleteModal()" aria-label="Tutup konfirmasi"></button>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-sm rounded-2xl border border-kazain-border bg-kazain-surface p-6 shadow-[0_24px_70px_-30px_rgba(29,48,39,0.45)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f4eae7] text-[#975b50]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008v.008H12v-.008ZM10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                    </svg>
                </span>
                <h2 id="deleteModalTitle" class="mt-4 text-lg font-semibold text-kazain-text">Hapus akun resepsionis?</h2>
                <p class="mt-1 text-sm leading-6 text-kazain-muted">Akun <span id="deleteNameLabel" class="font-semibold text-kazain-text">--</span> akan dihapus dari sistem.</p>
                <form id="deleteForm" method="POST" action="" class="mt-6">
                    @csrf
                    @method('DELETE')
                    <div class="flex gap-3">
                        <button type="button" onclick="closeDeleteModal()" class="min-h-11 flex-1 rounded-xl border border-kazain-border bg-white px-4 text-sm font-medium text-kazain-text transition hover:bg-[#f4f5f1] focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15">
                            Batal
                        </button>
                        <button type="submit" class="min-h-11 flex-1 rounded-xl bg-[#975b50] px-4 text-sm font-semibold text-white transition hover:bg-[#814b42] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#975b50]/20">
                            Hapus akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openCreateModal() {
                document.getElementById('modalTitle').innerText = 'Tambah resepsionis';
                document.getElementById('userForm').action = "{{ route('admin.users.store') }}";
                document.getElementById('methodField').innerHTML = '';
                document.getElementById('inputName').value = '';
                document.getElementById('inputUsername').value = '';
                document.getElementById('inputPassword').value = '';
                document.getElementById('inputPassword').required = true;
                document.getElementById('passwordHint').innerText = '(Wajib, minimal 6 karakter)';
                document.getElementById('userModal').classList.remove('hidden');
            }

            function openEditModal(id, name, username) {
                document.getElementById('modalTitle').innerText = 'Edit resepsionis';
                document.getElementById('userForm').action = "{{ url('/admin/kelola-akun') }}/" + id;
                document.getElementById('methodField').innerHTML = '@method("PUT")';
                document.getElementById('inputName').value = name;
                document.getElementById('inputUsername').value = username;
                document.getElementById('inputPassword').value = '';
                document.getElementById('inputPassword').required = false;
                document.getElementById('passwordHint').innerText = '(Kosongkan jika tidak ingin mengubah password)';
                document.getElementById('userModal').classList.remove('hidden');
            }

            function closeUserModal() {
                document.getElementById('userModal').classList.add('hidden');
            }

            function confirmDelete(id, name) {
                document.getElementById('deleteNameLabel').innerText = name;
                document.getElementById('deleteForm').action = "{{ url('/admin/kelola-akun') }}/" + id;
                document.getElementById('deleteModal').classList.remove('hidden');
            }

            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.add('hidden');
            }
        </script>
    @endpush
@endsection
