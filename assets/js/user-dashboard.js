// User Dashboard JavaScript
class UserDashboard {
    constructor() {
        this.user = null;
        this.currentPage = 'browse';
        this.filters = {
            faculty: '',
            course: '',
            unit: '',
            year: '',
            semester: ''
        };

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

        // Setup filters
        this.setupFilters();

        // Pre-populate filter dropdowns
        this.populateYearOptions();

        // Load initial data
        await this.loadFaculties();
        await this.loadCourses(); // load all courses initially
        await this.loadUnits(); // load all units initially
        await this.loadPapers();

        // Setup logout
        this.setupLogout();
    }

    checkAuth() {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (!user.id || user.role !== 'user') {
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
        // Mobile navigation toggle
        const mobileNavToggle = document.getElementById('mobileNavToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (mobileNavToggle) {
            mobileNavToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                sidebarOverlay.classList.toggle('active');
                
                // Update toggle icon
                const icon = mobileNavToggle.querySelector('i');
                if (sidebar.classList.contains('open')) {
                    icon.className = 'fas fa-times';
                } else {
                    icon.className = 'fas fa-bars';
                }
            });
        }

        // Close sidebar when overlay is clicked
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('active');
                const icon = mobileNavToggle.querySelector('i');
                icon.className = 'fas fa-bars';
            });
        }

        // Navigation links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Close mobile menu if open
                if (window.innerWidth <= 992) {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('active');
                    const icon = mobileNavToggle.querySelector('i');
                    icon.className = 'fas fa-bars';
                }
                const page = link.dataset.page;
                this.showPage(page);
            });
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
            browse: 'Browse Past Papers',
            downloads: 'My Downloads',
            profile: 'My Profile'
        };
        const subtitles = {
            browse: 'Find and download past papers by faculty, course, or unit',
            downloads: 'Track all your downloaded papers',
            profile: 'Manage your account information'
        };

        document.getElementById('pageTitle').textContent = titles[page];
        document.getElementById('pageSubtitle').textContent = subtitles[page];

        // Load page data
        this.currentPage = page;
        switch (page) {
            case 'browse':
                this.loadPapers();
                break;
            case 'downloads':
                this.loadDownloads();
                break;
            case 'profile':
                this.loadProfile();
                break;
        }
    }

    setupFilters() {
        // Faculty filter
        document.getElementById('facultyFilter').addEventListener('change', async (e) => {
            this.filters.faculty = e.target.value;
            this.filters.course = '';
            this.filters.unit = '';
            document.getElementById('courseFilter').innerHTML = '<option value="">All Courses</option>';
            document.getElementById('unitFilter').innerHTML = '<option value="">All Units</option>';

            if (this.filters.faculty) {
                await this.loadCourses(this.filters.faculty);
            }
            this.loadPapers();
        });

        // Course filter
        document.getElementById('courseFilter').addEventListener('change', async (e) => {
            this.filters.course = e.target.value;
            this.filters.unit = '';
            document.getElementById('unitFilter').innerHTML = '<option value="">All Units</option>';

            if (this.filters.course) {
                await this.loadUnits(this.filters.course);
            }
            this.loadPapers();
        });

        // Unit filter
        document.getElementById('unitFilter').addEventListener('change', (e) => {
            this.filters.unit = e.target.value;
            this.loadPapers();
        });

        // Year filter
        document.getElementById('yearFilter').addEventListener('change', (e) => {
            this.filters.year = e.target.value;
            this.loadPapers();
        });

        // Semester filter
        document.getElementById('semesterFilter').addEventListener('change', (e) => {
            this.filters.semester = e.target.value;
            this.loadPapers();
        });

        // Clear filters
        document.getElementById('clearFilters').addEventListener('click', () => {
            this.clearFilters();
        });
    }

    clearFilters() {
        this.filters = {
            faculty: '',
            course: '',
            unit: '',
            year: '',
            semester: ''
        };

        document.getElementById('facultyFilter').value = '';
        document.getElementById('courseFilter').innerHTML = '<option value="">All Courses</option>';
        document.getElementById('unitFilter').innerHTML = '<option value="">All Units</option>';
        document.getElementById('yearFilter').value = '';
        document.getElementById('semesterFilter').value = '';

        this.loadPapers();
    }

    async loadFaculties() {
        try {
            const response = await fetch('api/get-faculties.php');
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById('facultyFilter');
                data.faculties.forEach(faculty => {
                    const option = document.createElement('option');
                    option.value = faculty.id;
                    option.textContent = faculty.name;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading faculties:', error);
        }
    }

    async loadCourses(facultyId) {
        try {
            const url = (facultyId && facultyId !== '') ? `api/get-courses.php?faculty_id=${facultyId}` : 'api/get-courses.php';
            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById('courseFilter');
                // If loading all courses on init, keep existing "All Courses"
                if (!facultyId) {
                    select.innerHTML = '<option value="">All Courses</option>';
                }
                data.courses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.id;
                    option.textContent = course.name;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading courses:', error);
        }
    }

    async loadUnits(courseId) {
        try {
            const url = (courseId && courseId !== '') ? `api/get-units.php?course_id=${courseId}` : 'api/get-units.php';
            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById('unitFilter');
                // If loading all units on init, keep existing "All Units"
                if (!courseId) {
                    select.innerHTML = '<option value="">All Units</option>';
                }
                data.units.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.course_name ? `${unit.name} (${unit.course_name})` : unit.name;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading units:', error);
        }
    }

    populateYearOptions() {
        const select = document.getElementById('yearFilter');
        // Keep the default "All Years" option
        select.innerHTML = '<option value="">All Years</option>';
        const current = new Date().getFullYear();
        const start = 2015; // adjust as needed
        for (let y = current + 1; y >= start; y--) {
            const opt = document.createElement('option');
            opt.value = String(y);
            opt.textContent = String(y);
            select.appendChild(opt);
        }
    }

    async loadPapers() {
        const grid = document.getElementById('papersGrid');
        grid.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading papers...</p></div>';

        const params = new URLSearchParams();
        Object.keys(this.filters).forEach(key => {
            if (this.filters[key]) {
                params.append(key, this.filters[key]);
            }
        });

        try {
            const response = await fetch(`api/get-papers.php?${params}`);
            const data = await response.json();

            if (data.success) {
                document.getElementById('papersCount').textContent = `${data.papers.length} papers found`;

                if (data.papers.length === 0) {
                    grid.innerHTML = '<div class="loading-state"><i class="fas fa-search"></i><p>No papers found matching your criteria</p></div>';
                    return;
                }

                grid.innerHTML = data.papers.map(paper => this.createPaperCard(paper)).join('');
            } else {
                grid.innerHTML = '<div class="loading-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading papers</p></div>';
            }
        } catch (error) {
            grid.innerHTML = '<div class="loading-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading papers</p></div>';
            console.error('Error:', error);
        }
    }

    createPaperCard(paper) {
        return `
            <div class="paper-card" onclick="userDashboard.showPaperDetails(${paper.id})">
                <div class="paper-title">${this.escapeHtml(paper.title)}</div>
                <div class="paper-details">
                    <i class="fas fa-book"></i>
                    ${this.escapeHtml(paper.unit_name)} (${paper.unit_code})
                </div>
                <div class="paper-details">
                    <i class="fas fa-calendar"></i>
                    ${paper.year} - Semester ${paper.semester}
                </div>
                <div class="paper-details">
                    <i class="fas fa-file-pdf"></i>
                    ${this.formatFileSize(paper.file_size)}
                </div>
                <div class="paper-footer">
                    <span class="download-count">
                        <i class="fas fa-download"></i>
                        ${paper.downloads} downloads
                    </span>
                    <button class="download-btn" onclick="event.stopPropagation(); userDashboard.downloadPaper(${paper.id})">
                        <i class="fas fa-download"></i>
                        Download
                    </button>
                </div>
            </div>
        `;
    }

    async downloadPaper(paperId) {
        try {
            const response = await fetch('api/download-paper.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    paper_id: paperId,
                    user_id: this.user.id
                })
            });

            const data = await response.json();

            if (data.success) {
                // Always use secure streaming endpoint if provided
                const url = data.file_url || data.file_path || `api/serve-file.php?paper_id=${paperId}`;
                // Navigate in the same tab to guarantee session cookies are sent
                window.location.href = url;

                // Refresh papers to show updated download count
                this.loadPapers();
            } else {
                alert(data.message);
            }
        } catch (error) {
            alert('Download failed');
            console.error('Error:', error);
        }
    }

    async loadDownloads() {
        const container = document.getElementById('downloadsTable');
        container.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading download history...</p></div>';

        try {
            const response = await fetch(`api/get-downloads.php?user_id=${this.user.id}`);
            const data = await response.json();

            if (data.success) {
                if (data.downloads.length === 0) {
                    container.innerHTML = '<div class="loading-state"><i class="fas fa-download"></i><p>No downloads yet</p></div>';
                    return;
                }

                container.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Paper</th>
                                <th>Unit</th>
                                <th>Year</th>
                                <th>Downloaded</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.downloads.map(d => `
                                <tr>
                                    <td>${this.escapeHtml(d.title)}</td>
                                    <td>${this.escapeHtml(d.unit_name || 'N/A')}</td>
                                    <td>${d.year}</td>
                                    <td>${new Date(d.downloaded_at).toLocaleDateString()}</td>
                                    <td>
                                        <button class="download-btn" onclick="userDashboard.downloadPaper(${d.paper_id})">
                                            <i class="fas fa-download"></i>
                                            Download Again
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }
        } catch (error) {
            container.innerHTML = '<div class="loading-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading downloads</p></div>';
            console.error('Error:', error);
        }
    }

    async loadProfile() {
        // Display basic profile info
        document.getElementById('profileName').textContent = this.user.full_name;
        document.getElementById('profileEmail').textContent = this.user.email;
        document.getElementById('profileUsername').textContent = this.user.username;
        document.getElementById('profileAvatar').textContent = this.user.full_name.charAt(0).toUpperCase();

        try {
            // Load download statistics
            const response = await fetch(`api/get-downloads.php?user_id=${this.user.id}`);
            const data = await response.json();

            if (data.success) {
                const downloads = data.downloads || [];
                const uniquePapers = new Set(downloads.map(d => d.paper_id)).size;
                const lastDownload = downloads.length > 0 ? new Date(downloads[0].downloaded_at).toLocaleDateString() : 'Never';

                document.getElementById('profileDownloads').textContent = downloads.length;
                document.getElementById('totalDownloads').textContent = downloads.length;
                document.getElementById('uniquePapers').textContent = uniquePapers;
                document.getElementById('lastDownload').textContent = lastDownload;
            }
        } catch (error) {
            console.error('Error loading profile:', error);
        }
    }

    showPaperDetails(paperId) {
        // This would show a modal with paper details
        // For now, we'll just show an alert
        alert(`Paper details for ID: ${paperId}`);
    }

    setupLogout() {
        document.getElementById('logoutBtn').addEventListener('click', () => {
            if (confirm('Are you sure you want to logout?')) {
                localStorage.removeItem('user');
                window.location.href = 'index.html';
            }
        });
    }

    // Utility functions
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    // Responsive utilities
    makeTableResponsive(tableContainer) {
        if (!tableContainer) return;
        
        const table = tableContainer.querySelector('table');
        if (!table) return;
        
        // Add responsive wrapper if not exists
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentElement.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
        
        // Add mobile-friendly attributes
        const headers = table.querySelectorAll('th');
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (headers[index]) {
                    cell.setAttribute('data-label', headers[index].textContent);
                }
            });
        });
    }

    // Handle window resize
    handleResize() {
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mobileNavToggle = document.getElementById('mobileNavToggle');
        
        if (window.innerWidth > 992) {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
            if (mobileNavToggle) {
                const icon = mobileNavToggle.querySelector('i');
                icon.className = 'fas fa-bars';
            }
        }
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.userDashboard = new UserDashboard();
    
    // Handle window resize
    window.addEventListener('resize', () => {
        window.userDashboard.handleResize();
    });
});