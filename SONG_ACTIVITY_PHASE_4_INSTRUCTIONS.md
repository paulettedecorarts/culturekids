# Song Activity System - Phase 4: Frontend Player Implementation

## Overview
This phase implements the frontend player interface where children interact with song activities. This includes karaoke players, fill-the-blank games, lullaby modes, and progress tracking.

## Phase 4 Components to Implement

### 1. Core Song Player Component
**File**: `app/Livewire/Player/SongPlayer.php`
**View**: `resources/views/livewire/player/song-player.blade.php`

**Features**:
- Audio/video playback controls
- Real-time lyric display
- Activity type switching (karaoke, lullaby, fill-blanks)
- Progress tracking and star collection
- Child-friendly interface with large buttons
- Responsive design for tablets/mobile

**Properties**:
```php
public Song $song;
public string $activityMode = 'karaoke'; // karaoke, lullaby, fill_blanks
public float $currentTime = 0;
public bool $isPlaying = false;
public array $userAnswers = [];
public int $starsEarned = 0;
public bool $completed = false;
```

### 2. Karaoke Mode Interface
**Component**: Part of `SongPlayer`

**Features**:
- Highlight current lyric segment based on audio time
- Visual feedback for singing along
- Microphone input detection (optional)
- Timing accuracy scoring
- Animated text highlighting

**Implementation Notes**:
- Use JavaScript to sync audio currentTime with lyric segments
- Highlight active segment with CSS animations
- Track user participation percentage
- Award stars based on engagement time

### 3. Fill-the-Blanks Game Mode
**Component**: Part of `SongPlayer`

**Features**:
- Display lyrics with blank spaces
- Input fields for missing words
- Real-time validation
- Hint system
- Correct/incorrect feedback
- Score calculation

**Implementation Notes**:
- Load segments where `is_fill_blank = true`
- Replace `blank_answer` text with input fields
- Validate answers on submit or real-time
- Provide audio replay for each segment
- Track correct answers for star calculation

### 4. Lullaby Mode Interface
**Component**: Part of `SongPlayer`

**Features**:
- Gentle, calming interface
- Soft colors and animations
- Sleep timer functionality
- Volume fade-out
- Minimal interaction required
- Bedtime-appropriate visuals

**Implementation Notes**:
- Simplified controls for younger children
- Auto-play functionality
- Gentle visual effects (floating stars, soft gradients)
- Parent controls for timer settings

### 5. Progress Tracking System
**Files**: 
- `app/Services/SongActivityTracker.php`
- Database: Use existing `song_activities` table

**Features**:
- Track completion percentage
- Calculate stars earned (1-5 based on performance)
- Save progress to database
- Resume functionality
- Achievement unlocking

**Tracking Metrics**:
- **Karaoke**: Listening time percentage, engagement duration
- **Fill-blanks**: Correct answers ratio, attempts count
- **Lullaby**: Full listening completion, calm interaction

### 6. Child-Friendly UI Components
**Files**: 
- `resources/views/components/player/play-button.blade.php`
- `resources/views/components/player/progress-bar.blade.php`
- `resources/views/components/player/star-display.blade.php`

**Features**:
- Large, colorful buttons
- Visual progress indicators
- Animated star collection
- Cultural theme integration
- Accessibility compliance (WCAG)

## Technical Implementation Steps

### Step 1: Create Base Song Player
```bash
php artisan make:livewire Player/SongPlayer
```

### Step 2: Add Player Routes
Add to `routes/web.php`:
```php
// Child/Student routes (public or authenticated)
Route::get('/play/songs/{song}', \App\Livewire\Player\SongPlayer::class)->name('songs.play');
```

### Step 3: JavaScript Audio Integration
Create `resources/js/song-player.js`:
- HTML5 Audio API integration
- Real-time currentTime tracking
- Lyric synchronization
- Playback controls

### Step 4: CSS Animations
Create `resources/css/song-player.css`:
- Lyric highlighting animations
- Star collection effects
- Child-friendly color schemes
- Responsive layouts

### Step 5: Activity Tracking Service
```php
class SongActivityTracker
{
    public function startActivity(Song $song, User $user): SongActivity
    public function updateProgress(SongActivity $activity, array $data): void
    public function completeActivity(SongActivity $activity): int // returns stars
    public function calculateStars(string $activityType, array $data): int
}
```

## Database Considerations

### Existing Tables (Already Created)
- `song_activities` - Track user progress
- `song_lyric_segments` - Timed lyric data
- `songs` - Activity configuration

### Additional Fields Needed
Consider adding to `song_activities`:
```sql
ALTER TABLE song_activities ADD COLUMN session_data JSON NULL;
ALTER TABLE song_activities ADD COLUMN last_position DECIMAL(8,3) DEFAULT 0;
```

## Frontend Integration Points

### 1. Activity Library Integration
- Link from activity browser to song player
- Filter songs by age/difficulty
- Show completion status and stars earned

### 2. Teacher Dashboard Integration
- View student progress on song activities
- Generate reports on engagement
- Assign specific songs to students

### 3. Parent Portal Integration
- Home practice mode
- Progress sharing
- Offline download capabilities

## User Experience Flow

### Karaoke Mode Flow
1. Child selects song from library
2. Song loads with cover image and title
3. Audio begins, lyrics appear
4. Current segment highlights in real-time
5. Child sings along (optional mic feedback)
6. Progress tracked, stars awarded
7. Completion celebration with cultural elements

### Fill-the-Blanks Flow
1. Song introduction with full lyrics
2. Game mode activated with blanks
3. Child fills in missing words
4. Audio replays segments for hints
5. Immediate feedback on answers
6. Final score and star calculation
7. Option to replay or try new song

### Lullaby Mode Flow
1. Gentle loading screen
2. Soft music begins automatically
3. Calming visuals (stars, moon, cultural symbols)
4. Optional sleep timer
5. Gradual volume fade
6. Completion tracked for progress

## Testing Requirements

### Functional Testing
- Audio playback across browsers
- Lyric synchronization accuracy
- Progress saving and loading
- Star calculation correctness

### Usability Testing
- Child interaction testing (ages 3-12)
- Accessibility compliance
- Mobile/tablet responsiveness
- Cultural appropriateness

### Performance Testing
- Audio loading times
- Large lyric file handling
- Database query optimization
- Memory usage during playback

## Cultural Integration

### Visual Themes
- Incorporate tribal colors and patterns
- Use culturally appropriate imagery
- Respect sacred symbols and meanings
- Child-safe cultural education

### Audio Considerations
- Support for indigenous language pronunciation
- Cultural music styles and instruments
- Respectful handling of traditional songs
- Age-appropriate cultural content

## Accessibility Requirements

### WCAG Compliance
- Keyboard navigation support
- Screen reader compatibility
- High contrast mode
- Text size adjustment
- Audio descriptions for visual elements

### Inclusive Design
- Multiple language support
- Various learning styles accommodation
- Motor skill considerations
- Cognitive load management

## Future Enhancements

### Phase 4.1: Advanced Features
- Voice recognition for karaoke scoring
- Multiplayer singing sessions
- Custom playlist creation
- Offline mode support

### Phase 4.2: Gamification
- Achievement system
- Leaderboards (age-appropriate)
- Collectible cultural items
- Story mode progression

### Phase 4.3: Social Features
- Family sharing
- Classroom collaborations
- Cultural exchange programs
- Elder storytelling integration

## Success Metrics

### Engagement Metrics
- Average session duration
- Completion rates by activity type
- Return user percentage
- Star collection rates

### Educational Metrics
- Language learning progress
- Cultural knowledge retention
- Age-appropriate skill development
- Parent/teacher satisfaction

### Technical Metrics
- Audio playback reliability
- Cross-platform compatibility
- Performance benchmarks
- Error rates and resolution

---

## Implementation Priority

1. **High Priority**: Basic song player with karaoke mode
2. **Medium Priority**: Fill-the-blanks game mode
3. **Medium Priority**: Progress tracking and stars
4. **Low Priority**: Lullaby mode enhancements
5. **Future**: Advanced gamification features

This phase will complete the song activity system, providing children with engaging, culturally-rich musical experiences while maintaining educational value and cultural respect.