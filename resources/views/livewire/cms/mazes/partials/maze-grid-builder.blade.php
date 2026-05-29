{{-- Client-side grid editor (wire:ignore) — avoids 100+ Livewire round-trips per edit session --}}
@php
    $builderConfig = [
        'grid' => $grid,
        'rows' => (int) $grid_rows,
        'cols' => (int) $grid_cols,
        'start' => $start_position,
        'end' => $end_position,
        'collectibles' => $collectibles,
        'mode' => $gridMode,
    ];
@endphp

<div wire:ignore id="maze-grid-builder-root" class="maze-grid-builder-root">
    <div style="overflow-x:auto;padding-bottom:8px">
        <div id="maze-grid-builder-cells" class="maze-grid" style="grid-template-columns:repeat({{ (int) $grid_cols }}, 28px)"></div>
    </div>
    <div id="maze-grid-builder-status" style="margin-top:12px;font-size:11px;color:var(--cms-text-muted)"></div>
</div>

<script type="application/json" id="maze-grid-builder-config">@json($builderConfig)</script>
<script>
(function () {
    var PATH = 0;
    var WALL = 1;

    function readConfig() {
        var el = document.getElementById('maze-grid-builder-config');
        if (!el) return null;
        try { return JSON.parse(el.textContent); } catch (e) { return null; }
    }

    function wallCount(grid) {
        var n = 0;
        grid.forEach(function (row) {
            row.forEach(function (cell) { if (cell === WALL) n += 1; });
        });
        return n;
    }

    function defaultGrid(rows, cols) {
        var grid = [];
        for (var r = 0; r < rows; r++) {
            grid[r] = [];
            for (var c = 0; c < cols; c++) {
                grid[r][c] = (r === 0 || r === rows - 1 || c === 0 || c === cols - 1) ? WALL : PATH;
            }
        }
        return grid;
    }

    function findWire() {
        var root = document.getElementById('maze-grid-builder-root');
        if (!root) return null;
        var host = root.closest('[wire\\:id]');
        var id = host && host.getAttribute('wire:id');
        return id && window.Livewire ? window.Livewire.find(id) : null;
    }

    function MazeGridBuilder(cfg) {
        this.rows = cfg.rows || 10;
        this.cols = cfg.cols || 10;
        this.grid = (cfg.grid && cfg.grid.length) ? cfg.grid : defaultGrid(this.rows, this.cols);
        this.startPos = cfg.start || { row: 0, col: 1 };
        this.endPos = cfg.end || { row: this.rows - 1, col: this.cols - 2 };
        this.collectibles = cfg.collectibles || [];
        this.mode = cfg.mode || 'toggle';
        this.cellsEl = document.getElementById('maze-grid-builder-cells');
        this.statusEl = document.getElementById('maze-grid-builder-status');
    }

    MazeGridBuilder.prototype.setMode = function (mode) {
        this.mode = mode;
    };

    MazeGridBuilder.prototype.rebuild = function (rows, cols) {
        this.rows = Math.max(5, Math.min(20, parseInt(rows, 10) || this.rows));
        this.cols = Math.max(5, Math.min(20, parseInt(cols, 10) || this.cols));
        this.grid = defaultGrid(this.rows, this.cols);
        this.startPos = { row: 0, col: 1 };
        this.endPos = { row: this.rows - 1, col: this.cols - 2 };
        this.render();
    };

    MazeGridBuilder.prototype.fillWalls = function () {
        for (var r = 0; r < this.rows; r++) {
            for (var c = 0; c < this.cols; c++) this.grid[r][c] = WALL;
        }
        this.render();
    };

    MazeGridBuilder.prototype.clearWalls = function () {
        for (var r = 0; r < this.rows; r++) {
            for (var c = 0; c < this.cols; c++) this.grid[r][c] = PATH;
        }
        this.render();
    };

    MazeGridBuilder.prototype.handleClick = function (row, col) {
        if (this.mode === 'start') {
            this.grid[row][col] = PATH;
            this.startPos = { row: row, col: col };
        } else if (this.mode === 'end') {
            this.grid[row][col] = PATH;
            this.endPos = { row: row, col: col };
        } else {
            if (row === this.startPos.row && col === this.startPos.col) return;
            if (row === this.endPos.row && col === this.endPos.col) return;
            this.grid[row][col] = this.grid[row][col] ? PATH : WALL;
        }
        this.render();
    };

    MazeGridBuilder.prototype.render = function () {
        if (!this.cellsEl) return;
        this.cellsEl.style.gridTemplateColumns = 'repeat(' + this.cols + ', 28px)';
        var html = '';
        for (var r = 0; r < this.rows; r++) {
            for (var c = 0; c < this.cols; c++) {
                var cell = this.grid[r][c];
                var isStart = this.startPos.row === r && this.startPos.col === c;
                var isEnd = this.endPos.row === r && this.endPos.col === c;
                var collectible = this.collectibles.find(function (col) {
                    return parseInt(col.row, 10) === r && parseInt(col.col, 10) === c;
                });
                var cls = 'maze-cell ';
                if (isStart) cls += 'start';
                else if (isEnd) cls += 'end';
                else if (collectible) cls += 'collectible';
                else cls += (cell === WALL ? 'wall' : 'path');
                html += '<button type="button" class="' + cls + '" data-r="' + r + '" data-c="' + c + '" title="Row ' + r + ', Col ' + c + '">';
                if (isStart) html += '🟢';
                else if (isEnd) html += '🔴';
                else if (collectible) html += (collectible.emoji || '💎');
                html += '</button>';
            }
        }
        this.cellsEl.innerHTML = html;
        var self = this;
        this.cellsEl.querySelectorAll('button[data-r]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                self.handleClick(parseInt(btn.getAttribute('data-r'), 10), parseInt(btn.getAttribute('data-c'), 10));
            });
        });
        if (this.statusEl) {
            this.statusEl.textContent = 'Grid: ' + this.rows + '×' + this.cols
                + ' • Start: (' + this.startPos.row + ', ' + this.startPos.col + ')'
                + ' • End: (' + this.endPos.row + ', ' + this.endPos.col + ')'
                + ' • Walls: ' + wallCount(this.grid);
        }
    };

    MazeGridBuilder.prototype.syncToLivewire = function () {
        var wire = findWire();
        if (!wire) return Promise.resolve();
        return Promise.all([
            wire.set('grid', this.grid),
            wire.set('start_position', this.startPos),
            wire.set('end_position', this.endPos),
            wire.set('grid_rows', this.rows),
            wire.set('grid_cols', this.cols),
        ]);
    };

    function initBuilder() {
        var cfg = readConfig();
        if (!cfg) return;
        window.__mazeGridBuilder = new MazeGridBuilder(cfg);
        window.__mazeGridBuilder.render();
    }

    window.mazeGridBuilderSetMode = function (mode) {
        if (window.__mazeGridBuilder) {
            window.__mazeGridBuilder.setMode(mode);
        }
    };

    window.mazeGridBuilderFillWalls = function () {
        if (window.__mazeGridBuilder) window.__mazeGridBuilder.fillWalls();
    };

    window.mazeGridBuilderClearWalls = function () {
        if (window.__mazeGridBuilder) window.__mazeGridBuilder.clearWalls();
    };

    window.mazeGridBuilderResize = function (rows, cols) {
        if (window.__mazeGridBuilder) window.__mazeGridBuilder.rebuild(rows, cols);
    };

    window.mazeGridBuilderSyncBeforeSave = function () {
        if (!window.__mazeGridBuilder) return Promise.resolve();
        return window.__mazeGridBuilder.syncToLivewire();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBuilder);
    } else {
        initBuilder();
    }

    document.addEventListener('livewire:navigated', function () {
        window.__mazeGridBuilder = null;
        setTimeout(initBuilder, 50);
    });

    window.mazeGridBuilderApplySize = function () {
        var rows = document.getElementById('maze-grid-rows-input')?.value;
        var cols = document.getElementById('maze-grid-cols-input')?.value;
        if (window.mazeGridBuilderResize) {
            window.mazeGridBuilderResize(rows, cols);
        }
    };
})();
</script>
