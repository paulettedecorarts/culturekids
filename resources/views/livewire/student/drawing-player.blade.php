<div class="drawing-player-container">
    <!-- Header -->
    <div class="drawing-header">
        <div class="drawing-info">
            <h1 class="drawing-title">{{ $drawing->title }}</h1>
            <p class="drawing-subtitle">{{ $drawing->drawing_type_display }} • {{ $drawing->tribe->name }}</p>
        </div>
        <div class="drawing-stats">
            <div class="stat">
                <span class="stat-icon">⭐</span>
                <span class="stat-value">{{ $starsEarned }}/5</span>
            </div>
            <div class="stat">
                <span class="stat-icon">⏱️</span>
                <span class="stat-value" id="timer-display">{{ gmdate('i:s', $timeSpent) }}</span>
            </div>
        </div>
    </div>

    <!-- Main Drawing Area -->
    <div class="drawing-workspace">
        <!-- Canvas Container -->
        <div class="canvas-container">
            <!-- Template Image (if available) -->
            @if($drawing->template_path)
                <img id="templateImage" src="{{ asset('storage/' . $drawing->template_path) }}" alt="Template" class="template-image">
            @endif
            
            <!-- Drawing Canvas -->
            <canvas id="drawingCanvas" class="drawing-canvas"></canvas>
            
            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="loading-overlay">
                <div class="loading-spinner">🎨</div>
                <div class="loading-text">Loading canvas...</div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="drawing-toolbar {{ $showToolbar ? 'visible' : 'hidden' }}">
            <!-- Tools Section -->
            <div class="toolbar-section">
                <div class="section-title">Tools</div>
                <div class="tool-buttons">
                    <button class="tool-btn {{ $currentTool === 'brush' ? 'active' : '' }}" 
                            wire:click="selectTool('brush')" title="Brush">
                        🖌️
                    </button>
                    <button class="tool-btn {{ $currentTool === 'eraser' ? 'active' : '' }}" 
                            wire:click="selectTool('eraser')" title="Eraser">
                        🧽
                    </button>
                    @if($drawing->drawing_type === 'coloring')
                    <button class="tool-btn {{ $currentTool === 'fill' ? 'active' : '' }}" 
                            wire:click="selectTool('fill')" title="Fill">
                        🪣
                    </button>
                    @endif
                </div>
            </div>

            <!-- Brush Size Section -->
            <div class="toolbar-section">
                <div class="section-title">Size</div>
                <div class="size-controls">
                    <button class="size-btn {{ $brushSize === 2 ? 'active' : '' }}" 
                            wire:click="setBrushSize(2)" title="Small">
                        <div class="size-preview" style="width: 4px; height: 4px;"></div>
                    </button>
                    <button class="size-btn {{ $brushSize === 5 ? 'active' : '' }}" 
                            wire:click="setBrushSize(5)" title="Medium">
                        <div class="size-preview" style="width: 8px; height: 8px;"></div>
                    </button>
                    <button class="size-btn {{ $brushSize === 10 ? 'active' : '' }}" 
                            wire:click="setBrushSize(10)" title="Large">
                        <div class="size-preview" style="width: 12px; height: 12px;"></div>
                    </button>
                    <button class="size-btn {{ $brushSize === 15 ? 'active' : '' }}" 
                            wire:click="setBrushSize(15)" title="Extra Large">
                        <div class="size-preview" style="width: 16px; height: 16px;"></div>
                    </button>
                </div>
            </div>

            <!-- Colors Section -->
            <div class="toolbar-section">
                <div class="section-title">Colors</div>
                <div class="color-palette">
                    @foreach($drawing->color_palette ?? $drawing->getDefaultColorPaletteAttribute() as $color)
                        <button class="color-btn {{ $currentColor === $color ? 'active' : '' }}" 
                                style="background-color: {{ $color }}"
                                wire:click="selectColor('{{ $color }}')"
                                title="{{ $color }}">
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Actions Section -->
            <div class="toolbar-section">
                <div class="section-title">Actions</div>
                <div class="action-buttons">
                    <button class="action-btn" onclick="undoDrawing()" title="Undo">
                        ↶
                    </button>
                    <button class="action-btn" onclick="redoDrawing()" title="Redo">
                        ↷
                    </button>
                    <button class="action-btn" wire:click="clearCanvas" 
                            wire:confirm="Are you sure you want to clear your drawing?" title="Clear">
                        🗑️
                    </button>
                </div>
            </div>
        </div>

        <!-- Toolbar Toggle -->
        <button class="toolbar-toggle" wire:click="toggleToolbar">
            {{ $showToolbar ? '🔽' : '🔼' }}
        </button>
    </div>

    <!-- Control Buttons -->
    <div class="drawing-controls">
        <button class="control-btn secondary" onclick="saveDrawing()">
            💾 Save Progress
        </button>
        <button class="control-btn primary" onclick="completeDrawing()">
            ✅ Complete Drawing
        </button>
    </div>

    <!-- Completion Modal -->
    @if($completed)
    <div class="completion-modal">
        <div class="modal-content">
            <div class="completion-celebration">
                <div class="celebration-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="celebration-star {{ $i <= $starsEarned ? 'earned' : '' }}">⭐</span>
                    @endfor
                </div>
                <h2 class="completion-title">🎉 Amazing Artwork!</h2>
                <p class="completion-message">You earned {{ $starsEarned }} stars for your beautiful drawing!</p>
                <div class="completion-stats">
                    <div class="stat-item">
                        <span class="stat-label">Time Spent:</span>
                        <span class="stat-value">{{ gmdate('i:s', $timeSpent) }}</span>
                    </div>
                </div>
                <div class="completion-actions">
                    <button class="control-btn secondary" wire:click="resetDrawing">
                        🔄 Draw Again
                    </button>
                    <button class="control-btn primary" onclick="window.close()">
                        🏠 Back to Activities
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
    .drawing-player-container {
        min-height: 100vh;
        background: #f8fafc;
        font-family: var(--font-admin, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
        display: flex;
        flex-direction: column;
    }

    .drawing-header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .drawing-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        color: #1e293b;
    }

    .drawing-subtitle {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
        margin-top: 0.25rem;
    }

    .drawing-stats {
        display: flex;
        gap: 1rem;
    }

    .stat {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #f1f5f9;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .drawing-workspace {
        flex: 1;
        display: flex;
        flex-direction: column;
        position: relative;
        padding: 2rem;
        gap: 1.5rem;
    }

    .canvas-container {
        flex: 1;
        position: relative;
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        min-height: 500px;
    }

    .template-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        pointer-events: none;
        opacity: 0.2;
        z-index: 1;
    }

    .drawing-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        cursor: crosshair;
        z-index: 2;
    }

    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.95);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #e2e8f0;
        border-top: 4px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-text {
        margin-top: 1rem;
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
    }

    .drawing-toolbar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .drawing-toolbar.hidden {
        transform: translateY(100%);
        opacity: 0;
        pointer-events: none;
    }

    .toolbar-section {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        min-width: 120px;
    }

    .section-title {
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .tool-buttons, .size-controls, .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .tool-btn, .size-btn, .action-btn {
        width: 40px;
        height: 40px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #ffffff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        transition: all 0.2s ease;
        color: #374151;
    }

    .tool-btn:hover, .size-btn:hover, .action-btn:hover {
        border-color: #3b82f6;
        background: #eff6ff;
        transform: translateY(-1px);
    }

    .tool-btn.active, .size-btn.active {
        border-color: #3b82f6;
        background: #dbeafe;
        color: #1d4ed8;
    }

    .size-preview {
        background: #374151;
        border-radius: 50%;
    }

    .color-palette {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 0.375rem;
        max-width: 240px;
    }

    .color-btn {
        width: 32px;
        height: 32px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .color-btn:hover {
        transform: scale(1.1);
        border-color: #6b7280;
    }

    .color-btn.active {
        border-color: #1f2937;
        border-width: 3px;
        transform: scale(1.1);
    }

    .color-btn.active::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: bold;
        text-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
    }

    .toolbar-toggle {
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 0.5rem 1rem;
        cursor: pointer;
        font-size: 0.875rem;
        color: #64748b;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .toolbar-toggle:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .drawing-controls {
        display: flex;
        justify-content: center;
        gap: 1rem;
        padding: 1rem 0;
    }

    .control-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .control-btn.primary {
        background: #3b82f6;
        color: white;
    }

    .control-btn.primary:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .control-btn.secondary {
        background: #6b7280;
        color: white;
    }

    .control-btn.secondary:hover {
        background: #4b5563;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.4);
    }

    .completion-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background: #ffffff;
        border-radius: 16px;
        padding: 2rem;
        max-width: 400px;
        text-align: center;
        animation: modalAppear 0.3s ease-out;
        border: 1px solid #e2e8f0;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    @keyframes modalAppear {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .celebration-stars {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .celebration-star {
        display: inline-block;
        margin: 0 0.125rem;
        opacity: 0.3;
        animation: starPop 0.6s ease-out;
    }

    .celebration-star.earned {
        opacity: 1;
        color: #fbbf24;
    }

    @keyframes starPop {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    .completion-title {
        color: #059669;
        margin-bottom: 1rem;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .completion-message {
        color: #64748b;
        margin-bottom: 1.5rem;
        font-size: 1rem;
        line-height: 1.5;
    }

    .completion-stats {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
    }

    .stat-label {
        color: #64748b;
        font-weight: 500;
    }

    .stat-value {
        color: #1e293b;
        font-weight: 600;
    }

    .completion-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .drawing-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
            padding: 1rem;
        }

        .drawing-workspace {
            padding: 1rem;
        }

        .drawing-toolbar {
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
        }

        .toolbar-section {
            min-width: auto;
        }

        .color-palette {
            grid-template-columns: repeat(4, 1fr);
            max-width: 160px;
        }

        .drawing-controls {
            flex-direction: column;
            align-items: center;
            padding: 1rem;
        }

        .control-btn {
            width: 100%;
            max-width: 280px;
            justify-content: center;
        }

        .canvas-container {
            min-height: 400px;
        }
    }
    </style>

    <script>
    let canvas, ctx;
    let isDrawing = false;
    let startTime = Date.now();
    let drawingHistory = [];
    let historyIndex = -1;
    let currentTool = @json($currentTool);
    let currentColor = @json($currentColor);
    let brushSize = @json($brushSize);

    document.addEventListener('DOMContentLoaded', function() {
        initializeCanvas();
        loadExistingDrawing();
        startTimer();
    });

    function initializeCanvas() {
        canvas = document.getElementById('drawingCanvas');
        ctx = canvas.getContext('2d');

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        // Touch events
        canvas.addEventListener('touchstart', (e) => { e.preventDefault(); startDrawing(e.touches[0]); }, { passive: false });
        canvas.addEventListener('touchmove', (e) => { e.preventDefault(); draw(e.touches[0]); }, { passive: false });
        canvas.addEventListener('touchend', stopDrawing);

        document.getElementById('loadingOverlay').style.display = 'none';

        // Save blank initial state
        pushHistory();
    }

    function resizeCanvas() {
        const container = canvas.parentElement;
        const rect = container.getBoundingClientRect();
        // Preserve existing drawing
        const imageData = canvas.width > 0 ? canvas.toDataURL() : null;
        canvas.width = rect.width;
        canvas.height = rect.height;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.imageSmoothingEnabled = true;
        if (imageData) {
            const img = new Image();
            img.onload = () => ctx.drawImage(img, 0, 0);
            img.src = imageData;
        }
    }

    function startDrawing(e) {
        isDrawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!isDrawing) return;
        const pos = getPos(e);
        ctx.globalCompositeOperation = currentTool === 'eraser' ? 'destination-out' : 'source-over';
        ctx.strokeStyle = currentColor;
        ctx.lineWidth = brushSize;
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }

    function stopDrawing() {
        if (!isDrawing) return;
        isDrawing = false;
        ctx.beginPath(); // Reset path so next stroke is independent
        pushHistory();
    }

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: (e.clientX - rect.left) * (canvas.width / rect.width),
            y: (e.clientY - rect.top) * (canvas.height / rect.height)
        };
    }

    function pushHistory() {
        drawingHistory = drawingHistory.slice(0, historyIndex + 1);
        drawingHistory.push(canvas.toDataURL());
        historyIndex = drawingHistory.length - 1;
        if (drawingHistory.length > 30) {
            drawingHistory.shift();
            historyIndex--;
        }
    }

    function undoDrawing() {
        if (historyIndex <= 0) return;
        historyIndex--;
        restoreHistory(drawingHistory[historyIndex]);
    }

    function redoDrawing() {
        if (historyIndex >= drawingHistory.length - 1) return;
        historyIndex++;
        restoreHistory(drawingHistory[historyIndex]);
    }

    function restoreHistory(dataUrl) {
        const img = new Image();
        img.onload = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0);
        };
        img.src = dataUrl;
    }

    function clearCanvas() {
        if (!confirm('Clear your drawing? This cannot be undone.')) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawingHistory = [];
        historyIndex = -1;
        pushHistory();
    }

    // Called only when user clicks Save Progress
    function saveDrawing() {
        const canvasData = canvas.toDataURL();
        const timeSpent = Math.floor((Date.now() - startTime) / 1000);
        Livewire.dispatch('saveProgress', { canvasData, timeSpent });
    }

    // Called only when user clicks Complete Drawing
    function completeDrawing() {
        const canvasData = canvas.toDataURL();
        Livewire.dispatch('completeDrawing', { canvasData });
    }

    function loadExistingDrawing() {
        const existingData = @json($canvasData);
        if (existingData) {
            const img = new Image();
            img.onload = function() {
                ctx.drawImage(img, 0, 0);
                pushHistory();
            };
            img.src = existingData;
        }
    }

    function startTimer() {
        const existingTime = @json($timeSpent);
        startTime = Date.now() - (existingTime * 1000);

        setInterval(() => {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
            const seconds = (elapsed % 60).toString().padStart(2, '0');
            const el = document.getElementById('timer-display');
            if (el) el.textContent = minutes + ':' + seconds;
        }, 1000);
    }

    // Listen for tool/color/size changes from Livewire
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('toolChanged', ({ tool }) => { currentTool = tool; });
        Livewire.on('colorChanged', ({ color }) => { currentColor = color; });
        Livewire.on('sizeChanged', ({ size }) => { brushSize = size; });
        Livewire.on('clearCanvas', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            drawingHistory = [];
            historyIndex = -1;
            pushHistory();
        });
    });
    </script>
</div>