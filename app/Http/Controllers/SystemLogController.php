<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPageData;
use App\Models\SystemLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemLogController extends Controller
{
    use BuildsPageData;

    public function index(): View
    {
        $shop = auth()->user()?->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $logs = SystemLog::query()
            ->with('user')
            ->where('shop_id', $shop->id)
            ->where('action', 'not like', 'auth.%')
            ->latest()
            ->paginate(50);

        return view('pages.logs', $this->buildPageData('logs', [
            'heading' => 'History Log',
            'subheading' => 'Full history of created, updated, deleted, and stock actions.',
            'logs' => $logs,
        ]));
    }

    public function destroy(SystemLog $log): RedirectResponse
    {
        $this->authorizeLog($log);

        $log->delete();

        return redirect()
            ->route('logs')
            ->with('status', 'Log entry deleted successfully.')
            ->with('status_tone', 'danger');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $shop = $request->user()?->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        SystemLog::query()
            ->where('shop_id', $shop->id)
            ->delete();

        return redirect()
            ->route('logs')
            ->with('status', 'All log history deleted successfully.')
            ->with('status_tone', 'danger');
    }

    private function authorizeLog(SystemLog $log): void
    {
        $shop = auth()->user()?->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        abort_if((int) $log->shop_id !== (int) $shop->id, 404);
    }
}
