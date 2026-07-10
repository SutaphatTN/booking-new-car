@php
    $selectableRoles = [
        'sale' => 'Sale',
        'audit' => 'Audit',
        'account' => 'Account',
        'registration' => 'Registration',
        'bp' => 'BP',
        'cs' => 'CS',
        'manager' => 'Manager',
        'md' => 'MD',
    ];

    // role พิเศษ (admin/gm/audit_lead/cro/sp/marketing/adminPage) ตั้งใจไม่ให้เลือกจาก dropdown
    // ต้องส่งค่าเดิมกลับไปด้วย ไม่งั้นฟอร์มจะเขียนทับเป็น option แรก (sale) ตอนแก้ฟิลด์อื่น
    $isSpecialRole = !array_key_exists($user->role, $selectableRoles);
@endphp

<div class="modal fade editUser" tabindex="-1" role="dialog" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserLabel">แก้ไขข้อมูลผู้ใช้งาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('user.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <label for="name"
                            class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                        <div class="col-md-6">
                            <input id="name" type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ $user->name }}" required>

                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="email"
                            class="col-md-4 col-form-label text-md-end">{{ __('Email') }}</label>

                        <div class="col-md-6">
                            <input id="email" type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                name="email" value="{{ $user->email }}">

                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="username"
                            class="col-md-4 col-form-label text-md-end">{{ __('Username') }}</label>

                        <div class="col-md-6">
                            <input id="username" type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                name="username" value="{{ $user->username }}">

                            @error('username')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="password"
                            class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                        <div class="col-md-6">
                            <input id="password" type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                name="password" value="{{ $user->password_plain }}">

                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="role"
                            class="col-md-4 col-form-label text-md-end">{{ __('Role') }}</label>

                        <div class="col-md-6">
                            <select id="role" class="form-control @error('role') is-invalid @enderror" name="role"
                                required @disabled($isSpecialRole)>
                                @if ($isSpecialRole)
                                <option value="{{ $user->role }}" selected>{{ $user->role }}</option>
                                @endif

                                @foreach ($selectableRoles as $value => $label)
                                <option value="{{ $value }}" {{ $user->role === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>

                            @if ($isSpecialRole)
                            <input type="hidden" name="role" value="{{ $user->role }}">
                            <small class="text-muted">role พิเศษ — เปลี่ยนได้จากฐานข้อมูลเท่านั้น</small>
                            @endif

                            @error('role')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="branch"
                            class="col-md-4 col-form-label text-md-end">{{ __('สาขา') }}</label>

                        <div class="col-md-6">
                            <select id="branch" class="form-control" name="branch" required>
                                @foreach ($branch as $item)
                                <option value="{{ @$item->id }}" {{ $user->branch == $item->id ? 'selected' : '' }}>
                                    {{ @$item->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="brand"
                            class="col-md-4 col-form-label text-md-end">{{ __('Brand') }}</label>

                        <div class="col-md-6">
                            <select id="brand" class="form-control" name="brand" required>
                                @foreach ($brand as $item)
                                <option value="{{ @$item->id }}" {{ $user->brand == $item->id ? 'selected' : '' }}>
                                    {{ @$item->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="userZone"
                            class="col-md-4 col-form-label text-md-end">{{ __('Zone') }}</label>

                        <div class="col-md-6">
                            <select id="userZone" class="form-control" name="userZone" required>
                                <option value="10" {{ $user->userZone == '10' ? 'selected' : '' }}>ปัตตานี</option>
                                <option value="40" {{ $user->userZone == '40' ? 'selected' : '' }}>กระบี่</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="phone"
                            class="col-md-4 col-form-label text-md-end">{{ __('เบอร์โทร') }}</label>

                        <div class="col-md-6">
                            <input id="phone" type="text"
                                class="form-control phone-input @error('phone') is-invalid @enderror"
                                name="phone" maxlength="12"
                                value="{{ $user->formatted_phone }}">

                            @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <button type="button" class="btn btn-primary btnUpdateUser">
                                แก้ไขข้อมูล
                            </button>

                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .editUser .modal-header {
        border-bottom: 1px solid #dee2e6;
    }

    .editUser .modal-title {
        font-weight: bold;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }
</style>