package tn.esprit.services;

import io.github.cdimascio.dotenv.Dotenv;
import jakarta.mail.*;
import jakarta.mail.internet.InternetAddress;
import jakarta.mail.internet.MimeMessage;

import java.math.BigDecimal;
import java.util.Properties;

/**
 * Sends HTML emails via Gmail SMTP.
 *
 * HOW IT WORKS:
 * - Uses Jakarta Mail 2.0 (industry-standard Java email library)
 * - Authenticates to Gmail's SMTP server over TLS (port 587)
 * - Reads credentials from /.env so your password is NEVER hardcoded
 * - The .env file holds MY_EMAIL and MY_PASSWORD (a Gmail App Password,
 * NOT your real Gmail password — generate one at myaccount.google.com)
 *
 * TRIGGERED ON:
 * 1. Wallet deposit / withdrawal → sendTransactionReceipt()
 * 2. P2P contract completed → sendP2PTradeConfirmation()
 */
public class EmailService {

    private static final Dotenv dotenv = Dotenv.configure().ignoreIfMissing().load();
    private static final String MY_EMAIL = dotenv.get("MY_EMAIL", "");
    private static final String MY_PASSWORD = dotenv.get("MY_PASSWORD", "");

    // ─────────────────────────────────────────────────────────
    // Internal SMTP session factory
    // ─────────────────────────────────────────────────────────
    private Session buildSession() {
        Properties props = new Properties();
        props.put("mail.smtp.auth", "true");
        props.put("mail.smtp.starttls.enable", "true");
        props.put("mail.smtp.host", "smtp.gmail.com");
        props.put("mail.smtp.port", "587");

        return Session.getInstance(props, new Authenticator() {
            @Override
            protected PasswordAuthentication getPasswordAuthentication() {
                return new PasswordAuthentication(MY_EMAIL, MY_PASSWORD);
            }
        });
    }

    /**
     * Send a transaction receipt email after a wallet deposit or withdrawal.
     *
     * @param toEmail  User's email address
     * @param type     "DEPOSIT" or "WITHDRAW"
     * @param amount   Amount in TND
     * @param walletId The wallet ID involved
     * @return true if the email was sent successfully
     */
    public boolean sendTransactionReceipt(String toEmail, String type, BigDecimal amount, long walletId) {
        if (!isConfigured() || toEmail == null || toEmail.trim().isEmpty()) {
            System.out.println("EmailService: Credentials not set or no email — skipping.");
            return false;
        }

        String action = "DEPOSIT".equalsIgnoreCase(type) ? "Deposit" : "Withdrawal";
        String emoji = "DEPOSIT".equalsIgnoreCase(type) ? "📥" : "📤";

        String html = "<div style='font-family:sans-serif;max-width:520px;margin:auto;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;'>"
                + "<div style='background:#0f172a;padding:24px;text-align:center;'>"
                + "<h1 style='color:white;margin:0;font-size:22px;'>Nexora Trading</h1>"
                + "<p style='color:#94a3b8;margin:4px 0 0;font-size:13px;'>Transaction Confirmation</p>"
                + "</div>"
                + "<div style='padding:28px;'>"
                + "<h2 style='color:#1e293b;'>" + emoji + " " + action + " Confirmed</h2>"
                + "<p style='color:#475569;'>Hello,</p>"
                + "<p style='color:#475569;'>Your <strong>" + action + "</strong> of <strong>" + amount
                + " TND</strong> has been processed successfully on Wallet <strong>#" + walletId + "</strong>.</p>"
                + "<div style='background:#f1f5f9;border-radius:8px;padding:16px;margin:16px 0;'>"
                + "<table width='100%'><tr><td style='color:#64748b;'>Type</td><td style='color:#0f172a;font-weight:700;text-align:right;'>"
                + action + "</td></tr>"
                + "<tr><td style='color:#64748b;'>Amount</td><td style='color:#10b981;font-weight:700;text-align:right;'>"
                + amount + " TND</td></tr>"
                + "<tr><td style='color:#64748b;'>Wallet ID</td><td style='color:#0f172a;text-align:right;'>#"
                + walletId + "</td></tr>"
                + "</table></div>"
                + "<p style='color:#94a3b8;font-size:12px;'>If you did not initiate this transaction, please contact Nexora support immediately.</p>"
                + "<p style='color:#475569;'>Thank you for trading with Nexora 🚀</p>"
                + "</div></div>";

        return send(toEmail, emoji + " Transaction Confirmation – Nexora Wallet", html);
    }

    /**
     * Send a P2P trade completion email to both parties.
     *
     * @param toEmail       Recipient email
     * @param recipientName The recipient's display name
     * @param assetName     Name of the traded asset
     * @param quantity      Number of units traded
     * @param totalValue    Total trade value in TND
     * @param contractId    ID of the completed P2P contract
     */
    public boolean sendP2PTradeConfirmation(String toEmail, String recipientName, String assetName,
            int quantity, double totalValue, long contractId) {
        if (!isConfigured() || toEmail == null || toEmail.trim().isEmpty()) {
            System.out.println("EmailService: Credentials not set or no email — skipping.");
            return false;
        }

        String html = "<div style='font-family:sans-serif;max-width:520px;margin:auto;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;'>"
                + "<div style='background:#0f172a;padding:24px;text-align:center;'>"
                + "<h1 style='color:white;margin:0;font-size:22px;'>Nexora Trading</h1>"
                + "<p style='color:#94a3b8;margin:4px 0 0;font-size:13px;'>P2P Trade Executed</p>"
                + "</div>"
                + "<div style='padding:28px;'>"
                + "<h2 style='color:#1e293b;'>🤝 Your P2P Trade is Complete!</h2>"
                + "<p style='color:#475569;'>Hello <strong>" + recipientName + "</strong>,</p>"
                + "<p style='color:#475569;'>Your P2P contract <strong>#" + contractId
                + "</strong> has been successfully completed.</p>"
                + "<div style='background:#f1f5f9;border-radius:8px;padding:16px;margin:16px 0;'>"
                + "<table width='100%'><tr><td style='color:#64748b;'>Asset</td><td style='color:#0f172a;font-weight:700;text-align:right;'>"
                + assetName + "</td></tr>"
                + "<tr><td style='color:#64748b;'>Quantity</td><td style='color:#0f172a;text-align:right;'>" + quantity
                + " units</td></tr>"
                + "<tr><td style='color:#64748b;'>Total Value</td><td style='color:#10b981;font-weight:700;text-align:right;'>"
                + String.format("%.2f TND", totalValue) + "</td></tr>"
                + "</table></div>"
                + "<p style='color:#475569;'>A PDF receipt has also been generated. Thank you for using Nexora P2P! 🚀</p>"
                + "</div></div>";

        return send(toEmail, "🤝 P2P Trade Confirmed – Nexora", html);
    }

    // ─────────────────────────────────────────────────────────
    // Core send method — shared by all email types
    // ─────────────────────────────────────────────────────────
    private boolean send(String toEmail, String subject, String htmlBody) {
        try {
            Session session = buildSession();
            Message message = new MimeMessage(session);
            message.setFrom(new InternetAddress(MY_EMAIL));
            message.setRecipients(Message.RecipientType.TO, InternetAddress.parse(toEmail));
            message.setSubject(subject);
            message.setContent(htmlBody, "text/html; charset=utf-8");
            Transport.send(message);
            System.out.println("✅ Email sent to " + toEmail);
            return true;
        } catch (MessagingException e) {
            System.err.println("EmailService: Failed to send email → " + e.getMessage());
            return false;
        }
    }

    /** Returns true only if the .env credentials have been filled in. */
    private boolean isConfigured() {
        return MY_EMAIL != null && !MY_EMAIL.isBlank()
                && MY_PASSWORD != null && !MY_PASSWORD.isBlank()
                && !MY_EMAIL.contains("YOUR_");
    }
}
