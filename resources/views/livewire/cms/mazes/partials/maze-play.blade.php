@php
    $playGrid = $maze->grid ?? [];
    $playRows = (int) $maze->grid_rows;
    $playCols = (int) $maze->grid_cols;
    $playOk = $playRows > 0 && $playCols > 0 && count($playGrid) === $playRows;
@endphp

@if($playOk)
<style>
    .mz-play-section { margin-top: var(--sp-5); padding: 20px; background: var(--cms-surface); border: 1px solid var(--cms-border); border-radius: 16px; }
    .mz-play-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 14px; }
    .mz-play-title { font-size: 13px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: var(--cms-text-muted); margin-bottom: 6px; }
    .mz-play-sub { font-size: 12px; color: var(--cms-text-muted); margin: 0; line-height: 1.5; max-width: 640px; }
    .mz-play-actions { display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap; }
    .mz-play-btn { font-size: 12px; font-weight: 700; padding: 8px 14px; border-radius: 10px; cursor: pointer; border: 1px solid rgba(212,160,23,.45); background: rgba(212,160,23,.18); color: #F2CB5A; }
    .mz-play-btn-secondary { border-color: var(--cms-border); background: var(--cms-surface-raised); color: var(--cms-text); }
    .mz-play-win { background: rgba(74,124,89,.2); border: 1px solid rgba(74,124,89,.45); color: #a8d4b0; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 700; margin-bottom: 12px; }
    .mz-play-body { display: flex; flex-direction: column; align-items: center; gap: 16px; }
    .mz-play-board { display: grid; gap: 1px; background: var(--cms-border); padding: 4px; border-radius: 10px; border: 1px solid var(--cms-border); }
    .mz-play-cell { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 18px; border: 1px solid rgba(255,255,255,.08); }
    .mz-play-cell.path { background: #B8BDC6; }
    .mz-play-cell.wall { background: #fff; }
    .mz-play-cell.fog { background: #4B5563; opacity: .5; }
    .mz-play-controls { display: grid; grid-template-columns: repeat(3, 48px); gap: 6px; }
    .mz-play-controls button { width: 48px; height: 48px; border-radius: 10px; border: 1px solid rgba(212,160,23,.45); background: rgba(196,75,43,.85); color: #fff; font-size: 20px; font-weight: 700; cursor: pointer; }
    .mz-play-controls button:disabled { opacity: .4; cursor: not-allowed; }
    .mz-play-meta { font-size: 11px; color: var(--cms-text-muted); text-align: center; }
</style>

<div class="mz-play-section" wire:ignore data-mz-play-root>
    <div class="mz-play-head">
        <div>
            <div class="mz-play-title">Try this maze</div>
            <p class="mz-play-sub">Use the arrow buttons to move 👥 along gray paths to 🚩 — same rules as the child app (0 = path, 1 = wall).</p>
        </div>
        <div class="mz-play-actions">
            <button type="button" class="mz-play-btn" id="mz-play-reset">Reset</button>
            <button type="button" class="mz-play-btn mz-play-btn-secondary" id="mz-play-focus">Focus board</button>
        </div>
    </div>
    <div class="mz-play-win" id="mz-play-win" hidden>Maze completed — path reaches the goal.</div>
    <div class="mz-play-body">
        <div id="mz-play-board" class="mz-play-board"></div>
        <div class="mz-play-controls" aria-label="Move">
            <span></span>
            <button type="button" data-dir="up" title="Up">↑</button>
            <span></span>
            <button type="button" data-dir="left" title="Left">←</button>
            <span></span>
            <button type="button" data-dir="right" title="Right">→</button>
            <span></span>
            <button type="button" data-dir="down" title="Down">↓</button>
            <span></span>
        </div>
        <div class="mz-play-meta" id="mz-play-meta"></div>
    </div>
</div>

@push('scripts')
<script type="application/json" id="mz-play-json">{!! json_encode([
    'grid' => $playGrid,
    'rows' => $playRows,
    'cols' => $playCols,
    'start' => $maze->start_position,
    'end' => $maze->end_position,
    'collectibles' => $maze->collectibles ?? [],
    'mazeType' => $maze->maze_type,
    'timeLimit' => $maze->time_limit_seconds,
    'visibilityRadius' => $maze->visibility_radius ?? 2,
], JSON_THROW_ON_ERROR) !!}</script>
<script>
(function () {
    var PATH = 0;
    var WALL = 1;
    var PLAYER = '👥';

    function readCfg() {
        var el = document.getElementById('mz-play-json');
        if (!el) return null;
        try { return JSON.parse(el.textContent); } catch (e) { return null; }
    }

    function initMazePlay() {
        var root = document.querySelector('[data-mz-play-root]');
        var cfg = readCfg();
        if (!root || !cfg || root.getAttribute('data-mz-inited') === '1') return;

        var grid = cfg.grid.map(function (row) { return row.slice(); });
        var rows = cfg.rows;
        var cols = cfg.cols;
        if (!grid || !grid.length) return;

        root.setAttribute('data-mz-inited', '1');

        var start = cfg.start || { row: 0, col: 1 };
        var end = cfg.end || { row: rows - 1, col: cols - 1 };
        if (grid[start.row] && grid[start.row][start.col] !== undefined) grid[start.row][start.col] = PATH;
        if (grid[end.row] && grid[end.row][end.col] !== undefined) grid[end.row][end.col] = PATH;
        var collectibles = cfg.collectibles || [];
        var mazeType = cfg.mazeType || 'standard';
        var visibilityRadius = parseInt(cfg.visibilityRadius, 10) || 2;
        var timeLimit = cfg.timeLimit ? parseInt(cfg.timeLimit, 10) : null;

        var player = { row: start.row, col: start.col };
        var collected = {};
        var timedOut = false;
        var timer = null;
        var timeLeft = timeLimit;

        var boardEl = document.getElementById('mz-play-board');
        var winEl = document.getElementById('mz-play-win');
        var metaEl = document.getElementById('mz-play-meta');
        var btnReset = document.getElementById('mz-play-reset');
        var btnFocus = document.getElementById('mz-play-focus');

        function key(r, c) { return r + '-' + c; }

        function requiredLeft() {
            return collectibles.filter(function (item) {
                return item.required && !collected[key(item.row, item.col)];
            }).length;
        }

        function isVisible(r, c) {
            if (mazeType !== 'visibility') return true;
            if (grid[r][c] === WALL) return false;
            if (r === start.row && c === start.col) return true;
            if (r === end.row && c === end.col) return true;
            return Math.abs(r - player.row) + Math.abs(c - player.col) <= visibilityRadius;
        }

        function canWin() {
            if (player.row !== end.row || player.col !== end.col) return false;
            var needs = mazeType === 'collect_items' || collectibles.some(function (c) { return c.required; });
            return !needs || requiredLeft() === 0;
        }

        function render() {
            boardEl.style.gridTemplateColumns = 'repeat(' + cols + ', 32px)';
            var html = '';
            for (var r = 0; r < rows; r++) {
                for (var c = 0; c < cols; c++) {
                    var isStart = r === start.row && c === start.col;
                    var isEnd = r === end.row && c === end.col;
                    var isPlayer = r === player.row && c === player.col;
                    var wall = grid[r][c] === WALL && !isStart && !isEnd;
                    var visible = isVisible(r, c);
                    var item = collectibles.find(function (col) {
                        return parseInt(col.row, 10) === r && parseInt(col.col, 10) === c && !collected[key(r, c)];
                    });
                    var cls = 'mz-play-cell ';
                    if (!visible && !wall && !isStart && !isEnd) cls += 'fog';
                    else if (wall) cls += 'wall';
                    else cls += 'path';
                    var label = '';
                    if (visible && isPlayer) label = PLAYER;
                    else if (visible && isStart && !isPlayer) label = '🟢';
                    else if (visible && isEnd && !isPlayer) label = '🚩';
                    else if (visible && item) label = item.emoji || '💎';
                    html += '<div class="' + cls + '">' + label + '</div>';
                }
            }
            boardEl.innerHTML = html;
            if (winEl) winEl.hidden = !canWin();
            if (metaEl) {
                var parts = ['Pos: (' + player.row + ', ' + player.col + ')'];
                if (requiredLeft() > 0) parts.push('Items left: ' + requiredLeft());
                if (timeLimit) parts.push('Time: ' + timeLeft + 's');
                metaEl.textContent = parts.join(' • ');
            }
        }

        function tryMove(dr, dc) {
            if (timedOut || canWin()) return;
            var nr = player.row + (mazeType === 'reverse' ? -dr : dr);
            var nc = player.col + (mazeType === 'reverse' ? -dc : dc);
            if (mazeType === 'circular') {
                if (nr < 0) nr = rows - 1;
                if (nr >= rows) nr = 0;
                if (nc < 0) nc = cols - 1;
                if (nc >= cols) nc = 0;
            } else if (nr < 0 || nr >= rows || nc < 0 || nc >= cols) {
                return;
            }
            if (grid[nr][nc] !== PATH) return;
            player = { row: nr, col: nc };
            collectibles.forEach(function (item) {
                if (parseInt(item.row, 10) === nr && parseInt(item.col, 10) === nc) {
                    collected[key(nr, nc)] = true;
                }
            });
            render();
        }

        function reset() {
            player = { row: start.row, col: start.col };
            collected = {};
            timedOut = false;
            timeLeft = timeLimit;
            if (timer) clearInterval(timer);
            if (timeLimit && mazeType === 'timed') {
                timer = setInterval(function () {
                    timeLeft -= 1;
                    if (timeLeft <= 0) {
                        timedOut = true;
                        clearInterval(timer);
                    }
                    render();
                }, 1000);
            }
            render();
        }

        document.querySelectorAll('.mz-play-controls [data-dir]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var d = btn.getAttribute('data-dir');
                if (d === 'up') tryMove(-1, 0);
                if (d === 'down') tryMove(1, 0);
                if (d === 'left') tryMove(0, -1);
                if (d === 'right') tryMove(0, 1);
            });
        });

        if (btnReset) btnReset.addEventListener('click', reset);
        if (btnFocus) btnFocus.addEventListener('click', function () { boardEl.scrollIntoView({ behavior: 'smooth', block: 'center' }); });

        reset();
    }

    function resetMazePlay() {
        document.querySelectorAll('[data-mz-play-root]').forEach(function (el) {
            el.removeAttribute('data-mz-inited');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMazePlay);
    } else {
        initMazePlay();
    }
    document.addEventListener('livewire:navigated', function () {
        resetMazePlay();
        setTimeout(initMazePlay, 50);
    });
})();
</script>
@endpush
@endif
