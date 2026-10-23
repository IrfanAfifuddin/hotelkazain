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

    ::selection {
        background-color: #EADFCB;
        color: var(--kz-ink);
    }

    /* Slider foto: sembunyikan scrollbar */
    .no-scrollbar { scrollbar-width: none; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>