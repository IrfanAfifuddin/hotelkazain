<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCustomerBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_ids' => ['sometimes', 'array'],
            'room_ids.*' => ['integer', 'exists:rooms,id'],
            'room_quantities' => ['sometimes', 'array'],
            'room_quantities.*' => ['integer', 'min:1', 'max:12'],
            'customer_name' => ['required', 'string', 'regex:/^[a-zA-Z\s]+$/', 'max:255'],
            'phone_number' => ['required', 'numeric', 'digits_between:10,15'],
            'email' => ['required', 'string', app()->runningUnitTests() ? 'email:rfc' : 'email:dns', 'max:255'],
            'booking_type' => ['required', 'in:Short Time 5 Jam,Short Time 8 Jam,Full Day'],
            'check_in_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_in_time' => ['required', 'date_format:H:i'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'payment_method' => ['required', 'in:Virtual Account'],
            'agree_terms' => ['accepted'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                // Ensure at least one of room_ids or room_quantities is provided
                $hasRoomIds = $this->has('room_ids') && ! empty($this->input('room_ids'));
                $hasRoomQuantities = $this->has('room_quantities') && ! empty($this->input('room_quantities'));

                if (! $hasRoomIds && ! $hasRoomQuantities) {
                    $validator->errors()->add('room_ids', 'Either room_ids or room_quantities must be provided.');

                    return;
                }

                $checkIn = CarbonImmutable::parse(
                    $this->string('check_in_date')->toString().' '.$this->string('check_in_time')->toString(),
                );

                if ($checkIn->lessThanOrEqualTo(now())) {
                    $validator->errors()->add('check_in_time', 'Check-in must be later than the current time.');

                    return;
                }

                $bookingType = $this->string('booking_type')->toString();
                $durationHours = $this->integer('duration_hours');

                if (str_starts_with($bookingType, 'Short Time')) {
                    $expectedDuration = (int) preg_replace('/\D/', '', $bookingType);
                    if (! in_array($expectedDuration, [5, 8], true)) {
                        $validator->errors()->add('booking_type', 'Short Time must be 5 Jam or 8 Jam.');
                    }
                    if ($durationHours !== $expectedDuration) {
                        $validator->errors()->add('duration_hours', "Duration must match booking type ($expectedDuration hours).");
                    }
                }

                if ($bookingType === 'Full Day') {
                    if ($durationHours < 24 || $durationHours % 24 !== 0) {
                        $validator->errors()->add('duration_hours', 'Full Day bookings must be a whole number of 24-hour nights.');
                    }

                    if ($this->string('check_in_time')->toString() !== '14:00') {
                        $validator->errors()->add('check_in_time', 'Full Day check-in time must be 14:00.');
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'agree_terms.accepted' => 'You must accept the terms and conditions to continue.',
            'payment_method.in' => 'Customer payments are only available by bank transfer / Virtual Account.',
            'customer_name.regex' => 'Nama customer hanya boleh berisi huruf dan spasi.',
            'phone_number.numeric' => 'Nomor telepon harus berupa angka.',
            'phone_number.digits_between' => 'Nomor telepon harus antara 10 sampai 15 digit.',
            'email.email' => 'Format email tidak valid atau domain email tidak ditemukan.',
        ];
    }
}
