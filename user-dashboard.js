// Check authentication
function checkAuth() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (!user.id || user.role !== 'user') {
        window.location.href = 'index.html';
        return null;
    }
    return user;
}

const user = checkAuth();
if (!user) throw new Error('Not authenticated');

// Display user info
document.getElementById('userName').textContent = user.full_name;
document.getElementById('userAvatar').textContent = user.full_name.charAt(0).toUpperCase();

// Navigation
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const page = link.dataset.page;

        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));

        link.classList.add('active');
        document.getElementById(page).classList.add('active');

        const titles = {
            browse: 'Browse Past Papers',
            downloads: 'My Downloads',
            profile: 'My Profile'
        };
        document.getElementById('pageTitle').textContent = titles[page];

        if (page === 'browse') loadPapers();
        if (page === 'downloads') loadDownloads();
        if (page === 'profile') loadProfile();
    });
});

// Logout
document.getElementById('logoutBtn').addEventListener('click', () => {
    if (confirm('Logout?')) {
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    }
});

// Load faculties
async function loadFaculties() {
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

// Load courses when faculty changes
document.getElementById('facultyFilter').addEventListener('change', async(e) => {
    const facultyId = e.target.value;
    const courseSelect = document.getElementById('courseFilter');
    courseSelect.innerHTML = '<option value="">All Courses</option>';

    if (!facultyId) return;

    try {
        const response = await fetch(`api/get-courses.php?faculty_id=${facultyId}`);
        const data = await response.json();

        if (data.success) {
            data.courses.forEach(course => {
                const option = document.createElement('option');
                option.value = course.id;
                option.textContent = course.name;
                courseSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading courses:', error);
    }
});

// Load units when course changes
document.getElementById('courseFilter').addEventListener('change', async(e) => {
    const courseId = e.target.value;
    const unitSelect = document.getElementById('unitFilter');
    unitSelect.innerHTML = '<option value="">All Units</option>';

    if (!courseId) return;

    try {
        const response = await fetch(`api/get-units.php?course_id=${courseId}`);
        const data = await response.json();

        if (data.success) {
            data.units.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.id;
                option.textContent = unit.name;
                unitSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading units:', error);
    }
});

// Load papers
async function loadPapers() {
    const grid = document.getElementById('papersGrid');
    grid.innerHTML = '<div class="loading">Loading papers...</div>';

    const filters = {
        faculty: document.getElementById('facultyFilter').value,
        course: document.getElementById('courseFilter').value,
        unit: document.getElementById('unitFilter').value,
        year: document.getElementById('yearFilter').value
    };

    const params = new URLSearchParams(filters);

    try {
        const response = await fetch(`api/get-papers.php?${params}`);
        const data = await response.json();

        if (data.success) {
            if (data.papers.length === 0) {
                grid.innerHTML = '<div class="loading">No papers found</div>';
                return;
            }

            grid.innerHTML = data.papers.map(paper => `
                <div class="paper-card">
                    <div class="paper-title">${paper.title}</div>
                    <div class="paper-details">📚 ${paper.unit_name}</div>
                    <div class="paper-details">📅 ${paper.year} - Semester ${paper.semester}</div>
                    <div class="paper-details">📁 ${formatFileSize(paper.file_size)}</div>
                    <div class="paper-footer">
                        <span>⬇️ ${paper.downloads} downloads</span>
                        <button class="download-btn" onclick="downloadPaper(${paper.id})">Download</button>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        grid.innerHTML = '<div class="error">Error loading papers</div>';
        console.error('Error:', error);
    }
}

// Download paper
async function downloadPaper(paperId) {
    try {
        const response = await fetch('api/download-paper.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                paper_id: paperId,
                user_id: user.id
            })
        });

        const data = await response.json();

        if (data.success) {
            window.open(data.file_path, '_blank');
            loadPapers(); // Refresh to show updated download count
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Download failed');
        console.error('Error:', error);
    }
}

// Load downloads history
async function loadDownloads() {
    const container = document.getElementById('downloadsTable');
    container.innerHTML = '<div class="loading">Loading...</div>';

    try {
        const response = await fetch(`api/get-downloads.php?user_id=${user.id}`);
        const data = await response.json();

        if (data.success) {
            if (data.downloads.length === 0) {
                container.innerHTML = '<div class="loading">No downloads yet</div>';
                return;
            }

            container.innerHTML = `
                <table style="width:100%; background:white; border-radius:10px;">
                    <thead>
                        <tr style="background:#f5f5f5;">
                            <th style="padding:15px; text-align:left;">Paper</th>
                            <th style="padding:15px; text-align:left;">Date</th>
                            <th style="padding:15px; text-align:left;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.downloads.map(d => `
                            <tr>
                                <td style="padding:15px;">${d.title}</td>
                                <td style="padding:15px;">${new Date(d.downloaded_at).toLocaleDateString()}</td>
                                <td style="padding:15px;">
                                    <button class="download-btn" onclick="window.open('${d.file_path}', '_blank')">Download Again</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }
    } catch (error) {
        container.innerHTML = '<div class="error">Error loading downloads</div>';
    }
}

// Load profile
async function loadProfile() {
    document.getElementById('profileName').textContent = user.full_name;
    document.getElementById('profileEmail').textContent = user.email;
    document.getElementById('profileUsername').textContent = user.username;
    
    try {
        const response = await fetch(`api/get-downloads.php?user_id=${user.id}`);
        const data = await response.json();
        document.getElementById('profileDownloads').textContent = data.downloads?.length || 0;
    } catch (error) {
        document.getElementById('profileDownloads').textContent = '0';
    }
}

// Helper function
function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

// Filter changes
document.getElementById('unitFilter').addEventListener('change', loadPapers);
document.getElementById('yearFilter').addEventListener('change', loadPapers);

// Initialize
loadFaculties();
loadPapers();