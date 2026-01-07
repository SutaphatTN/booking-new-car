<div class="modal fade viewUser" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserLabel">ข้อมูลผู้ใช้งาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <label for="name"
                        class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                    <div class="col-md-6">
                        <input id="name" type="text"
                            class="form-control readonly-field bg-light"
                            name="name" value="{{ $user->name }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="email"
                        class="col-md-4 col-form-label text-md-end">{{ __('E-mail') }}</label>

                    <div class="col-md-6">
                        <input id="email" type="text"
                            class="form-control readonly-field bg-light"
                            name="email" value="{{ $user->email }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="username"
                        class="col-md-4 col-form-label text-md-end">{{ __('Username') }}</label>

                    <div class="col-md-6">
                        <input id="username" type="text"
                            class="form-control readonly-field bg-light"
                            name="username" value="{{ $user->username }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="password_plain"
                        class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                    <div class="col-md-6">
                        <input id="password_plain" type="text"
                            class="form-control readonly-field bg-light"
                            name="password_plain" value="{{ $user->password_plain }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="role"
                        class="col-md-4 col-form-label text-md-end">{{ __('Role') }}</label>

                    <div class="col-md-6">
                        <input id="role" type="text"
                            class="form-control readonly-field bg-light"
                            name="role" value="{{ ucfirst($user->role) }}">
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .viewUser .modal-header {
        border-bottom: 1px solid #dee2e6;
    }

    .viewUser .modal-title {
        font-weight: bold;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }
</style>