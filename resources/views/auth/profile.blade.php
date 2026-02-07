<x-layouts.main-layout pageTitle="User profile">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-6">
                    <p class="display-6">SET NEW PASSWORD</p>

                    <form action="{{ route('change_password') }}" method="post">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                            @error('current_password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            @error('new_password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">New password confirmation</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                            @error('new_password_confirmation')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mt-4">
                            <div class="col text-end">
                                <button type="submit" class="btn btn-secondary px-5">Change password</button>
                            </div>
                        </div>

                    </form>

                    @if (session('success'))
                        <div class="alert alert-success text-center mt-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('server_error'))
                        <div class="alert alert-danger text-center mt-3">
                            {{ session('server_error') }}
                        </div>
                    @endif

                    <hr>

                    <div class="card border-1 border-danger p-5 text-center">
                        If you want to remove your user account permanently, type "REMOVE" and click the button bellow.
                        <form action="{{ route('delete_account') }}" method="post">
                            @csrf
                            <div class="my-3">
                                <input type="text" name="delete_confirmation" class="form-control text-center" id="">
                                @error('delete_confirmation')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-danger">DELETE ACCOUNT</button>
                        </form>
                    </div>
            </div>
        </div>
    </div>
</x-layouts.main-layout>