<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    /**
     * Tampilkan daftar pemesanan + pencarian.
     */
    public function index(Request $request)
    {
        $search = $request->query('q');

        $query = Booking::with(['customer', 'room'])->latest();

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
                    'check_in_date' => Carbon::parse($b->check_in_time)->format('Y-m-d'),
                    'check_out_date' => Carbon::parse($b->check_out_time)->format('Y-m-d'),
                    'check_in_formatted' => Carbon::parse($b->check_in_time)->format('d M Y H:i'),
                    'check_out_formatted' => Carbon::parse($b->check_out_time)->format('d M Y H:i'),
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
            ->where('room_status', 'Available')
            ->orderBy('room_number')
            ->get();

        return view('bookings.create', compact('rooms'));
    }

    /**
     * Simpan pemesanan baru dengan pencegahan double booking yang ketat.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'room_id' => 'required|exists:rooms,id',
            'booking_type' => 'required|in:Short Time 5 Jam,Short Time 8 Jam,Full Day',
            'check_in_time' => ['required', 'date', 'after_or_equal:'.now('Asia/Jakarta')->format('Y-m-d H:i')],
            'booking_status' => 'nullable|in:Pending,Confirmed,Check-in,Check-out,Cancelled',
            'payment_method' => ['required', Rule::in(['Cash', 'Transfer', 'Virtual Account', 'Midtrans'])],
            'payment_status' => ['required', Rule::in(['Pending', 'Paid', 'Failed', 'Cancelled'])],
            'source' => 'required|in:Offline,Website,WhatsApp',
        ]);

        $checkIn = Carbon::parse($request->check_in_time);

        // Tentukan check_out_time berdasarkan booking_type
        if ($request->booking_type === 'Short Time 5 Jam') {
            $checkOut = (clone $checkIn)->addHours(5);
        } elseif ($request->booking_type === 'Short Time 8 Jam') {
            $checkOut = (clone $checkIn)->addHours(8);
        } else {
            // Full Day dihitung dari check-in sampai jam 12 siang hari berikutnya
            $checkOut = (clone $checkIn)->addDay()->startOfDay()->addHours(12);
        }

        // Validasi Ketat Double Booking (Overlap waktu pada kamar yang sama)
        $conflict = Booking::where('room_id', $request->room_id)
            ->whereIn('booking_status', ['Pending', 'Confirmed', 'Check-in'])
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    $q->where('check_in_time', '<', $checkOut)
                        ->where('check_out_time', '>', $checkIn);
                });
            })
            ->exists();

        if ($conflict) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Kamar sudah dipesan atau tidak tersedia pada rentang waktu tersebut (Double Booking terdeteksi).',
                    'errors' => [
                        'room_id' => ['Kamar sudah dipesan atau tidak tersedia pada rentang waktu tersebut (Double Booking terdeteksi).'],
                    ],
                ], 422);
            }

            return back()->withInput()->withErrors(['room_id' => 'Kamar sudah dipesan atau tidak tersedia pada rentang waktu tersebut (Double Booking terdeteksi).']);
        }

        return DB::transaction(function () use ($request, $checkIn, $checkOut) {
            // Simpan / Buat Data Customer (hindari duplikat)
            $customer = Customer::firstOrCreate(
                ['phone_number' => $request->phone_number],
                [
                    'customer_name' => $request->customer_name,
                    'email' => $request->email ?? strtolower(str_replace(' ', '', $request->customer_name)).'@guest.com',
                ]
            );

            // Update nama customer jika nomor telepon sudah ada dan nama baru diberikan
            if ($customer->customer_name !== $request->customer_name) {
                $customer->update(['customer_name' => $request->customer_name]);
            }

            // Hitung total harga
            $room = Room::findOrFail($request->room_id);
            $totalPrice = match ($request->booking_type) {
                'Short Time 5 Jam' => $room->price_5_hours,
                'Short Time 8 Jam' => $room->price_8_hours,
                'Full Day' => $room->full_day_price,
                default => $room->full_day_price,
            };

            $isMidtransPayment = $request->payment_method === 'Midtrans';
            $paymentStatus = $isMidtransPayment ? 'Pending' : $request->payment_status;
            $bookingStatus = $paymentStatus === 'Paid' ? 'Confirmed' : 'Pending';

            $booking = Booking::create([
                'room_id' => $room->id,
                'customer_id' => $customer->id,
                'booking_code' => Booking::generateCode(),
                'booking_type' => $request->booking_type,
                'check_in_time' => $checkIn,
                'check_out_time' => $checkOut,
                'booking_status' => $bookingStatus,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'payment_time' => ($paymentStatus === 'Paid') ? now() : null,
                'payment_expires_at' => $isMidtransPayment ? now()->addMinutes(20) : null,
                'total_price' => $totalPrice,
                'source' => $request->source,
            ]);

            // Jika status booking adalah Check-in, sinkronkan status kamar menjadi Occupied
            if ($bookingStatus === 'Check-in') {
                $room->update(['room_status' => 'Occupied']);
            }

            if ($request->expectsJson()) {
                $booking->load(['customer', 'room']);

                return response()->json([
                    'success' => true,
                    'message' => 'Pemesanan berhasil dibuat dengan kode '.$booking->booking_code,
                    'booking_code' => $booking->booking_code,
                    'booking' => [
                        'id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'customer_name' => $customer->customer_name,
                        'room_number' => $room->room_number,
                        'room_type' => $room->room_type,
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
        });
    }

    /**
     * Tampilkan form Edit Pemesanan.
     */
    public function edit(Booking $booking)
    {
        $rooms = Room::orderBy('room_number')->get();

        return view('bookings.edit', compact('booking', 'rooms'));
    }

    /**
     * Update data pemesanan & sinkronisasi status kamar otomatis.
     */
    public function update(Request $request, Booking $booking)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'booking_status' => 'required|in:Pending,Confirmed,Check-in,Check-out,Cancelled',
            'payment_status' => 'required|in:Pending,Paid,Failed,Cancelled',
        ]);

        return DB::transaction(function () use ($request, $booking) {
            $oldRoomId = $booking->room_id;
            $newRoomId = $request->room_id;
            $oldStatus = $booking->booking_status;
            $newStatus = $request->booking_status;

            $booking->update([
                'room_id' => $newRoomId,
                'booking_status' => $newStatus,
                'payment_status' => $request->payment_status,
                'payment_time' => ($request->payment_status === 'Paid' && ! $booking->payment_time) ? now() : $booking->payment_time,
            ]);

            // Sinkronisasi Otomatis Status Kamar Berdasarkan Status Booking
            $room = Room::findOrFail($newRoomId);

            if ($newStatus === 'Check-in') {
                $room->update(['room_status' => 'Occupied']);
            } elseif ($newStatus === 'Check-out') {
                $room->update(['room_status' => 'Cleaning']);
                // Jika pindah kamar, kamar lama set ke Cleaning juga
                if ($oldRoomId != $newRoomId) {
                    Room::where('id', $oldRoomId)->update(['room_status' => 'Cleaning']);
                }
            } elseif (in_array($newStatus, ['Cancelled', 'Pending']) && $oldStatus === 'Check-in') {
                $room->update(['room_status' => 'Cleaning']);
            }

            $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';

            return redirect()->route($prefix.'.pemesanan.index')->with('success', 'Data pemesanan berhasil diperbarui.');
        });
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
