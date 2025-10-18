// Admin Dashboard JavaScript
class AdminDashboard {
    constructor() {
        this.user = null;
        this.currentPage = 'overview';
        this.currentEditId = null;

        this.init();
    }

    async init() {
        // Check authentication
        this.user = this.checkAuth();
        if (!this.user) return;

        // Display user info
        this.displayUserInfo();

        // Setup navigation
        this.setupNavigation();

        // Setup modals
        this.setupModals();

        // Setup forms
        this.setupForms();

        // Load initial data
        await this.loadOverview();
    }

    checkAuth() {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (!user.id || user.role !== 'admin') {
            window.location.href = 'index.html';
            return null;
        }
        return user;
    }

    displayUserInfo() {
        document.getElementById('userName').textContent = this.user.full_name;
        document.getElementById('userAvatar').textContent = this.user.full_name.charAt(0).toUpperCase();
    }

    setupNavigation() {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.dataset.page;
                this.showPage(page);
            });
        });

        // Setup logout
        document.getElementById('logoutBtn').addEventListener('click', () => {
            if (confirm('Are you sure you want to logout?')) {
                localStorage.removeItem('user');
                window.location.href = 'index.html';
            }
        });
    }

    showPage(page) {
        // Update navigation
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));

        document.querySelector(`[data-page="${page}"]`).classList.add('active');
        document.getElementById(page).classList.add('active');

        // Update page title
        const titles = {
            overview: 'Dashboard Overview',
            faculties: 'Manage Faculties',
            courses: 'Manage Courses',
            units: 'Manage Units',
            papers: 'Manage Papers',
            users: 'Manage Users',
            uploads: 'Upload Papers'
        };
        const subtitles = {
            overview: 'Manage the university past papers portal',
            faculties: 'Add, edit, and manage faculties',
            courses: 'Add, edit, and manage courses',
            units: 'Add, edit, and manage course units',
            papers: 'View and manage all papers',
            users: 'Manage user accounts',
            uploads: 'Upload new past papers to the system'
        };

        document.getElementById('pageTitle').textContent = titles[page];
        document.getElementById('pageSubtitle').textContent = subtitles[page];

        // Load page data
        this.currentPage = page;
        switch (page) {
            case 'overview':
                this.loadOverview();
                break;
            case 'faculties':
                this.loadFaculties();
                break;
            case 'courses':
                this.loadCourses();
                break;
            case 'units':
                this.loadUnits();
                break;
            case 'papers':
                this.loadPapers();
                break;
            case 'users':
                this.loadUsers();
                break;
            case 'uploads':
                this.loadUploadForm();
                break;
        }
    }

    setupModals() {
        // Faculty modal
        document.getElementById('addFacultyBtn').addEventListener('click', () => {
            this.openFacultyModal();
        });

        document.getElementById('facultyModalClose').addEventListener('click', () => {
            this.closeModal('facultyModal');
        });

        document.getElementById('facultyCancel').addEventListener('click', () => {
            this.closeModal('facultyModal');
        });

        // Course modal
        document.getElementById('addCourseBtn').addEventListener('click', () => {
            this.openCourseModal();
        });

        document.getElementById('courseModalClose').addEventListener('click', () => {
            this.closeModal('courseModal');
        });

        document.getElementById('courseCancel').addEventListener('click', () => {
            this.closeModal('courseModal');
        });

        // Unit modal
        document.getElementById('addUnitBtn').addEventListener('click', () => {
            this.openUnitModal();
        });

        document.getElementById('unitModalClose').addEventListener('click', () => {
            this.closeModal('unitModal');
        });

        document.getElementById('unitCancel').addEventListener('click', () => {
            this.closeModal('unitModal');
        });

        // Close modals when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal.id);
                }
            });
        });
    }

    setupForms() {
        // Faculty form
        document.getElementById('facultyForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveFaculty();
        });

        // Course form
        document.getElementById('courseForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveCourse();
        });

        // Unit form
        document.getElementById('unitForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveUnit();
        });

        // Upload form
        document.getElementById('uploadForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.uploadPaper();
        });
    }

    // Overview methods
    async loadOverview() {
        try {
            const response = await fetch('api/get-stats.php');
            const data = await response.json();

            if (data.success) {
                document.getElementById('totalFaculties').textContent = data.stats.faculties || 0;
                document.getElementById('totalCourses').textContent = data.stats.courses || 0;
                document.getElementById('totalUnits').textContent = data.stats.units || 0;
                document.getElementById('totalPapers').textContent = data.stats.papers || 0;
                document.getElementById('totalUsers').textContent = data.stats.users || 0;
                document.getElementById('totalDownloads').textContent = data.stats.downloads || 0;
            }
        } catch (error) {
            console.error('Error loading overview:', error);
        }
    }

    // Faculty methods
    async loadFaculties() {
        const container = document.getElementById('facultiesTable');
        container.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading faculties...</p></div>';

        try {
            const response = await fetch('api/get-faculties.php');
            const data = await response.json();

            if (data.success) {
                if (data.faculties.length === 0) {
                    container.innerHTML = '<div class="loading-state"><i class="fas fa-university"></i><p>No faculties found</p></div>';
                    return;
                }

                container.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.faculties.map(faculty => `
                                <tr>
                                    <td>${this.escapeHtml(faculty.name)}</td>
                                    <td>${this.escapeHtml(faculty.code)}</td>
                                    <td>${this.escapeHtml(faculty.description || '')}</td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="adminDashboard.editFaculty(${faculty.id})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="adminDashboard.deleteFaculty(${faculty.id})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }
        } catch (error) {
            container.innerHTML = '<div class="loading-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading faculties</p></div>';
            console.error('Error:', error);
        }
    }

    openFacultyModal(faculty = null) {
        this.currentEditId = faculty ? faculty.id : null;
        document.getElementById('facultyModalTitle').textContent = faculty ? 'Edit Faculty' : 'Add Faculty';

        if (faculty) {
            document.getElementById('facultyName').value = faculty.name;
            document.getElementById('facultyCode').value = faculty.code;
            document.getElementById('facultyDescription').value = faculty.description || '';
        } else {
            document.getElementById('facultyForm').reset();
        }

        this.showModal('facultyModal');
    }

    async saveFaculty() {
        const formData = new FormData(document.getElementById('facultyForm'));
        const editId = document.getElementById('facultyForm').dataset.editId;
        const data = {
            name: formData.get('name'),
            code: formData.get('code'),
            description: formData.get('description')
        };

        if (editId) {
            data.id = editId;
        }

        try {
            const url = editId ? 'api/edit-faculty.php' : 'api/add-faculty.php';

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('facultyModal').style.display = 'none';
                this.loadFaculties();
                alert('Faculty saved successfully!');
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Error saving faculty');
            console.error('Error:', error);
        }
    }

    editFaculty(id) {
        // Get faculty data and populate modal
        fetch('api/get-faculties.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const faculty = data.faculties.find(f => f.id == id);
                    if (faculty) {
                        document.getElementById('facultyName').value = faculty.name;
                        document.getElementById('facultyCode').value = faculty.code;
                        document.getElementById('facultyDescription').value = faculty.description || '';
                        document.getElementById('facultyModalTitle').textContent = 'Edit Faculty';
                        document.getElementById('facultyForm').dataset.editId = id;
                        document.getElementById('facultyModal').style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading faculty data');
            });
    }

    deleteFaculty(id) {
        if (confirm('Are you sure you want to delete this faculty?')) {
            fetch('api/delete-faculty.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Faculty deleted successfully!');
                        this.loadFaculties();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting faculty');
                });
        }
    }

    // Course methods
    async loadCourses() {
        const container = document.getElementById('coursesTable');
        container.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading courses...</p></div>';

        try {
            const response = await fetch('api/get-courses.php');
            const data = await response.json();

            if (data.success) {
                if (data.courses.length === 0) {
                    container.innerHTML = '<div class="loading-state"><i class="fas fa-graduation-cap"></i><p>No courses found</p></div>';
                    return;
                }

                container.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Faculty</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.courses.map(course => `
                                <tr>
                                    <td>${this.escapeHtml(course.name)}</td>
                                    <td>${this.escapeHtml(course.code)}</td>
                                    <td>${this.escapeHtml(course.faculty_name || 'N/A')}</td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="adminDashboard.editCourse(${course.id})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="adminDashboard.deleteCourse(${course.id})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }
        } catch (error) {
            container.innerHTML = '<div class="loading-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading courses</p></div>';
            console.error('Error:', error);
        }
    }

    openCourseModal(course = null) {
        this.currentEditId = course ? course.id : null;
        document.getElementById('courseModalTitle').textContent = course ? 'Edit Course' : 'Add Course';

        if (course) {
            document.getElementById('courseName').value = course.name;
            document.getElementById('courseCode').value = course.code;
            document.getElementById('courseDescription').value = course.description || '';
            document.getElementById('courseFaculty').value = course.faculty_id;
        } else {
            document.getElementById('courseForm').reset();
        }

        this.showModal('courseModal');
    }

    async saveCourse() {
        const formData = new FormData(document.getElementById('courseForm'));
        const editId = document.getElementById('courseForm').dataset.editId;
        const data = {
            faculty_id: formData.get('faculty_id'),
            name: formData.get('name'),
            code: formData.get('code'),
            description: formData.get('description')
        };

        if (editId) {
            data.id = editId;
        }

        try {
            const url = editId ? 'api/edit-course.php' : 'api/add-course.php';

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('courseModal').style.display = 'none';
                this.loadCourses();
                alert('Course saved successfully!');
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Error saving course');
            console.error('Error:', error);
        }
    }

    editCourse(id) {
        // Get course data and populate modal
        fetch('api/get-courses.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const course = data.courses.find(c => c.id == id);
                    if (course) {
                        document.getElementById('courseName').value = course.name;
                        document.getElementById('courseCode').value = course.code;
                        document.getElementById('courseDescription').value = course.description || '';
                        document.getElementById('courseModalTitle').textContent = 'Edit Course';
                        document.getElementById('courseForm').dataset.editId = id;

                        // Set faculty selection
                        const facultySelect = document.getElementById('courseFaculty');
                        for (let option of facultySelect.options) {
                            if (option.value == course.faculty_id) {
                                option.selected = true;
                                break;
                            }
                        }

                        document.getElementById('courseModal').style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading course data');
            });
    }

    deleteCourse(id) {
        if (confirm('Are you sure you want to delete this course?')) {
            fetch('api/delete-course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Course deleted successfully!');
                        this.loadCourses();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting course');
                });
        }
    }

    // Unit methods
    async loadUnits() {
        const container = document.getElementById('unitsTable');
        container.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading units...</p></div>';

        try {
            const response = await fetch('api/get-units.php');
            const data = await response.json();

            if (data.success) {
                if (data.units.length === 0) {
                    container.innerHTML = '<div class="loading-state"><i class="fas fa-book"></i><p>No units found</p></div>';
                    return;
                }

                container.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Course</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.units.map(unit => `
                                <tr>
                                    <td>${this.escapeHtml(unit.name)}</td>
                                    <td>${this.escapeHtml(unit.code)}</td>
                                    <td>${this.escapeHtml(unit.course_name || 'N/A')}</td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="adminDashboard.editUnit(${unit.id})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="adminDashboard.deleteUnit(${unit.id})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }
        } catch (error) {
            container.innerHTML = '<div class="loading-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading units</p></div>';
            console.error('Error:', error);
        }
    }

    openUnitModal(unit = null) {
        this.currentEditId = unit ? unit.id : null;
        document.getElementById('unitModalTitle').textContent = unit ? 'Edit Unit' : 'Add Unit';

        if (unit) {
            document.getElementById('unitName').value = unit.name;
            document.getElementById('unitCode').value = unit.code;
            document.getElementById('unitDescription').value = unit.description || '';
            document.getElementById('unitCourse').value = unit.course_id;
        } else {
            document.getElementById('unitForm').reset();
        }

        this.showModal('unitModal');
    }

    async saveUnit() {
        const formData = new FormData(document.getElementById('unitForm'));
        const editId = document.getElementById('unitForm').dataset.editId;
        const data = {
            course_id: formData.get('course_id'),
            name: formData.get('name'),
            code: formData.get('code'),
            description: formData.get('description')
        };

        if (editId) {
            data.id = editId;
        }

        try {
            const url = editId ? 'api/edit-unit.php' : 'api/add-unit.php';

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('unitModal').style.display = 'none';
                this.loadUnits();
                alert('Unit saved successfully!');
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Error saving unit');
            console.error('Error:', error);
        }
    }

    editUnit(id) {
        // Get unit data and populate modal
        fetch('api/get-units.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const unit = data.units.find(u => u.id == id);
                    if (unit) {
                        document.getElementById('unitName').value = unit.name;
                        document.getElementById('unitCode').value = unit.code;
                        document.getElementById('unitDescription').value = unit.description || '';
                        document.getElementById('unitModalTitle').textContent = 'Edit Unit';
                        document.getElementById('unitForm').dataset.editId = id;

                        // Set course selection
                        const courseSelect = document.getElementById('unitCourse');
                        for (let option of courseSelect.options) {
                            if (option.value == unit.course_id) {
                                option.selected = true;
                                break;
                            }
                        }

                        document.getElementById('unitModal').style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading unit data');
            });
    }

    deleteUnit(id) {
        if (confirm('Are you sure you want to delete this unit?')) {
            fetch('api/delete-unit.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Unit deleted successfully!');
                        this.loadUnits();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting unit');
                });
        }
    }

    // Paper methods
    async loadPapers() {
        const container = document.getElementById('papersTable');
        container.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading papers...</p></div>';

        try {
            const response = await fetch('api/get-papers.php');
            const data = await response.json();

            if (data.success) {
                if (data.papers.length === 0) {
                    container.innerHTML = '<div class="loading-state"><i class="fas fa-file-pdf"></i><p>No papers found</p></div>';
                    return;
                }

                container.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Unit</th>
                                <th>Year</th>
                                <th>Semester</th>
                                <th>Downloads</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.papers.map(paper => `
                                <tr>
                                    <td>${this.escapeHtml(paper.title)}</td>
                                    <td>${this.escapeHtml(paper.unit_name)}</td>
                                    <td>${paper.year}</td>
                                    <td>${paper.semester}</td>
                                    <td>${paper.downloads}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="adminDashboard.downloadPaper(${paper.id})">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="adminDashboard.deletePaper(${paper.id})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }
        } catch (error) {
            container.innerHTML = '<div class="loading-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading papers</p></div>';
            console.error('Error:', error);
        }
    }

    downloadPaper(id) {
        alert(`Download paper with ID: ${id}`);
    }

    deletePaper(id) {
        if (confirm('Are you sure you want to delete this paper?')) {
            alert(`Delete paper with ID: ${id}`);
        }
    }

    // User methods
    async loadUsers() {
        const container = document.getElementById('usersTable');
        container.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading users...</p></div>';

        try {
            const response = await fetch('api/get-users.php');
            const data = await response.json();

            if (data.success) {
                if (data.users.length === 0) {
                    container.innerHTML = '<div class="loading-state"><i class="fas fa-users"></i><p>No users found</p></div>';
                    return;
                }

                container.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.users.map(user => `
                                <tr>
                                    <td>${this.escapeHtml(user.full_name)}</td>
                                    <td>${this.escapeHtml(user.email)}</td>
                                    <td>${this.escapeHtml(user.username)}</td>
                                    <td><span class="badge ${user.role === 'admin' ? 'badge-danger' : 'badge-primary'}">${user.role}</span></td>
                                    <td><span class="badge ${user.is_active ? 'badge-success' : 'badge-secondary'}">${user.is_active ? 'Active' : 'Inactive'}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="adminDashboard.editUser(${user.id})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm ${user.is_active ? 'btn-warning' : 'btn-success'}" onclick="adminDashboard.toggleUser(${user.id})">
                                            <i class="fas fa-${user.is_active ? 'ban' : 'check'}"></i> ${user.is_active ? 'Deactivate' : 'Activate'}
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }
        } catch (error) {
            container.innerHTML = '<div class="loading-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading users</p></div>';
            console.error('Error:', error);
        }
    }

    editUser(id) {
        alert(`Edit user with ID: ${id}`);
    }

    toggleUser(id) {
        alert(`Toggle user status with ID: ${id}`);
    }

    // Upload methods
    async loadUploadForm() {
        // Load units for upload form
        try {
            const response = await fetch('api/get-units.php');
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById('uploadUnit');
                select.innerHTML = '<option value="">Select Unit</option>';
                data.units.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.course_name ? `${unit.name} (${unit.course_name})` : `${unit.name} (${unit.code})`;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading units for upload:', error);
        }
    }

    async uploadPaper() {
        const formData = new FormData(document.getElementById('uploadForm'));

        // Add the uploaded_by field (admin's user ID)
        formData.append('uploaded_by', this.user.id);

        try {
            const response = await fetch('api/upload-paper.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('uploadForm').reset();
                this.showNotification('Paper uploaded successfully!', 'success');
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error uploading paper', 'error');
            console.error('Error:', error);
        }
    }

    // Modal methods
    showModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }

    closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        this.currentEditId = null;
    }

    // Utility methods
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.adminDashboard = new AdminDashboard();
});