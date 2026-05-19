<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Module;
use App\Models\Organisation;
use App\Services\Admin\PlatformAnalyticsService;
use App\Services\Admin\PlatformStatsService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public bool $maintenanceMode = false;

    public function mount(): void
    {
        $this->maintenanceMode = app()->isDownForMaintenance();
    }

    public function toggleMaintenance(): void
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
            AuditLog::record('MAINTENANCE_DISABLED', 'platform/maintenance', []);
            session()->flash('message', 'Maintenance mode disabled. The application is live again.');
        } else {
            $secret = Str::random(32);
            Artisan::call('down', [
                '--retry' => 60,
                '--secret' => $secret,
            ]);
            AuditLog::record('MAINTENANCE_ENABLED', 'platform/maintenance', [
                'bypass_query' => 'secret='.$secret,
            ]);
            session()->flash(
                'message',
                'Maintenance mode enabled. Super Admin routes remain available. Mobile/API visitors can use: '
                .url('/?secret='.$secret)
            );
        }

        $this->maintenanceMode = app()->isDownForMaintenance();
    }

    public function render(PlatformStatsService $stats, PlatformAnalyticsService $analytics)
    {
        $platformStats = $stats->snapshot();
        $engagement = $analytics->engagementSnapshot();

        $activeOrganizations = Organisation::query()
            ->withCount('users')
            ->where('status', 'active')
            ->latest()
            ->take(4)
            ->get();

        $previewModules = Module::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(6)
            ->get();

        return view('livewire.admin.dashboard', [
            'stats' => $platformStats,
            'analytics' => $engagement,
            'activeOrganizations' => $activeOrganizations,
            'previewModules' => $previewModules,
            'maintenanceMode' => app()->isDownForMaintenance(),
        ]);
    }
}
