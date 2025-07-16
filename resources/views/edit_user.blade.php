@extends('layout.layout')

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center rounded-top-4">
            <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit User</h4>
            <a href="{{ route('machine.devicedata') }}" class="btn btn-outline-light btn-sm">← Back</a>
        </div>

        <div class="row g-0">
            {{-- Kiri: Gambar --}}
            <div class="col-md-5 d-flex align-items-center justify-content-center bg-dark-subtle p-3">
                <img src="{{ asset('project.gif') }}" alt="Machine Image" class="img-fluid rounded-3 shadow-sm">
            </div>

            {{-- Kanan: Form --}}
            <div class="col-md-7">
                <div class="card-body p-4 bg-light rounded-bottom-4 h-100">
                    <form action="{{ route('machine.deviceupdateusersingle', $user['uid']) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">👤 User ID</label>
                            <input type="text" name="userid" value="{{ $user['userid'] }}" class="form-control rounded-3 shadow-sm" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">📝 Name</label>
                            <input type="text" name="name" value="{{ $user['name'] }}" class="form-control rounded-3 shadow-sm" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">🛡️ Role</label>
                            <select name="role" class="form-select rounded-3 shadow-sm" required>
                                <option value="0" {{ $user['role'] == 0 ? 'selected' : '' }}>User</option>
                                <option value="14" {{ $user['role'] == 14 ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">🔒 Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordField" value="{{ $user['password'] }}" class="form-control rounded-start-3 shadow-sm" required>
                                <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="togglePassword()">👁</button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">💳 Card No</label>
                            <input type="text" name="cardno" value="{{ $user['cardno'] }}" class="form-control rounded-3 shadow-sm">
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-success px-4">💾 Save</button>
                            <a href="{{ route('machine.devicedata') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script untuk toggle password --}}
<script>
function togglePassword() {
    const field = document.getElementById('passwordField');
    field.type = field.type === 'password' ? 'text' : 'password';
}
</script>
@endsection
