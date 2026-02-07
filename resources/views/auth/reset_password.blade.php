<x-layouts.main-layout pageTitle="Reset Password">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-6">
                    <p class="display-6">RESET PASSWORD</p>

                    <form action="{{ route('reset_password_update') }}" method="post">
                        @csrf
                        <input type="hidden" name="token" value={{ $token }}>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            @error('new_password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">New Password Confirmation</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                            @error('new_password_confirmation')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mt-4">
                            <div class="col">
                                <a href="{{ route('login') }}">I don't want to change my password</a>
                            </div>
                            <div class="col text-end">
                                <button type="submit" class="btn btn-secondary px-5">RESET PASSWORD</button>
                            </div>
                        </div>

                    </form>

                    @if(session('server_error'))
                        <div class="alert alert-danger text-center mt-3">
                            {{ session('server_error') }}
                        </div>
                    @endif

            </div>
        </div>
    </div>
</x-layouts.main-layout>