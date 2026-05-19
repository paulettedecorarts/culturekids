@php
    $template = $sourcePreview['template'] ?? 'generic';
    $data = $sourcePreview['data'] ?? [];
@endphp

<div class="tf-source">
    <div class="tf-source-head">
        <div class="tf-source-kicker">Source content</div>
        <div class="tf-source-title">{{ $sourcePreview['title'] ?? '—' }}</div>
        @if(!empty($sourcePreview['subtitle']))
            <div class="tf-source-sub">{{ $sourcePreview['subtitle'] }}</div>
        @endif
    </div>

    @if(!empty($subItemNav))
        <div class="tf-subnav">
            @foreach($subItemNav as $item)
                <button
                    type="button"
                    wire:click="selectSubItem('{{ $item['key'] }}')"
                    class="tf-subnav-btn {{ $item['active'] ? 'is-active' : '' }}"
                >{{ $item['label'] }}</button>
            @endforeach
        </div>
    @endif

    @if($template === 'story_panel')
        @php $panel = $data['panel'] ?? null; @endphp
        @if($panel)
            <div class="tf-preview-panel">
                <div class="tf-preview-image-wrap">
                    @if($panel['is_pdf'])
                        <div class="tf-preview-pdf">PDF panel</div>
                    @elseif($panel['image_url'])
                        <img src="{{ $panel['image_url'] }}" alt="" class="tf-preview-image">
                        @foreach($data['existing_tags'] ?? [] as $t)
                            @if($t['x_position'] !== null && $t['y_position'] !== null)
                                <span
                                    class="tf-preview-hotspot"
                                    style="left:{{ (int) $t['x_position'] }}%;top:{{ (int) $t['y_position'] }}%"
                                    title="{{ $t['word'] }}@if($t['translation']) → {{ $t['translation'] }}@endif"
                                ></span>
                            @endif
                        @endforeach
                    @else
                        <div class="tf-preview-empty">No panel image</div>
                    @endif
                </div>
                @if(!empty($panel['caption']))
                    <div class="tf-preview-caption">“{{ $panel['caption'] }}”</div>
                @endif
                <div class="tf-preview-meta">Panel {{ ((int) ($panel['order'] ?? 0)) + 1 }}</div>
            </div>
        @endif

    @elseif($template === 'flashcard_slide')
        @php $slide = $data['slide'] ?? null; @endphp
        @if($slide)
            <div class="tf-preview-flashcard">
                <div class="tf-fc-side tf-fc-front">
                    @if(!empty($slide['image_url']))
                        <img src="{{ $slide['image_url'] }}" alt="" class="tf-fc-img">
                    @elseif(!empty($slide['emoji']))
                        <span class="tf-fc-emoji">{{ $slide['emoji'] }}</span>
                    @endif
                    <div class="tf-fc-label">{{ $slide['front_label'] ?: '—' }}</div>
                    <div class="tf-fc-side-tag">Front</div>
                </div>
                <div class="tf-fc-side tf-fc-back">
                    <div class="tf-fc-label">{{ $slide['back_label'] ?: '—' }}</div>
                    @if(!empty($slide['phonetic']))
                        <div class="tf-fc-phonetic">{{ $slide['phonetic'] }}</div>
                    @endif
                    <div class="tf-fc-side-tag">Back</div>
                </div>
            </div>
        @endif

    @elseif($template === 'language_word')
        @php $word = $data['word'] ?? null; @endphp
        @if(in_array($data['activity_type'] ?? '', ['proverb_jumble', 'sentence_builder']))
            <div class="tf-preview-sentence">
                <div class="tf-preview-block">
                    <div class="tf-preview-block-label">Full sentence</div>
                    <div>{{ $data['full_sentence'] ?: '—' }}</div>
                </div>
                <div class="tf-preview-block">
                    <div class="tf-preview-block-label">Sentence translation</div>
                    <div>{{ $data['sentence_translation'] ?: '—' }}</div>
                </div>
            </div>
        @endif
        @if($word)
            <div class="tf-preview-word-row">
                @if(!empty($word['emoji']))
                    <span class="tf-preview-emoji">{{ $word['emoji'] }}</span>
                @endif
                <div>
                    <div class="tf-preview-word-main">{{ $word['word'] ?: '—' }}</div>
                    <div class="tf-preview-word-sub">{{ $word['translation'] ?: 'No translation yet' }}</div>
                    @if(!empty($word['phonetic']))
                        <div class="tf-preview-word-phonetic">{{ $word['phonetic'] }}</div>
                    @endif
                </div>
            </div>
        @endif

    @elseif($template === 'word_search')
        @php $entry = $data['entry'] ?? []; @endphp
        <div class="tf-preview-ws">
            <div class="tf-preview-row"><span>Grid word</span><strong>{{ $entry['word'] ?? '—' }}</strong></div>
            <div class="tf-preview-row"><span>Translation</span><strong>{{ $entry['translation'] ?? '—' }}</strong></div>
            <div class="tf-preview-row"><span>Hint</span><strong>{{ $entry['hint'] ?? '—' }}</strong></div>
            @if(!empty($data['grid_size']))
                <div class="tf-preview-meta">Grid {{ $data['grid_size'] }}×{{ $data['grid_size'] }}</div>
            @endif
        </div>

    @elseif($template === 'culture')
        <div class="tf-preview-culture">
            @if(!empty($data['clan_name']))
                <div class="tf-preview-meta">{{ $data['clan_name'] }}</div>
            @endif
            <div class="tf-preview-proverb">“{{ $data['proverb'] ?: '—' }}”</div>
            <div class="tf-preview-proverb-tr">{{ $data['proverb_translation'] ?: 'No translation yet' }}</div>
            @if(!empty($data['content_excerpt']))
                <div class="tf-preview-excerpt">{{ $data['content_excerpt'] }}</div>
            @endif
        </div>

    @elseif($template === 'song')
        <div class="tf-preview-generic">
            @if(!empty($data['language']))
                <div class="tf-preview-meta">Language: {{ $data['language'] }}</div>
            @endif
            <pre class="tf-preview-lyrics">{{ $data['lyrics_excerpt'] ?: 'No lyrics stored.' }}</pre>
        </div>


    @elseif($template === 'game_question')
        @php $q = $data['question'] ?? null; @endphp
        @if($q)
            <div class="tf-preview-ws">
                <div class="tf-preview-row"><span>Question</span><strong>{{ $q['question_text'] ?: $q['match_text'] ?: '—' }}</strong></div>
                <div class="tf-preview-row"><span>Answer</span><strong>{{ $q['correct_answer'] ?: '—' }}</strong></div>
                @if(!empty($q['hint']))
                    <div class="tf-preview-row"><span>Hint</span><strong>{{ $q['hint'] }}</strong></div>
                @endif
            </div>
        @endif

    @elseif($template === 'puzzle')
        @if(!empty($data['image_url']))
            <img src="{{ $data['image_url'] }}" alt="" class="tf-preview-image" style="max-height:200px;margin-bottom:12px">
        @endif
        <div class="tf-preview-ws">
            <div class="tf-preview-row"><span>Content tag</span><strong>{{ $data['content_tag'] ?? '—' }}</strong></div>
            <div class="tf-preview-row"><span>Description</span><strong>{{ \Illuminate\Support\Str::limit($data['description'] ?? '—', 120) }}</strong></div>
        </div>

    @elseif($template === 'drawing')
        @if(!empty($data['template_url']))
            <img src="{{ $data['template_url'] }}" alt="" class="tf-preview-image" style="max-height:200px;margin-bottom:12px">
        @elseif(!empty($data['preview_url']))
            <img src="{{ $data['preview_url'] }}" alt="" class="tf-preview-image" style="max-height:200px;margin-bottom:12px">
        @endif
        <div class="tf-preview-meta">Type: {{ $data['drawing_type'] ?? 'drawing' }}</div>
        @if(!empty($data['selected']['word']))
            <div class="tf-preview-block" style="margin-top:12px">
                <div class="tf-preview-block-label">Current source text</div>
                <div>{{ $data['selected']['word'] }}</div>
            </div>
        @endif

    @elseif($template === 'maze')
        @if(!empty($data['cover_url']))
            <img src="{{ $data['cover_url'] }}" alt="" class="tf-preview-image" style="max-height:160px;margin-bottom:12px">
        @endif
        <div class="tf-preview-ws">
            <div class="tf-preview-row"><span>Hero</span><strong>{{ $data['hero_character'] ?? '—' }}</strong></div>
            @if(!empty($data['collectible']))
                <div class="tf-preview-row"><span>Collectible</span><strong>{{ $data['collectible']['emoji'] ?? '' }} {{ $data['collectible']['label'] ?? '—' }}</strong></div>
            @endif
        </div>

    @elseif($template === 'spot_difference')
        @if(!empty($data['image_url']))
            <div class="tf-preview-image-wrap" style="min-height:200px">
                <img src="{{ $data['image_url'] }}" alt="" class="tf-preview-image">
                @if(!empty($data['zone']))
                    <span class="tf-preview-hotspot" style="left:{{ $data['zone']['x_percent'] }}%;top:{{ $data['zone']['y_percent'] }}%" title="{{ $data['zone']['label'] }}"></span>
                @endif
            </div>
        @endif
        @if(!empty($data['zone']['label']))
            <div class="tf-preview-word-main" style="margin-top:8px">{{ $data['zone']['label'] }}</div>
        @endif

    @else
        <div class="tf-preview-generic">
            @if(!empty($data['image_url']))
                <img src="{{ $data['image_url'] }}" alt="" class="tf-preview-image" style="max-height:240px;margin-bottom:12px">
            @endif
            <div class="tf-preview-excerpt">{{ $data['description'] ?? 'No preview available for this content type.' }}</div>
        </div>
    @endif
</div>
