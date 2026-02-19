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
                        <label for="edit_name"
                            class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                        <div class="col-md-6">
                            <input id="edit_name" type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                name="edit_name" value="{{ $user->name }}" required>

                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="edit_email"
                            class="col-md-4 col-form-label text-md-end">{{ __('Email') }}</label>

                        <div class="col-md-6">
                            <input id="edit_email" type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                name="edit_email" value="{{ $user->email }}">

                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="edit_username"
                            class="col-md-4 col-form-label text-md-end">{{ __('Username') }}</label>

                        <div class="col-md-6">
                            <input id="edit_username" type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                name="edit_username" value="{{ $user->username }}">

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
                            <select id="role" class="form-control @error('role') is-invalid @enderror" name="role" required>
                                <option value="sale" {{ $user->role == 'sale' ? 'selected' : '' }}>Sale</option>
                                <option value="audit" {{ $user->role == 'audit' ? 'selected' : '' }}>Audit</option>
                                <option value="manager" {{ $user->role == 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="md" {{ $user->role == 'md' ? 'selected' : '' }}>MD</option>
                            </select>

                            @error('role')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="edit_branch"
                            class="col-md-4 col-form-label text-md-end">{{ __('สาขา') }}</label>

                        <div class="col-md-6">
                            <select id="edit_branch" class="form-control" name="edit_branch" required>
                                @foreach ($branch as $item)
                                <option value="{{ @$item->id }}" {{ $user->branch == $item->id ? 'selected' : '' }}>
                                    {{ @$item->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="edit_brand"
                            class="col-md-4 col-form-label text-md-end">{{ __('Brand') }}</label>

                        <div class="col-md-6">
                            <select id="edit_brand" class="form-control" name="edit_brand" required>
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