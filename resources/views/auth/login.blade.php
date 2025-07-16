@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Left side - Decorative -->
            <div class="col-lg-6 d-none d-lg-flex auth-left-panel">
                <div class="auth-decoration">
                    <div class="decoration-content">
                        <div class="brand-logo mb-4">
                            <i class="fas fa-columns fa-3x text-white mb-3"></i>
                            <h2 class="text-white fw-bold">Kanban Board</h2>
                            <p class="text-white-50">Организуйте свои задачи эффективно</p>
                        </div>
                        <div class="features-list">
                            <div class="feature-item">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="text-white">Управление проектами</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="text-white">Командная работа</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="text-white">Отслеживание прогресса</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right side - Login form -->
            <div class="col-lg-6 auth-right-panel">
                <div class="auth-form-container">
                    <div class="auth-form-wrapper">
                        <!-- Mobile logo -->
                        <div class="d-lg-none text-center mb-4">
                            <i class="fas fa-columns fa-2x text-primary mb-2"></i>
                            <h4 class="text-dark">Kanban Board</h4>
                        </div>

                        <div class="auth-header text-center mb-4">
                            <h1 class="auth-title">Добро пожаловать!</h1>
                            <p class="auth-subtitle text-muted">Войдите в свой аккаунт для продолжения</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}" class="auth-form">
                            @csrf

                            <!-- Email field -->
                            <div class="form-floating mb-3">
                                <input id="email" 
                                       type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="name@example.com"
                                       required 
                                       autocomplete="email" 
                                       autofocus>
                                <label for="email">
                                    <i class="fas fa-envelope me-2"></i>Email адрес
                                </label>
                                @error('email')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <!-- Password field -->
                            <div class="form-floating mb-3">
                                <input id="password" 
                                       type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       name="password" 
                                       placeholder="Password"
                                       required 
                                       autocomplete="current-password">
                                <label for="password">
                                    <i class="fas fa-lock me-2"></i>Пароль
                                </label>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <!-- Remember me and forgot password -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="remember" 
                                           id="remember" 
                                           {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        Запомнить меня
                                    </label>
                                </div>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="forgot-password-link">
                                        Забыли пароль?
                                    </a>
                                @endif
                            </div>

                            <!-- Submit button -->
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 auth-submit-btn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Войти
                            </button>

                            <!-- Register link -->
                            @if (Route::has('register'))
                                <div class="text-center">
                                    <span class="text-muted">Нет аккаунта? </span>
                                    <a href="{{ route('register') }}" class="register-link">
                                        Зарегистрируйтесь
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.auth-wrapper {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.auth-left-panel {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.auth-left-panel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="1" fill="white" opacity="0.1"/><circle cx="20" cy="80" r="1" fill="white" opacity="0.1"/><circle cx="80" cy="30" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.auth-decoration {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 2rem;
}

.decoration-content {
    text-align: center;
}

.features-list {
    margin-top: 3rem;
}

.feature-item {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.auth-right-panel {
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.auth-form-container {
    width: 100%;
    max-width: 400px;
}

.auth-form-wrapper {
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    background: white;
}

.auth-title {
    color: #2d3748;
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.auth-subtitle {
    font-size: 1rem;
    margin-bottom: 2rem;
}

.form-floating > label {
    color: #718096;
}

.form-control {
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.auth-submit-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.auth-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.forgot-password-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.forgot-password-link:hover {
    color: #764ba2;
    text-decoration: underline;
}

.register-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.register-link:hover {
    color: #764ba2;
    text-decoration: underline;
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

@media (max-width: 991.98px) {
    .auth-wrapper {
        background: white;
    }
    
    .auth-form-wrapper {
        box-shadow: none;
        padding: 1rem;
    }
    
    .auth-right-panel {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .auth-form-wrapper {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
    }
}
</style>
@endsection
