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
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{8,30}$/'],
            'email' => ['required', 'email', 'max:255'],
            'booking_type' => ['required', 'in:Short Time,Full Day'],
            'check_in_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_in_time' => ['required', 'date_format:H:i'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'payment_method' => ['required', 'in:Virtual Account,QRIS'],
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

                $checkIn = CarbonImmutable::parse(
                    $this->string('check_in_date')->toString().' '.$this->string('check_in_time')->toString(),
                );

                if ($checkIn->lessThanOrEqualTo(now())) {
                    $validator->errors()->add('check_in_time', 'Check-in must be later than the current time.');

                    return;
                }

                $bookingType = $this->string('booking_type')->toString();
                $durationHours = $this->integer('duration_hours');

                if ($bookingType === 'Short Time' && ! in_array($durationHours, [5, 8], true)) {
                    $validator->errors()->add('duration_hours', 'Short Time bookings must be 5 or 8 hours.');
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
            'payment_method.in' => 'Choose Virtual Account or QRIS as the payment method.',
            'phone_number.regex' => 'Enter a valid phone number.',
        ];
    }
}
