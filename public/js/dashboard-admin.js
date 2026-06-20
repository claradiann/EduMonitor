// ===== Modal Toggle =====
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.toggle('hidden');
    }
}

// Close modal when clicking the dark overlay
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
        e.target.classList.add('hidden');
    }
});

// ===== Show/Hide Fields Based on Selected Role =====
function handleRoleChange(prefix) {
    const role = document.getElementById(prefix + '_role').value;

    const siswaFields = document.getElementById(prefix + '_siswa_fields');
    const guruFields = document.getElementById(prefix + '_guru_fields');
    const parentFields = document.getElementById(prefix + '_parent_fields');

    if (siswaFields) siswaFields.classList.add('hidden');
    if (guruFields) guruFields.classList.add('hidden');
    if (parentFields) parentFields.classList.add('hidden');

    if (role === 'siswa' && siswaFields) {
        siswaFields.classList.remove('hidden');
    } else if (role === 'guru' && guruFields) {
        guruFields.classList.remove('hidden');
    } else if (role === 'orang_tua' && parentFields) {
        parentFields.classList.remove('hidden');
    }
}

// ===== Populate & Open Edit Modal =====
function openEditModal(user) {
    document.getElementById('edit_name').value = user.name ?? '';
    document.getElementById('edit_username').value = user.username ?? '';
    document.getElementById('edit_email').value = user.email ?? '';

    const siswaFields = document.getElementById('edit_siswa_fields');
    const guruFields = document.getElementById('edit_guru_fields');

    siswaFields.classList.add('hidden');
    guruFields.classList.add('hidden');

    if (user.role === 'siswa') {
        siswaFields.classList.remove('hidden');
        document.getElementById('edit_nisn').value = user.nisn ?? '';
        document.getElementById('edit_kelas_id').value = user.kelas_id ?? '';
    } else if (user.role === 'guru') {
        guruFields.classList.remove('hidden');
        document.getElementById('edit_nip').value = user.nip ?? '';
    }

    // Set the form action dynamically to point at this user's update route
    const form = document.getElementById('editUserForm');
    form.action = '/admin/users/' + user.id;

    // Pass role along so backend / next edit knows it (hidden field)
    let roleInput = form.querySelector('input[name="role"]');
    if (!roleInput) {
        roleInput = document.createElement('input');
        roleInput.type = 'hidden';
        roleInput.name = 'role';
        form.appendChild(roleInput);
    }
    roleInput.value = user.role;

    toggleModal('editUserModal');
}

// Initialize add-user role field state on page load
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('add_role')) {
        handleRoleChange('add');
    }
});