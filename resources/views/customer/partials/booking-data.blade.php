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

            // Parse room quantities (new format: "King Bed:2|Twin Bed:1")
            const roomQuantitiesParam = p.get('room_quantities');
            const roomQuantities = {};
            let totalRooms = 0;
            if (roomQuantitiesParam) {
                roomQuantitiesParam.split('|').forEach(item => {
                    const [roomType, quantity] = item.split(':');
                    if (roomType && quantity) {
                        const qty = parseInt(quantity, 10);
                        if (qty > 0) {
                            roomQuantities[roomType] = qty;
                            totalRooms += qty;
                        }
                    }
                });
            }

            if (!date || !/^\d{4}-\d{2}-\d{2}$/.test(date) || !time || !/^\d{2}:\d{2}$/.test(time) || totalRooms === 0) return null;
            if (!['Full Day', 'Short Time 5 Jam', 'Short Time 8 Jam'].includes(bookingType)) return null;

            const isFull = bookingType === 'Full Day';
            // Full Day bisa lebih dari 1 malam: duration_hours = 24 × jumlah malam
            const nights = isFull ? Math.min(30, Math.max(1, Math.round(parseInt(p.get('duration_hours') || '24', 10) / 24))) : 0;
            const duration = isFull ? 24 * nights : parseInt(bookingType.match(/\d+/)[0], 10);
            const priceKey = isFull ? 'full' : String(duration);

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

            // Build readable room description from quantities
            const roomsDescription = Object.entries(roomQuantities)
                .map(([type, qty]) => `${qty} ${type}`)
                .join(', ');

            // Can't calculate exact total client-side without room details - server will calculate
            // Display placeholder until server response
            const total = 0;

            return {
                query: p,
                roomQuantities,
                totalRooms,
                date, time, bookingType, isFull, nights, duration, guests, start, end, total,
                roomsText: `${totalRooms} kamar (${roomsDescription})`,
                durationText: isFull ? `Full Day (${nights} malam)` : `${duration} Jam`,
                image: null,
            };
        }

        // Query string pemesanan saja (tanpa data pribadi) untuk diteruskan antarhalaman
        function bookingQuery(booking) {
            const q = new URLSearchParams();
            ['check_in_date', 'check_in_time', 'booking_type', 'duration_hours', 'guests', 'room_quantities'].forEach((k) => {
                if (booking.query.get(k)) q.set(k, booking.query.get(k));
            });
            return q;
        }

        return { ROOM_TYPES, rupiah, formatDateTime, formatDateTimeLong, parse, bookingQuery, pad };
    })();
</script>