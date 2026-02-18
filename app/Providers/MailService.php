<?php

namespace App\Services;

use App\Mail\BaseMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Enviar correo genérico con archivos adjuntos
     */
    public function sendCustomEmail(
        string $to,
        string $subject,
        string $viewName,
        array $viewData = [],
        array $attachments = []
    ) {
        try {
            $mail = new BaseMail(
                subject: $subject,
                viewName: $viewName,
                viewData: $viewData,
                attachmentPaths: $attachments
            );

            Mail::to($to)->send($mail);

            return true;
        } catch (\Exception $e) {
            Log::error('Error enviando correo personalizado: ' . $e->getMessage());
            return false;
        }
    }
}
