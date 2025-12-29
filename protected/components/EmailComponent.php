<?php
// /protected/components/EmailComponent.php
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailComponent extends CApplicationComponent
{
    public $smtpHost;
    public $smtpUsername;
    public $smtpPassword;
    public $smtpPort = 587;
    public $smtpEncryption = 'tls';
    public $fromEmail;
    public $fromName;
    public $debug = false;
    
    public function init()
    {
        parent::init();
        
        // Load from environment if not set
        if (empty($this->smtpHost)) {
            $this->smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        }
        if (empty($this->smtpUsername)) {
            $this->smtpUsername = getenv('SMTP_EMAIL');
        }
        if (empty($this->smtpPassword)) {
            $this->smtpPassword = getenv('SMTP_PASSWORD');
        }
        if (empty($this->smtpPort)) {
            $this->smtpPort = getenv('SMTP_PORT') ?: 587;
        }
        if (empty($this->fromEmail)) {
            $this->fromEmail = getenv('SMTP_FROM_EMAIL') ?: getenv('SMTP_EMAIL');
        }
        if (empty($this->fromName)) {
            $this->fromName = getenv('SMTP_FROM_NAME') ?: 'Your Application';
        }
        $this->debug = YII_DEBUG;
        
        if (!$this->smtpUsername || !$this->smtpPassword) {
            throw new CException('SMTP credentials are not configured.');
        }
    }
    
    /**
     * Send email
     */
    public function send($to, $toName, $subject, $body, $htmlBody = null, $attachments = array(), $replyTo = null, $replyToName = null)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = $this->smtpEncryption;
            $mail->Port = $this->smtpPort;
            
            if ($this->debug) {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function($str, $level) {
                    Yii::log("PHPMailer: $str", 'info', 'application.email');
                };
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];
            }
            
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to, $toName);
            
            if ($replyTo) {
                $mail->addReplyTo($replyTo, $replyToName ?: $replyTo);
            }
            
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            if ($htmlBody) {
                $mail->AltBody = $body;
                $mail->isHTML(true);
                $mail->Body = $htmlBody;
            }
            
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
            
            if ($mail->send()) {
                Yii::log("Email sent to: {$to}", 'info', 'application.email');
                return true;
            } else {
                Yii::log("Failed to send email to {$to}: " . $mail->ErrorInfo, 'error', 'application.email');
                return false;
            }
        } catch (Exception $e) {
            Yii::log("Email Exception for {$to}: " . $e->getMessage(), 'error', 'application.email');
            return false;
        }
    }
    
    /**
     * Send job application confirmation
     */
    public function sendJobApplicationConfirmation($application,$jobTitle)
    {
        $subject = 'Job Application Received';
        $body = "Dear {$application->fullName},\n\n" .
                "Thank you for applying for the position of {$jobTitle}. " .
                "We have received your application and will review it shortly.\n\n" .
                "Best regards,\n" .
                "HR Team";
                
        $htmlBody = "<p>Dear {$application->fullName},</p>" .
                   "<p>Thank you for applying for the position of <strong>{$jobTitle}</strong>. " .
                   "We have received your application and will review it shortly.</p>" .
                   "<p>Best regards,<br>HR Team</p>";
        
        return $this->send(
            $application->email,
            $application->fullName,
            $subject,
            $body,
            $htmlBody
        );
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordReset($user, $resetLink)
    {
        $subject = 'Password Reset Request';
        $body = "Dear {$user->name},\n\n" .
                "You requested a password reset. Click the link below to reset your password:\n" .
                "{$resetLink}\n\n" .
                "If you didn't request this, please ignore this email.\n\n" .
                "Best regards,\n" .
                "Support Team";
                
        $htmlBody = "<p>Dear {$user->name},</p>" .
                   "<p>You requested a password reset. Click the link below to reset your password:</p>" .
                   "<p><a href='{$resetLink}'>{$resetLink}</a></p>" .
                   "<p>If you didn't request this, please ignore this email.</p>" .
                   "<p>Best regards,<br>Support Team</p>";
        
        return $this->send(
            $user->email,
            $user->name,
            $subject,
            $body,
            $htmlBody
        );
    }
    
    /**
     * Send welcome email
     */
    public function sendWelcomeEmail($user, $loginLink = null)
    {
        $subject = 'Welcome to Our Platform';
        $body = "Dear {$user->name},\n\n" .
                "Welcome to our platform! Your account has been created successfully.\n\n";
                
        if ($loginLink) {
            $body .= "You can login here: {$loginLink}\n\n";
        }
        
        $body .= "Best regards,\n" .
                "Support Team";
                
        $htmlBody = "<p>Dear {$user->name},</p>" .
                   "<p>Welcome to our platform! Your account has been created successfully.</p>";
                   
        if ($loginLink) {
            $htmlBody .= "<p>You can <a href='{$loginLink}'>login here</a>.</p>";
        }
        
        $htmlBody .= "<p>Best regards,<br>Support Team</p>";
        
        return $this->send(
            $user->email,
            $user->name,
            $subject,
            $body,
            $htmlBody
        );
    }
}