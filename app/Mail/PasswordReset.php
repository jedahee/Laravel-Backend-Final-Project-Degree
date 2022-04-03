<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Queue\SerializesModels;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email,)
    {
        if (User::where('email', $email)->first()) {
            $this->email = $email;
        } else { 
            $this->email = "";
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        $token = Str::random(80);
        if ($this->email != "") {
            return $this->from('jedahee02@gmail.com', 'GestionPistas - Recuperar contraseña')
                        ->subject('Recuperar de contraseña')
                        ->view("emails.resetPassword", ["token"=>$token]);
        }
    }
}
