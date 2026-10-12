<?php
/**
 * TATVAM - SMTP Email Delivery Helper
 * Integrates automated mail dispatching containing download codes
 */

require_once __DIR__ . '/../config.php';

/**
 * Dispatches transactional email containing product download links
 *
 * @param string $recipient_email
 * @param string $recipient_name
 * @param string $product_title
 * @param string $download_link
 * @return bool
 */
function sendEbookEmail($recipient_email, $recipient_name, $product_title, $download_link) {
    $subject = "Aapka Ebook Tayar Hai! - TATVAM Download Link";
    
    // HTML Message Layout
    $message = "
    <html>
    <head>
        <title>TATVAM Download Link</title>
        <style>
            body { font-family: 'Inter', Arial, sans-serif; background-color: #FAF8F4; color: #1B1B1B; padding: 20px; line-height: 1.6; }
            .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid rgba(27,27,27,0.06); padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
            .logo { font-size: 24px; font-weight: bold; text-align: center; color: #1B1B1B; margin-bottom: 20px; letter-spacing: 1px; }
            .logo span { color: #D4AF37; }
            h2 { color: #1B1B1B; font-size: 20px; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; }
            .btn { display: block; text-align: center; background: #D4AF37; color: #ffffff !important; padding: 12px 24px; border-radius: 999px; text-decoration: none; font-weight: bold; margin: 25px 0; font-size: 16px; box-shadow: 0 8px 20px rgba(212,175,55,0.2); }
            .footer { font-size: 12px; color: #888888; text-align: center; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 20px; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class='card'>
            <div class='logo'>TATVAM<span>.SHOP</span></div>
            <h2>Pranam " . htmlspecialchars($recipient_name) . ",</h2>
            <p>TATVAM se buying karne ke liye dhanyawad! Aapka ordered material download ke liye ready ho chuka hai.</p>
            <p><strong>Ebook Title:</strong> " . htmlspecialchars($product_title) . "</p>
            <p>Niche diye gaye button par click karke aap ebook download kar sakte hain. Yeh link agle <strong>7 dino</strong> tak active rahegi.</p>
            
            <a href='" . htmlspecialchars($download_link) . "' class='btn'>Download Ebook PDF</a>
            
            <p>Agar aapko download karne me koi problem aati hai, to aap is email ka reply karke humari support team se directly contact kar sakte hain.</p>
            
            <div class='footer'>
                &copy; 2026 tatvam.shop. All rights reserved.<br>
                Transform Your Mind. Transform Your Life.
            </div>
        </div>
    </body>
    </html>
    ";

    // Setup headers for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SUPPORT_EMAIL . ">" . "\r\n";
    $headers .= "Reply-To: " . SUPPORT_EMAIL . "\r\n";

    // Attempt delivery via SMTP helper block if configured
    if (SMTP_USER !== 'your-email@gmail.com') {
        // Here we could include PHPMailer or run socket-based SMTP connection.
        // For portability, we fallback to standard mail() but structure it nicely.
        // On live hosts, local sendmail handles this automatically.
        return mail($recipient_email, $subject, $message, $headers);
    } else {
        // Sandbox fallback log
        if (DEBUG_MODE) {
            error_log("Email Sandbox Log: Ebook delivery link for $recipient_email is: $download_link");
        }
        return mail($recipient_email, $subject, $message, $headers);
    }
}
