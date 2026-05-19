<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;
use Illuminate\Support\Carbon;

class CheckWorkingHours
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get site context from custom header
        $siteContext = $request->header('X-Site-Context', 'acharu');
        $siteId = ($siteContext === 'tajashutki') ? 2 : 1;

        $site = Site::find($siteId);
        if ($site && isset($site->settings)) {
            $settings = $site->settings;
            $security = $settings['security'] ?? null;

            if ($security && ($security['working_hours_enabled'] ?? false)) {
                $now = Carbon::now('Asia/Dhaka');
                $currentDayOfWeek = (int) $now->format('w'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
                $currentTimeStr = $now->format('H:i'); // "HH:MM"

                $workingDays = $security['working_days'] ?? [0, 1, 2, 3, 4, 5, 6];
                
                // Ensure array values are integers
                $workingDays = array_map('intval', $workingDays);

                if (!in_array($currentDayOfWeek, $workingDays)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Today is not a working day.'
                    ], 401);
                }

                $startTime = $security['working_hours_start'] ?? '09:00';
                $endTime = $security['working_hours_end'] ?? '18:00';

                if ($currentTimeStr < $startTime || $currentTimeStr > $endTime) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Outside of admin panel working hours.'
                    ], 401);
                }
            }
        }

        return $next($request);
    }
}
