// Check if logged in for admin features
function isAdmin() {
    return localStorage.getItem('admin-login') === 'true';
}

// Get campaign ID from URL
function getCampaignIdFromUrl() {
    const params = new URLSearchParams(window.location.search);
    return params.get('id');
}

// Extract YouTube video ID from URL
function getYouTubeVideoId(url) {
    const regex = /(?:youtube.com\/watch\?v=|youtu.be\/)([a-zA-Z0-9_-]{11})/;
    const match = url.match(regex);
    return match ? match[1] : null;
}

// Load and display campaign detail
function loadCampaignDetail() {
    const campaignId = getCampaignIdFromUrl();
    const campaign = getCampaignById(campaignId);
    const detailContent = document.getElementById('campaign-detail');

    if (!campaign || !detailContent) return;

    const videoId = getYouTubeVideoId(campaign.video);
    const videoEmbed = videoId ? `https://www.youtube.com/embed/${videoId}` : campaign.video;
    const shareUrl = encodeURIComponent(window.location.href);
    const shareText = encodeURIComponent(`Lihat kampanye: ${campaign.title}`);

    detailContent.innerHTML = `
        <div class="detail-header">
            <h1>${campaign.title}</h1>
            <div class="detail-meta">📅 ${new Date(campaign.date).toLocaleDateString('id-ID')}</div>
        </div>

        <div class="detail-description">
            <p>${campaign.description}</p>
        </div>

        <div class="detail-flyer">
            <h3>Flyer Campaign</h3>
            <img src="${campaign.flyer}" alt="${campaign.title}" style="max-width: 100%; border-radius: 10px;">
        </div>

        <div class="detail-video">
            <h3>Video Campaign</h3>
            <iframe width="100%" height="500" src="${videoEmbed}" frameborder="0" allowfullscreen></iframe>
        </div>

        <div class="share-section">
            <h3>Bagikan Campaign</h3>
            <div class="share-buttons">
                <button class="share-btn share-whatsapp" onclick="shareToWhatsApp('${shareText}')">📱 WhatsApp</button>
                <button class="share-btn share-facebook" onclick="shareToFacebook('${shareUrl}')">📘 Facebook</button>
                <button class="share-btn share-twitter" onclick="shareToTwitter('${shareText}', '${shareUrl}')">𝕏 Twitter</button>
                <button class="share-btn share-copy" onclick="copyToClipboard('${shareUrl}')">📋 Copy Link</button>
            </div>
        </div>

        ${isAdmin() ? `
            <div class="admin-actions" style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #ddd;">
                <h3>Admin Actions</h3>
                <button class="btn-edit" onclick="editCampaign(${campaign.id})">✏️ Edit Campaign</button>
                <button class="btn-delete" onclick="deleteCampaignDetail(${campaign.id})">🗑️ Delete Campaign</button>
            </div>
        ` : ''}

        <div style="margin-top: 2rem;">
            <a href="index.html" class="btn-primary" style="text-decoration: none; display: inline-block;">← Kembali ke Beranda</a>
        </div>
    `;
}

// Share to WhatsApp
function shareToWhatsApp(text) {
    const url = `https://wa.me/?text=${text}%20${encodeURIComponent(window.location.href)}`;
    window.open(url, '_blank');
}

// Share to Facebook
function shareToFacebook(url) {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
}

// Share to Twitter
function shareToTwitter(text, url) {
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
}

// Copy link to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Link berhasil disalin!');
    });
}

// Edit campaign (admin)
function editCampaign(id) {
    const campaign = getCampaignById(id);
    if (!campaign) return;

    const newTitle = prompt('Judul baru:', campaign.title);
    if (newTitle === null) return;

    const newDescription = prompt('Deskripsi baru:', campaign.description);
    if (newDescription === null) return;

    updateCampaign(id, { title: newTitle, description: newDescription });
    alert('Campaign berhasil diperbarui!');
    location.reload();
}

// Delete campaign (admin)
function deleteCampaignDetail(id) {
    if (confirm('Apakah Anda yakin ingin menghapus campaign ini?')) {
        deleteCampaign(id);
        alert('Campaign berhasil dihapus!');
        window.location.href = 'index.html';
    }
}

// Load detail on page load
window.addEventListener('load', loadCampaignDetail);