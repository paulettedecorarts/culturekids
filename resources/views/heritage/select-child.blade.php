@extends('layouts.heritage')

@section('content')
<div id="loginPage" style="display:flex">
    <div class="lc" style="max-width:460px">
        <div class="lc-logo">
            <img src="{{ asset(config('brand.logo', 'images/brand/paulette-comics-logo.png')) }}" alt="">
        </div>
        <h1 class="lc-title" style="text-align:center;color:#fff;font-family:'Baloo 2',cursive;margin:18px 0 8px">Who is learning today?</h1>
        <p style="text-align:center;color:rgba(255,255,255,.65);font-size:.92rem;margin-bottom:24px">
            Choose a child profile for Heritage Heroes.
        </p>

        <form method="POST" action="{{ route('heritage.select-child.store') }}" style="display:flex;flex-direction:column;gap:12px">
            @csrf
            @foreach ($profiles as $profile)
                <label style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:16px;border:2px solid {{ (int) $activeId === (int) $profile->id ? '#F97316' : 'rgba(255,255,255,.12)' }};background:rgba(255,255,255,.04);cursor:pointer">
                    <input type="radio" name="child_profile_id" value="{{ $profile->id }}" @checked((int) $activeId === (int) $profile->id) style="accent-color:#F97316">
                    <span style="font-size:1.6rem">{{ $profile->avatar ?: '🧒' }}</span>
                    <span style="flex:1">
                        <strong style="display:block;color:#fff;font-size:1rem">{{ $profile->name }}</strong>
                        <span style="color:rgba(255,255,255,.55);font-size:.82rem">{{ $profile->age_band ?: 'Learner' }} · ⭐ {{ $profile->total_stars ?? 0 }}</span>
                    </span>
                </label>
            @endforeach
            <button type="submit" class="btn-go" style="margin-top:8px">Continue</button>
        </form>
    </div>
</div>
@endsection
