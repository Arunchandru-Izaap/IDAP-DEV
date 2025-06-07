<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        // dd($data);
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // dd($this->data);
        return $this->subject('IDAP Quotation Mail')
                    ->from('idap@example.com', 'IDAP')
                    ->cc('abhishek@noesis.tech', 'Mr. Abhishek')
                    ->bcc('venkitaraman@noesis.tech', 'Mr. Venkitaraman')
                    // ->attach(public_path()."/AJANTA HOSP. & IVF CENTRE PVT. LTD_price_sheet.zip")
                    ->view('welcome');
    }
}
