import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import QRCode from 'qrcode';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const mainAssetsDir = path.resolve(__dirname, '../public/assets/qr-codes');
const mobileAssetsDir = path.resolve(__dirname, '../../steriqore_mobile/assets/qr_codes');

fs.mkdirSync(mainAssetsDir, { recursive: true });
fs.mkdirSync(mobileAssetsDir, { recursive: true });

const qrList = [
    {
        filename: 'qr_01_valid_gracey',
        code: 'LBL-2026-001',
        aliasCode: '01_VALID',
        title: 'Curette Gracey 1/2 Micro',
        reference: 'CUR-GRA-012',
        lot: 'LOT-2026-89A',
        cycle: 'CYC-2026-089 (Melag 40B)',
        expiry: '2027-01-15 (DLC: 150j)',
        status: 'VALID',
        statusArabic: 'صالح للتشغيل (معلومات الأداة → اختيار المريض → تأكيد)',
        statusColor: '#10B981',
        statusBg: '#ECFDF5',
        statusBorder: '#059669',
        badge: '🟢 VALID / PRÊT À L\'EMPLOI',
        description: 'Instrument conforme & stérile. Déclenche le flux complet de sélection du patient.',
    },
    {
        filename: 'qr_02_expired_miroir',
        code: 'LBL-2026-002-EXP',
        aliasCode: '02_EXPIRED',
        title: 'Miroir Dentaire Front Surface #5',
        reference: 'MIR-FS-05',
        lot: 'LOT-2025-01X',
        cycle: 'CYC-2025-044 (Melag 40B)',
        expiry: '2026-07-15 (Expiré depuis 30j)',
        status: 'EXPIRED',
        statusArabic: 'منتهي الصلاحية (محظور الاستخدام)',
        statusColor: '#EF4444',
        statusBg: '#FEF2F2',
        statusBorder: '#DC2626',
        badge: '🔴 EXPIRED / DLC DÉPASSÉE',
        description: 'La date limite d\'utilisation est échue. L\'application bloque automatiquement l\'usage (HTTP 410).',
    },
    {
        filename: 'qr_03_recalled_sonde',
        code: 'LBL-2026-003-REC',
        aliasCode: '03_RECALLED',
        title: 'Sonde Parodontale WHO 11.5',
        reference: 'SON-WHO-115',
        lot: 'LOT-2026-RECALL-9',
        cycle: 'CYC-2026-078 (Euronda E10)',
        expiry: '2026-11-20 (Rappel de lot)',
        status: 'RECALLED',
        statusArabic: 'مسترجع / تالف بيولوجياً (محظور الاستخدام)',
        statusColor: '#DC2626',
        statusBg: '#FEF2F2',
        statusBorder: '#991B1B',
        badge: '🚨 RECALLED / LOT RETIRÉ',
        description: 'Échec de test d\'indicateur biologique lors du cycle. Blocage strict avec affichage du motif.',
    },
    {
        filename: 'qr_04_already_used_implant',
        code: 'LBL-2026-004-USD',
        aliasCode: '05_ALREADY_USED',
        title: 'Implant Titane 3.5mm Grade V',
        reference: 'IMP-TIT-35',
        lot: 'LOT-2026-99B',
        cycle: 'CYC-2026-090 (Melag 40B)',
        expiry: '2026-12-31 (Utilisé ce matin)',
        status: 'ALREADY_USED',
        statusArabic: 'مستخدم مسبقاً (تحذير وتنبيه للممارس)',
        statusColor: '#F59E0B',
        statusBg: '#FFFBEB',
        statusBorder: '#D97706',
        badge: '⚠️ ALREADY USED / DÉJÀ UTILISÉ',
        description: 'Cet instrument a déjà été scanné et affecté à un dossier patient (HTTP 409).',
    },
    {
        filename: 'qr_05_valid_pince_gouge',
        code: 'LBL-2026-005',
        aliasCode: 'LBL-2026-005',
        title: 'Pince Gouge Friedman 14cm',
        reference: 'PIN-GOU-140',
        lot: 'LOT-2026-55C',
        cycle: 'CYC-2026-091 (Euronda E10)',
        expiry: '2027-02-11 (DLC: 180j)',
        status: 'VALID',
        statusArabic: 'صالح للتشغيل (جراحة وأنسجة)',
        statusColor: '#10B981',
        statusBg: '#ECFDF5',
        statusBorder: '#059669',
        badge: '🟢 VALID / PRÊT À L\'EMPLOI',
        description: 'Instrument chirurgical prêt à être affecté à un patient dans le bloc.',
    },
];

async function generate() {
    console.log('Generating QR label assets...');

    for (const item of qrList) {
        // Generate pure QR SVG data URI
        const qrSvg = await QRCode.toString(item.code, {
            type: 'svg',
            margin: 1,
            color: {
                dark: '#111827',
                light: '#ffffff',
            },
        });

        // Strip xml header if present
        const cleanQrSvg = qrSvg.replace(/<\?xml.*?\?>/i, '').trim();

        // Create standalone high-res SVG Card (440 x 560 px)
        const fullCardSvg = `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 440 580" width="440" height="580" style="background:#ffffff; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <defs>
        <filter id="shadow" x="-5%" y="-5%" width="110%" height="110%">
            <feDropShadow dx="0" dy="4" stdDeviation="8" flood-color="#000000" flood-opacity="0.08"/>
        </filter>
        <clipPath id="cardClip">
            <rect x="10" y="10" width="420" height="560" rx="16" ry="16"/>
        </clipPath>
    </defs>

    <!-- Card Background with subtle border -->
    <rect x="10" y="10" width="420" height="560" rx="16" fill="#ffffff" stroke="#E5E7EB" stroke-width="2" filter="url(#shadow)"/>

    <g clip-path="url(#cardClip)">
        <!-- Top Brand Header Bar -->
        <rect x="10" y="10" width="420" height="48" fill="#18181B"/>
        <text x="30" y="40" fill="#FFFFFF" font-size="14" font-weight="700" letter-spacing="1.5">STERIQORE TRACEABILITY</text>
        <text x="360" y="40" fill="#9CA3AF" font-size="11" font-weight="600">POUCH LABEL</text>

        <!-- Product Information Header -->
        <text x="30" y="85" fill="#111827" font-size="18" font-weight="800">${item.title}</text>
        <text x="30" y="108" fill="#6B7280" font-size="12" font-weight="500">Ref: <tspan font-weight="700" fill="#374151">${item.reference}</tspan>  |  Lot: <tspan font-weight="700" fill="#374151">${item.lot}</tspan></text>
        <text x="30" y="128" fill="#6B7280" font-size="12" font-weight="500">Cycle: <tspan font-weight="600" fill="#374151">${item.cycle}</tspan></text>

        <!-- QR Code Container Box -->
        <rect x="75" y="145" width="290" height="290" rx="12" fill="#FAFAFA" stroke="#E5E7EB" stroke-width="1.5"/>

        <!-- Embedded QR Code -->
        <g transform="translate(90, 160) scale(1.04)">
            ${cleanQrSvg}
        </g>

        <!-- QR Code String Label Under Box -->
        <rect x="130" y="445" width="180" height="26" rx="6" fill="#F3F4F6"/>
        <text x="220" y="462" fill="#1F2937" font-size="13" font-weight="700" text-anchor="middle" font-family="monospace">${item.code}</text>

        <!-- Status Bottom Band / Line with Color -->
        <rect x="10" y="490" width="420" height="80" fill="${item.statusBg}" stroke="${item.statusBorder}" stroke-width="2"/>
        <!-- Accent indicator line right at top of banner -->
        <rect x="10" y="488" width="420" height="6" fill="${item.statusColor}"/>

        <!-- Status Badge & Description -->
        <text x="30" y="522" fill="${item.statusColor}" font-size="15" font-weight="800" letter-spacing="0.5">${item.badge}</text>
        <text x="30" y="546" fill="#374151" font-size="11" font-weight="600">${item.description}</text>
    </g>
</svg>
`.trim();

        // Write SVG files
        const svgPathMain = path.join(mainAssetsDir, `${item.filename}.svg`);
        const svgPathMobile = path.join(mobileAssetsDir, `${item.filename}.svg`);
        fs.writeFileSync(svgPathMain, fullCardSvg, 'utf8');
        fs.writeFileSync(svgPathMobile, fullCardSvg, 'utf8');

        // Also generate pure standard PNG QR Code for direct scanner compatibility
        const pngPathMain = path.join(mainAssetsDir, `${item.filename}.png`);
        const pngPathMobile = path.join(mobileAssetsDir, `${item.filename}.png`);
        await QRCode.toFile(pngPathMain, item.code, {
            width: 480,
            margin: 2,
            color: {
                dark: '#09090b',
                light: '#ffffff',
            },
        });
        fs.copyFileSync(pngPathMain, pngPathMobile);

        console.log(`✅ Generated: ${item.filename} (${item.code}) [${item.status}]`);
    }

    // Generate test dashboard HTML file
    const htmlContent = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steriqore QA QR-Code Test Suite</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #09090B;
            color: #F4F4F5;
            padding: 32px 20px;
            line-height: 1.5;
        }
        .header {
            max-width: 1200px;
            margin: 0 auto 36px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #FFFFFF, #A1A1AA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .header p {
            color: #A1A1AA;
            font-size: 15px;
            max-width: 680px;
            margin: 0 auto;
        }
        .grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }
        .card {
            background: #18181B;
            border-radius: 16px;
            border: 1px solid #27272A;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .card:hover {
            transform: translateY(-4px);
            border-color: #3F3F46;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #27272A;
        }
        .card-header h2 {
            font-size: 17px;
            font-weight: 700;
            color: #FAFAFA;
        }
        .card-header .meta {
            font-size: 12px;
            color: #71717A;
            margin-top: 2px;
        }
        .card-body {
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #09090B;
        }
        .qr-wrapper {
            background: #FFFFFF;
            padding: 14px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            margin-bottom: 16px;
        }
        .qr-img {
            width: 220px;
            height: 220px;
            display: block;
        }
        .code-pill {
            background: #27272A;
            color: #E4E4E7;
            font-family: ui-monospace, SFMono-Regular, monospace;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 9999px;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .card-footer {
            padding: 16px 20px;
            margin-top: auto;
            position: relative;
        }
        .status-stripe {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .desc {
            font-size: 12px;
            color: #A1A1AA;
            line-height: 1.4;
        }
        .arabic-text {
            font-size: 12px;
            font-weight: 600;
            margin-top: 6px;
            direction: rtl;
            text-align: right;
        }
        .copy-btn {
            background: #27272A;
            border: 1px solid #3F3F46;
            color: #FAFAFA;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            margin-top: 8px;
        }
        .copy-btn:hover {
            background: #3F3F46;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Steriqore Mobile QR QA Testing Suite</h1>
        <p>Directly scan these QR codes using the mobile scanner camera or type the code in manual entry to test all 4 lifecycle states.</p>
    </div>

    <div class="grid">
        ${qrList.map(item => `
            <div class="card">
                <div class="card-header">
                    <h2>${item.title}</h2>
                    <div class="meta">Ref: ${item.reference} • Lot: ${item.lot} • ${item.cycle}</div>
                </div>
                <div class="card-body">
                    <div class="qr-wrapper">
                        <img src="/assets/qr-codes/${item.filename}.png" alt="${item.code}" class="qr-img">
                    </div>
                    <div class="code-pill">${item.code}</div>
                    <button class="copy-btn" onclick="navigator.clipboard.writeText('${item.code}'); alert('Copied: ${item.code}')">📋 Copy Code</button>
                </div>
                <div class="card-footer" style="background: ${item.statusBg};">
                    <div class="status-stripe" style="background: ${item.statusColor};"></div>
                    <div class="badge" style="color: ${item.statusColor};">${item.badge}</div>
                    <div class="desc" style="color: #374151;">${item.description}</div>
                    <div class="arabic-text" style="color: ${item.statusColor};">${item.statusArabic}</div>
                </div>
            </div>
        `).join('')}
    </div>
</body>
</html>
    `.trim();

    const htmlPath = path.resolve(__dirname, '../public/qr-codes.html');
    fs.writeFileSync(htmlPath, htmlContent, 'utf8');
    console.log(`✅ Generated interactive HTML test suite at: ${htmlPath}`);
}

generate().catch(err => {
    console.error('Error generating QR labels:', err);
    process.exit(1);
});
