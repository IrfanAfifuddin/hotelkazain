{{--
    Identitas warna & huruf halaman customer Kazain Hotel.
    Konsep: "hitam–emas" — diambil dari bangunan hotel (pilar hitam, ornamen guci emas,
    kayu alami) dan logo joglo. Latar tetap putih seperti web hotel pada umumnya;
    warna brand dipakai seperlunya: header, tombol utama, label kecil, garis aktif.

    Dipasang di <head> layouts/customer dan layouts/booking, SETELAH @stack('styles').
    Halaman admin/resepsionis tidak terpengaruh.
--}}
<style>
    :root {
        --kz-ink: #26211D;          /* hitam kecokelatan: header, tombol utama, judul */
        --kz-ink-soft: #3A332D;     /* hover */
        --kz-gold: #A8844F;         /* emas antik: garis, ikon, aksen */
        --kz-gold-text: #8A6A3B;    /* emas untuk teks kecil di latar putih (kontras cukup) */
        --kz-sand: #F7F3EC;         /* latar lembut untuk bagian tertentu */
        --kz-serif: 'Playfair Display', Georgia, 'Times New Roman', serif;

        /* Tombol & teks gelap di halaman pemesanan ikut warna brand */
        --color-gray-900: var(--kz-ink);
        --color-gray-800: var(--kz-ink-soft);
    }

    .font-display,
    main h1 {
        font-family: var(--kz-serif);
        letter-spacing: -0.01em;
    }

    .text-gold { color: var(--kz-gold-text); }
    .bg-ink { background-color: var(--kz-ink); }
    .bg-sand { background-color: var(--kz-sand); }
    .border-gold { border-color: var(--kz-gold); }
    .bg-gold { background-color: var(--kz-gold); }

    /* Tombol utama emas, ujung membulat */
    .btn-gold {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        background-color: var(--kz-gold);
        color: #fff;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-radius: 9999px;
        box-shadow: 0 8px 20px -8px rgba(38, 33, 29, .55);
        transition: background-color .2s ease, transform .2s ease, box-shadow .2s ease;
    }
    .btn-gold:hover { background-color: #94723F; transform: translateY(-1px); box-shadow: 0 12px 24px -10px rgba(38, 33, 29, .6); }
    .btn-gold:focus-visible { outline: 2px solid #fff; outline-offset: 3px; }

    /* Foto hero membesar pelan saat halaman dibuka */
    @keyframes kz-zoom { from { transform: scale(1.08); } to { transform: scale(1); } }
    .hero-zoom { animation: kz-zoom 8s ease-out both; }

    /* Muncul halus saat di-scroll (hanya aktif jika JavaScript jalan) */
    .js-reveal [data-reveal] { opacity: 0; transform: translateY(24px); transition: opacity .7s ease, transform .7s ease; }
    .js-reveal [data-reveal].is-visible { opacity: 1; transform: none; }

    @media (prefers-reduced-motion: reduce) {
        .hero-zoom { animation: none; }
        .js-reveal [data-reveal] { opacity: 1; transform: none; transition: none; }
        .btn-gold:hover { transform: none; }
    }

    ::selection {
        background-color: #EADFCB;
        color: var(--kz-ink);
    }

    /* Slider foto: sembunyikan scrollbar */
    .no-scrollbar { scrollbar-width: none; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>