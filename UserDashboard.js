function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });

    // Remove active class from nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });

    // Show selected section
    document.getElementById(sectionId).classList.add('active');

    // Add active class to clicked nav item
    event.target.closest('.nav-item').classList.add('active');
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

function changePassword() {
    const newPass = document.getElementById('newPassword').value;
    const confirmPass = document.getElementById('confirmPassword').value;

    if (!newPass || !confirmPass) {
        alert('Please fill in both password fields');
        return;
    }

    if (newPass !== confirmPass) {
        alert('Passwords do not match');
        return;
    }

    if (newPass.length < 6) {
        alert('Password must be at least 6 characters long');
        return;
    }

    alert('Password change feature coming soon!');
}