<div class="drawing-editor-container">
    <!-- Header -->
    <div class="editor-header">
        <div class="header-content">
            <div class="breadcrumb-section">
                <a href="{{ route($routePrefix . '.drawings') }}" class="back-link">← Drawings</a>
                <h1 class="page-title">{{ $isEdit ? 'Edit Drawing Activity' : 'New Drawing Activity' }}</h1>
                <p class="page-subtitle">{{ $isEdit ? 'Update drawing activity details and configuration' : 'Create a new interactive drawing activity' }}</p>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session()->has('message'))
        <div class="success-message">
            {{ session('message') }}
        </div>
    @endif

    <!-- Main Form -->
    <form wire:submit="save" class="drawing-form">
        <!-- Basic Information Section -->
        <div class="form-section">
            <h2 class="section-title">Basic Information</h2>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input wire:model="title" type="text" class="form-input" placeholder="Draw the Mighty Lion" required>
                    @error('title') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Tribe</label>
                    <select wire:model="tribe_id" class="form-select" required>
                        <option value="">Select Tribe</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Drawing Type</label>
                    <select wire:model.live="drawing_type" class="form-select" required>
                        <option value="coloring">Coloring Page</option>
                        <option value="colour_by_number">Colour by Number</option>
                        <option value="hero_drawing">Hero Drawing</option>
                        <option value="design_tool">Design Tool</option>
                        <option value="free_draw">Free Drawing</option>
                    </select>
                    @error('drawing_type') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Difficulty Level</label>
                    <select wire:model="difficulty_level" class="form-select" required>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                    @error('difficulty_level') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group full-width">
                <label class="form-label">Description</label>
                <textarea wire:model="description" class="form-textarea" rows="3" placeholder="Describe what children will draw and learn..."></textarea>
                @error('description') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-grid-small">
                <div class="form-group">
                    <label class="form-label">Minimum Age</label>
                    <input wire:model="age_min" type="number" class="form-input" min="1" max="18" required>
                    @error('age_min') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Maximum Age</label>
                    <input wire:model="age_max" type="number" class="form-input" min="1" max="18" required>
                    @error('age_max') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Star Points</label>
                    <input wire:model="star_points" type="number" class="form-input" min="1" max="100" required>
                    @error('star_points') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select wire:model="status" class="form-select" required>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                    @error('status') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Template & Preview Images Section -->
        <div class="form-section">
            <h2 class="section-title">Template & Preview Images</h2>
            
            <div class="upload-grid">
                {{-- Template Image --}}
                <div class="upload-group">
                    <label class="form-label">Template Image</label>
                    <p class="upload-description">Upload the base template (coloring page outline, drawing guide, etc.)</p>
                    <div class="file-upload-wrapper">
                        <input wire:model="template_file" type="file" class="file-input" accept="image/*" id="template-file"
                            onchange="
                                const file = this.files[0];
                                if (file) {
                                    document.getElementById('template-filename').textContent = file.name;
                                    const reader = new FileReader();
                                    reader.onload = e => {
                                        const img = document.getElementById('template-preview');
                                        img.src = e.target.result;
                                        img.style.display = 'block';
                                        document.getElementById('template-preview-caption').style.display = 'block';
                                    };
                                    reader.readAsDataURL(file);
                                }
                            ">
                        <label for="template-file" class="file-upload-label">
                            <span class="upload-icon">📁</span>
                            <span class="upload-text">Choose File</span>
                            <span class="file-info" id="template-filename">No file selected.</span>
                        </label>
                    </div>
                    @error('template_file') <div class="form-error">{{ $message }}</div> @enderror
                    <img id="template-preview" src="" alt="Template preview" style="display:none;margin-top:10px;max-width:100%;max-height:200px;border-radius:8px;border:1px solid var(--cms-border)">
                    <p id="template-preview-caption" style="display:none;font-size:11px;color:var(--cms-text-muted);margin-top:4px">New template (not saved yet)</p>
                    @if($drawing && $drawing->template_path)
                        <div class="current-image" style="margin-top:10px">
                            <img src="{{ asset('storage/' . $drawing->template_path) }}" alt="Current template" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid var(--cms-border)">
                            <p class="image-caption">Current template</p>
                        </div>
                    @endif
                </div>

                {{-- Preview Image --}}
                <div class="upload-group">
                    <label class="form-label">Preview Image</label>
                    <p class="upload-description">Upload a completed example or preview thumbnail</p>
                    <div class="file-upload-wrapper">
                        <input wire:model="preview_file" type="file" class="file-input" accept="image/*" id="preview-file"
                            onchange="
                                const file = this.files[0];
                                if (file) {
                                    document.getElementById('preview-filename').textContent = file.name;
                                    const reader = new FileReader();
                                    reader.onload = e => {
                                        const img = document.getElementById('preview-img');
                                        img.src = e.target.result;
                                        img.style.display = 'block';
                                        document.getElementById('preview-caption').style.display = 'block';
                                    };
                                    reader.readAsDataURL(file);
                                }
                            ">
                        <label for="preview-file" class="file-upload-label">
                            <span class="upload-icon">📁</span>
                            <span class="upload-text">Choose File</span>
                            <span class="file-info" id="preview-filename">No file selected.</span>
                        </label>
                    </div>
                    @error('preview_file') <div class="form-error">{{ $message }}</div> @enderror
                    <img id="preview-img" src="" alt="Preview" style="display:none;margin-top:10px;max-width:100%;max-height:200px;border-radius:8px;border:1px solid var(--cms-border)">
                    <p id="preview-caption" style="display:none;font-size:11px;color:var(--cms-text-muted);margin-top:4px">New preview (not saved yet)</p>
                    @if($drawing && $drawing->preview_path)
                        <div class="current-image" style="margin-top:10px">
                            <img src="{{ asset('storage/' . $drawing->preview_path) }}" alt="Current preview" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid var(--cms-border)">
                            <p class="image-caption">Current preview</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Colour by Number Labels (only for colour_by_number type) -->
        @if($drawing_type === 'colour_by_number')
        <div class="form-section">
            <h2 class="section-title">🎨 Colour by Number Labels</h2>
            <p style="color:var(--cms-text-muted);font-size:12px;margin-bottom:16px">
                Define the 5 colour zones. Upload a template image where each zone is labelled with a number (1–5). Children will tap a colour button then paint the matching numbered zones.
            </p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">
                @foreach([1,2,3,4,5] as $num)
                <div>
                    <label style="display:block;font-size:11px;font-weight:600;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px">
                        Zone {{ $num }} Label
                    </label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <div style="width:28px;height:28px;border-radius:6px;background:{{ ['#3498DB','#2ECC71','#E74C3C','#F1C40F','#9B59B6'][$num-1] }};flex-shrink:0;border:2px solid var(--cms-border)"></div>
                        <input wire:model="metadata.colour_labels.{{ $num }}"
                            type="text"
                            style="flex:1;padding:8px 10px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);font-size:13px;outline:none"
                            placeholder="{{ ['Bead Blue','Forest Green','Sunset Red','Sacred Gold','Royal Purple'][$num-1] }}">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Coloring Page specific -->
        @if($drawing_type === 'coloring')
        <div class="form-section">
            <h2 class="section-title">🖍️ Coloring Page Settings</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div>
                    <label class="form-label">Scene Description</label>
                    <textarea wire:model="metadata.coloring.scene_description"
                        class="form-input" rows="3"
                        placeholder="Describe what's in the scene — e.g. 'A lush forest with tall trees, exotic birds, and the River Nile in the background'"></textarea>
                </div>
                <div>
                    <label class="form-label">Colour Guide Hint <span style="color:var(--cms-text-muted);font-size:11px;font-weight:400">optional</span></label>
                    <textarea wire:model="metadata.coloring.colour_hint"
                        class="form-input" rows="3"
                        placeholder="e.g. 'Use deep greens for the trees, light blue for the sky, and brown for the tree trunks'"></textarea>
                </div>
            </div>
        </div>
        @endif

        <!-- Hero Drawing specific -->
        @if($drawing_type === 'hero_drawing')
        <div class="form-section">
            <h2 class="section-title">🦸 Hero Drawing Settings</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                <div>
                    <label class="form-label">Hero Name</label>
                    <input wire:model="metadata.hero.name" type="text" class="form-input" placeholder="e.g. Gipir">
                </div>
                <div>
                    <label class="form-label">Hero Title / Role</label>
                    <input wire:model="metadata.hero.title" type="text" class="form-input" placeholder="e.g. Keeper of the Sacred Beads">
                </div>
            </div>
            <div>
                <label class="form-label">Step-by-Step Drawing Instructions <span style="color:var(--cms-text-muted);font-size:11px;font-weight:400">optional — shown to child</span></label>
                <textarea wire:model="metadata.hero.instructions"
                    class="form-input" rows="4"
                    placeholder="Step 1: Draw a circle for the head&#10;Step 2: Add the body and arms&#10;Step 3: Draw the hero's spear&#10;Step 4: Add traditional clothing patterns"></textarea>
            </div>
        </div>
        @endif

        <!-- Design Tool specific -->
        @if($drawing_type === 'design_tool')
        <div class="form-section">
            <h2 class="section-title">🛠️ Design Tool Settings</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                <div>
                    <label class="form-label">Design Prompt</label>
                    <textarea wire:model="metadata.design.prompt"
                        class="form-input" rows="3"
                        placeholder="e.g. 'Design a bead necklace for a hero using your favourite colours. Add patterns and share it with the tribe!'"></textarea>
                </div>
                <div>
                    <label class="form-label">Available Stamps / Stickers <span style="color:var(--cms-text-muted);font-size:11px;font-weight:400">comma separated emojis</span></label>
                    <input wire:model="metadata.design.stamps" type="text" class="form-input" placeholder="💎, 🌳, 🏹, ⭐, 🌊, 🥁, 🐊">
                    <div style="font-size:10px;color:var(--cms-text-muted);margin-top:4px">These emojis will be available as stamps in the design tool</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Free Drawing specific -->
        @if($drawing_type === 'free_draw')
        <div class="form-section">
            <h2 class="section-title">✏️ Free Drawing Settings</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div>
                    <label class="form-label">Drawing Prompt / Inspiration</label>
                    <textarea wire:model="metadata.free_draw.prompt"
                        class="form-input" rows="3"
                        placeholder="e.g. 'Draw what you think Gipir's village looks like. Add the river, trees, and huts!'"></textarea>
                </div>
                <div>
                    <label class="form-label">What to Include <span style="color:var(--cms-text-muted);font-size:11px;font-weight:400">optional checklist for child</span></label>
                    <textarea wire:model="metadata.free_draw.checklist"
                        class="form-input" rows="3"
                        placeholder="e.g. 'A river, at least 2 trees, a hut, the hero character'"></textarea>
                </div>
            </div>
        </div>
        @endif

        <!-- Required Materials Section -->
        <div class="form-section">
            <h2 class="section-title">Required Materials</h2>
            
            <div class="material-input-group">
                <input wire:model="materialInput" type="text" class="form-input" placeholder="Add material (e.g., Crayons)">
                <button type="button" wire:click="addMaterial" class="add-button">Add</button>
            </div>

            <div class="materials-list">
                @foreach($materials as $index => $material)
                    <div class="material-tag">
                        <span class="material-name">{{ $material }}</span>
                        <button type="button" wire:click="removeMaterial({{ $index }})" class="remove-button">×</button>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Color Palette Section -->
        <div class="form-section">
            <div class="section-header">
                <h2 class="section-title">Color Palette</h2>
                <button type="button" wire:click="resetToDefaultColors" class="reset-button">Reset to Default</button>
            </div>
            
            <div class="color-input-group">
                <input wire:model="colorInput" type="color" class="color-picker">
                <button type="button" wire:click="addColor" class="add-button">Add Color</button>
            </div>

            <div class="color-palette-grid">
                @foreach($color_palette as $index => $color)
                    <div class="color-item">
                        <div class="color-swatch" style="background-color: {{ $color }}"></div>
                        <button type="button" wire:click="removeColor({{ $index }})" class="color-remove">×</button>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route($routePrefix . '.drawings') }}" class="cancel-button">Cancel</a>
            <button type="submit" class="create-button">
                {{ $isEdit ? 'Update Drawing' : 'Create Drawing' }}
            </button>
        </div>
    </form>

    <style>
.drawing-editor-container {
    min-height: 100vh;
    background: #2c3e50;
    color: #ecf0f1;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.editor-header {
    background: #34495e;
    border-bottom: 1px solid #4a5f7a;
    padding: 2rem;
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
}

.back-link {
    color: #bdc3c7;
    text-decoration: none;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    display: inline-block;
    transition: color 0.2s ease;
}

.back-link:hover {
    color: #ecf0f1;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
    color: #ecf0f1;
}

.page-subtitle {
    color: #95a5a6;
    margin: 0;
    font-size: 0.875rem;
}

.success-message {
    background: rgba(46, 204, 113, 0.1);
    border: 1px solid rgba(46, 204, 113, 0.3);
    color: #2ecc71;
    padding: 1rem;
    border-radius: 8px;
    margin: 2rem auto;
    max-width: 1200px;
    font-size: 0.875rem;
    font-weight: 500;
}

.drawing-form {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.form-section {
    background: #34495e;
    border: 1px solid #4a5f7a;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 1.5rem 0;
    color: #ecf0f1;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-grid-small {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #ecf0f1;
    margin-bottom: 0.5rem;
}

.form-input, .form-select, .form-textarea {
    background: #2c3e50;
    border: 1px solid #4a5f7a;
    border-radius: 6px;
    padding: 0.75rem;
    color: #ecf0f1;
    font-size: 0.875rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-error {
    color: #e74c3c;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.upload-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.upload-group {
    display: flex;
    flex-direction: column;
}

.upload-description {
    color: #95a5a6;
    font-size: 0.75rem;
    margin: 0 0 1rem 0;
}

.file-upload-wrapper {
    position: relative;
}

.file-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-upload-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #2c3e50;
    border: 2px dashed #4a5f7a;
    border-radius: 8px;
    cursor: pointer;
    transition: border-color 0.2s ease, background-color 0.2s ease;
}

.file-upload-label:hover {
    border-color: #3498db;
    background: rgba(52, 152, 219, 0.05);
}

.upload-icon {
    font-size: 1.25rem;
}

.upload-text {
    font-weight: 500;
    color: #ecf0f1;
}

.file-info {
    color: #95a5a6;
    font-size: 0.75rem;
}

.current-image {
    margin-top: 1rem;
}

.preview-image {
    max-width: 200px;
    height: auto;
    border-radius: 8px;
    border: 1px solid #4a5f7a;
}

.image-caption {
    color: #95a5a6;
    font-size: 0.75rem;
    margin: 0.5rem 0 0 0;
}

.material-input-group {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    align-items: flex-start;
}

.material-input-group .form-input {
    flex: 1;
}

.add-button {
    background: rgba(46, 204, 113, 0.2);
    color: #2ecc71;
    border: 1px solid rgba(46, 204, 113, 0.3);
    border-radius: 6px;
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.add-button:hover {
    background: rgba(46, 204, 113, 0.3);
    border-color: rgba(46, 204, 113, 0.5);
}

.reset-button {
    background: rgba(241, 196, 15, 0.2);
    color: #f1c40f;
    border: 1px solid rgba(241, 196, 15, 0.3);
    border-radius: 6px;
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.reset-button:hover {
    background: rgba(241, 196, 15, 0.3);
    border-color: rgba(241, 196, 15, 0.5);
}

.materials-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.material-tag {
    background: var(--cms-surface-raised);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.material-name {
    color: #ecf0f1;
}

.remove-button {
    background: none;
    border: none;
    color: #95a5a6;
    cursor: pointer;
    font-size: 1.125rem;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.remove-button:hover {
    background: rgba(231, 76, 60, 0.2);
    color: #e74c3c;
}

.color-input-group {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    align-items: center;
}

.color-picker {
    width: 50px;
    height: 40px;
    border: 1px solid #4a5f7a;
    border-radius: 6px;
    cursor: pointer;
    background: none;
}

.color-palette-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
    gap: 0.75rem;
    max-width: 600px;
}

.color-item {
    position: relative;
    width: 50px;
    height: 50px;
}

.color-swatch {
    width: 100%;
    height: 100%;
    border-radius: 8px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    cursor: pointer;
    transition: transform 0.2s ease;
}

.color-swatch:hover {
    transform: scale(1.05);
}

.color-remove {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 20px;
    height: 20px;
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 0.75rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.color-remove:hover {
    background: #c0392b;
    transform: scale(1.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #4a5f7a;
}

.cancel-button {
    background: transparent;
    color: #95a5a6;
    border: 1px solid #4a5f7a;
    border-radius: 6px;
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.cancel-button:hover {
    background: rgba(149, 165, 166, 0.1);
    border-color: #95a5a6;
    color: #bdc3c7;
}

.create-button {
    background: #e74c3c;
    color: white;
    border: 1px solid #c0392b;
    border-radius: 6px;
    padding: 0.75rem 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.create-button:hover {
    background: #c0392b;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

@media (max-width: 768px) {
    .editor-header, .drawing-form {
        padding: 1rem;
    }
    
    .form-section {
        padding: 1.5rem;
    }
    
    .form-grid, .form-grid-small {
        grid-template-columns: 1fr;
    }
    
    .upload-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .color-palette-grid {
        grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
    }
}
</style>
</div>