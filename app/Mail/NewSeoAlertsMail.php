<?php

namespace App\Mail;

use App\Models\SeoAlert;
use App\Models\Website;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewSeoAlertsMail extends Mailable
{
    /**
     * @param  list<SeoAlert>  $alerts
     */
    public function __construct(public Website $website, public array $alerts) {}

    public function envelope(): Envelope
    {
        $count = count($this->alerts);

        return new Envelope(
            subject: "[SEO] {$count} new alert".($count === 1 ? '' : 's')." for {$this->website->name}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.new-alerts');
    }
}
