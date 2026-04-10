<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Novelya Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        .font-mono { font-family: 'Fira Code', monospace; }

        body {
            background: #05070f;
            min-height: 100vh;
            overflow: hidden;
        }

        /* ── Animated background ── */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(99,102,241,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,0.06) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: gridMove 20s linear infinite;
        }
        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(60px, 60px); }
        }

        /* ── Floating orbs ── */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            animation: float 12s ease-in-out infinite;
            pointer-events: none;
        }
        .orb-1 { width: 500px; height: 500px; background: #6366f1; top: -120px; left: -100px; animation-delay: 0s; }
        .orb-2 { width: 400px; height: 400px; background: #8b5cf6; bottom: -80px; right: -80px; animation-delay: -4s; }
        .orb-3 { width: 300px; height: 300px; background: #06b6d4; top: 40%; left: 60%; animation-delay: -8s; }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -40px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* ── Particles ── */
        .particles { position: fixed; inset: 0; pointer-events: none; z-index: 2; }
        .particle  { position: absolute; pointer-events: none; }

        @keyframes particleFly {
            0%   { transform: translateY(100vh) translateX(0) rotate(0deg);            opacity: 0; }
            8%   { opacity: 1; }
            92%  { opacity: 1; }
            100% { transform: translateY(-10vh) translateX(var(--drift)) rotate(var(--rot)); opacity: 0; }
        }
        @keyframes electricFlicker {
            0%,100% { opacity: 1; }
            15% { opacity: 0.15; }
            30% { opacity: 1; }
            55% { opacity: 0.4; }
            70% { opacity: 1; }
            88% { opacity: 0.1; }
        }
        @keyframes boltPulse {
            0%,100% { filter: brightness(1)   drop-shadow(0 0 4px  rgba(6,182,212,0.8)); }
            40%     { filter: brightness(0.2) drop-shadow(0 0 2px  rgba(6,182,212,0.3)); }
            60%     { filter: brightness(2.5) drop-shadow(0 0 12px rgba(6,182,212,1));   }
        }

        /* ── Card ── */
        .login-card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            box-shadow:
                0 0 0 1px rgba(99,102,241,0.15),
                0 24px 64px rgba(0,0,0,0.5),
                inset 0 1px 0 rgba(255,255,255,0.08);
            animation: cardEntrance 0.7s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(32px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── Logo ── */
        .logo-ring {
            position: relative;
            width: 64px;
            height: 64px;
        }
        .logo-ring::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, #6366f1, #8b5cf6, #06b6d4, #6366f1);
            animation: spinRing 3s linear infinite;
        }
        .logo-ring::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, #6366f1, #8b5cf6, #06b6d4, #6366f1);
            animation: spinRing 3s linear infinite;
            filter: blur(8px);
            opacity: 0.5;
        }
        .logo-inner {
            position: relative;
            z-index: 1;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #0d0f1a;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @keyframes spinRing {
            to { transform: rotate(360deg); }
        }

        /* ── Inputs ── */
        .input-field {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px;
            color: #f1f5f9;
            font-size: 14px;
            outline: none;
            transition: all 0.25s ease;
        }
        .input-field::placeholder { color: rgba(148,163,184,0.5); }
        .input-field:focus {
            background: rgba(99,102,241,0.08);
            border-color: rgba(99,102,241,0.5);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15), 0 0 20px rgba(99,102,241,0.1);
        }
        .input-field:focus + .input-glow { opacity: 1; }
        .input-wrapper { position: relative; }

        /* ── Button ── */
        .btn-login {
            position: relative;
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            overflow: hidden;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            box-shadow: 0 4px 24px rgba(99,102,241,0.35);
            transition: all 0.25s ease;
            letter-spacing: 0.01em;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(99,102,241,0.5);
        }
        .btn-login:active { transform: translateY(0); }
        .btn-login .shimmer {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.15) 50%, transparent 100%);
            transform: translateX(-100%);
            animation: shimmer 2.5s ease-in-out infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            60%, 100% { transform: translateX(100%); }
        }

        /* ── Label animation ── */
        .input-label {
            font-size: 12px;
            font-weight: 500;
            color: rgba(148,163,184,0.8);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        /* ── Error ── */
        .error-box {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fca5a5;
            font-size: 13px;
            animation: shakeError 0.4s ease;
        }
        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-8px); }
            40% { transform: translateX(8px); }
            60% { transform: translateX(-5px); }
            80% { transform: translateX(5px); }
        }

        /* ── Stats ticker ── */
        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 99px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            font-size: 11px;
            color: rgba(148,163,184,0.7);
            animation: cardEntrance 0.7s cubic-bezier(0.16,1,0.3,1) both;
            animation-delay: 0.3s;
        }
        .stat-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 6px #22c55e;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.8); }
        }

        /* ── Divider ── */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(148,163,184,0.3);
            font-size: 11px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.07);
        }
    </style>
</head>
<body>

    {{-- Background layers --}}
    <div class="bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="particles" id="particles"></div>
    <canvas id="electric-canvas" style="position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:3;"></canvas>

    {{-- Main layout --}}
    <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; position:relative; z-index:10;">
        <div style="width:100%; max-width:420px;">

            {{-- Top badge --}}
            <div style="display:flex; justify-content:center; margin-bottom:28px;">
                <div class="stat-badge">
                    <div class="stat-dot"></div>
                    System Online · Novelya Analytics
                </div>
            </div>

            {{-- Logo --}}
            <div style="display:flex; flex-direction:column; align-items:center; margin-bottom:32px;">
                <div class="logo-ring" style="margin-bottom:16px;">
                    <div class="logo-inner">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="url(#logoGrad)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <defs>
                                <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#818cf8"/>
                                    <stop offset="100%" stop-color="#06b6d4"/>
                                </linearGradient>
                            </defs>
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <h1 class="font-mono" style="font-size:22px; font-weight:700; color:#f8fafc; letter-spacing:-0.02em; margin:0 0 6px;">
                    Novelya <span style="background: linear-gradient(135deg, #818cf8, #06b6d4); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">Analytics</span>
                </h1>
                <p style="font-size:13px; color:rgba(148,163,184,0.6); margin:0;">Admin Dashboard · Masuk untuk melanjutkan</p>
            </div>

            {{-- Card --}}
            <div class="login-card" style="padding:32px;">

                {{-- Error --}}
                @if($errors->any())
                <div class="error-box" style="margin-bottom:24px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $errors->first() }}
                </div>
                @endif

                <form action="{{ route('admin.login.post') }}" method="POST" id="loginForm" style="display:flex; flex-direction:column; gap:20px;">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label class="input-label" for="email">Email</label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                required autocomplete="email"
                                placeholder="admin@novelya.id"
                                class="input-field {{ $errors->has('email') ? 'border-red-500/50' : '' }}">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="input-label" for="password">Password</label>
                        <div class="input-wrapper" style="position:relative;">
                            <input type="password" id="password" name="password"
                                required autocomplete="current-password"
                                placeholder="••••••••"
                                class="input-field"
                                style="padding-right: 48px;">
                            <button type="button" onclick="togglePass()"
                                style="position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:rgba(148,163,184,0.5); padding:4px; transition:color 0.2s;"
                                onmouseover="this.style.color='rgba(148,163,184,0.9)'"
                                onmouseout="this.style.color='rgba(148,163,184,0.5)'">
                                <svg id="eye-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn-login" id="submitBtn" style="margin-top:4px;">
                        <div class="shimmer"></div>
                        <span id="btnText" style="position:relative; z-index:1; display:flex; align-items:center; justify-content:center; gap:8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                            </svg>
                            Masuk
                        </span>
                    </button>
                </form>

                {{-- Divider --}}
                <div class="divider" style="margin-top:24px;">Internal Access Only</div>
            </div>

            {{-- Footer --}}
            <p style="text-align:center; font-size:11px; color:rgba(71,85,105,0.8); margin-top:24px;" class="font-mono">
                © {{ date('Y') }} Novelya Analytics
            </p>
        </div>
    </div>

<script>
    // ── Varied particles ──
    (function() {
        const container = document.getElementById('particles');

        // Palette: [r, g, b]
        const pal = [
            [139, 92,  246],   // violet
            [ 99,102,  241],   // indigo
            [  6,182,  212],   // cyan
            [ 96,165,  250],   // blue
            [167,139,  250],   // light-purple
            [250,204,   21],   // electric yellow (bolts only)
        ];

        const shapes = ['cube','cube','diamond','diamond','ring','shard','shard','cross','bolt','bolt'];

        function make(shape, [r,g,b], size) {
            const el = document.createElement('div');
            el.className = 'particle';
            const c = `${r},${g},${b}`;

            if (shape === 'cube') {
                Object.assign(el.style, {
                    width: size+'px', height: size+'px',
                    background: `rgba(${c},0.07)`,
                    border: `1px solid rgba(${c},0.42)`,
                    borderRadius: '4px',
                    backdropFilter: 'blur(4px)',
                    boxShadow: `inset 0 0 8px rgba(255,255,255,0.07), 0 0 14px rgba(${c},0.22)`,
                });
            } else if (shape === 'diamond') {
                Object.assign(el.style, {
                    width: size+'px', height: size+'px',
                    background: `rgba(${c},0.11)`,
                    border: `1px solid rgba(${c},0.5)`,
                    clipPath: 'polygon(50% 0%,100% 50%,50% 100%,0% 50%)',
                    boxShadow: `0 0 12px rgba(${c},0.35)`,
                });
            } else if (shape === 'ring') {
                const s2 = size * 1.3;
                Object.assign(el.style, {
                    width: s2+'px', height: s2+'px',
                    background: 'transparent',
                    border: `1.5px solid rgba(${c},0.65)`,
                    borderRadius: '50%',
                    boxShadow: `0 0 9px rgba(${c},0.3), inset 0 0 6px rgba(${c},0.1)`,
                });
            } else if (shape === 'shard') {
                // irregular triangle
                const ax=35+Math.random()*25, bx=65+Math.random()*30, by=60+Math.random()*30, cx=Math.random()*30, cy=65+Math.random()*25;
                Object.assign(el.style, {
                    width: (size*1.2)+'px', height: (size*1.3)+'px',
                    background: `rgba(${c},0.13)`,
                    border: `1px solid rgba(${c},0.45)`,
                    clipPath: `polygon(${ax}% 0%,${bx}% ${by}%,${cx}% ${cy}%)`,
                    boxShadow: `0 0 10px rgba(${c},0.25)`,
                });
            } else if (shape === 'cross') {
                Object.assign(el.style, {
                    width: size+'px', height: size+'px',
                    background: `rgba(${c},0.22)`,
                    clipPath: 'polygon(33% 0%,66% 0%,66% 33%,100% 33%,100% 66%,66% 66%,66% 100%,33% 100%,33% 66%,0% 66%,0% 33%,33% 33%)',
                    boxShadow: `0 0 14px rgba(${c},0.5)`,
                });
            } else if (shape === 'bolt') {
                const bw = Math.round(size*0.65), bh = Math.round(size*1.25);
                Object.assign(el.style, {
                    width: bw+'px', height: bh+'px',
                });
                el.innerHTML = `<svg width="${bw}" height="${bh}" viewBox="0 0 10 17" xmlns="http://www.w3.org/2000/svg">`
                    + `<polygon points="6.5,0 2,9.5 5.5,9.5 3.5,17 8,7 4.5,7" fill="rgba(${c},0.95)"`
                    + ` filter="url(#boltGlow${r})"/>`
                    + `<defs><filter id="boltGlow${r}" x="-80%" y="-80%" width="260%" height="260%">`
                    + `<feGaussianBlur stdDeviation="2" result="blur"/>`
                    + `<feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>`
                    + `</filter></defs></svg>`;
            }

            return el;
        }

        for (let i = 0; i < 38; i++) {
            const shape = shapes[Math.floor(Math.random() * shapes.length)];
            // Bolts prefer electric yellow or cyan
            const colorPool = shape === 'bolt'
                ? [[6,182,212],[250,204,21],[96,165,250]]
                : pal.slice(0,5);
            const color = colorPool[Math.floor(Math.random() * colorPool.length)];
            const size  = Math.random() * 11 + 6;   // 6–17 px
            const dur   = Math.random() * 20 + 12;
            const delay = Math.random() * -32;
            const drift = (Math.random() - 0.5) * 140;
            const rot   = (Math.random() - 0.5) * 720;

            const el = make(shape, color, size);
            el.style.left   = Math.random() * 100 + '%';
            el.style.opacity = (Math.random() * 0.45 + 0.3).toFixed(2);
            el.style.setProperty('--drift', drift + 'px');
            el.style.setProperty('--rot',   rot   + 'deg');

            if (shape === 'bolt') {
                el.style.animation = `particleFly ${dur}s linear ${delay}s infinite, electricFlicker ${(Math.random()*0.5+0.25).toFixed(2)}s ease-in-out ${delay}s infinite`;
            } else {
                el.style.animation = `particleFly ${dur}s linear ${delay}s infinite`;
            }

            container.appendChild(el);
        }
    })();

    // ── Canvas electric arcs ──
    (function() {
        const canvas = document.getElementById('electric-canvas');
        const ctx    = canvas.getContext('2d');

        function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
        resize();
        window.addEventListener('resize', resize);

        // Recursive fractal bolt between two points
        function bolt(x1, y1, x2, y2, spread, depth) {
            if (depth === 0) {
                ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
                return;
            }
            const mx = (x1+x2)/2, my = (y1+y2)/2;
            const perp = Math.atan2(y2-y1, x2-x1) + Math.PI/2;
            const ofs  = (Math.random()-0.5) * spread;
            const bx   = mx + Math.cos(perp)*ofs;
            const by   = my + Math.sin(perp)*ofs;
            bolt(x1,y1, bx,by, spread*0.6, depth-1);
            bolt(bx,by, x2,y2, spread*0.6, depth-1);
            // random branch
            if (Math.random() < 0.28 && depth > 1) {
                const bl = 0.3 + Math.random()*0.45;
                bolt(bx, by,
                     bx + (x2-bx)*bl + (Math.random()-0.5)*25,
                     by + (y2-by)*bl + (Math.random()-0.5)*25,
                     spread*0.3, depth-2);
            }
        }

        const arcPal = [[6,182,212],[139,92,246],[96,165,250]];
        let   arcs   = [];
        let   nextAt = performance.now() + 1200 + Math.random()*2500;

        function addArc() {
            const [r,g,b] = arcPal[Math.floor(Math.random()*arcPal.length)];
            const x   = Math.random() * canvas.width;
            const y   = canvas.height * (0.35 + Math.random()*0.55);
            const len = 60 + Math.random()*160;
            const ang = -(Math.PI/2) + (Math.random()-0.5)*0.9; // roughly upward
            arcs.push({ x1:x, y1:y, x2:x+Math.cos(ang)*len, y2:y+Math.sin(ang)*len,
                        spread:len*0.38, alpha:0.85+Math.random()*0.15,
                        decay:0.06+Math.random()*0.07, r,g,b });
        }

        function frame(now) {
            ctx.clearRect(0,0,canvas.width,canvas.height);

            if (now >= nextAt) {
                nextAt = now + 1200 + Math.random()*4000;
                addArc();
                if (Math.random() < 0.35) addArc();
            }

            arcs = arcs.filter(a => a.alpha > 0.04);
            arcs.forEach(a => {
                const col = `rgba(${a.r},${a.g},${a.b},${a.alpha.toFixed(2)})`;
                ctx.save();
                ctx.globalAlpha = a.alpha * 0.85;
                ctx.strokeStyle = col;
                ctx.lineWidth   = 0.75;
                ctx.shadowBlur  = 12;
                ctx.shadowColor = col;
                bolt(a.x1,a.y1, a.x2,a.y2, a.spread, 4);

                // white bright core on fresh bolts
                if (a.alpha > 0.65) {
                    ctx.globalAlpha = (a.alpha-0.65)*0.45;
                    ctx.strokeStyle = 'rgba(255,255,255,0.95)';
                    ctx.lineWidth   = 0.35;
                    ctx.shadowBlur  = 6;
                    ctx.shadowColor = 'white';
                    bolt(a.x1,a.y1, a.x2,a.y2, a.spread*0.45, 3);
                }
                ctx.restore();
                a.alpha -= a.decay;
            });

            requestAnimationFrame(frame);
        }
        requestAnimationFrame(frame);
    })();

    // Toggle password
    function togglePass() {
        const el = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        const isText = el.type === 'text';
        el.type = isText ? 'password' : 'text';
        icon.innerHTML = isText
            ? '<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>'
            : '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    }

    // Submit loading state
    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        const text = document.getElementById('btnText');
        btn.disabled = true;
        btn.style.opacity = '0.8';
        text.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 0.8s linear infinite;">
                <path d="M21 12a9 9 0 11-6.219-8.56"/>
            </svg>
            Memverifikasi...
        `;
    });

    // Input focus glow effect
    document.querySelectorAll('.input-field').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.filter = 'drop-shadow(0 0 8px rgba(99,102,241,0.2))';
        });
        input.addEventListener('blur', function() {
            this.parentElement.style.filter = '';
        });
    });
</script>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
</body>
</html>
