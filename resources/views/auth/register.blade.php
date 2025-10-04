@extends('layouts.app')

@section('title', 'Register - DELEXES UNIVERSITY COLLEGE')

@section('content')
<div class="auth-container">
    <!-- Background Section (70% width) -->
    <div class="auth-background">
        <div class="background-overlay">
            <div class="background-content">
                <div class="university-info">
                    <h1 class="university-title">DELEXES UNIVERSITY COLLEGE</h1>
                    <p class="university-subtitle">Be Knowledgeable and Innovative in Science and Technology</p>
                    <div class="university-features">
                        <div class="feature-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Excellence in Education</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-flask"></i>
                            <span>Innovation & Research</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <span>Community Focused</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Section (30% width) -->
    <div class="auth-form-section">
        <div class="form-container">
            <div class="form-header">
                <h2 class="form-title">{{ __('Register') }}</h2>
                <p class="form-subtitle">Join our community! Create your account to get started.</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Enter your full name">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">{{ __('Email Address') }}</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Enter your email">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Create a password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-delexes-primary btn-block">
                        {{ __('Register') }}
                    </button>
                </div>

                <div class="form-footer">
                    <p class="signup-link">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="signup-link-text">Sign in here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.auth-container {
    display: flex;
    min-height: calc(100vh - 76px);
    margin: 0;
    padding: 0;
}

.auth-background {
    flex: 0 0 70%;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #1e40af 100%);
    background-image: url('{{ asset("images/background.jpg") }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.background-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(30, 58, 138, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
}

.background-content {
    text-align: center;
    color: white;
    padding: 2rem;
    max-width: 600px;
}

.university-title {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.university-subtitle {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.university-features {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.feature-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    font-size: 1.1rem;
}

.feature-item i {
    font-size: 1.5rem;
    color: #3b82f6;
}

.auth-form-section {
    flex: 0 0 30%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.form-container {
    width: 100%;
    max-width: 400px;
}

.form-header {
    text-align: center;
    margin-bottom: 2rem;
}

.form-title {
    font-size: 2rem;
    font-weight: bold;
    color: #1e3a8a;
    margin-bottom: 0.5rem;
}

.form-subtitle {
    color: #6b7280;
    font-size: 0.9rem;
}

.auth-form {
    width: 100%;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-delexes-primary {
    width: 100%;
    padding: 0.75rem;
    background-color: #1e3a8a;
    border: none;
    border-radius: 0.5rem;
    color: white;
    font-size: 1rem;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.btn-delexes-primary:hover {
    background-color: #1e40af;
}

.form-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.signup-link {
    color: #6b7280;
    font-size: 0.9rem;
    margin: 0;
}

.signup-link-text {
    color: #1e3a8a;
    text-decoration: none;
    font-weight: 500;
}

.signup-link-text:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .auth-container {
        flex-direction: column;
    }
    
    .auth-background {
        flex: 0 0 40%;
        min-height: 300px;
    }
    
    .auth-form-section {
        flex: 0 0 60%;
    }
    
    .university-title {
        font-size: 2rem;
    }
    
    .university-subtitle {
        font-size: 1rem;
    }
    
    .feature-item {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .auth-form-section {
        padding: 1rem;
    }
    
    .form-container {
        max-width: 100%;
    }
    
    .university-title {
        font-size: 1.5rem;
    }
    
    .university-features {
        gap: 0.5rem;
    }
    
    .feature-item {
        font-size: 0.8rem;
    }
}
</style>
@endsection
