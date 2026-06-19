<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerBridge — Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }
        .auth-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .brand-title { color: #e94560; font-weight: 800; font-size: 1.8rem; }
        .btn-primary { background-color: #0f3460; border-color: #0f3460; }
        .btn-primary:hover { background-color: #e94560; border-color: #e94560; }
        .role-card {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .role-card:hover { border-color: #0f3460; background-color: #f0f4ff; }
        input[type="radio"]:checked + label .role-card {
            border-color: #0f3460;
            background-color: #e8eeff;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="text-center mb-4">
                <h1 class="brand-title">
                    <i class="bi bi-briefcase-fill"></i> CareerBridge
                </h1>
                <p class="text-white-50 small">Create your account to get started</p>
            </div>

            <div class="card auth-card p-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-1">Create Account</h5>
                    <p class="text-muted small mb-4">Fill in the details below</p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.post') }}">
                        @csrf

                        {{-- Role Selection --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">I am registering as</label>
                            <div class="row g-3">

                                <div class="col-6">
                                    <input type="radio" name="role" id="role_student"
                                           value="student" class="d-none"
                                           {{ old('role', 'student') === 'student' ? 'checked' : '' }}>
                                    <label for="role_student" class="w-100">
                                        <div class="role-card text-center">
                                            <i class="bi bi-mortarboard-fill fs-2 text-primary"></i>
                                            <div class="fw-semibold mt-1">Student</div>
                                            <small class="text-muted">Looking for internships</small>
                                        </div>
                                    </label>
                                </div>

                                <div class="col-6">
                                    <input type="radio" name="role" id="role_company"
                                           value="company" class="d-none"
                                           {{ old('role') === 'company' ? 'checked' : '' }}>
                                    <label for="role_company" class="w-100">
                                        <div class="role-card text-center">
                                            <i class="bi bi-building-fill fs-2 text-success"></i>
                                            <div class="fw-semibold mt-1">Company</div>
                                            <small class="text-muted">Posting internships</small>
                                        </div>
                                    </label>
                                </div>

                            </div>
                            @error('role')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}"
                                    placeholder="you@example.com"
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input
                                    type="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Minimum 8 characters"
                                    required
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Confirm Password --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    class="form-control"
                                    placeholder="Repeat your password"
                                    required
                                >
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-person-plus me-1"></i> Create Account
                        </button>
                    </form>

                    <hr class="my-4">
                    <p class="text-center text-muted small mb-0">
                        Already have an account?
                        <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">
                            Sign in here
                        </a>
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>