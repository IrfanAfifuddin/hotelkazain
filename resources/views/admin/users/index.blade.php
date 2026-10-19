@extends('layouts.app')

@section('title', 'Kelola Akun Resepsionis')

@section('content')
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kelola Akun Resepsionis</h1>
            <p class="text-sm text-gray-500 mt-1">Tambah, ubah, atau hapus akun akses bagi petugas resepsionis.</p>
        </div>
        <div>
            <button onclick="openCreateModal()" class="inline-flex items-center space-x-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
                <span>Tambah Resepsionis</span>
            </button>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tabel Akun --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Nama Lengkap</th>
                        <th class="px-6 py-3">Username</th>
                        <th class="px-6 py-3">Dibuat Pada</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($receptionists as $index => $u)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $u->user_name }}</td>
                            <td class="px-6 py-4 text-gray-600 font-mono text-xs">{{ $u->username }}</td>
                            <td class="px-6 py-4 text-gray-500 text-xs">{{ $u->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <button type="button" onclick="openEditModal({{ $u->user_id }}, '{{ $u->user_name }}', '{{ $u->username }}')" class="text-xs font-medium text-blue-600 hover:text-blue-900">
                                    Edit
                                </button>
                                <button type="button" onclick="confirmDelete({{ $u->user_id }}, '{{ $u->user_name }}')" class="text-xs font-medium text-red-600 hover:text-red-900">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                Belum ada akun resepsionis terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Create / Edit --}}
    <div id="userModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" onclick="closeUserModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-900 mb-4">Tambah Resepsionis</h3>
                <form id="userForm" method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                    @csrf
                    <div id="methodField"></div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nama Lengkap \*</label>
                        <input type="text" name="user_name" id="inputName" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Username \*</label>
                        <input type="text" name="username" id="inputUsername" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Password <span id="passwordHint" class="text-gray-400 font-normal">(Minimal 6 karakter)</span></label>
                        <input type="password" name="password" id="inputPassword" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-gray-900">
                    </div>
                    <div class="flex space-x-3 pt-2">
                        <button type="button" onclick="closeUserModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-gray-900 rounded-lg text-sm font-medium text-white hover:bg-gray-800">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Delete --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" onclick="closeDeleteModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 relative">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Akun</h3>
                <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin menghapus akun <span id="deleteNameLabel" class="font-semibold text-gray-900">--</span>?</p>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-red-600 rounded-lg text-sm font-medium text-white hover:bg-red-700">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openCreateModal() {
                document.getElementById('modalTitle').innerText = 'Tambah Resepsionis';
                document.getElementById('userForm').action = "{{ route('admin.users.store') }}";
                document.getElementById('methodField').innerHTML = '';
                document.getElementById('inputName').value = '';
                document.getElementById('inputUsername').value = '';
                document.getElementById('inputPassword').value = '';
                document.getElementById('inputPassword').required = true;
                document.getElementById('passwordHint').innerText = '(Wajib, min. 6 karakter)';
                document.getElementById('userModal').classList.remove('hidden');
            }

            function openEditModal(id, name, username) {
                document.getElementById('modalTitle').innerText = 'Edit Resepsionis';
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
            }/
        </script>
    @endpush
@endsection
