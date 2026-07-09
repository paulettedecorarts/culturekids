@extends('layouts.heritage')

@section('content')
<div id="loginPage" style="display:flex">
    <div class="lc" style="max-width:460px;text-align:center">
        <div class="lc-logo">
            <img src="{{ asset(config('brand.logo', 'images/brand/paulette-comics-logo.png')) }}" alt="">
        </div>
        <h1 class="lc-title" style="color:#fff;font-family:'Baloo 2',cursive;margin:18px 0 8px">Add a child profile</h1>
        <p style="color:rgba(255,255,255,.65);font-size:.92rem;margin-bottom:24px;line-height:1.6">
            Heritage Heroes links progress to a child profile under your parent account
            @if ($profiles->isNotEmpty())
                or your organisation.
            @else
                . Create a child profile in the mobile app or ask your school administrator to assign your learner account.
            @endif
        </p>
        @if (session('message'))
            <p style="color:#FDE68A;font-size:.9rem;margin-bottom:16px">{{ session('message') }}</p>
        @endif
        <a href="{{ route('profile') }}" class="btn-go" style="display:inline-block;text-decoration:none">Account settings</a>
    </div>
</div>
@endsection
