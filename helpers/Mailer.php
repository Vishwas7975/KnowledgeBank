<?php
// helpers/Mailer.php — PHPMailer wrapper for sending OTP emails

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public static function sendOtp(string $toEmail, string $toName, string $otp): bool
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = MAIL_PORT;

            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = 'Your KnowledgeBank Password Reset Code';
            $mail->Body    = self::htmlTemplate($toName, $otp);
            $mail->AltBody = "Hi {$toName},\n\nYour KnowledgeBank password reset OTP is: {$otp}\n\nThis code expires in 10 minutes.\nIf you did not request this, please ignore this email.";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('KnowledgeBank Mailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private static function htmlTemplate(string $name, string $otp): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <body style="font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f1f5f9;padding:30px;margin:0;color:#0f172a;">
            <div style="max-width:480px;margin:0 auto;background:#ffffff;padding:36px;border-radius:12px;border:1px solid #e2e8f0;">

                <div style="text-align:center;margin-bottom:24px;">
                    <h1 style="margin:0;font-size:24px;color:#1e40af;">🔒 KnowledgeBank</h1>
                    <p style="margin:4px 0 0;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:1.5px;">Password Reset</p>
                </div>

                <hr style="border:none;border-top:1px solid #f1f5f9;margin:0 0 24px;">

                <p style="font-size:15px;line-height:1.6;color:#334155;margin:0 0 8px;">Hi {$name},</p>
                <p style="font-size:15px;line-height:1.6;color:#334155;margin:0 0 28px;">
                    We received a request to reset your KnowledgeBank account password.
                    Use the one-time code below — it expires in <strong>10 minutes</strong>.
                </p>

                <div style="text-align:center;margin:0 0 28px;">
                    <span style="display:inline-block;font-size:38px;font-weight:800;letter-spacing:10px;color:#1e40af;background:#eff6ff;padding:14px 32px;border-radius:10px;border:2px dashed #93c5fd;">
                        {$otp}
                    </span>
                </div>

                <p style="font-size:13px;color:#64748b;line-height:1.6;margin:0 0 24px;">
                    If you didn't request a password reset, you can safely ignore this email.
                </p>

                <hr style="border:none;border-top:1px solid #f1f5f9;margin:0 0 20px;">

                <p style="font-size:11px;text-align:center;color:#94a3b8;margin:0;">
                    &copy; 2026 KnowledgeBank Technologies Pvt Ltd. All rights reserved.
                </p>
            </div>
        </body>
        </html>
        HTML;
    }
}