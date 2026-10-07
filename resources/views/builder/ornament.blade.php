<div class="hero-ornament" aria-hidden="true">
    <svg viewBox="0 0 200 180" fill="none" focusable="false">
        @switch($draft['template'] ?? 'minimalist')
            @case('web')
            @case('tech')
                <rect x="18" y="24" width="164" height="124" rx="12" class="ornament-fill"/>
                <path d="M18 52h164M34 38h1m10 0h1m10 0h1M70 76 50 94l20 18m60-36 20 18-20 18m-21-42-18 48"/>
                <path d="M142 160h40m-20-20v40"/>
                @break
            @case('android')
                <rect x="56" y="10" width="88" height="150" rx="22" class="ornament-fill"/>
                <path d="M86 24h28M90 146h20"/>
                <rect x="70" y="48" width="60" height="50" rx="12"/>
                <path d="M80 115h40m-40 12h26M20 50v20m-10-10h20m140 60v20m-10-10h20"/>
                @break
            @case('graphic')
                <path d="m100 14 12 39 36-19-19 36 39 12-39 12 19 36-36-19-12 39-12-39-36 19 19-36-39-12 39-12-19-36 36 19Z" class="ornament-fill"/>
                <circle cx="100" cy="82" r="22"/>
                <path d="m154 132 24 24m0-24-24 24M20 140h36v28H20z"/>
                @break
            @case('product')
                <rect x="25" y="38" width="130" height="104" rx="18" class="ornament-fill"/>
                <circle cx="56" cy="69" r="12"/><path d="M82 64h48M82 76h28M44 104h92m-92 16h58"/>
                <path d="m150 112 28 18-17 3-6 17Z" class="ornament-fill"/>
                <path d="M154 16v20m-10-10h20"/>
                @break
            @case('photography')
                <path d="M24 60V24h36m80 0h36v36M24 120v36h36m80 0h36v-36"/>
                <circle cx="100" cy="90" r="48"/><circle cx="100" cy="90" r="32"/>
                <path d="m100 58 28 16v32l-28 16-28-16V74Z"/>
                @break
            @case('data')
                <rect x="20" y="24" width="160" height="132" rx="12" class="ornament-fill"/>
                <path d="M40 48h42m-42 84h120"/>
                <path d="M48 116V92h20v24m20 0V76h20v40m20 0V60h20v56M44 80l48-24 46-18"/>
                <circle cx="44" cy="80" r="3"/><circle cx="92" cy="56" r="3"/><circle cx="138" cy="38" r="3"/>
                @break
            @case('modern')
                <path d="M48 150V76a52 52 0 0 1 104 0v74Z" class="ornament-fill"/>
                <path d="M64 150V80a36 36 0 0 1 72 0v70M30 150h140M156 40v24m-12-12h24"/>
                @break
            @default
                <ellipse cx="100" cy="90" rx="76" ry="30" transform="rotate(-35 100 90)"/>
                <ellipse cx="100" cy="90" rx="76" ry="30" transform="rotate(35 100 90)"/>
                <circle cx="100" cy="90" r="20" class="ornament-fill"/>
                <path d="M162 24v20m-10-10h20"/>
        @endswitch
    </svg>
</div>
