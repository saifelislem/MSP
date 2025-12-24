/**
 * Composant File Picker pour l'administration
 * Permet de sélectionner des fichiers depuis le gestionnaire ou d'en uploader de nouveaux
 */
class FilePicker {
    constructor(options = {}) {
        this.options = {
            category: 'general',
            multiple: false,
            accept: 'image/*',
            maxSize: 5 * 1024 * 1024, // 5MB
            ...options
        };
        
        this.callbacks = {};
        this.init();
    }

    init() {
        // Créer le modal de sélection s'il n'existe pas
        this.createModal();
        
        // Écouter les événements
        this.setupEventListeners();
    }

    createModal() {
        if (document.getElementById('filePickerModal')) return;

        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'filePickerModal';
        modal.tabIndex = -1;
        
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa fa-image"></i> Sélectionner un Fichier
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Onglets -->
                        <ul class="nav nav-tabs mb-3" id="filePickerTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#selectTab">
                                    <i class="fa fa-folder"></i> Sélectionner
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#uploadTab">
                                    <i class="fa fa-upload"></i> Uploader
                                </a>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
                            <!-- Onglet Sélection -->
                            <div class="tab-pane fade show active" id="selectTab">
                                <div class="row" id="filePickerGrid">
                                    <!-- Les fichiers seront chargés ici -->
                                </div>
                                <div id="filePickerEmpty" class="text-center text-muted py-5" style="display: none;">
                                    <i class="fa fa-folder-open fa-3x mb-3"></i>
                                    <p>Aucun fichier disponible</p>
                                </div>
                            </div>
                            
                            <!-- Onglet Upload -->
                            <div class="tab-pane fade" id="uploadTab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div id="filePickerDropZone" class="border border-dashed border-primary rounded p-4 text-center mb-3" style="min-height: 200px; cursor: pointer;">
                                            <i class="fa fa-cloud-upload fa-3x text-primary mb-3"></i>
                                            <p class="mb-0">Glissez vos fichiers ici</p>
                                            <p class="mb-0">ou cliquez pour parcourir</p>
                                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WebP, SVG</small>
                                        </div>
                                        <input type="file" id="filePickerInput" style="display: none;" accept="image/*">
                                    </div>
                                    <div class="col-md-6">
                                        <div id="filePickerPreview"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" id="filePickerConfirm" style="display: none;">
                            <i class="fa fa-check"></i> Sélectionner
                        </button>
                        <button type="button" class="btn btn-success" id="filePickerUpload" style="display: none;">
                            <i class="fa fa-upload"></i> Uploader
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    setupEventListeners() {
        const modal = document.getElementById('filePickerModal');
        const dropZone = document.getElementById('filePickerDropZone');
        const fileInput = document.getElementById('filePickerInput');
        const uploadBtn = document.getElementById('filePickerUpload');
        const confirmBtn = document.getElementById('filePickerConfirm');

        // Drag & Drop
        dropZone.addEventListener('click', () => fileInput.click());
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('bg-light');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('bg-light');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-light');
            this.handleFiles(e.dataTransfer.files);
        });

        // Sélection de fichiers
        fileInput.addEventListener('change', (e) => {
            this.handleFiles(e.target.files);
        });

        // Boutons
        uploadBtn.addEventListener('click', () => this.uploadFiles());
        confirmBtn.addEventListener('click', () => this.confirmSelection());

        // Onglets
        document.addEventListener('shown.bs.tab', (e) => {
            if (e.target.getAttribute('href') === '#selectTab') {
                this.loadFiles();
            }
        });
    }

    // Ouvrir le sélecteur
    open(callback, category = 'general') {
        this.currentCallback = callback;
        this.options.category = category;
        this.selectedFile = null;
        this.selectedFiles = [];

        // Réinitialiser l'interface
        this.resetInterface();
        
        // Charger les fichiers
        this.loadFiles();
        
        // Ouvrir le modal
        const modal = new bootstrap.Modal(document.getElementById('filePickerModal'));
        modal.show();
    }

    resetInterface() {
        document.getElementById('filePickerPreview').innerHTML = '';
        document.getElementById('filePickerUpload').style.display = 'none';
        document.getElementById('filePickerConfirm').style.display = 'none';
        
        // Réactiver l'onglet sélection
        const selectTab = document.querySelector('a[href="#selectTab"]');
        const uploadTab = document.querySelector('a[href="#uploadTab"]');
        
        selectTab.classList.add('active');
        uploadTab.classList.remove('active');
        
        document.getElementById('selectTab').classList.add('show', 'active');
        document.getElementById('uploadTab').classList.remove('show', 'active');
    }

    // Charger les fichiers
    async loadFiles() {
        try {
            const response = await fetch(`/admin/files/api/list?category=${this.options.category}`);
            const data = await response.json();
            this.displayFiles(data.files || []);
        } catch (error) {
            console.error('Erreur lors du chargement des fichiers:', error);
        }
    }

    // Afficher les fichiers
    displayFiles(files) {
        const grid = document.getElementById('filePickerGrid');
        const empty = document.getElementById('filePickerEmpty');
        
        if (files.length === 0) {
            grid.style.display = 'none';
            empty.style.display = 'block';
            return;
        }
        
        grid.style.display = 'block';
        empty.style.display = 'none';
        grid.innerHTML = '';

        files.forEach(file => {
            const col = document.createElement('div');
            col.className = 'col-md-2 col-sm-3 col-4 mb-3';
            
            col.innerHTML = `
                <div class="file-item border rounded p-2 text-center" style="cursor: pointer;" data-path="${file.path}">
                    <img src="${file.path}" alt="${file.name}" class="img-fluid mb-2" style="height: 80px; object-fit: cover;">
                    <small class="d-block text-truncate" title="${file.name}">${file.name}</small>
                </div>
            `;
            
            col.addEventListener('click', () => this.selectFile(file.path, col));
            grid.appendChild(col);
        });
    }

    // Sélectionner un fichier
    selectFile(path, element) {
        // Désélectionner les autres
        document.querySelectorAll('.file-item').forEach(item => {
            item.classList.remove('border-primary', 'bg-light');
        });
        
        // Sélectionner le fichier actuel
        element.querySelector('.file-item').classList.add('border-primary', 'bg-light');
        this.selectedFile = path;
        
        // Afficher le bouton de confirmation
        document.getElementById('filePickerConfirm').style.display = 'inline-block';
    }

    // Gérer les fichiers sélectionnés pour upload
    handleFiles(files) {
        this.selectedFiles = Array.from(files);
        this.displayUploadPreview();
        
        // Afficher le bouton d'upload
        document.getElementById('filePickerUpload').style.display = 'inline-block';
    }

    // Afficher l'aperçu des fichiers à uploader
    displayUploadPreview() {
        const preview = document.getElementById('filePickerPreview');
        preview.innerHTML = '';

        this.selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'mb-3 p-2 border rounded';
                div.innerHTML = `
                    <img src="${e.target.result}" class="img-fluid mb-2" style="max-height: 100px;">
                    <p class="mb-1"><strong>${file.name}</strong></p>
                    <small class="text-muted">${this.formatFileSize(file.size)}</small>
                    <button type="button" class="btn btn-sm btn-danger float-end" onclick="filePicker.removeFile(${index})">
                        <i class="fa fa-times"></i>
                    </button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    // Supprimer un fichier de la sélection
    removeFile(index) {
        this.selectedFiles.splice(index, 1);
        this.displayUploadPreview();
        
        if (this.selectedFiles.length === 0) {
            document.getElementById('filePickerUpload').style.display = 'none';
        }
    }

    // Uploader les fichiers
    async uploadFiles() {
        if (this.selectedFiles.length === 0) return;

        const uploadBtn = document.getElementById('filePickerUpload');
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Upload...';

        try {
            const promises = this.selectedFiles.map(file => {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('category', this.options.category);

                return fetch('/admin/files/upload', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json());
            });

            const results = await Promise.all(promises);
            const errors = results.filter(r => r.error);
            
            if (errors.length > 0) {
                alert('Erreurs lors de l\'upload: ' + errors.map(e => e.error).join(', '));
            } else {
                // Sélectionner automatiquement le premier fichier uploadé
                if (results.length > 0 && results[0].path) {
                    this.selectedFile = results[0].path;
                    this.confirmSelection();
                }
            }
        } catch (error) {
            alert('Erreur lors de l\'upload: ' + error.message);
        } finally {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fa fa-upload"></i> Uploader';
        }
    }

    // Confirmer la sélection
    confirmSelection() {
        if (this.selectedFile && this.currentCallback) {
            this.currentCallback(this.selectedFile);
            bootstrap.Modal.getInstance(document.getElementById('filePickerModal')).hide();
        }
    }

    // Utilitaires
    formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
}

// Instance globale
const filePicker = new FilePicker();

// Fonction helper pour créer un champ de sélection de fichier
function createFilePickerField(inputId, category = 'general', placeholder = 'Aucun fichier sélectionné') {
    const input = document.getElementById(inputId);
    if (!input) return;

    const wrapper = document.createElement('div');
    wrapper.className = 'input-group';
    
    const newInput = input.cloneNode(true);
    newInput.readOnly = true;
    newInput.placeholder = placeholder;
    
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-outline-secondary';
    button.innerHTML = '<i class="fa fa-folder-open"></i> Parcourir';
    
    button.addEventListener('click', () => {
        filePicker.open((path) => {
            newInput.value = path;
            
            // Déclencher l'événement change
            const event = new Event('change', { bubbles: true });
            newInput.dispatchEvent(event);
        }, category);
    });
    
    wrapper.appendChild(newInput);
    wrapper.appendChild(button);
    
    input.parentNode.replaceChild(wrapper, input);
    
    return newInput;
}