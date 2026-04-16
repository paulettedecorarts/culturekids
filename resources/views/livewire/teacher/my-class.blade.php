<div class="teacher-class-hub">
    <div class="header">
        <div>
            <h1 class="page-title">My class</h1>
            <div class="breadcrumb">Classroom · Children in your active class</div>
        </div>
    </div>

    @if (! $hasOrganisation)
        <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:24px; padding:24px; font-size:14px; font-weight:600; color:#92400E">
            {{ __('Your account is not linked to an organisation. Contact support.') }}
        </div>
    @elseif ($assignedClasses->isEmpty())
        <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:24px; padding:24px; font-size:14px; font-weight:600; color:#92400E; line-height:1.5">
            {{ __('You are not assigned to any class yet. Ask your organisation admin to open Classrooms, edit a class, and set you as the class teacher.') }}
        </div>
    @elseif (! $activeClassroomId)
        <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:24px; padding:24px; font-size:14px; font-weight:600; color:#92400E">
            {{ __('Choose an active class from the sidebar.') }}
        </div>
    @else
        <div style="margin-bottom:20px; font-size:14px; font-weight:700; color:var(--ink-light)">
            @php($room = $assignedClasses->firstWhere('id', $activeClassroomId))
            @if ($room)
                <span style="color:var(--stone); font-weight:600">{{ __('Class:') }}</span> {{ $room->name }}
                @if ($room->description)
                    <span style="display:block; margin-top:8px; font-size:13px; font-weight:600; color:var(--stone); white-space:pre-wrap">{{ $room->description }}</span>
                @endif
            @endif
        </div>

        <div style="background:#fff; border-radius:32px; padding:32px; border:1px solid var(--cream-mid); box-shadow:0 8px 32px rgba(0,0,0,.04); overflow:hidden">
            @if ($children->isEmpty())
                <div style="text-align:center; padding:40px 24px; color:var(--stone); font-weight:600; font-size:14px">
                    {{ __('No children in this class yet. Your organisation admin can invite children and add them to this class from the Classrooms page.') }}
                </div>
            @else
                <table style="width:100%; border-collapse:collapse; text-align:left">
                    <thead>
                        <tr style="border-bottom:2px solid var(--cream-mid); font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px">
                            <th style="padding:16px 20px">{{ __('Child') }}</th>
                            <th style="padding:16px 20px">{{ __('Email') }}</th>
                            <th style="padding:16px 20px">{{ __('Joined') }}</th>
                            <th style="padding:16px 20px">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($children as $child)
                            <tr style="border-bottom:1px solid var(--cream-mid); font-size:14px; color:var(--ink)">
                                <td style="padding:20px">
                                    <div style="display:flex; align-items:center; gap:12px">
                                        <div style="width:36px; height:36px; border-radius:12px; background:var(--sky-dusk); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800">{{ \Illuminate\Support\Str::substr($child->name, 0, 1) }}</div>
                                        <div style="font-weight:700">{{ $child->name }}</div>
                                    </div>
                                </td>
                                <td style="padding:20px; color:var(--stone); font-size:13px; word-break:break-word">{{ $child->email }}</td>
                                <td style="padding:20px; color:var(--stone); font-size:13px">{{ $child->created_at->diffForHumans() }}</td>
                                <td style="padding:20px">
                                    <a href="{{ route('teacher.child-detail', $child->id) }}" 
                                       style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:var(--sky-dusk); color:#fff; border-radius:12px; font-size:13px; font-weight:700; text-decoration:none; transition:all .2s">
                                        <svg style="width:16px; height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif
</div>
