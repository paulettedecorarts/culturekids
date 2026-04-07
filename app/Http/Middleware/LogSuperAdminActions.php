<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;

class LogSuperAdminActions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated super admins
        if (auth()->check() && auth()->user()->hasRole('super_admin')) {
            // Skip logging for GET requests to avoid noise (except specific pages)
            if ($request->isMethod('GET') && !$this->shouldLogGetRequest($request)) {
                return $response;
            }

            // Determine action type
            $action = $this->determineAction($request);
            
            if ($action) {
                AuditLog::record(
                    $action,
                    $request->path(),
                    $this->getPayload($request),
                    $response->isSuccessful() ? 'success' : 'failed'
                );
            }
        }

        return $response;
    }

    /**
     * Determine if GET request should be logged
     */
    private function shouldLogGetRequest(Request $request): bool
    {
        // Log access to sensitive pages
        $sensitivePages = [
            'admin/impersonate',
            'admin/audit-logs',
            'admin/permissions',
        ];

        foreach ($sensitivePages as $page) {
            if (str_contains($request->path(), $page)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine the action type based on request
     */
    private function determineAction(Request $request): ?string
    {
        $method = $request->method();
        $path = $request->path();

        // Specific action mappings
        if (str_contains($path, 'modules') && $method === 'POST') {
            return 'MODULE_TOGGLE';
        }

        if (str_contains($path, 'impersonate')) {
            return 'IMPERSONATE';
        }

        if (str_contains($path, 'stop-impersonation')) {
            return 'STOP_IMPERSONATE';
        }

        // Generic CRUD actions
        return match($method) {
            'POST' => 'CREATE',
            'PUT', 'PATCH' => 'UPDATE',
            'DELETE' => 'DELETE',
            default => null,
        };
    }

    /**
     * Get relevant payload data
     */
    private function getPayload(Request $request): ?array
    {
        $payload = [];

        // Include relevant request data (excluding sensitive fields)
        $data = $request->except(['password', 'password_confirmation', '_token', '_method']);
        
        if (!empty($data)) {
            $payload['request_data'] = $data;
        }

        return !empty($payload) ? $payload : null;
    }
}
