// Sample campaigns data
const campaigns = [
    {
        id: 1,
        title: "Safety First - Penggunaan Helm",
        description: "Kampanye penggunaan helm yang benar untuk mencegah kecelakaan kepala. Helm harus digunakan di semua kesempatan berkendara.",
        flyer: "https://via.placeholder.com/400x500?text=Helm+Safety",
        video: "https://www.youtube.com/embed/dQw4w9WgXcQ",
        date: "2026-06-01"
    },
    {
        id: 2,
        title: "Keselamatan Kerja di Pabrik",
        description: "Program keselamatan kerja untuk pekerja industri. Penting untuk mematuhi semua protokol keselamatan di tempat kerja.",
        flyer: "https://via.placeholder.com/400x500?text=Keselamatan+Kerja",
        video: "https://www.youtube.com/embed/dQw4w9WgXcQ",
        date: "2026-05-28"
    },
    {
        id: 3,
        title: "Jaminan Kesehatan Masyarakat",
        description: "Kampanye kesehatan masyarakat untuk meningkatkan kesadaran tentang pentingnya jaminan kesehatan.",
        flyer: "https://via.placeholder.com/400x500?text=Jaminan+Kesehatan",
        video: "https://www.youtube.com/embed/dQw4w9WgXcQ",
        date: "2026-05-25"
    }
];

// Load campaigns from localStorage or use default
function getCampaigns() {
    const stored = localStorage.getItem('campaigns');
    return stored ? JSON.parse(stored) : campaigns;
}

// Save campaigns to localStorage
function saveCampaigns(data) {
    localStorage.setItem('campaigns', JSON.stringify(data));
}

// Add new campaign
function addCampaign(campaign) {
    const data = getCampaigns();
    campaign.id = data.length > 0 ? Math.max(...data.map(c => c.id)) + 1 : 1;
    data.push(campaign);
    saveCampaigns(data);
    return campaign;
}

// Update campaign
function updateCampaign(id, updatedData) {
    const data = getCampaigns();
    const index = data.findIndex(c => c.id === id);
    if (index !== -1) {
        data[index] = { ...data[index], ...updatedData };
        saveCampaigns(data);
        return data[index];
    }
    return null;
}

// Delete campaign
function deleteCampaign(id) {
    const data = getCampaigns();
    const filtered = data.filter(c => c.id !== id);
    saveCampaigns(filtered);
}

// Get single campaign by ID
function getCampaignById(id) {
    const data = getCampaigns();
    return data.find(c => c.id === parseInt(id));
}

// Initialize campaigns on first load
if (!localStorage.getItem('campaigns')) {
    saveCampaigns(campaigns);
}