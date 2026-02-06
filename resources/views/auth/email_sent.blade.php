<x-layouts.main-layout pageTitle="E-mail Sent">
    <div class="container mt-5">
        <div class="row">
            <div class="col text-center">
                <div class="card p-5 text-center">
                    <p class="display-6">A confirmation e-mail was sent to:</p>
                    <p class="display-6 text-info fw-bold">{{ $email }}</p>
                    <p>Please confirm in the link sent to finish your registration.</p>
                    <div class="mt-5">
                        <a href="{{ route('login') }}" class="btn btn-secondary px-5">Home Page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.main-layout>
