<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Error' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="card shadow-sm border-0" style="max-width: 560px; width: 100%;">
            <div class="card-body text-center p-5">
                <h1 class="display-5 fw-bold text-secondary mb-3">
                    {{ $code ?? 'Error' }}
                </h1>

                <h2 class="h4 mb-3">
                    {{ $title ?? 'Something went wrong' }}
                </h2>

                <p class="text-muted mb-4">
                    {{ $message ?? 'An unexpected error occurred.' }}
                </p>

                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        Back to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        Go to Login
                    </a>
                @endauth
            </div>
        </div>
    </div>
</body>
</html> 
<!-- LAYOUT DOCUMENT DO NOT ALTER  -->