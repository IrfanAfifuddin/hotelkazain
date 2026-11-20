<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Rules\EmailDomainIsAllowed;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /**
     * Tampilkan daftar pemesanan + pencarian.
     */
    public function index(Request $request)
    {
        $search = $request->query('q');

        $query = Booking::with(['customer', 'room', 'rooms', 'extensions'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('room', function ($rq) use ($search) {
                        $rq->where('room_number', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->paginate(10)->withQueryString();

        // Data seluruh pemesanan aktif untuk komponen Kalender (tidak termasuk yang dibatalkan)
        $calendarBookings = Booking::with(['customer', 'room'])
            ->where('booking_status', '!=', 'Cancelled')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'booking_code' => $b->booking_code,
                    'customer_name' => $b->customer->customer_name ?? '-',
                    'phone_number' => $b->customer->phone_number ?? '-',
                    'room_number' => $b->room->room_number ?? '-',
                    'room_type' => $b->room->room_type ?? '-',
                    'booking_type' => $b->booking_type,
                    'booking_status' => $b->booking_status,
                    'payment_method' => $b->payment_method ?? 'Belum ada data',
                    'payment_status' => $b->payment_status ?? 'Pending',
                    'total_price' => $b->total_price,
                    'total_price_formatted' => 'Rp '.number_format($b->total_price, 0, ',', '.'),
                    'check_in_date' => $b->check_in_time->copy()->shiftTimezone('Asia/Jakarta')->format('Y-m-d'),
                    'check_out_date' => $b->check_out_time->copy()->shiftTimezone('Asia/Jakarta')->format('Y-m-d'),
                    'check_in_formatted' => $b->check_in_time->copy()->shiftTimezone('Asia/Jakarta')->format('d M Y H:i'),
                    'check_out_formatted' => $b->check_out_time->copy()->shiftTimezone('Asia/Jakarta')->format('d M Y H:i'),
                ];
            });

        return view('bookings.index', compact('bookings', 'search', 'calendarBookings'));
    }

    /**
     * Tampilkan form Tambah Pemesanan.
     */
    public function create()
    {
        $rooms = Room::query()
            ->orderBy('room_number')
            ->get();

        return view('bookings.create', compact('rooms'));
    }

    /**
     * Kembalikan daftar kamar beserta ketersediaannya untuk rentang waktu yang dipilih.
     */
    public function availableRooms(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'check_in_time' => ['required', 'date'],
            'check_out_time' => ['sometimes', 'nullable', 'date', 'after:check_in_time'],
            'booking_type' => ['required', 'in:Short Time 5 Jam,Short Time 8 Jam,Full Day'],
            'exclude_booking_id' => ['sometimes', 'nullable', 'integer', 'exists:bookings,id'],
        ]);

        $checkIn = Carbon::parse($validated['check_in_time']);
        $checkOut = isset($validated['check_out_time'])
            ? Carbon::parse($validated['check_out_time'])
            : $this->calculateCheckOutTime($checkIn, $validated['booking_type']);

        $unavailableRoomIds = Booking::overlappingRoomIds(
            $checkIn,
            $checkOut,
            $validated['exclude_booking_id'] ?? null,
        );

        $rooms = Room::query()
            ->orderBy('room_number')
            ->get([
                'id',
                'room_number',
                'room_type',
                'room_status',
                'price_5_hours',
                'price_8_hours',
                'full_day_price',
            ])
            ->map(function (Room $room) use ($unavailableRoomIds): array {
                $isMaintenance = $room->room_status === 'Maintenance';
                $isBooked = in_array($room->id, $unavailableRoomIds, true);

                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type' => $room->room_type,
                    'status' => match (true) {
                        $isMaintenance => 'Maintenance',
                        $isBooked => 'Booked',
                        default => 'Available',
                    },
                    'is_available' => ! $isMaintenance && ! $isBooked,
                    'is_maintenance' => $isMaintenance,
                    'is_booked' => $isBooked,
                    'price_5_hours' => (int) $room->price_5_hours,
                    'price_8_hours' => (int) $room->price_8_hours,
                    'full_day_price' => (int) $room->full_day_price,
                ];
            });

        return response()->json([
            'check_in_time' => $checkIn->toDateTimeString(),
            'check_out_time' => $checkOut->toDateTimeString(),
            'rooms' => $rooms->values()->all(),
        ]);
    }

    /**
     * Simpan pemesanan baru dengan pencegahan double booking yang ketat.
     * Mendukung single room dan multiple room bookings.
     */
    public function store(Request $request, EmailService $emailService)
    {
        $roomCount = is_array($request->room_ids) ? count($request->room_ids) : 1;
        $maxGuests = max(2, $roomCount * 2);

        $request->validate([
            'customer_name' => ['required', 'string', 'regex:/^[a-zA-Z\s]+$/', 'max:255'],
            'phone_number' => ['required', 'numeric', 'digits_between:10,15'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', new EmailDomainIsAllowed],
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'required|integer|exists:rooms,id',
            'booking_type' => 'required|in:Short Time 5 Jam,Short Time 8 Jam,Full Day',
            'check_in_time' => ['required', 'date', 'after_or_equal:'.now('Asia/Jakarta')->format('Y-m-d H:i')],
            'booking_status' => 'required|in:Confirmed,Check-in',
            'payment_method' => ['required', Rule::in(['Cash', 'Transfer'])],
            'number_of_guests' => ['sometimes', 'required', 'integer', 'min:1', 'max:'.$maxGuests],
        ], [
            'customer_name.regex' => 'Nama customer hanya boleh berisi huruf dan spasi.',
            'phone_number.numeric' => 'Nomor telepon harus berupa angka.',
            'phone_number.digits_between' => 'Nomor telepon harus antara 10 sampai 15 digit.',
            'email.email' => 'Domain email tidak valid atau tidak aktif.',
            'number_of_guests.required' => 'Jumlah orang wajib diisi.',
            'number_of_guests.integer' => 'Jumlah orang harus berupa angka.',
            'number_of_guests.min' => 'Jumlah orang minimal 1 orang.',
            'number_of_guests.max' => "Jumlah orang maksimal {$maxGuests} orang (maksimal 2 orang per kamar).",
        ]);

        $checkIn = Carbon::parse($request->check_in_time);
        $checkOut = $this->calculateCheckOutTime($checkIn, $request->booking_type);

        // Validasi ketat double booking untuk semua kamar yang dipilih.
        // Termasuk kamar tambahan pada pivot booking_rooms, bukan hanya bookings.room_id.
        $roomIds = array_values(array_unique(array_map('intval', $request->room_ids)));
        $conflictingRoomIds = array_values(array_intersect(
            $roomIds,
            Booking::overlappingRoomIds($checkIn, $checkOut),
        ));

        if ($conflictingRoomIds !== []) {
            $conflictRoomNumbers = Room::whereIn('id', $conflictingRoomIds)
                ->orderBy('room_number')
                ->pluck('room_number')
                ->join(', ');

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Kamar {$conflictRoomNumbers} sudah dipesan atau tidak tersedia pada rentang waktu tersebut.",
                    'errors' => [
                        'room_ids' => ["Kamar {$conflictRoomNumbers} sudah dipesan atau tidak tersedia pada rentang waktu tersebut."],
                    ],
                ], 422);
            }

            return back()->withInput()->withErrors(['room_ids' => "Kamar {$conflictRoomNumbers} sudah dipesan atau tidak tersedia pada rentang waktu tersebut."]);
        }

        $booking = DB::transaction(function () use ($request, $checkIn, $checkOut, $roomIds) {
            // Simpan / Buat Data Customer
            $customer = Customer::updateOrCreate(
                ['email' => $request->email],
                [
                    'customer_name' => $request->customer_name,
                    'phone_number' => $request->phone_number,
                ]
            );

            // Fetch semua kamar dan hitung total harga
            $rooms = Room::whereIn('id', $roomIds)->get();
            $totalPrice = 0;
            $roomPrices = [];

            foreach ($rooms as $room) {
                $roomPrice = match ($request->booking_type) {
                    'Short Time 5 Jam' => $room->price_5_hours,
                    'Short Time 8 Jam' => $room->price_8_hours,
                    'Full Day' => $room->full_day_price,
                    default => 0,
                };
                $totalPrice += $roomPrice;
                $roomPrices[$room->id] = $roomPrice;
            }

            // Gunakan room_id pertama untuk backward compatibility
            $primaryRoomId = $roomIds[0];

            $booking = Booking::create([
                'room_id' => $primaryRoomId,
                'customer_id' => $customer->id,
                'booking_code' => Booking::generateCode(),
                'booking_type' => $request->booking_type,
                'check_in_time' => $checkIn,
                'check_out_time' => $checkOut,
                'booking_status' => $request->booking_status,
                'payment_method' => $request->payment_method,
                'payment_status' => 'Paid',
                'payment_time' => now(),
                'payment_expires_at' => null,
                'total_price' => (int) $totalPrice,
                'source' => 'Offline',
                'number_of_guests' => (int) ($request->number_of_guests ?? 1),
            ]);

            // Simpan pivot records untuk semua kamar
            foreach ($rooms as $room) {
                $booking->rooms()->attach($room->id, ['price' => $roomPrices[$room->id]]);
            }

            // Jika status booking adalah Check-in, sinkronkan status kamar menjadi Occupied
            if ($request->booking_status === 'Check-in') {
                Room::whereIn('id', $roomIds)->update(['room_status' => 'Occupied']);
            }

            return $booking;
        });

        // Kirim email konfirmasi secara sinkron setelah transaksi selesai.
        // Bukan queue, sehingga email tetap terkirim walaupun queue worker tidak aktif.
        $emailService->sendBookingConfirmation($booking);

        if ($request->expectsJson()) {
            $booking->load(['customer', 'rooms']);

            return response()->json([
                'success' => true,
                'message' => 'Pemesanan berhasil dibuat dengan kode '.$booking->booking_code,
                'booking_code' => $booking->booking_code,
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'customer_name' => $booking->customer->customer_name,
                    'room_count' => $booking->rooms->count(),
                    'check_in_formatted' => $booking->check_in_time->format('d M Y H:i'),
                    'check_out_formatted' => $booking->check_out_time->format('d M Y H:i'),
                    'booking_status' => $booking->booking_status,
                    'total_price_formatted' => 'Rp '.number_format($booking->total_price, 0, ',', '.'),
                ],
                'stats' => [
                    'available' => Room::where('room_status', 'Available')->count(),
                    'occupied' => Room::where('room_status', 'Occupied')->count(),
                    'today_check_in' => Booking::whereDate('check_in_time', today())->count(),
                    'today_check_out' => Booking::whereDate('check_out_time', today())->count(),
                ],
            ], 201);
        }

        $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';

        return redirect()->route($prefix.'.pemesanan.index')->with('success', 'Pemesanan berhasil dibuat dengan kode '.$booking->booking_code);
    }

    /**
     * Tampilkan form Edit Pemesanan.
     */
    public function edit(Booking $booking)
    {
        $booking->load(['customer', 'rooms']);
        $rooms = Room::orderBy('room_number')->get();

        return view('bookings.edit', compact('booking', 'rooms'));
    }

    /**
     * Update data pemesanan lengkap & sinkronisasi status kamar otomatis.
     */
    public function update(Request $request, Booking $booking)
    {
        $booking->load(['customer', 'rooms']);
        $roomCount = $booking->rooms()->count();
        $maxGuests = max(2, $roomCount * 2);

        $request->validate([
            'customer_name' => ['required', 'string', 'regex:/^[a-zA-Z\s]+$/', 'max:255'],
            'phone_number' => ['required', 'numeric', 'digits_between:10,15'],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                Rule::unique('customers', 'email')->ignore($booking->customer_id),
                new EmailDomainIsAllowed,
            ],
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'required|integer|exists:rooms,id',
            'booking_type' => 'required|in:Short Time 5 Jam,Short Time 8 Jam,Full Day',
            'check_in_time' => ['required', 'date'],
            'check_out_time' => ['required', 'date', 'after:check_in_time'],
            'booking_status' => 'required|in:Pending,Confirmed,Check-in,Check-out,Cancelled',
            'payment_status' => 'required|in:Pending,Paid,Failed,Cancelled',
            'payment_method' => ['required', Rule::in(['Cash', 'Transfer'])],
            'number_of_guests' => ['sometimes', 'required', 'integer', 'min:1', 'max:'.$maxGuests],
        ], [
            'customer_name.regex' => 'Nama customer hanya boleh berisi huruf dan spasi.',
            'phone_number.numeric' => 'Nomor telepon harus berupa angka.',
            'phone_number.digits_between' => 'Nomor telepon harus antara 10 sampai 15 digit.',
            'email.email' => 'Domain email tidak valid atau tidak aktif.',
            'email.unique' => 'Email sudah digunakan oleh data customer lain. Gunakan email lain.',
        ]);

        return DB::transaction(function () use ($request, $booking) {
            $oldStatus = $booking->booking_status;
            $newStatus = $request->booking_status;
            $oldRoomIds = $booking->rooms()->pluck('room_id')->map(fn (int|string $roomId): int => (int) $roomId)->sort()->values()->all();
            $newRoomIds = collect($request->room_ids)->map(fn (int|string $roomId): int => (int) $roomId)->unique()->sort()->values()->all();
            $checkIn = Carbon::parse($request->check_in_time);
            $checkOut = Carbon::parse($request->check_out_time);
            $scheduleChanged = ! $booking->check_in_time->equalTo($checkIn)
                || ! $booking->check_out_time->equalTo($checkOut);
            $roomsChanged = $oldRoomIds !== $newRoomIds;

            // Update customer info
            $booking->customer->update([
                'customer_name' => $request->customer_name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
            ]);

            // Parse dates
            $bookingType = $request->booking_type;
            $durationHours = match ($bookingType) {
                'Short Time 5 Jam' => 5,
                'Short Time 8 Jam' => 8,
                default => 0,
            };

            // Calculate nights for Full Day
            $nights = $bookingType === 'Full Day' ? $checkIn->diffInDays($checkOut) : 0;

            if ($roomsChanged || $scheduleChanged) {
                // Periksa konflik hanya jika kamar atau jadwal berubah; booking ini dikecualikan.
                $conflictingRoomIds = array_values(array_intersect(
                    $newRoomIds,
                    Booking::overlappingRoomIds($checkIn, $checkOut, $booking->id),
                ));

                if ($conflictingRoomIds !== []) {
                    $roomNumbers = Room::whereIn('id', $conflictingRoomIds)
                        ->orderBy('room_number')
                        ->pluck('room_number')
                        ->join(', ');
                    throw ValidationException::withMessages([
                        'room_ids' => ["Kamar No. {$roomNumbers} tidak tersedia untuk slot waktu tersebut."],
                    ]);
                }
            }

            // Calculate new prices
            $totalPrice = 0;
            $roomPrices = [];
            $rooms = Room::whereIn('id', $newRoomIds)->get();
            foreach ($rooms as $room) {
                $roomPrice = str_starts_with($bookingType, 'Short Time')
                    ? (int) $room->{'price_'.$durationHours.'_hours'}
                    : (int) $room->full_day_price * max(1, $nights);
                $totalPrice += $roomPrice;
                $roomPrices[$room->id] = ['price' => $roomPrice];
            }

            $updateData = [
                'booking_type' => $bookingType,
                'check_in_time' => $checkIn,
                'check_out_time' => $checkOut,
                'booking_status' => $newStatus,
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,
                'payment_time' => ($request->payment_status === 'Paid' && ! $booking->payment_time) ? now() : $booking->payment_time,
                'total_price' => $totalPrice,
                'number_of_guests' => (int) $request->number_of_guests,
            ];

            $booking->update($updateData);

            // Sync rooms with new prices
            $booking->rooms()->sync($roomPrices);

            // Handle room status sync for old rooms that are no longer booked
            $removedRoomIds = array_diff($oldRoomIds, $newRoomIds);
            if (! empty($removedRoomIds)) {
                // Only set to Available if not occupied by other active bookings
                foreach ($removedRoomIds as $roomId) {
                    $hasActiveBooking = Booking::query()
                        ->activeOverlapping(now('Asia/Jakarta'), now('Asia/Jakarta'))
                        ->whereHas('rooms', fn ($q) => $q->where('rooms.id', $roomId))
                        ->exists();
                    if (! $hasActiveBooking) {
                        Room::where('id', $roomId)->update(['room_status' => 'Available']);
                    }
                }
            }

            // Sinkronisasi Otomatis Status Kamar Berdasarkan Status Booking (multi-room)
            if ($newStatus === 'Check-in') {
                Room::whereIn('id', $newRoomIds)->update(['room_status' => 'Occupied']);
            } elseif ($newStatus === 'Check-out') {
                Room::whereIn('id', $newRoomIds)->update(['room_status' => 'Available']);
            } elseif (in_array($newStatus, ['Cancelled', 'Pending']) && $oldStatus === 'Check-in') {
                Room::whereIn('id', $newRoomIds)->update(['room_status' => 'Available']);
            }

            $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';

            return redirect()->route($prefix.'.pemesanan.index')->with('success', 'Data pemesanan berhasil diperbarui.');
        });
    }

    /**
     * Hitung waktu check-out berdasarkan tipe pemesanan.
     */
    private function calculateCheckOutTime(Carbon $checkIn, string $bookingType): Carbon
    {
        $checkOut = clone $checkIn;

        if ($bookingType === 'Short Time 5 Jam') {
            return $checkOut->addHours(5);
        }

        if ($bookingType === 'Short Time 8 Jam') {
            return $checkOut->addHours(8);
        }

        // Full Day: pukul 12:00 hari yang sama bila check-in sebelum 12:00,
        // atau pukul 12:00 keesokan hari bila check-in pada/di atas 12:00.
        if ($checkIn->hour >= 12) {
            $checkOut->addDay();
        }

        return $checkOut->setTime(12, 0);
    }

    /**
     * Hapus pemesanan.
     */
    public function destroy(Booking $booking)
    {
        $booking->delete();

        $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';

        return redirect()->route($prefix.'.pemesanan.index')->with('success', 'Pemesanan berhasil dihapus.');
    }
}
