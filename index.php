<?php
/* ─────────────────────────────────────────────
   Session / Language Bootstrap
───────────────────────────────────────────── */
/* BUILD_LANG is defined by build.php when generating the static site
   (en => /, fa => /fa/). Otherwise fall back to the session/?lang= switch
   so the page still works locally with `php -S`. */
if (defined('BUILD_LANG')) {
    $lang = BUILD_LANG;
    $toggleHref = (BUILD_LANG === 'fa') ? '/' : '/fa/';
} else {
    session_start();
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fa'])) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    $lang = $_SESSION['lang'] ?? 'en';
    $toggleHref = '?lang=' . ($lang === 'fa' ? 'en' : 'fa');
}
$isRtl  = ($lang === 'fa');
$dir    = $isRtl ? 'rtl' : 'ltr';
$toggleLang  = $isRtl ? 'en' : 'fa';
$toggleLabel = $isRtl ? 'EN' : 'FA';
$fontFamily  = $isRtl ? "'Vazirmatn', sans-serif" : "'Inter', sans-serif";

/* ─────────────────────────────────────────────
   Data
───────────────────────────────────────────── */
$jsonPath = __DIR__ . '/data/content.json';
$content  = json_decode(file_get_contents($jsonPath), true);

$p    = $content['personal'];
$stats = $content['stats'];
$skills = $content['skills'];
$experience = $content['experience'];
$education  = $content['education'];
$certifications = $content['certifications'];

/* ─────────────────────────────────────────────
   Helpers
───────────────────────────────────────────── */
function t(string $en, string $fa): string {
    global $lang;
    return $lang === 'fa' ? $fa : $en;
}
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/* Pre-compute reused values */
$name        = h(t($p['name_en'], $p['name_fa']));
$titlesJson  = json_encode(
    $isRtl ? $p['titles_fa'] : $p['titles_en'],
    JSON_UNESCAPED_UNICODE
);
$showPhoto   = !empty($p['photo']) && file_exists(__DIR__ . '/' . $p['photo']);
$photoSrc    = h('/' . ltrim($p['photo'] ?? '', '/'));
$cvFile      = h('/' . ltrim($p['cv_file'] ?? 'assets/files/cv.pdf', '/'));
$email       = h($p['email']);
$phone       = h($p['phone']);
$linkedin    = h($p['linkedin'] ?? '');
$github      = h($p['github'] ?? '');
$telegram    = h($p['telegram'] ?? '');

/* Certification icons (cycle) */
$certIcons = [
    'fa-network-wired', 'fa-shield-halved', 'fa-lock',
    'fa-server',        'fa-terminal',      'fa-diagram-project',
    'fa-headset',       'fa-user-shield',   'fa-linux',
    'fa-cloud',
];

/* Gradient directions for LTR/RTL */
$gradDir = $isRtl ? '270deg' : '90deg';
?>
<!DOCTYPE html>
<html lang="<?= $isRtl ? 'fa' : 'en' ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="<?= $name ?> — Professional Resume" />
    <title><?= $name ?> — Resume</title>

    <!-- Google Fonts: Inter (EN) + Vazirmatn (FA) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- External stylesheet (for overrides / additions) -->
    <link rel="stylesheet" href="/assets/css/style.css" />

    <style>
    /* ══════════════════════════════════════════
       CSS VARIABLES
    ══════════════════════════════════════════ */
    :root {
        --navy:           #0a192f;
        --navy-light:     #112240;
        --slate:          #8892b0;
        --light-slate:    #a8b2d8;
        --lightest-slate: #ccd6f6;
        --accent:         #64ffda;   /* on dark backgrounds  */
        --accent-ring:    rgba(100,255,218,.30);
        --accent-tint:    rgba(100,255,218,.08);
        --accent-d:       #0d9488;   /* on light backgrounds */
        --white-text:     #e6f1ff;
        --bg-white:       #ffffff;
        --bg-light:       #f7fafc;
        --bg-teal:        #f0fdf9;
        --text-dark:      #1a202c;
        --text-mid:       #4a5568;
        --text-light:     #718096;
        --border:         #e2e8f0;
        --shadow:         0 4px 20px rgba(0,0,0,.08);
        --shadow-lg:      0 12px 40px rgba(0,0,0,.14);
        --radius:         12px;
        --radius-lg:      20px;
        --ease:           all .3s ease;
        --ff:             <?= $fontFamily ?>;
    }

    /* ══════════════════════════════════════════
       RESET
    ══════════════════════════════════════════ */
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    html  { scroll-behavior: smooth; scroll-padding-top: 72px; }
    body  {
        font-family: var(--ff);
        color: var(--text-dark);
        background: var(--bg-white);
        line-height: 1.7;
        -webkit-font-smoothing: antialiased;
    }
    a   { text-decoration: none; color: inherit; }
    img { max-width: 100%; display: block; }
    ul  { list-style: none; }

    /* ══════════════════════════════════════════
       UTILITIES
    ══════════════════════════════════════════ */
    .container { max-width: 1200px; margin: 0 auto; }
    .section   { padding: 90px 5%; }

    .section-label {
        display: block;
        font-size: .78rem;
        font-weight: 700;
        color: var(--accent-d);
        text-transform: uppercase;
        letter-spacing: 3px;
        margin-bottom: .5rem;
    }
    .section-title {
        font-size: clamp(1.75rem, 3vw, 2.5rem);
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1.15;
        margin-bottom: .75rem;
    }
    .section-title span { color: var(--accent-d); }
    .section-line {
        width: 60px; height: 4px;
        border-radius: 2px;
        background: linear-gradient(<?= $gradDir ?>, var(--accent), transparent);
        margin-bottom: 3rem;
    }

    /* scroll-reveal */
    .fade-up {
        opacity: 0;
        transform: translateY(28px);
        transition: opacity .65s ease, transform .65s ease;
    }
    .fade-up.visible { opacity: 1; transform: translateY(0); }
    .fade-up.d1 { transition-delay: .12s; }
    .fade-up.d2 { transition-delay: .24s; }

    /* ══════════════════════════════════════════
       NAVBAR
    ══════════════════════════════════════════ */
    .navbar {
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1000;
        height: 70px;
        padding: 0 5%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(10,25,47,.82);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border-bottom: 1px solid rgba(100,255,218,.07);
        transition: var(--ease);
    }
    .navbar.scrolled {
        background: rgba(10,25,47,.97);
        box-shadow: 0 4px 30px rgba(0,0,0,.35);
    }

    .nav-logo {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--accent);
        letter-spacing: -.5px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .nav-logo em { color: var(--white-text); font-style: normal; }

    .nav-links {
        display: flex;
        align-items: center;
        gap: 1.6rem;
    }
    .nav-links a {
        color: var(--light-slate);
        font-size: .84rem;
        font-weight: 500;
        position: relative;
        padding-bottom: 3px;
        transition: color .2s;
    }
    .nav-links a::after {
        content: '';
        position: absolute;
        bottom: 0;
        <?= $isRtl ? 'right' : 'left' ?>: 0;
        width: 0; height: 2px;
        background: var(--accent);
        border-radius: 1px;
        transition: width .22s;
    }
    .nav-links a:hover { color: var(--accent); }
    .nav-links a:hover::after { width: 100%; }

    .nav-actions {
        display: flex;
        align-items: center;
        gap: .7rem;
        flex-shrink: 0;
    }

    .btn-lang {
        padding: .32rem .8rem;
        border: 1.5px solid var(--accent);
        border-radius: 6px;
        color: var(--accent);
        font-size: .78rem;
        font-weight: 700;
        background: transparent;
        cursor: pointer;
        transition: var(--ease);
        font-family: var(--ff);
        letter-spacing: 1px;
    }
    .btn-lang:hover { background: var(--accent-tint); }

    .btn-cv-nav {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .42rem 1.1rem;
        background: var(--accent);
        color: var(--navy);
        border-radius: 8px;
        font-size: .82rem;
        font-weight: 700;
        transition: var(--ease);
    }
    .btn-cv-nav:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(100,255,218,.35);
    }

    .hamburger {
        display: none;
        flex-direction: column;
        gap: 5px;
        cursor: pointer;
        padding: 6px;
        border-radius: 6px;
        background: transparent;
        border: none;
    }
    .hamburger span {
        display: block;
        width: 22px; height: 2px;
        background: var(--accent);
        border-radius: 2px;
        transition: var(--ease);
    }
    .hamburger.open span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
    .hamburger.open span:nth-child(2) { opacity: 0; transform: translateX(10px); }
    .hamburger.open span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

    /* ══════════════════════════════════════════
       HERO
    ══════════════════════════════════════════ */
    .hero {
        min-height: 100vh;
        background: var(--navy);
        display: flex;
        align-items: center;
        padding: 90px 5% 70px;
        position: relative;
        overflow: hidden;
    }

    /* Dot grid */
    .hero-dots {
        position: absolute; inset: 0;
        background-image: radial-gradient(rgba(100,255,218,.07) 1.5px, transparent 1.5px);
        background-size: 44px 44px;
        pointer-events: none;
    }
    /* Ambient glows */
    .hero-glow-1 {
        position: absolute;
        width: 700px; height: 700px; border-radius: 50%;
        background: radial-gradient(circle, rgba(100,255,218,.055) 0%, transparent 65%);
        top: -160px; right: -160px;
        pointer-events: none;
    }
    .hero-glow-2 {
        position: absolute;
        width: 400px; height: 400px; border-radius: 50%;
        background: radial-gradient(circle, rgba(14,165,233,.05) 0%, transparent 65%);
        bottom: -80px; left: -80px;
        pointer-events: none;
    }

    .hero-inner {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
        display: flex;
        align-items: center;
        gap: 5rem;
        position: relative;
        z-index: 1;
    }
    .hero-content { flex: 1; min-width: 0; }

    .hero-greeting {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        font-size: .82rem;
        color: var(--accent);
        font-weight: 600;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        margin-bottom: 1.25rem;
    }
    .hero-greeting::before {
        content: '';
        display: block;
        width: 28px; height: 1.5px;
        background: var(--accent);
        border-radius: 1px;
    }

    .hero-name {
        font-size: clamp(2.4rem, 4.8vw, 4.2rem);
        font-weight: 800;
        color: var(--white-text);
        line-height: 1.1;
        margin-bottom: 1rem;
        letter-spacing: -1.5px;
    }

    .hero-role {
        font-size: clamp(.95rem, 2vw, 1.25rem);
        color: var(--light-slate);
        margin-bottom: 2rem;
        min-height: 2.2em;
        display: flex;
        align-items: center;
        gap: .3rem;
    }
    .typed-text { color: var(--accent); font-weight: 600; }
    .cursor {
        display: inline-block;
        width: 2.5px;
        background: var(--accent);
        border-radius: 1px;
        align-self: stretch;
        animation: blink .85s step-end infinite;
    }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }

    .hero-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        margin-bottom: 2.5rem;
    }
    .chip {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .4rem .9rem;
        border-radius: 25px;
        border: 1px solid rgba(100,255,218,.2);
        background: rgba(100,255,218,.04);
        color: var(--light-slate);
        font-size: .82rem;
        white-space: nowrap;
    }
    .chip i { color: var(--accent); font-size: .75rem; }

    .pulse-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 3px rgba(34,197,94,.25);
        animation: pulse-g 2s ease-in-out infinite;
        flex-shrink: 0;
    }
    @keyframes pulse-g {
        0%,100%{box-shadow:0 0 0 3px rgba(34,197,94,.25)}
        50%     {box-shadow:0 0 0 7px rgba(34,197,94,.08)}
    }

    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .85rem 2rem;
        background: var(--accent);
        color: var(--navy);
        border-radius: 10px;
        font-weight: 700;
        font-size: .94rem;
        transition: var(--ease);
    }
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 32px rgba(100,255,218,.35);
    }
    .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .85rem 2rem;
        border: 2px solid var(--accent);
        color: var(--accent);
        border-radius: 10px;
        font-weight: 600;
        font-size: .94rem;
        background: transparent;
        transition: var(--ease);
    }
    .btn-outline:hover { background: var(--accent-tint); transform: translateY(-3px); }

    /* Photo */
    .hero-photo { flex-shrink: 0; }
    .photo-ring {
        width: 280px; height: 280px;
        border-radius: 50%;
        padding: 4px;
        background: linear-gradient(135deg, var(--accent) 0%, #0ea5e9 50%, #8b5cf6 100%);
        animation: ring-pulse 4s ease-in-out infinite;
    }
    @keyframes ring-pulse {
        0%,100%{ box-shadow: 0 0 45px rgba(100,255,218,.2), 0 0 90px rgba(100,255,218,.07); }
        50%    { box-shadow: 0 0 65px rgba(100,255,218,.35),0 0 120px rgba(100,255,218,.14); }
    }
    .photo-inner {
        width: 100%; height: 100%;
        border-radius: 50%;
        overflow: hidden;
        background: var(--navy-light);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .photo-inner img { width:100%;height:100%;object-fit:cover; }
    .photo-placeholder { color: var(--accent); font-size: 5rem; opacity: .55; }

    /* Scroll indicator */
    .scroll-hint {
        position: absolute;
        bottom: 2.25rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .45rem;
        animation: float 2.6s ease-in-out infinite;
        z-index: 1;
    }
    .scroll-mouse {
        width: 22px; height: 34px;
        border: 2px solid rgba(168,178,216,.4);
        border-radius: 12px;
        display: flex;
        justify-content: center;
        padding-top: 6px;
    }
    .scroll-wheel {
        width: 3px; height: 6px;
        background: var(--accent);
        border-radius: 2px;
        animation: wheel-drop 1.6s ease-in-out infinite;
    }
    @keyframes wheel-drop { 0%{opacity:1;transform:translateY(0)} 100%{opacity:0;transform:translateY(9px)} }
    @keyframes float { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(-7px)} }
    .scroll-label {
        font-size: .64rem;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: var(--slate);
    }

    /* ══════════════════════════════════════════
       ABOUT SECTION
    ══════════════════════════════════════════ */
    .about-section { background: var(--bg-light); }

    .about-grid {
        display: grid;
        grid-template-columns: 3fr 2fr;
        gap: 4rem;
        align-items: start;
    }
    .about-text p {
        color: var(--text-mid);
        margin-bottom: 1.25rem;
        font-size: .96rem;
        line-height: 1.9;
    }
    .about-text p:last-child { margin-bottom: 0; }

    .info-card {
        background: var(--bg-white);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        position: sticky;
        top: 88px;
    }
    .info-card-head {
        background: var(--navy);
        padding: 1.25rem 1.6rem;
    }
    .info-card-head h3 {
        color: var(--accent);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 2.5px;
        text-transform: uppercase;
    }
    .info-list {}
    .info-row {
        display: flex;
        align-items: center;
        gap: .9rem;
        padding: .85rem 1.6rem;
        border-bottom: 1px solid var(--border);
        transition: background .2s;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row:hover { background: var(--bg-light); }
    .info-ico {
        width: 34px; height: 34px;
        border-radius: 8px;
        background: rgba(13,148,136,.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent-d);
        flex-shrink: 0;
        font-size: .82rem;
    }
    .info-lbl { font-size: .68rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
    .info-val { font-size: .87rem; color: var(--text-dark); font-weight: 600; line-height: 1.3; }
    .info-val a { color: var(--accent-d); }
    .avail-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        color: #15803d;
        font-weight: 600;
    }
    .avail-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #22c55e;
        animation: pulse-g 2s ease-in-out infinite;
    }

    /* ══════════════════════════════════════════
       STATS SECTION
    ══════════════════════════════════════════ */
    .stats-band { background: var(--navy); padding: 70px 5%; }
    .stats-grid {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(3,1fr);
        gap: 2rem;
    }
    .stat-card {
        background: rgba(255,255,255,.03);
        border: 1px solid rgba(100,255,218,.12);
        border-radius: var(--radius-lg);
        padding: 2.5rem 1.5rem;
        text-align: center;
        transition: var(--ease);
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content:'';
        position: absolute; inset: 0;
        border-radius: inherit;
        background: linear-gradient(135deg, rgba(100,255,218,.05) 0%, transparent 60%);
        opacity: 0; transition: opacity .3s;
    }
    .stat-card:hover::before { opacity: 1; }
    .stat-card:hover {
        border-color: rgba(100,255,218,.35);
        transform: translateY(-6px);
        box-shadow: 0 22px 50px rgba(0,0,0,.3);
    }
    .stat-ico-wrap {
        width: 52px; height: 52px;
        border-radius: 13px;
        background: rgba(100,255,218,.08);
        border: 1px solid rgba(100,255,218,.18);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.2rem;
        color: var(--accent);
        font-size: 1.2rem;
    }
    .stat-num {
        font-size: clamp(2.5rem,5vw,3.5rem);
        font-weight: 800;
        color: var(--white-text);
        line-height: 1;
        margin-bottom: .5rem;
        letter-spacing: -2px;
    }
    .stat-num .acc { color: var(--accent); }
    .stat-lbl { font-size: .87rem; color: var(--light-slate); font-weight: 500; }

    /* ══════════════════════════════════════════
       SKILLS SECTION
    ══════════════════════════════════════════ */
    .skills-section { background: var(--bg-white); }
    .skills-grid {
        display: grid;
        grid-template-columns: repeat(2,1fr);
        gap: 1.75rem 5rem;
    }
    .skill-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .55rem;
    }
    .skill-name  { font-size: .9rem; font-weight: 600; color: var(--text-dark); }
    .skill-pct   { font-size: .82rem; font-weight: 700; color: var(--accent-d); }
    .skill-track {
        height: 7px;
        background: var(--border);
        border-radius: 4px;
        overflow: hidden;
    }
    .skill-fill {
        height: 100%;
        border-radius: 4px;
        background: linear-gradient(<?= $gradDir ?>, #64ffda, #0ea5e9);
        width: 0%;
        transition: width 1.4s cubic-bezier(.25,.46,.45,.94);
    }

    /* ══════════════════════════════════════════
       EXPERIENCE (TIMELINE)
    ══════════════════════════════════════════ */
    .experience-section { background: var(--bg-light); }

    .timeline {
        position: relative;
        padding-<?= $isRtl ? 'right' : 'left' ?>: 1.5rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        <?= $isRtl ? 'right' : 'left' ?>: 0;
        top: 8px; bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--accent), rgba(100,255,218,.1));
        border-radius: 1px;
    }
    .tl-item {
        position: relative;
        padding-<?= $isRtl ? 'right' : 'left' ?>: 2.25rem;
        padding-bottom: 2.5rem;
    }
    .tl-item:last-child { padding-bottom: 0; }
    .tl-dot {
        position: absolute;
        <?= $isRtl ? 'right' : 'left' ?>: -.45rem;
        top: .45rem;
        width: 14px; height: 14px;
        border-radius: 50%;
        background: var(--accent);
        border: 3px solid var(--bg-light);
        box-shadow: 0 0 0 3px var(--accent-ring);
    }
    .tl-card {
        background: var(--bg-white);
        border-radius: var(--radius);
        padding: 1.75rem;
        border: 1px solid var(--border);
        border-<?= $isRtl ? 'right' : 'left' ?>: 4px solid var(--accent);
        box-shadow: var(--shadow);
        transition: var(--ease);
    }
    .tl-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translate<?= $isRtl ? 'X(-4px)' : 'X(4px)' ?>;
    }
    .tl-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: .35rem;
    }
    .tl-company {
        display: flex;
        align-items: center;
        gap: .35rem;
        font-size: .82rem;
        font-weight: 700;
        color: var(--accent-d);
    }
    .tl-date {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .77rem;
        color: var(--text-light);
        background: var(--bg-teal);
        border: 1px solid rgba(13,148,136,.2);
        padding: .2rem .6rem;
        border-radius: 4px;
        font-weight: 500;
        white-space: nowrap;
    }
    .tl-title { font-size: 1.05rem; font-weight: 700; color: var(--text-dark); margin-bottom: .75rem; }
    .tl-desc  { font-size: .875rem; color: var(--text-mid); line-height: 1.76; }

    /* ══════════════════════════════════════════
       EDUCATION
    ══════════════════════════════════════════ */
    .education-section { background: var(--bg-white); }
    .edu-grid {
        display: grid;
        grid-template-columns: repeat(2,1fr);
        gap: 2rem;
    }
    .edu-card {
        background: var(--bg-light);
        border-radius: var(--radius-lg);
        padding: 2rem;
        border: 1px solid var(--border);
        border-top: 4px solid var(--accent);
        position: relative;
        overflow: hidden;
        transition: var(--ease);
    }
    .edu-card::after {
        content: '\f19d';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        bottom: -.5rem;
        <?= $isRtl ? 'left' : 'right' ?>: 1rem;
        font-size: 5rem;
        color: rgba(100,255,218,.06);
        pointer-events: none;
        line-height: 1;
    }
    .edu-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-5px); }

    .edu-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .28rem .8rem;
        border-radius: 5px;
        background: rgba(13,148,136,.09);
        color: var(--accent-d);
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .5px;
        margin-bottom: 1rem;
    }
    .edu-degree { font-size: 1.02rem; font-weight: 700; color: var(--text-dark); margin-bottom: .5rem; line-height: 1.3; }
    .edu-uni  {
        display: flex; align-items: center; gap: .4rem;
        font-size: .87rem; font-weight: 600; color: var(--accent-d); margin-bottom: .35rem;
    }
    .edu-date {
        display: flex; align-items: center; gap: .35rem;
        font-size: .8rem; color: var(--text-light); margin-bottom: 1rem;
    }
    .edu-desc { font-size: .855rem; color: var(--text-mid); line-height: 1.76; }

    /* ══════════════════════════════════════════
       CERTIFICATIONS
    ══════════════════════════════════════════ */
    .cert-section { background: var(--bg-teal); }
    .cert-grid {
        display: grid;
        grid-template-columns: repeat(3,1fr);
        gap: 1.5rem;
    }
    .cert-card {
        background: var(--bg-white);
        border-radius: var(--radius);
        padding: 1.6rem;
        border: 1px solid rgba(13,148,136,.15);
        border-top: 3px solid var(--accent);
        position: relative;
        overflow: hidden;
        transition: var(--ease);
    }
    .cert-card::before {
        content: '';
        position: absolute; top: 0; <?= $isRtl ? 'left' : 'right' ?>: 0;
        width: 80px; height: 80px;
        background: radial-gradient(circle at top <?= $isRtl ? 'left' : 'right' ?>, rgba(100,255,218,.07), transparent);
        pointer-events: none;
    }
    .cert-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-5px); border-color: rgba(13,148,136,.4); }

    .cert-ico {
        width: 44px; height: 44px;
        border-radius: 10px;
        background: rgba(13,148,136,.1);
        display: flex; align-items: center; justify-content: center;
        color: var(--accent-d);
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }
    .cert-title  { font-size: .9rem; font-weight: 700; color: var(--text-dark); margin-bottom: .5rem; line-height: 1.35; }
    .cert-issuer {
        display: flex; align-items: center; gap: .3rem;
        font-size: .77rem; font-weight: 600; color: var(--accent-d); margin-bottom: .25rem;
    }
    .cert-date {
        display: flex; align-items: center; gap: .3rem;
        font-size: .74rem; color: var(--text-light); margin-bottom: .8rem;
    }
    .cert-desc {
        font-size: .8rem; color: var(--text-mid); line-height: 1.65;
        border-top: 1px solid var(--border); padding-top: .75rem; margin-top: .25rem;
    }

    /* ══════════════════════════════════════════
       CONTACT SECTION
    ══════════════════════════════════════════ */
    .contact-section { background: var(--navy); padding: 90px 5%; }
    .contact-inner {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5rem;
        align-items: center;
    }
    .contact-section .section-label { color: var(--accent); }
    .contact-section .section-title { color: var(--white-text); }
    .contact-section .section-title span { color: var(--accent); }
    .contact-section .section-line { background: linear-gradient(<?= $gradDir ?>, var(--accent), transparent); }

    .contact-tagline { color: var(--light-slate); font-size: .96rem; line-height: 1.82; margin-bottom: 2.5rem; }

    .contact-links { display: flex; flex-direction: column; gap: 1rem; }
    .contact-link {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-radius: var(--radius);
        background: rgba(255,255,255,.03);
        border: 1px solid rgba(100,255,218,.1);
        color: var(--lightest-slate);
        font-size: .9rem;
        transition: var(--ease);
    }
    .contact-link:hover {
        background: rgba(100,255,218,.06);
        border-color: rgba(100,255,218,.35);
        color: var(--accent);
        transform: translate<?= $isRtl ? 'X(-5px)' : 'X(5px)' ?>;
    }
    .cl-ico {
        width: 42px; height: 42px;
        border-radius: 10px;
        background: rgba(100,255,218,.08);
        border: 1px solid rgba(100,255,218,.15);
        display: flex; align-items: center; justify-content: center;
        color: var(--accent);
        flex-shrink: 0;
    }
    .cl-meta { flex: 1; min-width: 0; }
    .cl-label { font-size: .72rem; color: var(--slate); margin-bottom: .12rem; }
    .cl-value { font-size: .9rem; }
    .cl-arrow { color: var(--slate); font-size: .78rem; flex-shrink: 0; }

    /* CTA box */
    .cta-box {
        background: rgba(255,255,255,.03);
        border: 1px solid rgba(100,255,218,.15);
        border-radius: var(--radius-lg);
        padding: 3rem 2.25rem;
        text-align: center;
    }
    .cta-icon { font-size: 2.75rem; color: var(--accent); margin-bottom: 1.5rem; opacity: .85; }
    .cta-title { font-size: 1.45rem; font-weight: 700; color: var(--white-text); margin-bottom: .75rem; }
    .cta-text  { color: var(--light-slate); font-size: .9rem; margin-bottom: 2rem; line-height: 1.7; }

    .btn-cta {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .9rem 2.2rem;
        background: var(--accent);
        color: var(--navy);
        border-radius: 10px;
        font-weight: 700;
        font-size: .95rem;
        transition: var(--ease);
    }
    .btn-cta:hover { transform: translateY(-3px); box-shadow: 0 16px 40px rgba(100,255,218,.3); }

    .social-strip {
        display: flex;
        justify-content: center;
        gap: .9rem;
        margin-top: 1.75rem;
    }
    .soc-btn {
        width: 42px; height: 42px;
        border-radius: 10px;
        border: 1px solid rgba(100,255,218,.2);
        display: flex; align-items: center; justify-content: center;
        color: var(--light-slate);
        transition: var(--ease);
        font-size: 1rem;
    }
    .soc-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-tint); transform: translateY(-2px); }

    /* ══════════════════════════════════════════
       FOOTER
    ══════════════════════════════════════════ */
    footer {
        background: #060f1e;
        padding: 1.8rem 5%;
        text-align: center;
        border-top: 1px solid rgba(100,255,218,.07);
    }
    footer p { color: var(--slate); font-size: .82rem; line-height: 1.8; }
    footer a { color: var(--accent); }
    footer .heart { color: #f43f5e; }

    /* ══════════════════════════════════════════
       BACK TO TOP
    ══════════════════════════════════════════ */
    .btt {
        position: fixed;
        bottom: 2rem;
        <?= $isRtl ? 'left' : 'right' ?>: 2rem;
        width: 44px; height: 44px;
        border-radius: 10px;
        background: var(--accent);
        color: var(--navy);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        box-shadow: 0 4px 20px rgba(100,255,218,.4);
        transition: var(--ease);
        opacity: 0;
        pointer-events: none;
        z-index: 990;
    }
    .btt.show { opacity: 1; pointer-events: all; }
    .btt:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(100,255,218,.5); }

    /* ══════════════════════════════════════════
       RESPONSIVE
    ══════════════════════════════════════════ */
    @media (max-width: 900px) {
        .nav-links {
            position: fixed;
            top: 70px; left: 0; right: 0;
            flex-direction: column;
            align-items: flex-start;
            background: rgba(10,25,47,.98);
            backdrop-filter: blur(20px);
            padding: 1.5rem 5%;
            gap: 1.25rem;
            border-top: 1px solid rgba(100,255,218,.1);
            display: none;
            z-index: 999;
        }
        .nav-links.open { display: flex; }
        .nav-links a { font-size: .95rem; }
        .hamburger { display: flex; }
    }
    @media (max-width: 768px) {
        .section { padding: 65px 5%; }
        .section-line { margin-bottom: 2.25rem; }

        .hero-inner { flex-direction: column-reverse; text-align: center; gap: 2.5rem; }
        .hero-greeting { justify-content: center; }
        .hero-greeting::before { display: none; }
        .hero-chips { justify-content: center; }
        .hero-actions { justify-content: center; }
        .photo-ring { width: 210px; height: 210px; }

        .about-grid { grid-template-columns: 1fr; gap: 2rem; }
        .info-card  { position: static; }

        .stats-grid { grid-template-columns: 1fr; gap: 1.25rem; }

        .skills-grid { grid-template-columns: 1fr; gap: 1.25rem; }

        .edu-grid { grid-template-columns: 1fr; }

        .cert-grid { grid-template-columns: repeat(2,1fr); }

        .contact-inner { grid-template-columns: 1fr; gap: 3rem; }

        .btt { bottom: 1.25rem; <?= $isRtl ? 'left' : 'right' ?>: 1.25rem; }
    }
    @media (max-width: 480px) {
        .cert-grid { grid-template-columns: 1fr; }
        .hero-name { letter-spacing: -1px; }
        .btn-primary, .btn-outline { padding: .75rem 1.5rem; font-size: .88rem; }
    }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════
     NAVBAR
════════════════════════════════════════════════════ -->
<nav class="navbar" id="navbar" role="navigation" aria-label="Main navigation">
    <a href="#hero" class="nav-logo">
        <?= $isRtl ? 'محمدرضا قربانی' : 'MR' ?><em>/</em><?= t('Portfolio', 'وبسایت شخصی') ?>
    </a>

    <ul class="nav-links" id="navLinks" role="list">
        <li><a href="#about"><?= t('About', 'درباره') ?></a></li>
        <li><a href="#skills"><?= t('Skills', 'مهارت‌ها') ?></a></li>
        <li><a href="#experience"><?= t('Experience', 'تجربه') ?></a></li>
        <li><a href="#education"><?= t('Education', 'تحصیلات') ?></a></li>
        <li><a href="#certifications"><?= t('Certs', 'گواهینامه‌ها') ?></a></li>
        <li><a href="#contact"><?= t('Contact', 'تماس') ?></a></li>
    </ul>

    <div class="nav-actions">
        <a href="<?= $toggleHref ?>" class="btn-lang" title="Switch language"><?= $toggleLabel ?></a>
        <a href="<?= $cvFile ?>" class="btn-cv-nav" download>
            <i class="fa-solid fa-download"></i>
            <?= t('CV', 'رزومه') ?>
        </a>
        <button class="hamburger" id="hamburger" aria-label="<?= t('Toggle menu', 'باز کردن منو') ?>">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>


<!-- ═══════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════ -->
<section class="hero" id="hero">
    <div class="hero-dots" aria-hidden="true"></div>
    <div class="hero-glow-1" aria-hidden="true"></div>
    <div class="hero-glow-2" aria-hidden="true"></div>

    <div class="hero-inner">
        <div class="hero-content">
            <div class="hero-greeting"><?= t("Hello, I'm", 'سلام، من') ?></div>

            <h1 class="hero-name"><?= $name ?></h1>
            <div class="hero-greeting"><?= t(" ", 'هستم') ?></div>

            <div class="hero-role" aria-live="polite">
                <span class="typed-text" id="typed-text"></span>
                <span class="cursor" aria-hidden="true"></span>
            </div>

            <div class="hero-chips">
                <div class="chip">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <?= h(t($p['degree_en'], $p['degree_fa'])) ?>
                </div>
                <div class="chip">
                    <i class="fa-solid fa-briefcase"></i>
                    <?= h(t($p['experience_en'], $p['experience_fa'])) ?> <?= t('Experience', 'تجربه') ?>
                </div>
                <div class="chip">
                    <span class="pulse-dot"></span>
                    <?= t('Freelance', 'فریلنس') ?>: <?= h(t($p['freelance_en'], $p['freelance_fa'])) ?>
                </div>
            </div>

            <div class="hero-actions">
                <a href="#contact" class="btn-primary">
                    <i class="fa-solid fa-envelope"></i>
                    <?= t('Contact Me', 'تماس با من') ?>
                </a>
                <a href="<?= $cvFile ?>" class="btn-outline" download>
                    <i class="fa-solid fa-download"></i>
                    <?= t('Download CV', 'دانلود رزومه') ?>
                </a>
            </div>
        </div>

        <div class="hero-photo">
            <div class="photo-ring">
                <div class="photo-inner">
                    <?php if ($showPhoto): ?>
                    <img src="<?= $photoSrc ?>" alt="<?= $name ?>" loading="eager"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                    <span class="photo-placeholder" style="display:none"><i class="fa-solid fa-user"></i></span>
                    <?php else: ?>
                    <span class="photo-placeholder"><i class="fa-solid fa-user"></i></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="scroll-hint" aria-hidden="true">
        <div class="scroll-mouse"><div class="scroll-wheel"></div></div>
        <span class="scroll-label"><?= t('scroll', 'پایین') ?></span>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════
     ABOUT
════════════════════════════════════════════════════ -->
<section class="about-section section" id="about">
    <div class="container">
        <span class="section-label"><?= t('Get to know me', 'بیشتر بدانید') ?></span>
        <h2 class="section-title"><?= t('About <span>Me</span>', 'درباره <span>من</span>') ?></h2>
        <div class="section-line"></div>

        <div class="about-grid">
            <!-- Paragraphs -->
            <div class="about-text fade-up">
                <?php foreach ($p['about_' . $lang] as $para): ?>
                <p><?= h($para) ?></p>
                <?php endforeach; ?>
            </div>

            <!-- Info card -->
            <div class="fade-up d1">
                <div class="info-card">
                    <div class="info-card-head">
                        <h3><?= t('Personal Info', 'اطلاعات شخصی') ?></h3>
                    </div>
                    <div class="info-list">
                        <div class="info-row">
                            <div class="info-ico"><i class="fa-solid fa-user"></i></div>
                            <div>
                                <div class="info-lbl"><?= t('Full Name', 'نام کامل') ?></div>
                                <div class="info-val"><?= $name ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-ico"><i class="fa-solid fa-envelope"></i></div>
                            <div>
                                <div class="info-lbl"><?= t('Email', 'ایمیل') ?></div>
                                <div class="info-val"><a href="mailto:<?= $email ?>"><?= $email ?></a></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-ico"><i class="fa-solid fa-phone"></i></div>
                            <div>
                                <div class="info-lbl"><?= t('Phone', 'تلفن') ?></div>
                                <div class="info-val" dir="ltr"><a href="tel:<?= $phone ?>"><?= $phone ?></a></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-ico"><i class="fa-solid fa-graduation-cap"></i></div>
                            <div>
                                <div class="info-lbl"><?= t('Degree', 'مدرک تحصیلی') ?></div>
                                <div class="info-val" style="white-space:normal"><?= h(t($p['degree_en'], $p['degree_fa'])) ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-ico"><i class="fa-solid fa-circle-check"></i></div>
                            <div>
                                <div class="info-lbl"><?= t('Freelance', 'فریلنس') ?></div>
                                <div class="info-val">
                                    <span class="avail-badge">
                                        <span class="avail-dot"></span>
                                        <?= h(t($p['freelance_en'], $p['freelance_fa'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════
     STATS
════════════════════════════════════════════════════ -->
<div class="stats-band" role="region" aria-label="<?= t('Statistics', 'آمار') ?>">
    <div class="stats-grid">
        <div class="stat-card fade-up">
            <div class="stat-ico-wrap"><i class="fa-solid fa-calendar-days"></i></div>
            <div class="stat-num">
                <span class="stat-count" data-target="<?= (int)$stats['years'] ?>">0</span><span class="acc">+</span>
            </div>
            <div class="stat-lbl"><?= t('Years of Experience', 'سال تجربه') ?></div>
        </div>

        <div class="stat-card fade-up d1">
            <div class="stat-ico-wrap"><i class="fa-solid fa-users"></i></div>
            <div class="stat-num">
                <span class="stat-count" data-target="<?= (int)$stats['clients'] ?>">0</span><span class="acc">+</span>
            </div>
            <div class="stat-lbl"><?= t('Satisfied Clients', 'مشتری راضی') ?></div>
        </div>

        <div class="stat-card fade-up d2">
            <div class="stat-ico-wrap"><i class="fa-solid fa-layer-group"></i></div>
            <div class="stat-num">
                <span class="stat-count" data-target="<?= (int)$stats['projects'] ?>">0</span><span class="acc">+</span>
            </div>
            <div class="stat-lbl"><?= t('Projects Completed', 'پروژه تکمیل‌شده') ?></div>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════
     SKILLS
════════════════════════════════════════════════════ -->
<section class="skills-section section" id="skills">
    <div class="container">
        <span class="section-label"><?= t('What I know', 'توانایی‌ها') ?></span>
        <h2 class="section-title"><?= t('My <span>Skills</span>', 'مهارت‌های <span>من</span>') ?></h2>
        <div class="section-line"></div>

        <div class="skills-grid">
            <?php foreach ($skills as $i => $skill): ?>
            <div class="fade-up<?= ($i % 2 === 1) ? ' d1' : '' ?>">
                <div class="skill-header">
                    <span class="skill-name"><?= h(t($skill['name_en'], $skill['name_fa'])) ?></span>
                    <span class="skill-pct"><?= (int)$skill['percent'] ?>%</span>
                </div>
                <div class="skill-track">
                    <div class="skill-fill" data-percent="<?= (int)$skill['percent'] ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════
     EXPERIENCE
════════════════════════════════════════════════════ -->
<section class="experience-section section" id="experience">
    <div class="container">
        <span class="section-label"><?= t("Where I've worked", 'سابقه کاری') ?></span>
        <h2 class="section-title"><?= t('Work <span>Experience</span>', 'تجربه <span>کاری</span>') ?></h2>
        <div class="section-line"></div>

        <div class="timeline">
            <?php foreach ($experience as $exp): ?>
            <div class="tl-item fade-up">
                <div class="tl-dot" aria-hidden="true"></div>
                <div class="tl-card">
                    <div class="tl-top">
                        <div class="tl-company">
                            <i class="fa-solid fa-building"></i>
                            <?= h(t($exp['company_en'], $exp['company_fa'])) ?>
                        </div>
                        <span class="tl-date">
                            <i class="fa-regular fa-calendar"></i>
                            <?= h(t($exp['date_en'], $exp['date_fa'])) ?>
                        </span>
                    </div>
                    <div class="tl-title"><?= h(t($exp['title_en'], $exp['title_fa'])) ?></div>
                    <div class="tl-desc"><?= h(t($exp['desc_en'], $exp['desc_fa'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════
     EDUCATION
════════════════════════════════════════════════════ -->
<section class="education-section section" id="education">
    <div class="container">
        <span class="section-label"><?= t('Academic Background', 'تحصیلات') ?></span>
        <h2 class="section-title"><?= t('My <span>Education</span>', 'تحصیلات <span>من</span>') ?></h2>
        <div class="section-line"></div>

        <div class="edu-grid">
            <?php foreach ($education as $i => $edu): ?>
            <div class="edu-card fade-up<?= ($i % 2 === 1) ? ' d1' : '' ?>">
                <div class="edu-badge">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <?= t('Degree', 'مدرک') ?>
                </div>
                <div class="edu-degree"><?= h(t($edu['degree_en'], $edu['degree_fa'])) ?></div>
                <div class="edu-uni">
                    <i class="fa-solid fa-university"></i>
                    <?= h(t($edu['university_en'], $edu['university_fa'])) ?>
                </div>
                <div class="edu-date">
                    <i class="fa-regular fa-calendar"></i>
                    <?= h(t($edu['date_en'], $edu['date_fa'])) ?>
                </div>
                <div class="edu-desc"><?= h(t($edu['desc_en'], $edu['desc_fa'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════
     CERTIFICATIONS
════════════════════════════════════════════════════ -->
<section class="cert-section section" id="certifications">
    <div class="container">
        <span class="section-label"><?= t('Credentials', 'اعتبارنامه‌ها') ?></span>
        <h2 class="section-title"><?= t('My <span>Certifications</span>', 'گواهینامه‌های <span>من</span>') ?></h2>
        <div class="section-line"></div>

        <div class="cert-grid">
            <?php foreach ($certifications as $i => $cert):
                $icon  = $certIcons[$i % count($certIcons)];
                $delay = match ($i % 3) { 1 => ' d1', 2 => ' d2', default => '' };
            ?>
            <div class="cert-card fade-up<?= $delay ?>">
                <div class="cert-ico"><i class="fa-solid <?= $icon ?>"></i></div>
                <div class="cert-title"><?= h(t($cert['title_en'], $cert['title_fa'])) ?></div>
                <div class="cert-issuer">
                    <i class="fa-solid fa-award"></i>
                    <?= h(t($cert['issuer_en'], $cert['issuer_fa'])) ?>
                </div>
                <div class="cert-date">
                    <i class="fa-regular fa-calendar-check"></i>
                    <?= h(t($cert['date_en'], $cert['date_fa'])) ?>
                </div>
                <div class="cert-desc"><?= h(t($cert['desc_en'], $cert['desc_fa'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════
     CONTACT
════════════════════════════════════════════════════ -->
<section class="contact-section" id="contact">
    <div class="contact-inner">

        <!-- Left: info + links -->
        <div class="fade-up">
            <span class="section-label"><?= t("Let's talk", 'بیایید صحبت کنیم') ?></span>
            <h2 class="section-title"><?= t('Get In <span>Touch</span>', 'با من <span>تماس</span> بگیرید') ?></h2>
            <div class="section-line"></div>

            <p class="contact-tagline">
                <?= t(
                    "I'm currently open to new opportunities and collaborations. Whether you have a project in mind, a question, or just want to say hello — feel free to reach out.",
                    'در حال حاضر برای فرصت‌ها و همکاری‌های جدید آماده‌ام. چه پروژه‌ای در ذهن داشته باشید، سوالی داشته باشید، یا فقط بخواهید سلام کنید — با کمال میل پاسخگو هستم.'
                ) ?>
            </p>

            <div class="contact-links">
                <a href="mailto:<?= $email ?>" class="contact-link">
                    <div class="cl-ico"><i class="fa-solid fa-envelope"></i></div>
                    <div class="cl-meta">
                        <div class="cl-label"><?= t('Email Address', 'آدرس ایمیل') ?></div>
                        <div class="cl-value"><?= $email ?></div>
                    </div>
                    <i class="fa-solid fa-arrow-<?= $isRtl ? 'left' : 'right' ?> cl-arrow"></i>
                </a>

                <a href="tel:<?= $phone ?>" class="contact-link">
                    <div class="cl-ico"><i class="fa-solid fa-phone"></i></div>
                    <div class="cl-meta">
                        <div class="cl-label"><?= t('Phone Number', 'شماره تلفن') ?></div>
                        <div class="cl-value" dir="ltr"><?= $phone ?></div>
                    </div>
                    <i class="fa-solid fa-arrow-<?= $isRtl ? 'left' : 'right' ?> cl-arrow"></i>
                </a>

                <a href="<?= $cvFile ?>" class="contact-link" download>
                    <div class="cl-ico"><i class="fa-solid fa-file-pdf"></i></div>
                    <div class="cl-meta">
                        <div class="cl-label"><?= t('Resume / CV', 'رزومه') ?></div>
                        <div class="cl-value"><?= t('Download PDF', 'دانلود PDF') ?></div>
                    </div>
                    <i class="fa-solid fa-download cl-arrow"></i>
                </a>
            </div>
        </div>

        <!-- Right: CTA box -->
        <div class="fade-up d1">
            <div class="cta-box">
                <div class="cta-icon"><i class="fa-solid fa-paper-plane"></i></div>
                <div class="cta-title"><?= t("Let's Work Together", 'با هم کار کنیم') ?></div>
                <div class="cta-text">
                    <?= t(
                        "Available for freelance projects, full-time positions, and consulting engagements worldwide.",
                        'آماده برای پروژه‌های فریلنس، پست‌های تمام وقت و همکاری‌های مشاوره‌ای در سراسر جهان.'
                    ) ?>
                </div>
                <a href="mailto:<?= $email ?>" class="btn-cta">
                    <i class="fa-solid fa-envelope"></i>
                    <?= t('Send a Message', 'ارسال پیام') ?>
                </a>
                <div class="social-strip">
                    <a href="mailto:<?= $email ?>" class="soc-btn" title="Email">
                        <i class="fa-solid fa-envelope"></i>
                    </a>
                    <a href="tel:<?= $phone ?>" class="soc-btn" title="Phone">
                        <i class="fa-solid fa-phone"></i>
                    </a>
                    <?php if ($linkedin): ?>
                    <a href="<?= $linkedin ?>" class="soc-btn" title="LinkedIn" target="_blank" rel="noopener">
                        <i class="fa-brands fa-linkedin-in"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($github): ?>
                    <a href="<?= $github ?>" class="soc-btn" title="GitHub" target="_blank" rel="noopener">
                        <i class="fa-brands fa-github"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($telegram): ?>
                    <a href="<?= $telegram ?>" class="soc-btn" title="Telegram" target="_blank" rel="noopener">
                        <i class="fa-brands fa-telegram"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════════════ -->
<footer>
    <p>
        &copy; <?= date('Y') ?> <a href="#"><?= $name ?></a>.
        <?= t('All rights reserved.', 'تمام حقوق محفوظ است.') ?>
    </p>
    <p style="margin-top:.3rem;font-size:.75rem">
        <?= t('Crafted with', 'ساخته شده با') ?>
        <i class="fa-solid fa-heart heart"></i>
        <?= t('using PHP &amp; vanilla CSS', 'با PHP و CSS خالص') ?>
    </p>
</footer>

<!-- Back to top -->
<button class="btt" id="btt" aria-label="<?= t('Back to top', 'بازگشت به بالا') ?>">
    <i class="fa-solid fa-chevron-up"></i>
</button>


<!-- ═══════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════ -->
<script src="/assets/js/main.js"></script>
<script>
(function () {
    'use strict';

    /* ── Navbar scroll state ── */
    const navbar   = document.getElementById('navbar');
    const hamburger = document.getElementById('hamburger');
    const navLinks  = document.getElementById('navLinks');
    const btt       = document.getElementById('btt');

    window.addEventListener('scroll', () => {
        const y = window.scrollY;
        navbar.classList.toggle('scrolled', y > 60);
        btt.classList.toggle('show', y > 500);
    }, { passive: true });

    /* ── Mobile menu ── */
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('open');
        navLinks.classList.toggle('open');
    });
    navLinks.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
            hamburger.classList.remove('open');
            navLinks.classList.remove('open');
        });
    });

    /* ── Back to top ── */
    btt.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    /* ── Typing animation ── */
    const titles   = <?= $titlesJson ?>;
    const typedEl  = document.getElementById('typed-text');
    let idx = 0, ci = 0, deleting = false;

    function type() {
        const cur = titles[idx];
        typedEl.textContent = deleting
            ? cur.substring(0, --ci)
            : cur.substring(0, ++ci);

        let delay = deleting ? 52 : 90;

        if (!deleting && ci === cur.length) {
            delay = 2300; deleting = true;
        } else if (deleting && ci === 0) {
            deleting = false;
            idx = (idx + 1) % titles.length;
            delay = 420;
        }
        setTimeout(type, delay);
    }
    type();

    /* ── Generic IntersectionObserver helper ── */
    function onVisible(selector, cb, opts) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) { cb(e.target); observer.unobserve(e.target); }
            });
        }, Object.assign({ threshold: 0.14 }, opts));
        document.querySelectorAll(selector).forEach(el => observer.observe(el));
    }

    /* ── Fade-up reveal ── */
    onVisible('.fade-up', el => el.classList.add('visible'));

    /* ── Skill bar animation ── */
    onVisible('.skill-fill', el => {
        el.style.width = el.dataset.percent + '%';
    });

    /* ── Counter animation ── */
    function countUp(el, target) {
        const dur   = 1800;
        const start = performance.now();
        (function step(now) {
            const progress = Math.min((now - start) / dur, 1);
            const eased    = 1 - Math.pow(1 - progress, 3); /* ease-out cubic */
            el.textContent = Math.round(eased * target);
            if (progress < 1) requestAnimationFrame(step);
        })(start);
    }

    onVisible('.stat-count',
        el => countUp(el, parseInt(el.dataset.target, 10)),
        { threshold: 0.5 }
    );

})();
</script>
</body>
</html>
