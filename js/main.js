// Mobile menu toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');

if (hamburger) {
    hamburger.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });
}

// Load and display campaigns
function loadCampaigns() {
    const campaignsList = document.getElementById('campaigns-list');
    if (!campaignsList) return;

    const campaigns = getCampaigns();
    
    campaignsList.innerHTML = campaigns.map(campaign => `
        <div class="campaign-card" onclick="viewCampaign(${campaign.id})">
            <img src="${campaign.flyer}" alt="${campaign.title}">
            <div class="campaign-card-content">
                <h3>${campaign.title}</h3>
                <p>${campaign.description.substring(0, 100)}...</p>
                <div class="campaign-date">📅 ${new Date(campaign.date).toLocaleDateString('id-ID')}</div>
                <button class="btn-view" onclick="event.stopPropagation(); viewCampaign(${campaign.id})">Lihat Detail</button>
            </div>
        </div>
    `).join('');
}

// Navigate to campaign detail
function viewCampaign(id) {
    window.location.href = `detail.html?id=${id}`;
}

// Load campaigns on page load
window.addEventListener('load', loadCampaigns);