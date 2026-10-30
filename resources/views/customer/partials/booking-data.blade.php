{{--
    Data & helper bersama untuk halaman Isi Data Diri dan Pembayaran.
    Membaca parameter pemesanan dari URL (dikirim dari halaman Cek Ketersediaan).
    Pakai di dalam @push('scripts'):  @include('customer.partials.booking-data')

--}}
<script>
    window.KazainBooking = (function () {
        const ROOM_TYPES = @json($roomTypes);
        const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const pad = (n) => String(n).padStart(2, '0');
        const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
        const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const formatDateTime = (d) => `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}, ${pad(d.getHours())}:${pad(d.getMinutes())} WIB`;
        // Contoh: "Selasa, 21 Juli 2026, 09:00 WIB"
        const formatDateTimeLong = (d) => `${DAYS[d.getDay()]}, ${formatDateTime(d)}`;

        // Baca & validasi parameter pemesanan dari URL
        function parse(search = window.location.search) {
            const p = new URLSearchParams(search);
            const date = p.get('check_in_date');
            const time = p.get('check_in_time');
            const bookingType = p.get('booking_type');
            const guests = Math.max(1, parseInt(p.get('guests') || '1', 10));

            if (!date || !/^\d{4}-\d{2}-\d{2}$/.test(date) || !time || !/^\d{2}:\d{2}$/.test(time)) return null;
            if (!['Full Day', 'Short Time 5 Jam', 'Short Time 8 Jam'].includes(bookingType)) return null;

            const isFull = bookingType === 'Full Day';
            // Full Day bisa lebih dari 1 malam: duration_hours = 24 × jumlah malam
            const nights = isFull ? Math.min(30, Math.max(1, Math.round(parseInt(p.get('duration_hours') || '24', 10) / 24))) : 0;
            const duration = isFull ? 24 * nights : parseInt(bookingType.match(/\d+/)[0], 10);
            const priceKey = isFull ? 'full' : String(duration);

            const rooms = ROOM_TYPES
                .map((room) => ({ room, qty: Math.max(0, parseInt(p.get(`rooms[${room.type}]`) || '0', 10)) }))
                .filter((r) => r.qty > 0);
            if (!rooms.length) return null;

            const [y, m, d] = date.split('-').map(Number);
            const [hh, mm] = time.split(':').map(Number);
            const start = new Date(y, m - 1, d, hh, mm);
            const end = new Date(start);
            if (isFull) {
                end.setDate(end.getDate() + nights);
                end.setHours(12, 0, 0, 0);
            } else {
                end.setHours(end.getHours() + duration);
            }

            // Perkiraan untuk ditampilkan; total yang ditagih tetap dihitung ulang oleh server
            const total = rooms.reduce((sum, r) => sum + r.room.prices[priceKey] * r.qty, 0) * (isFull ? nights : 1);

            return {
                query: p,
                date, time, bookingType, isFull, nights, duration, guests, rooms, start, end, total,
                roomsText: rooms.map((r) => `${r.qty}× ${r.room.short_name}`).join(', '),
                durationText: isFull ? `Full Day (${nights} malam)` : `${duration} Jam`,
                image: rooms[0].room.image,
            };
        }

        // Query string pemesanan saja (tanpa data pribadi) untuk diteruskan antarhalaman
        function bookingQuery(booking) {
            const q = new URLSearchParams();
            ['check_in_date', 'check_in_time', 'booking_type', 'duration_hours', 'guests'].forEach((k) => {
                if (booking.query.get(k)) q.set(k, booking.query.get(k));
            });
            booking.rooms.forEach((r) => q.set(`rooms[${r.room.type}]`, String(r.qty)));
            return q;
        }

        return { ROOM_TYPES, rupiah, formatDateTime, formatDateTimeLong, parse, bookingQuery, pad };
    })();
</script>