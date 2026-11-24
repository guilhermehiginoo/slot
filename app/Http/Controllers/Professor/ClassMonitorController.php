<?php

namespace App\Http\Controllers\Professor;

use App\Actions\Monitoring\RemoveClassMonitor;
use App\Http\Controllers\Controller;
use App\Models\{ClassGroup, User};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ClassMonitorController extends Controller
{
    public function destroy(ClassGroup $classGroup, User $monitor, RemoveClassMonitor $removeClassMonitor): RedirectResponse
    {
        Gate::authorize('manageMonitors', $classGroup);

        abort_unless($classGroup->monitors()->whereKey($monitor->id)->exists(), 404);

        $removeClassMonitor->handle($classGroup, $monitor);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Monitor removido.']);

        return back();
    }
}
