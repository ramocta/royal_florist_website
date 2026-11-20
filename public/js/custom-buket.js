/* ==========================================================
   Custom Buket - Live Preview & Hitung Harga
   ========================================================== */
(function () {
    'use strict';

    const MAX_PER_FLOWER = 20;
    const FEE = (window.CB_CONFIG && window.CB_CONFIG.assemblyFee) || 0;

    const form = document.getElementById('customForm');
    const svg = document.getElementById('bouquetSvg');
    const totalEl = document.getElementById('totalPrice');

    const sumFlowers = document.getElementById('sumFlowers');
    const sumWrap = document.getElementById('sumWrap');
    const sumRibbon = document.getElementById('sumRibbon');
    const sumCard = document.getElementById('sumCard');

    const wrapInput = document.getElementById('wrapInput');
    const ribbonInput = document.getElementById('ribbonInput');
    const cardMessage = document.getElementById('cardMessage');

    const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');

    /* ---------- Ambil state saat ini dari DOM ---------- */
    function getState() {
        const flowers = [];
        document.querySelectorAll('.cb-flower').forEach((el) => {
            flowers.push({
                key: el.dataset.flower,
                name: el.dataset.name,
                price: parseInt(el.dataset.price, 10),
                qty: parseInt(el.querySelector('.qty-value').textContent, 10),
            });
        });

        const wrapEl = document.querySelector('.cb-swatch.active');
        const ribbonEl = document.querySelector('.cb-ribbon.active');

        return {
            flowers,
            wrap: {
                name: wrapEl.dataset.name,
                outer: wrapEl.dataset.outer,
                inner: wrapEl.dataset.inner,
            },
            ribbon: {
                name: ribbonEl.dataset.name,
                price: parseInt(ribbonEl.dataset.price, 10),
                color: ribbonEl.dataset.color,
            },
            hasCard: cardMessage.value.trim().length > 0,
        };
    }

    /* ---------- Hitung harga ---------- */
    function calcTotal(s) {
        const flowerTotal = s.flowers.reduce((sum, f) => sum + f.price * f.qty, 0);
        return flowerTotal + s.ribbon.price + FEE;
    }

    /* ---------- Gambar SVG buket ---------- */
    // Posisi bunga di dalam wrapping (urut dari tengah ke luar)
    const SLOTS = [
        [130, 78], [100, 92], [160, 92], [130, 110],
        [78, 100], [182, 100], [115, 70], [145, 70],
        [90, 118], [170, 118], [130, 60], [105, 130],
        [155, 130], [70, 84], [190, 84], [130, 132],
    ];

    function flowerSvg(key, x, y) {
        switch (key) {
            case 'mawar':
                return `<circle cx="${x}" cy="${y}" r="13" fill="#8b2547"/>
                        <circle cx="${x + 3}" cy="${y - 4}" r="3" fill="#fff"/>`;
            case 'lily':
                return `<polygon points="${x},${y - 14} ${x + 4},${y - 4} ${x + 14},${y} ${x + 4},${y + 4} ${x},${y + 14} ${x - 4},${y + 4} ${x - 14},${y} ${x - 4},${y - 4}" fill="#fff" stroke="#f4a8c0" stroke-width="1.5"/>
                        <circle cx="${x}" cy="${y}" r="3" fill="#f5c518"/>`;
            case 'tulip':
                return `<ellipse cx="${x}" cy="${y}" r="11" ry="13" fill="#ea8fb4"/>
                        <path d="M${x - 8} ${y - 6} L${x} ${y - 14} L${x + 8} ${y - 6}" fill="#d96b97"/>`;
            case 'baby':
                return `<circle cx="${x}" cy="${y}" r="3" fill="#fff"/>
                        <circle cx="${x + 6}" cy="${y + 4}" r="2.5" fill="#fff"/>
                        <circle cx="${x - 6}" cy="${y + 3}" r="2.5" fill="#fff"/>`;
            default:
                return '';
        }
    }

    function renderBouquet(s) {
        const { outer, inner } = s.wrap;
        const ribbon = s.ribbon.color;

        // Susun daftar bunga: baby's breath di belakang, lainnya di depan
        const list = [];
        s.flowers.forEach((f) => {
            for (let i = 0; i < Math.min(f.qty, MAX_PER_FLOWER); i++) list.push(f.key);
        });
        list.sort((a, b) => (a === 'baby') - (b === 'baby')).reverse();

        let flowersSvg = '';
        list.slice(0, SLOTS.length).forEach((key, i) => {
            const [x, y] = SLOTS[i];
            flowersSvg += flowerSvg(key, x, y);
        });

        svg.innerHTML = `
            <polygon points="20,40 240,40 195,270 65,270" fill="${outer}"/>
            <polygon points="38,52 222,52 184,254 76,254" fill="${inner}"/>
            ${flowersSvg}
            <path d="M130 228 L98 262 L112 266 L130 244 L148 266 L162 262 Z" fill="${ribbon}" stroke="rgba(0,0,0,.08)"/>
            <ellipse cx="130" cy="236" rx="8" ry="6" fill="${outer}"/>
        `;
    }

    /* ---------- Ringkasan teks ---------- */
    function renderSummary(s) {
        const chosen = s.flowers.filter((f) => f.qty > 0);
        sumFlowers.textContent = chosen.length
            ? chosen.map((f) => `${f.qty} ${f.name}`).join(', ')
            : 'Belum ada bunga dipilih';
        sumWrap.textContent = s.wrap.name;
        sumRibbon.textContent = s.ribbon.name;
        sumCard.textContent = s.hasCard ? 'Ya (Included)' : 'Tidak';
    }

    /* ---------- Satu fungsi untuk update semuanya ---------- */
    function update() {
        const s = getState();

        // sorot kartu bunga yang qty-nya > 0
        document.querySelectorAll('.cb-flower').forEach((el) => {
            el.classList.toggle('selected', parseInt(el.querySelector('.qty-value').textContent, 10) > 0);
        });

        renderBouquet(s);
        renderSummary(s);
        totalEl.textContent = rupiah(calcTotal(s));
    }

    /* ---------- Event: tombol +/- jumlah bunga ---------- */
    document.querySelectorAll('.cb-flower').forEach((card) => {
        const valueEl = card.querySelector('.qty-value');
        const hidden = card.querySelector('input[type="hidden"]');

        const setQty = (n) => {
            // batas maksimal mengikuti stok dari database (data-max)
            const max = parseInt(card.dataset.max, 10) || MAX_PER_FLOWER;
            n = Math.max(0, Math.min(max, n));
            valueEl.textContent = n;
            hidden.value = n;
            update();
        };

        card.querySelector('.qty-minus').addEventListener('click', () => setQty(parseInt(valueEl.textContent, 10) - 1));
        card.querySelector('.qty-plus').addEventListener('click', () => setQty(parseInt(valueEl.textContent, 10) + 1));
    });

    /* ---------- Event: warna wrapping ---------- */
    document.querySelectorAll('.cb-swatch').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.cb-swatch').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            wrapInput.value = btn.dataset.wrap;
            update();
        });
    });

    /* ---------- Event: pita ---------- */
    document.querySelectorAll('.cb-ribbon').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.cb-ribbon').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            ribbonInput.value = btn.dataset.ribbon;
            update();
        });
    });

    /* ---------- Event: kartu ucapan ---------- */
    cardMessage.addEventListener('input', update);

    /* ---------- Validasi sebelum submit ---------- */
    form.addEventListener('submit', (e) => {
        const total = getState().flowers.reduce((n, f) => n + f.qty, 0);
        if (total === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 tangkai bunga terlebih dahulu.');
        }
    });

    update(); // render awal
})();