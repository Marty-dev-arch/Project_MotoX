@props(['name', 'class' => 'h-5 w-5'])

<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }}>
    @switch($name)
        @case('dashboard')
            <rect x="3" y="3" width="7" height="7" rx="1.5" />
            <rect x="14" y="3" width="7" height="7" rx="1.5" />
            <rect x="14" y="14" width="7" height="7" rx="1.5" />
            <rect x="3" y="14" width="7" height="7" rx="1.5" />
            @break

        @case('customers')
            <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" />
            <circle cx="9.5" cy="7" r="3" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            @break

        @case('job-orders')
            <rect x="5" y="3" width="14" height="18" rx="2" />
            <path d="M9 7h6" />
            <path d="M9 11h6" />
            <path d="M9 15h4" />
            @break

        @case('inventory')
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
            <path d="m3.3 7 8.7 5 8.7-5" />
            <path d="M12 22V12" />
            @break

        @case('clipboard')
            <rect x="6" y="4" width="12" height="17" rx="2" />
            <path d="M9 4.5h6" />
            <path d="M9 9h6" />
            <path d="M9 13h6" />
            @break

        @case('billing')
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="M3 10h18" />
            <path d="M7 15h4" />
            @break

        @case('reports')
            <path d="M4 19h16" />
            <path d="M7 16V8" />
            <path d="M12 16V5" />
            <path d="M17 16v-4" />
            @break

        @case('settings')
            <circle cx="12" cy="12" r="3" />
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06A2 2 0 0 1 3.4 16.97l.06-.06A1.65 1.65 0 0 0 3.79 15a1.65 1.65 0 0 0-1.51-1H2.2a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82L3.4 7.12A2 2 0 0 1 6.23 4.3l.06.06A1.65 1.65 0 0 0 8.11 4a1.65 1.65 0 0 0 1-1.51V2.4a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 20 8.11c.17.54.69.91 1.26.89h.14a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
            @break

        @case('support')
            <circle cx="12" cy="12" r="9" />
            <path d="M9.09 9a3 3 0 1 1 5.82 1c0 2-3 2-3 4" />
            <path d="M12 17h.01" />
            @break

        @case('logout')
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
            <path d="M10 17l5-5-5-5" />
            <path d="M15 12H3" />
            @break

        @case('plus')
            <path d="M12 5v14" />
            <path d="M5 12h14" />
            @break

        @case('search')
            <circle cx="11" cy="11" r="7" />
            <path d="m21 21-4.35-4.35" />
            @break

        @case('camera')
            <path d="M14.5 4 16 7h3a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3l1.5-3h5Z" />
            <circle cx="12" cy="13" r="3.2" />
            @break

        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2" />
            <path d="M16 3v4" />
            <path d="M8 3v4" />
            <path d="M3 11h18" />
            @break

        @case('image')
            <rect x="3" y="4" width="18" height="16" rx="2" />
            <circle cx="8.5" cy="9.5" r="1.5" />
            <path d="m21 15-4.5-4.5L5 20" />
            @break

        @case('menu')
            <path d="M4 7h16" />
            <path d="M4 12h16" />
            <path d="M4 17h16" />
            @break

        @case('bell')
            <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.17V11a6 6 0 1 0-12 0v3.17a2 2 0 0 1-.59 1.42L4 17h5" />
            <path d="M10 17a2 2 0 0 0 4 0" />
            @break

        @case('messages')
            <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5H6l-3 3v-6.5A8.5 8.5 0 1 1 21 11.5Z" />
            <path d="M8 10h8" />
            <path d="M8 14h5" />
            @break

        @case('user')
            <circle cx="12" cy="7" r="3.2" />
            <path d="M5 20a7 7 0 0 1 14 0" />
            @break

        @case('lock')
            <rect x="5" y="10" width="14" height="11" rx="2" />
            <path d="M8 10V7a4 4 0 0 1 8 0v3" />
            @break

        @case('eye')
            <path d="M2.5 12s3.7-6.2 9.5-6.2 9.5 6.2 9.5 6.2-3.7 6.2-9.5 6.2S2.5 12 2.5 12Z" />
            <circle cx="12" cy="12" r="2.7" />
            @break

        @case('eye-off')
            <path d="M2.5 12s3.7-6.2 9.5-6.2 9.5 6.2 9.5 6.2-3.7 6.2-9.5 6.2S2.5 12 2.5 12Z" />
            <circle cx="12" cy="12" r="2.7" />
            <path d="M4 4 20 20" />
            @break

        @case('id-card')
            <rect x="3" y="4" width="18" height="16" rx="2" />
            <circle cx="9" cy="11" r="2" />
            <path d="M6.5 15a3 3 0 0 1 5 0" />
            <path d="M14 9h4" />
            <path d="M14 13h4" />
            @break

        @case('wrench')
            <path d="M14.7 6.3a4 4 0 0 0-5.4 5.88l-5.48 5.47a2 2 0 0 0 2.83 2.83l5.47-5.48A4 4 0 0 0 17.7 9.3l-2.4 2.4-3-3Z" />
            @break

        @case('alert')
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
            <path d="M12 9v4" />
            <path d="M12 17h.01" />
            @break

        @case('file')
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
            <path d="M14 2v6h6" />
            <path d="M16 13H8" />
            <path d="M16 17H8" />
            @break

        @case('car')
            <path d="M14 16H9m10 0h2v-3l-2-5a2 2 0 0 0-1.9-1.37H6.9A2 2 0 0 0 5 8l-2 5v3h2" />
            <path d="M6 16h12" />
            <circle cx="7.5" cy="16.5" r="1.5" />
            <circle cx="16.5" cy="16.5" r="1.5" />
            @break

        @case('phone')
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.86.33 1.7.61 2.5a2 2 0 0 1-.45 2.11L8 9.59a16 16 0 0 0 6.41 6.41l1.26-1.27a2 2 0 0 1 2.11-.45c.8.28 1.64.49 2.5.61A2 2 0 0 1 22 16.92Z" />
            @break

        @case('printer')
            <path d="M6 9V4h12v5" />
            <rect x="6" y="14" width="12" height="7" rx="1" />
            <rect x="3" y="9" width="18" height="7" rx="2" />
            @break

        @case('export')
            <path d="M12 3v12" />
            <path d="m16 7-4-4-4 4" />
            <path d="M20 21H4" />
            @break

        @case('download')
            <path d="M12 3v12" />
            <path d="m7 10 5 5 5-5" />
            <path d="M5 21h14" />
            @break

        @case('moon')
            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
            @break

        @case('sun')
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2" />
            <path d="M12 20v2" />
            <path d="m4.93 4.93 1.41 1.41" />
            <path d="m17.66 17.66 1.41 1.41" />
            <path d="M2 12h2" />
            <path d="M20 12h2" />
            <path d="m6.34 17.66-1.41 1.41" />
            <path d="m19.07 4.93-1.41 1.41" />
            @break

        @case('trend')
            <path d="M3 17 9 11l4 4 8-8" />
            <path d="M14 7h7v7" />
            @break

        @case('chevron-right')
            <path d="m9 18 6-6-6-6" />
            @break

        @case('chevron-down')
            <path d="m6 9 6 6 6-6" />
            @break

        @case('arrow-up')
            <path d="M12 19V5" />
            <path d="m5 12 7-7 7 7" />
            @break

        @case('instagram')
            <rect x="4" y="4" width="16" height="16" rx="4" />
            <circle cx="12" cy="12" r="3.2" />
            <path d="M16.8 7.2h.01" />
            @break

        @case('facebook')
            <path d="M14 8h2.5V4.5H14a4 4 0 0 0-4 4V11H7v3.5h3V21h3.8v-6.5h2.9L17.2 11h-3.4V8.7c0-.45.25-.7.7-.7Z" />
            @break

        @case('tiktok')
            <path d="M14 4v9.2a4 4 0 1 1-4-4" />
            <path d="M14 4c.6 3.3 2.5 5.1 5 5.4" />
            @break

        @case('check-circle')
            <circle cx="12" cy="12" r="9" />
            <path d="m8.5 12 2.2 2.2 4.8-4.8" />
            @break

        @case('pencil')
            <path d="M12 20h9" />
            <path d="m16.5 3.5 4 4L8 20l-5 1 1-5L16.5 3.5Z" />
            @break

        @case('trash')
            <path d="M3 6h18" />
            <path d="M8 6V4h8v2" />
            <path d="m19 6-1 14H6L5 6" />
            <path d="M10 11v6" />
            <path d="M14 11v6" />
            @break

        @case('x')
            <path d="m6 6 12 12" />
            <path d="m18 6-12 12" />
            @break

        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
