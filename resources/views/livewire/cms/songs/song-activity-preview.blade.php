<div class="song-preview-container">
    <!-- Header with Preview Notice -->
    <div class="preview-header">
        <div class="preview-notice">
            🎭 <strong>PREVIEW MODE</strong> - This is how children will see the activity
        </div>
        <div class="preview-controls">
            <select wire:model.live="mode" class="mode-selector">
                <option value="karaoke">🎤 Karaoke Mode</option>
                @if($song->has_fill_blanks)
                <option value="fill_blanks">📝 Fill the Blanks</option>
                @endif
                <option value="lullaby">🌙 Lullaby Mode</option>
            </select>
            <button wire:click="resetActivity" class="reset-btn">🔄 Reset</button>
        </div>
    </div>

    <!-- Song Activity Player -->
    <div class="song-player" data-mode="{{ $mode }}">
        <!-- Song Header -->
        <div class="song-header">
            @if($song->cover_image_path)
                <img src="{{ asset('storage/' . $song->cover_image_path) }}" alt="Cover" class="song-cover">
            @else
                <div class="song-cover-placeholder">🎵</div>
            @endif
            <div class="song-info">
                <h1 class="song-title">{{ $song->title }}</h1>
                <p class="song-subtitle">{{ ucfirst($song->activity_type) }} • {{ $song->tribe->name ?? 'Unknown '.heritage('people') }}</p>
            </div>
            <div class="star-display">
                @for($i = 1; $i <= 5; $i++)
                    <span class="star {{ $i <= $starsEarned ? 'earned' : '' }}">⭐</span>
                @endfor
            </div>
        </div>

        <!-- Audio/Video Player -->
        @if($song->audio_path || $song->video_path)
        <div class="media-player">
            @if($song->video_path)
                <video id="songMedia" controls class="media-element">
                    <source src="{{ asset('storage/' . $song->video_path) }}" type="video/mp4">
                </video>
            @elseif($song->audio_path)
                <audio id="songMedia" controls class="media-element">
                    <source src="{{ asset('storage/' . $song->audio_path) }}" type="audio/mpeg">
                </audio>
            @endif
        </div>
        @endif

        <!-- Karaoke Mode -->
        @if($mode === 'karaoke')
        <div class="karaoke-mode">
            <div class="lyrics-display">
                @if($song->lyricSegments->count() > 0)
                    @foreach($song->lyricSegments as $index => $segment)
                    <div class="lyric-segment {{ $activeSegmentIndex === $index ? 'active' : '' }}" 
                         data-start="{{ $segment->start_time }}" 
                         data-end="{{ $segment->end_time }}">
                        <span class="segment-text">{{ $segment->segment_text }}</span>
                        <span class="segment-timing">{{ $segment->start_time }}s</span>
                    </div>
                    @endforeach
                @else
                    <div class="lyrics-fallback">
                        <pre>{{ $song->lyrics ?: 'No lyrics available' }}</pre>
                    </div>
                @endif
            </div>
            
            <div class="karaoke-controls">
                <button class="big-btn primary" onclick="simulateKaraoke()">🎤 Start Singing!</button>
                <div class="participation-meter">
                    <div class="meter-label">Participation</div>
                    <div class="meter-bar">
                        <div class="meter-fill" style="width: {{ rand(60, 95) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Fill the Blanks Mode -->
        @if($mode === 'fill_blanks')
        <div class="fill-blanks-mode">
            <div class="game-instructions">
                <h3>🎯 Fill in the Missing Words!</h3>
                <p>Listen to the song and fill in the blanks with the correct words.</p>
            </div>
            
            <div class="blanks-container">
                @foreach($song->lyricSegments as $segment)
                    @if($segment->is_fill_blank)
                    <div class="blank-question">
                        <div class="segment-audio">
                            <button class="play-segment-btn" data-start="{{ $segment->start_time }}" data-end="{{ $segment->end_time }}">
                                ▶️ Play Segment
                            </button>
                        </div>
                        <div class="blank-text">
                            {{ str_replace($segment->blank_answer, '____', $segment->segment_text) }}
                        </div>
                        <input type="text" 
                               wire:model.live="userAnswers.{{ $segment->id }}" 
                               class="blank-input" 
                               placeholder="Type your answer">
                        <div class="answer-feedback">
                            @if(isset($userAnswers[$segment->id]) && trim($userAnswers[$segment->id]) !== '')
                                @if(strtolower(trim($userAnswers[$segment->id])) === strtolower(trim($segment->blank_answer)))
                                    <span class="correct">✅ Correct!</span>
                                @else
                                    <span class="incorrect">❌ Try again</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="regular-lyric">{{ $segment->segment_text }}</div>
                    @endif
                @endforeach
            </div>
            
            <div class="game-controls">
                <button wire:click="checkAnswers" class="big-btn primary">🎯 Check My Answers</button>
                @if($completed)
                <div class="completion-message">
                    🎉 Great job! You earned {{ $starsEarned }} stars!
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Lullaby Mode -->
        @if($mode === 'lullaby')
        <div class="lullaby-mode">
            <div class="lullaby-scene">
                <div class="floating-stars">
                    <span class="star-float">⭐</span>
                    <span class="star-float">✨</span>
                    <span class="star-float">🌟</span>
                </div>
                <div class="lullaby-message">
                    <h3>🌙 Sweet Dreams</h3>
                    <p>Close your eyes and listen to this peaceful song</p>
                </div>
                <div class="sleep-timer">
                    <label>Sleep Timer:</label>
                    <select class="timer-select">
                        <option>No timer</option>
                        <option>5 minutes</option>
                        <option>10 minutes</option>
                        <option>15 minutes</option>
                    </select>
                </div>
            </div>
            
            <div class="lullaby-lyrics">
                @if($song->lyrics)
                <div class="gentle-lyrics">{{ $song->lyrics }}</div>
                @endif
            </div>
            
            <div class="lullaby-controls">
                <button class="big-btn gentle" wire:click="simulateCompletion">🌙 Start Lullaby</button>
            </div>
        </div>
        @endif

        <!-- Completion Celebration -->
        @if($completed)
        <div class="completion-overlay">
            <div class="celebration">
                <div class="celebration-stars">
                    @for($i = 1; $i <= $starsEarned; $i++)
                        <span class="celebration-star">⭐</span>
                    @endfor
                </div>
                <h2>🎉 Amazing Work!</h2>
                <p>You earned {{ $starsEarned }} stars!</p>
                <button wire:click="resetActivity" class="big-btn primary">🔄 Try Again</button>
            </div>
        </div>
        @endif
    </div>

    <style>
    .song-preview-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        font-family: 'Comic Sans MS', cursive, sans-serif;
    }

    .preview-header {
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .preview-notice {
        font-size: 0.9rem;
        background: #ff6b35;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }

    .preview-controls {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .mode-selector, .timer-select {
        padding: 0.5rem;
        border-radius: 10px;
        border: none;
        background: white;
        font-size: 0.9rem;
    }

    .reset-btn {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .song-player {
        max-width: 800px;
        margin: 2rem auto;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        overflow: hidden;
        position: relative;
    }

    .song-header {
        background: linear-gradient(45deg, #ff6b35, #f7931e);
        color: white;
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .song-cover {
        width: 80px;
        height: 80px;
        border-radius: 15px;
        object-fit: cover;
        border: 3px solid white;
    }

    .song-cover-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 15px;
        background: var(--cms-surface-raised);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        border: 3px solid white;
    }

    .song-info {
        flex: 1;
    }

    .song-title {
        font-size: 1.8rem;
        font-weight: bold;
        margin: 0 0 0.5rem 0;
    }

    .song-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        margin: 0;
    }

    .star-display {
        display: flex;
        gap: 0.25rem;
    }

    .star {
        font-size: 1.5rem;
        opacity: 0.3;
        transition: all 0.3s;
    }

    .star.earned {
        opacity: 1;
        animation: starPulse 0.6s ease-in-out;
    }

    @keyframes starPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }

    .media-player {
        padding: 1.5rem;
        background: #f8f9fa;
    }

    .media-element {
        width: 100%;
        border-radius: 10px;
    }

    .karaoke-mode, .fill-blanks-mode, .lullaby-mode {
        padding: 2rem;
    }

    .lyrics-display {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        max-height: 300px;
        overflow-y: auto;
    }

    .lyric-segment {
        padding: 0.75rem;
        margin: 0.5rem 0;
        border-radius: 10px;
        transition: all 0.3s;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .lyric-segment.active {
        background: #fff3cd;
        border: 2px solid #ffc107;
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(255,193,7,0.3);
    }

    .segment-text {
        font-size: 1.1rem;
        font-weight: 500;
    }

    .segment-timing {
        font-size: 0.8rem;
        color: #666;
        background: #e9ecef;
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
    }

    .lyrics-fallback pre {
        font-family: inherit;
        white-space: pre-wrap;
        font-size: 1.1rem;
        line-height: 1.6;
    }

    .karaoke-controls {
        text-align: center;
    }

    .big-btn {
        font-size: 1.2rem;
        padding: 1rem 2rem;
        border: none;
        border-radius: 15px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s;
        margin: 0.5rem;
    }

    .big-btn.primary {
        background: linear-gradient(45deg, #4CAF50, #45a049);
        color: white;
    }

    .big-btn.gentle {
        background: linear-gradient(45deg, #9c88ff, #8c7ae6);
        color: white;
    }

    .big-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    }

    .participation-meter {
        margin-top: 1rem;
        text-align: center;
    }

    .meter-label {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .meter-bar {
        background: #e9ecef;
        height: 20px;
        border-radius: 10px;
        overflow: hidden;
        max-width: 300px;
        margin: 0 auto;
    }

    .meter-fill {
        background: linear-gradient(45deg, #4CAF50, #45a049);
        height: 100%;
        transition: width 0.5s ease;
    }

    .game-instructions {
        text-align: center;
        margin-bottom: 2rem;
    }

    .game-instructions h3 {
        color: #333;
        margin-bottom: 0.5rem;
    }

    .blanks-container {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .blank-question {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        margin: 1rem 0;
        border: 2px solid #e9ecef;
    }

    .segment-audio {
        margin-bottom: 0.5rem;
    }

    .play-segment-btn {
        background: #17a2b8;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .blank-text {
        font-size: 1.1rem;
        margin: 0.5rem 0;
        line-height: 1.6;
    }

    .blank-input {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        margin: 0.5rem 0;
    }

    .blank-input:focus {
        border-color: #4CAF50;
        outline: none;
    }

    .answer-feedback {
        margin-top: 0.5rem;
    }

    .correct {
        color: #4CAF50;
        font-weight: bold;
    }

    .incorrect {
        color: #f44336;
        font-weight: bold;
    }

    .regular-lyric {
        padding: 0.5rem;
        color: #666;
        font-style: italic;
    }

    .game-controls {
        text-align: center;
    }

    .completion-message {
        background: #d4edda;
        color: #155724;
        padding: 1rem;
        border-radius: 10px;
        margin-top: 1rem;
        font-weight: bold;
        text-align: center;
    }

    .lullaby-mode {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        position: relative;
        overflow: hidden;
    }

    .lullaby-scene {
        text-align: center;
        padding: 2rem;
        position: relative;
    }

    .floating-stars {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
    }

    .star-float {
        position: absolute;
        font-size: 1.5rem;
        animation: float 6s ease-in-out infinite;
    }

    .star-float:nth-child(1) { top: 20%; left: 20%; animation-delay: 0s; }
    .star-float:nth-child(2) { top: 60%; right: 30%; animation-delay: 2s; }
    .star-float:nth-child(3) { bottom: 30%; left: 60%; animation-delay: 4s; }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    .lullaby-message h3 {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .sleep-timer {
        margin-top: 2rem;
    }

    .gentle-lyrics {
        background: var(--cms-surface-raised);
        border-radius: 10px;
        padding: 1.5rem;
        margin: 1rem 0;
        font-size: 1.1rem;
        line-height: 1.8;
        text-align: center;
    }

    .lullaby-controls {
        text-align: center;
        padding: 1rem;
    }

    .completion-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
    }

    .celebration {
        background: white;
        border-radius: 20px;
        padding: 3rem;
        text-align: center;
        max-width: 400px;
    }

    .celebration-stars {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .celebration-star {
        display: inline-block;
        animation: bounce 0.6s ease-in-out infinite alternate;
    }

    .celebration-star:nth-child(2) { animation-delay: 0.1s; }
    .celebration-star:nth-child(3) { animation-delay: 0.2s; }
    .celebration-star:nth-child(4) { animation-delay: 0.3s; }
    .celebration-star:nth-child(5) { animation-delay: 0.4s; }

    @keyframes bounce {
        from { transform: translateY(0px); }
        to { transform: translateY(-10px); }
    }

    .celebration h2 {
        color: #4CAF50;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .song-player {
            margin: 1rem;
            border-radius: 15px;
        }
        
        .song-header {
            padding: 1.5rem;
            flex-direction: column;
            text-align: center;
        }
        
        .karaoke-mode, .fill-blanks-mode, .lullaby-mode {
            padding: 1rem;
        }
        
        .preview-header {
            flex-direction: column;
            text-align: center;
        }
    }
    </style>

    <script>
    function simulateKaraoke() {
        // Simulate karaoke participation
        const meter = document.querySelector('.meter-fill');
        if (meter) {
            let width = 0;
            const interval = setInterval(() => {
                width += Math.random() * 10;
                if (width >= 85) {
                    width = 85 + Math.random() * 10;
                    clearInterval(interval);
                    // Simulate completion
                    setTimeout(() => {
                        @this.call('simulateCompletion');
                    }, 2000);
                }
                meter.style.width = width + '%';
            }, 200);
        }
    }

    // Simulate audio segment playback
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('play-segment-btn')) {
            const start = parseFloat(e.target.dataset.start);
            const end = parseFloat(e.target.dataset.end);
            const media = document.getElementById('songMedia');
            
            if (media) {
                media.currentTime = start;
                media.play();
                
                // Stop at end time
                const checkTime = setInterval(() => {
                    if (media.currentTime >= end) {
                        media.pause();
                        clearInterval(checkTime);
                    }
                }, 100);
            }
            
            e.target.textContent = '⏸️ Playing...';
            setTimeout(() => {
                e.target.textContent = '▶️ Play Segment';
            }, (end - start) * 1000);
        }
    });
    </script>
</div>