<?php
/**
 * Admin Resources View. - Modern Admin UI
 * 
 * @package Shena\Views\Admin
 */
$resources = $resources ?? [];
$stats = $stats ?? ['total' => 0, 'marketing' => 0, 'training' => 0, 'forms' => 0, 'policy' => 0, 'other' => 0];
$category = $category ?? 'all';
$csrf_token = $csrf_token ?? '';
?>
<?php include_once __DIR__ . '/../layouts/admin-header.php'; ?>

<style>
/* Page Header */
.page-header { margin-bottom: 24px; }
.page-title { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #1F2937; margin: 0 0 4px 0; }
.page-subtitle { font-size: 13px; color: #9CA3AF; margin: 0; }

/* Stats Grid */
.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: white; border-radius: 12px; padding: 20px; border: 1px solid #E5E7EB; transition: all 0.2s; }
.stat-card:hover { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); transform: translateY(-2px); }
.stat-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.stat-icon.purple { background: #EDE9FE; color: #7F3D9E; }
.stat-icon.green { background: #D1FAE5; color: #059669; }
.stat-icon.blue { background: #DBEAFE; color: #2563EB; }
.stat-icon.orange { background: #FEF3C7; color: #D97706; }
.stat-icon.gray { background: #F3F4F6; color: #6B7280; }
.stat-label { font-size: 11px; color: #9CA3AF; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-value { font-size: 28px; font-weight: 700; color: #1F2937; }

/* Tabs Container */
.tabs-container { background: white; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); overflow: hidden; }
.tabs-nav { display: flex; border-bottom: 2px solid #F3F4F6; overflow-x: auto; }
.tab-item { padding: 16px 24px; border: none; background: transparent; cursor: pointer; font-size: 14px; font-weight: 600; color: #6B7280; border-bottom: 3px solid transparent; transition: all 0.2s; white-space: nowrap; display: flex; align-items: center; gap: 8px; text-decoration: none; }
.tab-item:hover { color: #7F3D9E; background: #F9FAFB; }
.tab-item.active { color: #7F3D9E; border-bottom-color: #7F3D9E; }
.tab-badge { background: #F3F4F6; color: #6B7280; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; }
.tab-item.active .tab-badge { background: #EDE9FE; color: #7F3D9E; }
.tab-content { display: none; padding: 24px; }
.tab-content.active { display: block; }

/* Tab Actions */
.tab-actions { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.tab-action-btn { padding: 10px 18px; border: 1px solid #E5E7EB; border-radius: 8px; background: white; cursor: pointer; font-size: 13px; font-weight: 600; color: #374151; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
.tab-action-btn:hover { background: #F9FAFB; border-color: #7F3D9E; color: #7F3D9E; }
.tab-action-btn.primary { background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%); border: none; color: white; }
.tab-action-btn.primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3); }

/* Filter Bar */
.filter-bar { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
.search-box { flex: 1; min-width: 250px; position: relative; }
.search-box input { width: 100%; padding: 10px 16px 10px 40px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 14px; }
.search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9CA3AF; }

/* Resources Table */
.resources-table { width: 100%; border-collapse: collapse; }
.resources-table thead { border-bottom: 1px solid #E5E7EB; }
.resources-table th { text-align: left; padding: 12px 16px; font-size: 11px; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; }
.resources-table td { padding: 16px; font-size: 13px; color: #1F2937; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
.resources-table tbody tr { transition: all 0.2s; }
.resources-table tbody tr:hover { background: #F9FAFB; }
.resource-info { display: flex; align-items: center; gap: 12px; }
.resource-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; color: white; flex-shrink: 0; }
.resource-icon.pdf { background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); }
.resource-icon.doc { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); }
.resource-icon.xls { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
.resource-icon.img { background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); }
.resource-icon.zip { background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%); }
.resource-icon.default { background: linear-gradient(135deg, #A78BFA 0%, #7F3D9E 100%); }
.resource-details { flex: 1; }
.resource-name { font-weight: 600; color: #1F2937; margin-bottom: 2px; }
.resource-meta { font-size: 11px; color: #9CA3AF; }
.category-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.category-badge.marketing { background: #EDE9FE; color: #7F3D9E; }
.category-badge.training { background: #D1FAE5; color: #059669; }
.category-badge.policy { background: #DBEAFE; color: #2563EB; }
.category-badge.forms { background: #FEF3C7; color: #D97706; }
.category-badge.other { background: #F3F4F6; color: #6B7280; }
.file-size { font-weight: 500; color: #4B5563; }
.download-count { color: #6B7280; }
.upload-date { color: #6B7280; font-size: 12px; }
.new-badge { display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%); color: white; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 12px; margin-left: 8px; }

/* Action Buttons */
.action-btns { display: flex; gap: 8px; }
.action-btn { width: 32px; height: 32px; border-radius: 6px; border: 1px solid #E5E7EB; background: white; color: #6B7280; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.action-btn:hover { background: #F9FAFB; border-color: #7F3D9E; color: #7F3D9E; }
.action-btn.delete:hover { background: #FEE2E2; border-color: #DC2626; color: #DC2626; }

/* Pagination */
.pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #E5E7EB; }
.pagination-info { font-size: 14px; color: #6B7280; }
.pagination-controls { display: flex; gap: 8px; }
.page-btn { padding: 8px 12px; border: 1px solid #D1D5DB; border-radius: 6px; background: white; cursor: pointer; font-size: 13px; font-weight: 600; color: #374151; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
.page-btn:hover { background: #F9FAFB; border-color: #7F3D9E; }
.page-btn.active { background: #7F3D9E; color: white; border-color: #7F3D9E; }
.page-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Empty State */
.empty-state { text-align: center; padding: 60px 20px; }
.empty-state-icon { width: 80px; height: 80px; margin: 0 auto 24px; background: #F3F4F6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #9CA3AF; font-size: 32px; }
.empty-state h3 { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: #1F2937; margin: 0 0 8px 0; }
.empty-state p { font-size: 14px; color: #6B7280; margin: 0 0 24px 0; }

/* Alert Messages */
.alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-size: 14px; }
.alert-success { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
.alert-error { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
.alert i { font-size: 18px; }

.modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.upload-modal { background: white; border-radius: 16px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
.upload-modal .modal-header { padding: 24px; border-bottom: 1px solid #E5E7EB; display: flex; justify-content: space-between; align-items: center; }
.upload-modal .modal-header h3 { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: #1F2937; margin: 0; }
.upload-modal .modal-close { width: 32px; height: 32px; border-radius: 8px; border: none; background: #F3F4F6; color: #6B7280; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.upload-modal .modal-close:hover { background: #E5E7EB; color: #1F2937; }
.upload-modal .modal-body { padding: 24px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 14px; transition: all 0.2s; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #7F3D9E; box-shadow: 0 0 0 3px rgba(127, 61, 158, 0.1); }
.form-group textarea { min-height: 100px; resize: vertical; }
.file-input-wrapper { position: relative; border: 2px dashed #D1D5DB; border-radius: 12px; padding: 40px; text-align: center; transition: all 0.2s; cursor: pointer; }
.file-input-wrapper:hover { border-color: #7F3D9E; background: #F9FAFB; }
.file-input-wrapper input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
.file-input-icon { font-size: 48px; color: #9CA3AF; margin-bottom: 16px; }
.file-input-text { font-size: 14px; color: #6B7280; margin-bottom: 8px; }
.file-input-hint { font-size: 12px; color: #9CA3AF; }
.upload-modal .modal-footer { padding: 20px 24px; border-top: 1px solid #E5E7EB; display: flex; justify-content: flex-end; gap: 12px; }
.btn-cancel { padding: 10px 20px; border: 1px solid #E5E7EB; border-radius: 8px; background: white; color: #6B7280; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-cancel:hover { background: #F9FAFB; border-color: #D1D5DB; }
.btn-submit { padding: 10px 24px; border: none; border-radius: 8px; background: linear-gradient(135deg, #7F3D9E 0%, #7C3AED 100%); color: white; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-submit:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(127, 61, 158, 0.3); }

/* Responsive */
@media (max-width: 768px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .tab-item { font-size: 13px; padding: 12px 16px; }
    .filter-bar { flex-direction: column; }
    .search-box { width: 100%; }
    .tab-actions { flex-direction: column; }
    .tab-action-btn { width: 100%; justify-content: center; }
    .resources-table { display: block; overflow-x: auto; }
    .pagination { flex-direction: column; gap: 12px; }
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Resource Management</h1>
    <p class="page-subtitle">Upload and manage resources for agents and members</p>
</div>

<!-- Alert Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-header"><div class="stat-icon purple"><i class="fas fa-folder-open"></i></div></div>
        <div class="stat-label">Total Resources</div>
        <div class="stat-value"><?php echo number_format($stats['total'] ?? 0); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-header"><div class="stat-icon green"><i class="fas fa-bullhorn"></i></div></div>
        <div class="stat-label">Marketing</div>
        <div class="stat-value"><?php echo number_format($stats['marketing'] ?? 0); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-header"><div class="stat-icon blue"><i class="fas fa-graduation-cap"></i></div></div>
        <div class="stat-label">Training</div>
        <div class="stat-value"><?php echo number_format($stats['training'] ?? 0); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-header"><div class="stat-icon orange"><i class="fas fa-file-alt"></i></div></div>
        <div class="stat-label">Forms</div>
        <div class="stat-value"><?php echo number_format($stats['forms'] ?? 0); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-header"><div class="stat-icon gray"><i class="fas fa-file-contract"></i></div></div>
        <div class="stat-label">Policy</div>
        <div class="stat-value"><?php echo number_format($stats['policy'] ?? 0); ?></div>
    </div>
</div>

<!-- Tabs Container -->
<div class="tabs-container">
    <div class="tabs-nav">
        <a href="/admin/agents/resources?category=all" class="tab-item <?php echo $category === 'all' ? 'active' : ''; ?>">
            <i class="fas fa-folder-open"></i> All Resources <span class="tab-badge"><?php echo $stats['total'] ?? 0; ?></span>
        </a>
        <a href="/admin/agents/resources?category=marketing_materials" class="tab-item <?php echo $category === 'marketing_materials' ? 'active' : ''; ?>">
            <i class="fas fa-bullhorn"></i> Marketing <span class="tab-badge"><?php echo $stats['marketing'] ?? 0; ?></span>
        </a>
        <a href="/admin/agents/resources?category=training_documents" class="tab-item <?php echo $category === 'training_documents' ? 'active' : ''; ?>">
            <i class="fas fa-graduation-cap"></i> Training <span class="tab-badge"><?php echo $stats['training'] ?? 0; ?></span>
        </a>
        <a href="/admin/agents/resources?category=forms" class="tab-item <?php echo $category === 'forms' ? 'active' : ''; ?>">
            <i class="fas fa-file-alt"></i> Forms <span class="tab-badge"><?php echo $stats['forms'] ?? 0; ?></span>
        </a>
        <a href="/admin/agents/resources?category=policy_documents" class="tab-item <?php echo $category === 'policy_documents' ? 'active' : ''; ?>">
            <i class="fas fa-file-contract"></i> Policy <span class="tab-badge"><?php echo $stats['policy'] ?? 0; ?></span>
        </a>
        <a href="/admin/agents/resources?category=other" class="tab-item <?php echo $category === 'other' ? 'active' : ''; ?>">
            <i class="fas fa-file"></i> Other <span class="tab-badge"><?php echo $stats['other'] ?? 0; ?></span>
        </a>
    </div>

    <div class="tab-content active">
        <!-- Tab Actions -->
        <div class="tab-actions">
            <button class="tab-action-btn primary" onclick="openUploadModal()">
                <i class="fas fa-cloud-upload-alt"></i> Upload Resource
            </button>
            <a href="/admin/agents/resources/export" class="tab-action-btn">
                <i class="fas fa-file-export"></i> Export CSV
            </a>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search-resources" placeholder="Search resources..." onkeyup="filterResources()">
            </div>
        </div>

        <!-- Resources Table -->
        <?php if (empty($resources)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-folder-open"></i></div>
                <h3>No Resources Found</h3>
                <p>Upload your first resource to share with agents and members.</p>
                <button class="tab-action-btn primary" onclick="openUploadModal()" style="display: inline-flex;">
                    <i class="fas fa-cloud-upload-alt"></i> Upload First Resource
                </button>
            </div>
        <?php else: ?>
            <table class="resources-table">
                <thead>
                    <tr>
                        <th>RESOURCE</th>
                        <th>CATEGORY</th>
                        <th>FILE SIZE</th>
                        <th>DOWNLOADS</th>
                        <th>UPLOADED</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resources as $resource): 
                        $createdAt = $resource['created_at'] ?? '';
                        $isNew = $createdAt && strtotime($createdAt) > strtotime('-7 days');
                        $originalName = $resource['original_name'] ?? '';
                        $fileExt = $originalName ? strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) : '';
                        
                        $iconClass = 'default';
                        if (in_array($fileExt, ['pdf'])) $iconClass = 'pdf';
                        elseif (in_array($fileExt, ['doc', 'docx'])) $iconClass = 'doc';
                        elseif (in_array($fileExt, ['xls', 'xlsx'])) $iconClass = 'xls';
                        elseif (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) $iconClass = 'img';
                        elseif (in_array($fileExt, ['zip', 'rar'])) $iconClass = 'zip';
                        
                        $category = $resource['category'] ?? '';
                        $catClass = str_replace('_', '-', $category);
                    ?>
                        <tr>
                            <td>
                                <div class="resource-info">
                                    <div class="resource-icon <?php echo $iconClass; ?>">
                                        <i class="fas fa-file<?php echo $fileExt === 'pdf' ? '-pdf' : ''; ?>"></i>
                                    </div>
                                    <div class="resource-details">
                                        <div class="resource-name">
                                            <?php echo htmlspecialchars($resource['title'] ?? ''); ?>
                                            <?php if ($isNew): ?>
                                                <span class="new-badge"><i class="fas fa-sparkles"></i> NEW</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="resource-meta"><?php echo htmlspecialchars($originalName); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="category-badge <?php echo $catClass; ?>"><?php echo ucwords(str_replace('_', ' ', $category)); ?></span></td>
                            <td class="file-size"><?php echo Resource::formatFileSize($resource['file_size'] ?? 0); ?></td>
                            <td class="download-count"><i class="fas fa-download" style="margin-right: 4px; color: #9CA3AF;"></i><?php echo number_format($resource['download_count'] ?? 0); ?></td>
                            <td class="upload-date"><?php echo $createdAt ? date('M d, Y', strtotime($createdAt)) : ''; ?><br><small style="color: #9CA3AF;">by <?php echo htmlspecialchars($resource['uploaded_by_name'] ?? $resource['uploader_name'] ?? 'Admin'); ?></small></td>
                            <td>
                                <div class="action-btns">
                                    <a href="/admin/agents/resources/download/<?php echo $resource['id'] ?? 0; ?>" class="action-btn" title="Download"><i class="fas fa-download"></i></a>
                                    <button class="action-btn delete" onclick="confirmDelete(<?php echo $resource['id'] ?? 0; ?>, '<?php echo htmlspecialchars(addslashes($resource['title'] ?? '')); ?>')" title="Delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal-overlay" id="uploadModal">
    <div class="upload-modal">
        <div class="modal-header">
            <h3><i class="fas fa-cloud-upload-alt"></i> Upload New Resource</h3>
            <button class="modal-close" onclick="closeUploadModal()"><i class="fas fa-times"></i></button>
        </div>
        <form action="/admin/agents/resources/upload" method="POST" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="title">Resource Title *</label>
                    <input type="text" id="title" name="title" required placeholder="Enter resource title">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Enter resource description (optional)"></textarea>
                </div>
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="marketing_materials">Marketing Materials</option>
                        <option value="training_documents">Training Documents</option>
                        <option value="policy_documents">Policy Documents</option>
                        <option value="forms">Forms</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>File *</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="file" name="resource_file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip">
                        <div class="file-input-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="file-input-text">Click to select file or drag and drop</div>
                        <div class="file-input-hint">Max 10MB. Allowed: PDF, DOC, XLS, PPT, JPG, PNG, ZIP</div>
                    </div>
                    <div id="selectedFile" style="margin-top: 12px; font-size: 13px; color: #059669; display: none;">
                        <i class="fas fa-check-circle"></i> <span id="fileName"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeUploadModal()">Cancel</button>
                <button type="submit" class="btn-submit"><i class="fas fa-cloud-upload-alt"></i> Upload Resource</button>
            </div>
        </form>
    </div>
</div>

<script>
const CSRF_TOKEN = <?php echo json_encode($csrf_token); ?>;
function openUploadModal() { document.getElementById('uploadModal').classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeUploadModal() { document.getElementById('uploadModal').classList.remove('active'); document.body.style.overflow = ''; document.getElementById('uploadForm').reset(); document.getElementById('selectedFile').style.display = 'none'; }
const fileInput = document.getElementById('file');
if (fileInput) {
    fileInput.addEventListener('change', function(e) { if (e.target.files.length > 0) { document.getElementById('fileName').textContent = e.target.files[0].name; document.getElementById('selectedFile').style.display = 'block'; } });
}
const uploadModalEl = document.getElementById('uploadModal');
if (uploadModalEl) {
    uploadModalEl.addEventListener('click', function(e) { if (e.target === this) closeUploadModal(); });
}
function confirmDelete(id, title) {
    const proceed = () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/agents/resources/delete/' + id;
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'csrf_token';
        tokenInput.value = CSRF_TOKEN || '';
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    };
    if (window.ShenaApp && typeof ShenaApp.confirmAction === 'function') {
        ShenaApp.confirmAction(
            'Are you sure you want to delete "' + title + '"?\n\nThis action cannot be undone.',
            proceed,
            null,
            { type: 'danger', title: 'Delete Resource', confirmText: 'Delete' }
        );
        return;
    }
    if (!confirm('Are you sure you want to delete "' + title + '"?\n\nThis action cannot be undone.')) return;
    proceed();
}
function filterResources() { const searchValue = (document.getElementById('search-resources')?.value || '').toLowerCase(); const rows = document.querySelectorAll('.resources-table tbody tr'); rows.forEach(row => { const name = row.querySelector('.resource-name')?.textContent.toLowerCase() || ''; row.style.display = name.includes(searchValue) ? '' : 'none'; }); }
</script>

<?php include_once __DIR__ . '/../layouts/admin-footer.php'; ?>
