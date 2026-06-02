// Check admin login
function checkAdminLogin() {
    if (localStorage.getItem('admin-login') !== 'true') {
        window.location.href = 'admin-login.html';
    }
}

// Logout
function logout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        localStorage.removeItem('admin-login');
        window.location.href = 'admin-login.html';
    }
}

// Show section
function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.admin-section').forEach(section => {
        section.classList.remove('active');
    });

    // Show selected section
    document.getElementById(sectionId).classList.add('active');

    // Update menu active state
    document.querySelectorAll('.admin-menu a').forEach(link => {
        link.classList.remove('active');
    });
    event.target.classList.add('active');

    // Load campaigns list if showing campaigns section
    if (sectionId === 'campaigns-list') {
        loadAdminCampaigns();
    }
}

// Load campaigns for admin
function loadAdminCampaigns() {
    const campaignsList = document.getElementById('admin-campaigns-list');
    if (!campaignsList) return;

    const campaigns = getCampaigns();

    if (campaigns.length === 0) {
        campaignsList.innerHTML = '<p>Tidak ada campaign. <a href="#" onclick="showSection(\'add-campaign\'); return false;">Buat campaign baru</a></p>';
        return;
    }

    campaignsList.innerHTML = `
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                ${campaigns.map((campaign, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${campaign.title}</td>
                        <td>${new Date(campaign.date).toLocaleDateString('id-ID')}</td>
                        <td>
                            <button class="btn-edit" onclick="editAdminCampaign(${campaign.id})">Edit</button>
                            <button class="btn-delete" onclick="deleteAdminCampaign(${campaign.id})">Hapus</button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

// Edit campaign in admin
function editAdminCampaign(id) {
    const campaign = getCampaignById(id);
    if (!campaign) return;

    const newTitle = prompt('Judul:', campaign.title);
    if (newTitle === null) return;

    const newDescription = prompt('Deskripsi:', campaign.description);
    if (newDescription === null) return;

    const newFlyer = prompt('URL Flyer:', campaign.flyer);
    if (newFlyer === null) return;

    const newVideo = prompt('URL Video:', campaign.video);
    if (newVideo === null) return;

    updateCampaign(id, {
        title: newTitle,
        description: newDescription,
        flyer: newFlyer,
        video: newVideo
    });

    alert('Campaign berhasil diperbarui!');
    loadAdminCampaigns();
}

// Delete campaign in admin
function deleteAdminCampaign(id) {
    if (confirm('Apakah Anda yakin ingin menghapus campaign ini?')) {
        deleteCampaign(id);
        alert('Campaign berhasil dihapus!');
        loadAdminCampaigns();
    }
}

// Add new campaign form submission
document.addEventListener('DOMContentLoaded', function() {
    checkAdminLogin();

    const form = document.getElementById('add-campaign-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const campaign = {
                title: document.getElementById('title').value,
                description: document.getElementById('description').value,
                flyer: document.getElementById('flyer').value,
                video: document.getElementById('video').value,
                date: document.getElementById('date').value
            };

            addCampaign(campaign);
            alert('Campaign berhasil ditambahkan!');
            form.reset();
            document.getElementById('title').focus();
        });
    }

    // Load campaigns on page load
    loadAdminCampaigns();
});