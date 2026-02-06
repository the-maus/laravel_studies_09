<x-layouts.main-layout pageTitle="Registration confirmation">
    <div class="container mt-5">
        <div class="row">
            <div class="col text-center">
                <div class="card p-5 text-center">
                    <p class="display-6">Your user account was successfully confirmed.</p>
                    <p class="display-6">Welcome, <strong>{{ Auth::user()->username }}</strong></p>
                    <div class="mt-5">
                        <a href="{{ route('home') }}" class="btn btn-secondary px-5">OK</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.main-layout>