@extends('layouts.app')

@section('title', 'Dashboard Admin - EduMonitor')

@section('content')
<div class="space-y-8">
    
    <!-- Top Greeting Banner -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-lg shadow-indigo-100">
        <div class="absolute right-0 top-0 w-80 h-full opacity-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white via-indigo-200 to-indigo-900 pointer-events-none"></div>
        <h2 class="text-4xl font-extrabold tracking-tight">Halo, Admin / Wakasek!</h2>
        <p class="text-indigo-100 text-sm mt-1 font-medium">Kelola data pengguna sekolah dan pantau kualitas pengajaran melalui rekap kuesioner evaluasi.</p>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Siswa -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Total Siswa</h3>
                <p class="text-4xl font-extrabold text-slate-700 mt-2">{{ $stats['total_students'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
        </div>

        <!-- Guru -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Total Guru</h3>
                <p class="text-4xl font-extrabold text-slate-700 mt-2">{{ $stats['total_teachers'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-chalkboard-user text-xl"></i>
            </div>
        </div>

        <!-- Kelas -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Total Kelas</h3>
                <p class="text-4xl font-extrabold text-slate-700 mt-2">{{ $stats['total_classes'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-school text-xl"></i>
            </div>
        </div>

        <!-- Evaluasi -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center justify-between hover-lift">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-wider uppercase">Evaluasi Masuk</h3>
                <p class="text-4xl font-extrabold text-slate-700 mt-2">{{ $stats['total_evaluations'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                <i class="fa-solid fa-square-poll-vertical text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Tab Navigation Toggles -->
    <div class="border-b border-slate-200">
        <nav class="flex space-x-6" aria-label="Tabs">
            <button onclick="switchTab('recap-tab', 'users-tab')" 
                    id="btn-recap-tab"
                    class="border-b-2 border-indigo-600 py-4 px-1 text-sm font-bold text-indigo-600 flex items-center gap-2">
                <i class="fa-solid fa-square-poll-horizontal text-base"></i>
                <span>Rekap Hasil Evaluasi</span>
            </button>
            <button onclick="switchTab('users-tab', 'recap-tab')" 
                    id="btn-users-tab"
                    class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 flex items-center gap-2">
                <i class="fa-solid fa-users-gear text-base"></i>
                <span>Manajemen User</span>
            </button>
        </nav>
    </div>

    <!-- Section 1: Rekap Hasil Evaluasi -->
    <div id="recap-section" class="space-y-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Rata-rata Skor per Indikator Sekolah -->
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm lg:col-span-5">
                <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-6">Poin Rata-rata Sekolah Per Indikator</h3>
                <div class="space-y-6">
                    @foreach($indicators as $ind)
                        <div>
                            <div class="flex justify-between items-center mb-1 text-xs font-bold text-slate-500">
                                <span>{{ $ind['name'] }}</span>
                                <span class="text-indigo-600 font-extrabold">{{ $ind['average'] }}</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-indigo-600 h-full rounded-full transition-all" style="width: {{ ($ind['average'] / 5) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Rankings/Peringkat Guru -->
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm lg:col-span-7">
                <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider mb-6">Peringkat & Rekap Nilai Evaluasi Guru</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase">
                                <th class="py-3 px-2">Guru</th>
                                <th class="py-3 px-2 text-center">Rata-rata Skor</th>
                                <th class="py-3 px-2 text-center">Kuesioner Masuk</th>
                                <th class="py-3 px-2 text-right">Predikat</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-medium text-slate-600">
                            @forelse($teachersEvaluations as $t)
                                <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                    <td class="py-4 px-2">
                                        <div class="font-bold text-slate-700">{{ $t['name'] }}</div>
                                        <div class="text-xs text-slate-400 font-semibold">{{ $t['nip'] }}</div>
                                    </td>
                                    <td class="py-4 px-2 text-center font-bold text-indigo-600">
                                        <div class="flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                                            <span>{{ $t['average_rating'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-2 text-center text-slate-500">
                                        {{ $t['responses_count'] }} ulasan
                                    </td>
                                    <td class="py-4 px-2 text-right">
                                        @if($t['average_rating'] >= 4.5)
                                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-full text-xs font-bold">Sangat Baik</span>
                                        @elseif($t['average_rating'] >= 4.0)
                                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-bold">Baik</span>
                                        @elseif($t['average_rating'] > 0)
                                            <span class="px-2.5 py-1 bg-amber-50 text-amber-600 rounded-full text-xs font-bold">Cukup</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-slate-100 text-slate-400 rounded-full text-xs font-semibold">Belum Dinilai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-slate-400 font-medium">Data evaluasi guru belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Section 2: Manajemen User (Hidden by default) -->
    <div id="users-section" class="hidden space-y-6">
        
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-600 uppercase tracking-wider">Daftar Pengguna EduMonitor</h3>
                    <p class="text-xs text-slate-400 mt-1 font-semibold">Kelola akun Siswa, Orang Tua, Guru, dan Admin.</p>
                </div>
                <button onclick="toggleModal('addUserModal')" 
                        id="btn-add-user"
                        class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-2xl transition-all shadow-md shadow-indigo-100 flex items-center gap-2 hover-lift">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Tambah Pengguna</span>
                </button>
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase">
                            <th class="py-3 px-2">Nama</th>
                            <th class="py-3 px-2">Username</th>
                            <th class="py-3 px-2">Role</th>
                            <th class="py-3 px-2">Info Khusus</th>
                            <th class="py-3 px-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-medium text-slate-600">
                        @foreach($usersList as $usr)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-2">
                                    <div class="font-bold text-slate-700">{{ $usr->name }}</div>
                                    <div class="text-xs text-slate-400 font-semibold">{{ $usr->email ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-2 font-mono text-slate-500">{{ $usr->username }}</td>
                                <td class="py-4 px-2">
                                    @if($usr->role == 'admin')
                                        <span class="px-2.5 py-1 bg-purple-50 text-purple-600 rounded-full text-xs font-bold uppercase">Admin</span>
                                    @elseif($usr->role == 'guru')
                                        <span class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold uppercase">Guru</span>
                                    @elseif($usr->role == 'siswa')
                                        <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-bold uppercase">Siswa</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-600 rounded-full text-xs font-bold uppercase">Orang Tua</span>
                                    @endif
                                </td>
                                <td class="py-4 px-2 text-xs text-slate-500">
                                    @if($usr->role == 'siswa')
                                        NISN: {{ $usr->nisn ?? '-' }} | Kelas: {{ $usr->kelas->nama_kelas ?? '-' }}
                                    @elseif($usr->role == 'guru')
                                        NIP: {{ $usr->nip ?? '-' }}
                                    @elseif($usr->role == 'orang_tua')
                                        Wali dari: {{ $usr->student->name ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-4 px-2 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Edit trigger -->
                                        <button onclick="openEditModal({{ json_encode($usr) }})" 
                                                class="w-9 h-9 rounded-xl bg-slate-50 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-center transition-all">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        
                                        <!-- Delete form -->
                                        @if(Auth::id() !== $usr->id)
                                            <form action="{{ route('admin.users.destroy', $usr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-500 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center transition-all">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <!-- Modal: Add User -->
    <div id="addUserModal" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl relative">
            <button onclick="toggleModal('addUserModal')" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            <h3 class="text-xl font-bold text-slate-800 mb-6">Tambah Pengguna Baru</h3>
            
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Nama Lengkap</label>
                        <input type="text" name="name" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Username</label>
                        <input type="text" name="username" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Email (Opsional)</label>
                        <input type="email" name="email" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600">Pilih Role</label>
                    <select name="role" id="add_role" onchange="handleRoleChange('add')" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                        <option value="siswa">Siswa</option>
                        <option value="guru">Guru</option>
                        <option value="orang_tua">Orang Tua</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <!-- Fields for Student only -->
                <div id="add_siswa_fields" class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">NISN</label>
                        <input type="text" name="nisn" id="add_nisn" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Kelas</label>
                        <select name="kelas_id" id="add_kelas_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Fields for Teacher only -->
                <div id="add_guru_fields" class="space-y-1.5 hidden">
                    <label class="text-xs font-bold text-slate-600">NIP</label>
                    <input type="text" name="nip" id="add_nip" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                </div>

                <!-- Fields for Parent only -->
                <div id="add_parent_fields" class="space-y-1.5 hidden">
                    <label class="text-xs font-bold text-slate-600">Wali Dari (Siswa)</label>
                    <select name="parent_of_id" id="add_parent_of_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                        @foreach($usersList->where('role', 'siswa') as $siswa)
                            <option value="{{ $siswa->id }}">{{ $siswa->name }} (NISN: {{ $siswa->nisn }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-2 hover-lift">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>SIMPAN PENGGUNA</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit User -->
    <div id="editUserModal" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl relative">
            <button onclick="toggleModal('editUserModal')" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            <h3 class="text-xl font-bold text-slate-800 mb-6">Ubah Data Pengguna</h3>
            
            <form id="editUserForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Nama Lengkap</label>
                        <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Username</label>
                        <input type="text" name="username" id="edit_username" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Email (Opsional)</label>
                        <input type="email" name="email" id="edit_email" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Password (Kosongkan jika tetap)</label>
                        <input type="password" name="password" placeholder="Minimal 6 karakter" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                    </div>
                </div>

                <!-- Fields for Student only -->
                <div id="edit_siswa_fields" class="grid grid-cols-2 gap-4 hidden">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">NISN</label>
                        <input type="text" name="nisn" id="edit_nisn" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Kelas</label>
                        <select name="kelas_id" id="edit_kelas_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Fields for Teacher only -->
                <div id="edit_guru_fields" class="space-y-1.5 hidden">
                    <label class="text-xs font-bold text-slate-600">NIP</label>
                    <input type="text" name="nip" id="edit_nip" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 text-sm">
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-2 hover-lift">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>PERBARUI PENGGUNA</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function switchTab(activeTabId, inactiveTabId) {
        // Toggle view
        document.getElementById(activeTabId.replace('-tab', '-section')).classList.remove('hidden');
        document.getElementById(inactiveTabId.replace('-tab', '-section')).classList.add('hidden');

        // Toggle classes on buttons
        const btnActive = document.getElementById('btn-' + activeTabId);
        const btnInactive = document.getElementById('btn-' + inactiveTabId);

        btnActive.className = "border-b-2 border-indigo-600 py-4 px-1 text-sm font-bold text-indigo-600 flex items-center gap-2";
        btnInactive.className = "border-b-2 border-transparent py-4 px-1 text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 flex items-center gap-2";
    }

    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.toggle('hidden');
    }

    function handleRoleChange(type) {
        const role = document.getElementById(type + '_role').value;
        const siswaFields = document.getElementById(type + '_siswa_fields');
        const guruFields = document.getElementById(type + '_guru_fields');
        const parentFields = document.getElementById(type + '_parent_fields');

        if (role === 'siswa') {
            siswaFields.classList.remove('hidden');
            guruFields.classList.add('hidden');
            if (parentFields) parentFields.classList.add('hidden');
            document.getElementById(type + '_nisn').required = true;
        } else if (role === 'guru') {
            siswaFields.classList.add('hidden');
            guruFields.classList.remove('hidden');
            if (parentFields) parentFields.classList.add('hidden');
            document.getElementById(type + '_nip').required = true;
        } else if (role === 'orang_tua') {
            siswaFields.classList.add('hidden');
            guruFields.classList.add('hidden');
            if (parentFields) parentFields.classList.remove('hidden');
        } else {
            siswaFields.classList.add('hidden');
            guruFields.classList.add('hidden');
            if (parentFields) parentFields.classList.add('hidden');
        }
    }

    function openEditModal(user) {
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_email').value = user.email || '';
        
        // Form Action update
        document.getElementById('editUserForm').action = "/admin/users/" + user.id;

        // Toggle special fields depending on user role
        const siswaFields = document.getElementById('edit_siswa_fields');
        const guruFields = document.getElementById('edit_guru_fields');

        if (user.role === 'siswa') {
            siswaFields.classList.remove('hidden');
            guruFields.classList.add('hidden');
            document.getElementById('edit_nisn').value = user.nisn || '';
            document.getElementById('edit_kelas_id').value = user.kelas_id || '';
        } else if (user.role === 'guru') {
            siswaFields.classList.add('hidden');
            guruFields.classList.remove('hidden');
            document.getElementById('edit_nip').value = user.nip || '';
        } else {
            siswaFields.classList.add('hidden');
            guruFields.classList.add('hidden');
        }

        toggleModal('editUserModal');
    }
</script>
@endsection
