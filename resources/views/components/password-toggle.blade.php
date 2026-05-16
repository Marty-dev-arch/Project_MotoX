@props(['target'])


{{-- Purpose: Renders the password visibility toggle button. --}}
<button
    type="button"
    class="password-toggle"
    data-password-toggle
    data-target="{{ $target }}"
    aria-controls="{{ $target }}"
    aria-label="Show password"
    title="Show password"
    style="position: absolute !important; top: auto !important; right: 0.65rem !important; bottom: 0.05rem !important; display: inline-flex !important; align-items: flex-end !important; justify-content: center !important; width: 2rem !important; height: 2rem !important; min-width: 2rem !important; min-height: 2rem !important; padding: 0 0 0.08rem 0 !important; margin: 0 !important; transform: none !important; border: 0 !important; border-radius: 9999px !important; background: transparent !important; color: #64748b !important; box-shadow: none !important;"
>
    <x-icon name="eye-off" class="password-toggle-icon hidden" data-password-icon="show" style="transform: translateY(0.14rem) !important;" />
    <x-icon name="eye" class="password-toggle-icon" data-password-icon="hide" style="transform: translateY(0.14rem) !important;" />
</button>
