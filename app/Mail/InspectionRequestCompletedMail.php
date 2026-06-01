<?php

namespace App\Mail;

use App\Models\InspectionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the client once every inspection of an inspection request is approved.
 */
class InspectionRequestCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{equipment: string, result: ?string, certificate: ?string, public_url: ?string}>  $inspections
     */
    public function __construct(
        public InspectionRequest $inspectionRequest,
        public array $inspections,
    ) {}

    public function build()
    {
        $number = $this->inspectionRequest->request_number ?? "#{$this->inspectionRequest->id}";

        return $this->subject("Inspecciones finalizadas — Solicitud {$number}")
            ->view('emails.inspection-request-completed');
    }
}
