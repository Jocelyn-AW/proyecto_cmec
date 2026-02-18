<?php

namespace App\Services;

use App\Mail\BaseMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Crear instancia de correo (SIN enviarlo)
     */
    public function make(
        string $subject,
        string $viewName,
        array $viewData = [],
        array $attachments = []
    ): BaseMail {
        return new BaseMail(
            subject: $subject,
            viewName: $viewName,
            viewData: $viewData,
            attachmentPaths: $attachments
        );
    }

    /**
     * Enviar correo
     */
    public function sendCustomEmail(
        string $to,
        string $subject,
        string $viewName,
        array $viewData = [],
        array $attachments = []
    ): bool {
        try {

            $mail = $this->make(
                subject: $subject,
                viewName: $viewName,
                viewData: $viewData,
                attachments: $attachments
            );

            Mail::to($to)->send($mail);

            return true;
        } catch (\Exception $e) {

            Log::error('Error enviando correo: ' . $e->getMessage());

            return false;
        }
    }
}
