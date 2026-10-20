<?php

namespace App\Mail;

use App\Models\Website;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReportDataCheckMail extends Mailable
{
    /**
     * @param  list<array{website: Website, issues: list<string>}>  $problems
     */
    public function __construct(public string $month, public array $problems) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "[SEO] Monthly report data for {$this->month}: ".count($this->problems).' website(s) need attention');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.report-data-check');
    }
}
