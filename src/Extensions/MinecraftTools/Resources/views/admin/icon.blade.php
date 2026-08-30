@extends('admin.layouts.default')

@section('title', 'Server Icon - Minecraft Tools')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Server Icon</h1>
                <div>
                    <button type="button" class="btn btn-primary me-2" id="uploadIconBtn">
                        <i class="fas fa-upload"></i> Upload Icon
                    </button>
                    <button type="button" class="btn btn-outline-primary me-2" id="generateIconBtn">
                        <i class="fas fa-magic"></i> Generate Icon
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="refreshIconBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Current Icon</h5>
                        </div>
                        <div class="card-body text-center">
                            <div id="currentIconPreview" class="mb-3">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                            <div id="currentIconInfo" class="text-muted"></div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-danger me-2" id="deleteIconBtn">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="historyIconBtn">
                                    <i class="fas fa-history"></i> History
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" id="uploadIconBtn2">
                                    <i class="fas fa-upload me-2"></i> Upload New Icon
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="generateIconBtn2">
                                    <i class="fas fa-magic me-2"></i> Generate Custom Icon
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="templateIconBtn">
                                    <i class="fas fa-palette me-2"></i> Use Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Icon Templates</h5>
                        </div>
                        <div class="card-body">
                            <div class="row" id="templatesGrid">
                                <div class="col-12 text-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Icon Modal -->
<div class="modal fade" id="uploadIconModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Server Icon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadIconForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Icon Image</label>
                        <input type="file" class="form-control" name="image" accept="image/png,image/jpeg" required>
                        <div class="form-text">Must be 64x64 pixels, PNG or JPEG, max 1MB</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Preview</label>
                        <div class="text-center">
                            <img id="uploadPreview" src="" alt="Preview" style="max-width: 64px; max-height: 64px; border: 1px solid #ddd; display: none;">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmUploadIcon" disabled>Upload</button>
            </div>
        </div>
    </div>
</div>

<!-- Generate Icon Modal -->
<div class="modal fade" id="generateIconModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Server Icon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="generateIconForm">
                    <div class="mb-3">
                        <label class="form-label">Text</label>
                        <input type="text" class="form-control" name="text" id="generateText" value="MC" maxlength="20">
                        <div class="form-text">Max 20 characters (2-3 recommended for best visibility)</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Background Color</label>
                                <input type="color" class="form-control form-control-color" name="background" id="generateBackground" value="#2E86C1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Text Color</label>
                                <input type="color" class="form-control form-control-color" name="font_color" id="generateFontColor" value="#FFFFFF">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Font Size</label>
                        <input type="range" class="form-range" name="font_size" id="generateFontSize" min="10" max="30" value="24">
                        <div class="d-flex justify-content-between text-muted small">
                            <span>10</span>
                            <span id="fontSizeValue">24</span>
                            <span>30</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Preview</label>
                        <div class="text-center">
                            <canvas id="generatePreview" width="128" height="128" style="border: 1px solid #ddd; background: #f8f9fa;"></canvas>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmGenerateIcon">Generate & Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Template Selection Modal -->
<div class="modal fade" id="templateIconModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Icon Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="templateGrid">
                    <div class="col-12 text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Icon History Modal -->
<div class="modal fade" #iconHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Icon History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="historyGrid">
                    <div class="col-12 text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
let currentIcon = null;

document.addEventListener('DOMContentLoaded', function() {
    loadCurrentIcon();
    loadTemplates();
    
    document.getElementById('uploadIconBtn').addEventListener('click', () => showUploadModal());
    document.getElementById('uploadIconBtn2').addEventListener('click', () => showUploadModal());
    document.getElementById('generateIconBtn').addEventListener('click', () => showGenerateModal());
    document.getElementById('generateIconBtn2').addEventListener('click', () => showGenerateModal());
    document.getElementById('templateIconBtn').addEventListener('click', () => showTemplateModal());
    document.getElementById('refreshIconBtn').addEventListener('click', loadCurrentIcon);
    document.getElementById('deleteIconBtn').addEventListener('click', deleteIcon);
    document.getElementById('historyIconBtn').addEventListener('click', showHistoryModal);
    
    document.getElementById('confirmUploadIcon').addEventListener('click', uploadIcon);
    document.getElementById('confirmGenerateIcon').addEventListener('click', generateIcon);
    
    document.querySelector('#uploadIconForm input[name="image"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('uploadPreview');
        const confirmBtn = document.getElementById('confirmUploadIcon');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                
                const img = new Image();
                img.onload = function() {
                    if (this.width === 64 && this.height === 64) {
                        confirmBtn.disabled = false;
                    } else {
                        confirmBtn.disabled = true;
                        showAlert('Image must be exactly 64x64 pixels', 'warning');
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            confirmBtn.disabled = true;
        }
    });
    
    document.getElementById('generateFontSize').addEventListener('input', function() {
        document.getElementById('fontSizeValue').textContent = this.value;
        updateGeneratePreview();
    });
    
    document.getElementById('generateText').addEventListener('input', updateGeneratePreview);
    document.getElementById('generateBackground').addEventListener('input', updateGeneratePreview);
    document.getElementById('generateFontColor').addEventListener('input', updateGeneratePreview);
    
    // Initial preview
    updateGeneratePreview();
});

function loadCurrentIcon() {
    fetch('/api/extensions/minecraft-tools/icon', {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        currentIcon = data.icon;
        renderCurrentIcon(data.icon);
    })
    .catch(console.error);
}

function renderCurrentIcon(iconUrl) {
    const preview = document.getElementById('currentIconPreview');
    const info = document.getElementById('currentIconInfo');
    
    if (iconUrl && iconUrl !== '/placeholder-icon.png') {
        preview.innerHTML = `<img src="${iconUrl}?t=${Date.now()}" alt="Server Icon" style="width: 64px; height: 64px; image-rendering: pixelated; border: 1px solid #ddd;">`;
        info.textContent = 'Custom icon uploaded';
    } else {
        preview.innerHTML = `<div class="text-muted">No custom icon set (using default)</div>`;
        info.textContent = 'Using default Minecraft server icon';
    }
}

function loadTemplates() {
    fetch('/api/extensions/minecraft-tools/icon/templates', {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        renderTemplates(data.templates, 'templatesGrid');
        renderTemplates(data.templates, 'templateGrid');
    })
    .catch(console.error);
}

function renderTemplates(templates, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    
    templates.forEach(template => {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-4 col-lg-3 col-xl-2';
        col.innerHTML = `
            <div class="card template-card h-100" data-template="${template.id}">
                <img src="${template.preview}?t=${Date.now()}" class="card-img-top" alt="${template.name}" style="height: 80px; object-fit: contain; background: #f8f9fa;">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-0 small">${template.name}</h6>
                </div>
            </div>
        `;
        col.querySelector('.template-card').addEventListener('click', function() {
            applyTemplate(template.id);
        });
        container.appendChild(col);
    });
}

function showUploadModal() {
    document.getElementById('uploadIconForm').reset();
    document.getElementById('uploadPreview').style.display = 'none';
    document.getElementById('confirmUploadIcon').disabled = true;
    new bootstrap.Modal(document.getElementById('uploadIconModal')).show();
}

function showGenerateModal() {
    document.getElementById('generateIconForm').reset();
    updateGeneratePreview();
    new bootstrap.Modal(document.getElementById('generateIconModal')).show();
}

function showTemplateModal() {
    new bootstrap.Modal(document.getElementById('templateIconModal')).show();
}

function showHistoryModal() {
    loadIconHistory();
    new bootstrap.Modal(document.getElementById('iconHistoryModal')).show();
}

function uploadIcon() {
    const form = document.getElementById('uploadIconForm');
    const formData = new FormData(form);
    const btn = document.getElementById('confirmUploadIcon');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    
    fetch('/api/extensions/minecraft-tools/icon', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
        },
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'uploaded') {
            bootstrap.Modal.getInstance(document.getElementById('uploadIconModal')).hide();
            loadCurrentIcon();
            showAlert('Icon uploaded successfully', 'success');
        }
    })
    .catch(console.error)
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Upload';
    });
}

function generateIcon() {
    const form = document.getElementById('generateIconForm');
    const formData = new FormData(form);
    const btn = document.getElementById('confirmGenerateIcon');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    
    fetch('/api/extensions/minecraft-tools/icon/generate', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(formData)),
    })
    .then(response => response.json())
    .then(data => {
        if (data.icon) {
            bootstrap.Modal.getInstance(document.getElementById('generateIconModal')).hide();
            loadCurrentIcon();
            showAlert('Icon generated and applied', 'success');
        }
    })
    .catch(console.error)
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Generate & Apply';
    });
}

function updateGeneratePreview() {
    const canvas = document.getElementById('generatePreview');
    const ctx = canvas.getContext('2d');
    
    const text = document.getElementById('generateText').value || 'MC';
    const background = document.getElementById('generateBackground').value;
    const fontColor = document.getElementById('generateFontColor').value;
    const fontSize = parseInt(document.getElementById('generateFontSize').value);
    
    // Clear canvas
    ctx.fillStyle = background;
    ctx.fillRect(0, 0, 128, 128);
    
    // Draw text
    ctx.fillStyle = fontColor;
    ctx.font = `bold ${fontSize}px Arial`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(text, 64, 64);
}

function applyTemplate(templateId) {
    fetch(`/api/extensions/minecraft-tools/icon/templates/${templateId}/apply`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'applied') {
            bootstrap.Modal.getInstance(document.getElementById('templateIconModal')).hide();
            loadCurrentIcon();
            showAlert('Template applied', 'success');
        }
    })
    .catch(console.error);
}

function deleteIcon() {
    if (!confirm('Delete the current server icon? This will revert to the default icon.')) return;
    
    fetch('/api/extensions/minecraft-tools/icon', {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'deleted') {
            loadCurrentIcon();
            showAlert('Icon deleted', 'success');
        }
    })
    .catch(console.error);
}

function loadIconHistory() {
    fetch('/api/extensions/minecraft-tools/icon/history', {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const grid = document.getElementById('historyGrid');
        grid.innerHTML = '';
        
        if (data.history && data.history.length > 0) {
            data.history.forEach(item => {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-4 col-lg-3';
                col.innerHTML = `
                    <div class="card h-100">
                        <img src="${item.url}?t=${Date.now()}" class="card-img-top" alt="Icon" style="height: 80px; object-fit: contain;">
                        <div class="card-body p-2">
                            <small class="text-muted">${new Date(item.created_at).toLocaleString()}</small>
                            <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="restoreIcon('${item.id}')">
                                <i class="fas fa-undo"></i> Restore
                            </button>
                        </div>
                    </div>
                `;
                grid.appendChild(col);
            });
        } else {
            grid.innerHTML = '<div class="col-12 text-center text-muted">No icon history</div>';
        }
    })
    .catch(console.error);
}

function restoreIcon(id) {
    if (!confirm('Restore this icon? Current icon will be replaced.')) return;
    
    fetch(`/api/extensions/minecraft-tools/icon/history/${id}/restore`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'restored') {
            bootstrap.Modal.getInstance(document.getElementById('iconHistoryModal')).hide();
            loadCurrentIcon();
            showAlert('Icon restored', 'success');
        }
    })
    .catch(console.error);
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alert.style.zIndex = '9999';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}
</script>
@endsection