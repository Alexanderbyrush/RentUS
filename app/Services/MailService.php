<?php

namespace App\Services;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Enviar correo de confirmación de registro
     */
    public function sendConfirmationEmail(User $user, VerificationCode $verificationCode): bool
    {
        try {
            $verificationLink = config('app.frontend_url')
                . '/confirm-email?token=' . $verificationCode->token
                . '&code=' . $verificationCode->code;

            $subject = 'Confirma tu cuenta - ' . config('app.name');

            $body = "
            <p>Hola {$user->name},</p>
            <p>Gracias por registrarte en " . config('app.name') . ".</p>
            <p>Tu código de verificación es: <strong>{$verificationCode->code}</strong></p>
            <p>O haz clic en este enlace para verificar tu correo:</p>
            <p><a href='{$verificationLink}'>{$verificationLink}</a></p>
            <p>Este código expira en 10 minutos.</p>
            ";

            Mail::html($body, function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de verificación', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Enviar correo de recuperación de contraseña
     */
    public function sendResetPasswordEmail(User $user, VerificationCode $verificationCode): bool
    {
        try {

            $subject = 'Recuperación de contraseña - ' . config('app.name');

            $body = "
            <p>Hola {$user->name},</p>
            <p>Solicitaste recuperar tu contraseña en " . config('app.name') . ".</p>
            <p>Tu código de recuperación es: <strong>{$verificationCode->code}</strong></p>
            <p>Este código expira en 10 minutos.</p>
            ";

            Mail::html($body, function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de recuperación', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Enviar correo de reenvío de código
     */
    public function sendCodeResendEmail(User $user, VerificationCode $verificationCode): bool
    {
        try {
            $verificationLink = config('app.frontend_url')
                . '/confirm-email?token=' . $verificationCode->token
                . '&code=' . $verificationCode->code;

            $subject = 'Nuevo código de verificación - ' . config('app.name');

            $body = "
            <p>Hola {$user->name},</p>
            <p>Solicitaste un nuevo código de verificación en " . config('app.name') . ".</p>
            <p>Tu código es: <strong>{$verificationCode->code}</strong></p>
            <p>O haz clic en este enlace para verificar tu correo:</p>
            <p><a href='{$verificationLink}'>{$verificationLink}</a></p>
            <p>Este código expira en 10 minutos.</p>
            ";

            Mail::html($body, function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Error al reenviar código', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Enviar notificación de contraseña cambiada exitosamente
     */
    public function sendPasswordChangedNotification(User $user): bool
    {
        try {
            $subject = 'Tu contraseña ha sido cambiada - ' . config('app.name');

            $body = "
            <p>Hola {$user->name},</p>
            <p>Te informamos que tu contraseña en " . config('app.name') . " ha sido cambiada exitosamente.</p>
            <p>Si no realizaste este cambio, por favor contacta al soporte inmediatamente.</p>
            ";

            Mail::html($body, function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación de cambio de contraseña', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
