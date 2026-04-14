<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'TH Trade Corporation') }}</title>
    <!-- Bootstrap CSS (Traditional Style) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #343a40;
        }
        .navbar-brand {
            color: #ffffff !important;
            font-weight: bold;
        }
        .card {
            border-radius: 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn {
            border-radius: 0;
        }
        .form-control {
            border-radius: 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                TH TRADE CORPORATION
            </a>
            @auth
            <div class="ms-auto">
                <span class="text-white me-3">Welcome, {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})</span>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
            @endauth
        </div>
    </nav>

    <div class="container">
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
