<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPageData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    use BuildsPageData;

    public function index(Request $request): View
    {
        $user = $request->user();
        $shop = $user->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        return view('pages.settings', $this->buildPageData('settings', [
            'heading' => 'Settings',
            'subheading' => 'Update your profile and workshop preferences.',
            'searchPlaceholder' => 'Search setting...',
            'profile' => [
                'owner_name' => $shop->owner_name ?: $user->name,
                'email' => $user->email,
                'contact_number' => $shop->contact_number,
                'shop_name' => $shop->name,
                'avatar_url' => $user->avatar_path ? Storage::url($user->avatar_path) : null,
            ],
            'preferences' => [
                'default_labor_rate' => number_format((float) $shop->default_labor_rate, 2, '.', ''),
                'currency_code' => $shop->currency_code ?: 'PHP',
                'auto_assign_job_orders' => (bool) $shop->auto_assign_job_orders,
            ],
            'notifications' => [
                'notify_low_stock_alerts' => (bool) $shop->notify_low_stock_alerts,
                'notify_job_order_updates' => (bool) $shop->notify_job_order_updates,
                'notify_billing_updates' => (bool) $shop->notify_billing_updates,
            ],
        ]));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $shop = $user->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $validated = $request->validate([
            'owner_name' => ['required', 'string', 'max:120'],
            'shop_name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'contact_number' => ['nullable', 'string', 'max:40'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'default_labor_rate' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'currency_code' => ['required', Rule::in(['PHP', 'USD', 'EUR', 'GBP'])],
            'auto_assign_job_orders' => ['nullable', 'boolean'],
            'notify_low_stock_alerts' => ['nullable', 'boolean'],
            'notify_job_order_updates' => ['nullable', 'boolean'],
            'notify_billing_updates' => ['nullable', 'boolean'],
        ]);

        $avatarPath = $user->avatar_path;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->storePublicly('avatars', 'public');

            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
        }

        $user->update([
            'name' => trim($validated['owner_name']),
            'email' => strtolower(trim($validated['email'])),
            'avatar_path' => $avatarPath,
        ]);

        $shop->update([
            'owner_name' => trim($validated['owner_name']),
            'name' => trim($validated['shop_name']),
            'contact_number' => $validated['contact_number'] ? trim($validated['contact_number']) : null,
            'default_labor_rate' => $validated['default_labor_rate'],
            'currency_code' => $validated['currency_code'],
            'auto_assign_job_orders' => (bool) ($validated['auto_assign_job_orders'] ?? false),
            'notify_low_stock_alerts' => (bool) ($validated['notify_low_stock_alerts'] ?? false),
            'notify_job_order_updates' => (bool) ($validated['notify_job_order_updates'] ?? false),
            'notify_billing_updates' => (bool) ($validated['notify_billing_updates'] ?? false),
        ]);

        return redirect()
            ->route('settings')
            ->with('status', 'Settings updated.');
    }
}
