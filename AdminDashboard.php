<?php
require_once 'includes/check_auth.php';
require_once 'config/database.php';

requireAdmin();

$user = getCurrentUser();

// Get total users count
$user_count_query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$user_count_result = $conn->query($user_count_query);
$total_users = $user_count_result->fetch_assoc()['total'];

// Get all users
$users_query = "SELECT id, full_name, username, email, role, created_at FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="AdminDashboard.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <p>System Management</p>
                <span class="admin-badge">ADMINISTRATOR</span>
            </div>

            <nav class="nav-menu">
                <a class="nav-item active" onclick="showSection('dashboard')">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a class="nav-item" onclick="showSection('users')">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>Manage Users</span>
                </a>

                <a class="nav-item" onclick="showSection('settings')">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Settings</span>
                </a>
            </nav>

            <button class="logout-btn" onclick="logout()">Logout</button>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
                    <div>
                        <div style="font-weight: 600; color: #333;"><?php echo htmlspecialchars($user['full_name']); ?></div>
                        <div style="font-size: 0.9em; color: #666;">Administrator</div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Section -->
            <div id="dashboard" class="content-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <div class="value"><?php echo $total_users; ?></div>
                    </div>

                    <div class="stat-card">
                        <h3>Total Admins</h3>
                        <div class="value">1</div>
                    </div>

                    <div class="stat-card">
                        <h3>Active Sessions</h3>
                        <div class="value">1</div>
                    </div>

                    <div class="stat-card">
                        <h3>System Status</h3>
                        <div class="value" style="color: #27ae60; font-size: 1.5em;">Online</div>
                    </div>
                </div>

                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h2>Welcome, Administrator!</h2>
                    <p style="color: #666; margin-top: 10px; line-height: 1.6;">
                        You have full access to manage users, view system statistics, and configure settings. 
                        Use the navigation menu to access different management sections.
                    </p>
                </div>
            </div>

            <!-- Users Management Section -->
            <div id="users" class="content-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; border: none; padding: 0;">User Management</h2>
                    <button class="btn-primary" onclick="alert('Add user feature coming soon!')">+ Add New User</button>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $row['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <button class="btn-action btn-edit" onclick="editUser(<?php echo $row['id']; ?>)">Edit</button>
                                    <?php if($row['id'] !== $user['id']): ?>
                                    <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['username']); ?>')">Delete</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Settings Section -->
            <div id="settings" class="content-section">
                <h2>System Settings</h2>
                <div style="max-width: 600px;">
                    <div class="form-group">
                        <label>System Name</label>
                        <input type="text" value="User Management System">
                    </div>

                    <div class="form-group">
                        <label>Admin Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Enable User Registration</label>
                        <select>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>

                    <button class="btn-primary" onclick="alert('Settings update feature coming soon!')">Save Settings</button>
                </div>
            </div>
        </main>
    </div>

    <script src="AdminDashboard.js"></script>
</body>
</html>