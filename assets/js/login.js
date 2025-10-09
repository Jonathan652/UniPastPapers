// Login and Registration JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const authForm = document.getElementById('authForm');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const errorMsg = document.getElementById('errorMsg');
    const successMsg = document.getElementById('successMsg');
    const formTitle = document.getElementById('formTitle');
    const formSubtitle = document.getElementById('formSubtitle');

    // Form submission handler
    authForm.addEventListener('submit', handleFormSubmit);

    // Show register form
    window.showRegisterForm = function() {
        loginForm.classList.remove('active');
        registerForm.classList.add('active');
        formTitle.textContent = 'Create Account';
        formSubtitle.textContent = 'Join the university past papers portal';
        hideMessages();
    };

    // Show login form
    window.showLoginForm = function() {
        registerForm.classList.remove('active');
        loginForm.classList.add('active');
        formTitle.textContent = 'Welcome Back';
        formSubtitle.textContent = 'Sign in to access your dashboard';
        hideMessages();
    };

    // Handle form submission
    async function handleFormSubmit(e) {
        e.preventDefault();

        const isLogin = loginForm.classList.contains('active');
        const formData = new FormData(authForm);

        hideMessages();

        if (isLogin) {
            await handleLogin(formData);
        } else {
            await handleRegister(formData);
        }
    }

    // Handle login
    async function handleLogin(formData) {
        const username = formData.get('username');
        const password = formData.get('password');

        if (!username || !password) {
            showError('Please fill in all fields');
            return;
        }

        try {
            showLoading(true);

            const response = await fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            });

            const data = await response.json();
            showLoading(false);

            if (data.success) {
                showSuccess('Login successful! Redirecting...');

                // Store user data in localStorage
                localStorage.setItem('user', JSON.stringify(data.user));

                // Redirect after short delay
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                showError(data.message);
            }
        } catch (error) {
            showLoading(false);
            showError('Network error. Please try again.');
            console.error('Login error:', error);
        }
    }

    // Handle registration
    async function handleRegister(formData) {
        const fullName = formData.get('full_name');
        const email = formData.get('email');
        const username = formData.get('username');
        const password = formData.get('password');
        const confirmPassword = formData.get('confirm_password');

        // Client-side validation
        if (!fullName || !email || !username || !password || !confirmPassword) {
            showError('Please fill in all fields');
            return;
        }

        if (password.length < 6) {
            showError('Password must be at least 6 characters long');
            return;
        }

        if (password !== confirmPassword) {
            showError('Passwords do not match');
            return;
        }

        if (username.length < 3) {
            showError('Username must be at least 3 characters long');
            return;
        }

        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            showError('Username can only contain letters, numbers, and underscores');
            return;
        }

        try {
            showLoading(true);

            const response = await fetch('register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    full_name: fullName,
                    email: email,
                    username: username,
                    password: password,
                    confirm_password: confirmPassword
                })
            });

            const data = await response.json();
            showLoading(false);

            if (data.success) {
                showSuccess('Account created successfully! Please login.');
                setTimeout(() => {
                    showLoginForm();
                }, 2000);
            } else {
                showError(data.message);
            }
        } catch (error) {
            showLoading(false);
            showError('Network error. Please try again.');
            console.error('Registration error:', error);
        }
    }

    // Toggle password visibility
    window.togglePassword = function(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    };

    // Show error message
    function showError(message) {
        errorMsg.textContent = message;
        errorMsg.style.display = 'block';
        successMsg.style.display = 'none';
    }

    // Show success message
    function showSuccess(message) {
        successMsg.textContent = message;
        successMsg.style.display = 'block';
        errorMsg.style.display = 'none';
    }

    // Hide messages
    function hideMessages() {
        errorMsg.style.display = 'none';
        successMsg.style.display = 'none';
    }

    // Show loading state
    function showLoading(show) {
        const submitBtn = authForm.querySelector('button[type="submit"]');
        if (show) {
            submitBtn.innerHTML = '<span class="loading"></span> Processing...';
            submitBtn.disabled = true;
        } else {
            const isLogin = loginForm.classList.contains('active');
            if (isLogin) {
                submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
            } else {
                submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> Create Account';
            }
            submitBtn.disabled = false;
        }
    }

    // Real-time validation
    const inputs = authForm.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
    });

    function validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;

        switch (fieldName) {
            case 'username':
                if (value && value.length < 3) {
                    showFieldError(field, 'Username must be at least 3 characters');
                } else if (value && !/^[a-zA-Z0-9_]+$/.test(value)) {
                    showFieldError(field, 'Username can only contain letters, numbers, and underscores');
                } else {
                    clearFieldError(field);
                }
                break;

            case 'email':
                if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    showFieldError(field, 'Please enter a valid email address');
                } else {
                    clearFieldError(field);
                }
                break;

            case 'password':
                if (value && value.length < 6) {
                    showFieldError(field, 'Password must be at least 6 characters');
                } else {
                    clearFieldError(field);
                }
                break;

            case 'confirm_password':
                const password = document.getElementById('regPassword').value;
                if (value && value !== password) {
                    showFieldError(field, 'Passwords do not match');
                } else {
                    clearFieldError(field);
                }
                break;
        }
    }

    function showFieldError(field, message) {
        clearFieldError(field);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.color = '#c33';
        errorDiv.style.fontSize = '0.8rem';
        errorDiv.style.marginTop = '5px';
        field.parentNode.appendChild(errorDiv);
        field.style.borderColor = '#c33';
    }

    function clearFieldError(field) {
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        field.style.borderColor = '#e1e5e9';
    }
});