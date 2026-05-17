package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.PasswordField;
import javafx.scene.control.ProgressBar;
import javafx.scene.control.TextField;
import tn.esprit.entities.User;
import tn.esprit.services.AuthService;
import tn.esprit.utils.SceneNavigator;
import tn.esprit.utils.ValidationUtil;

/**
 * Controller for user registration screen
 */
public class RegisterController {

    @FXML
    private TextField tfFullName;
    @FXML
    private TextField tfEmail;
    @FXML
    private PasswordField tfPassword;
    @FXML
    private PasswordField tfConfirmPassword;
    @FXML
    private ProgressBar pbPasswordStrength;
    @FXML
    private Label lblPasswordStrength;
    @FXML
    private Label lblErrorMessage;
    @FXML
    private Label lblSuccessMessage;

    private AuthService authService;

    @FXML
    public void initialize() {
        authService = new AuthService();
        lblErrorMessage.setText("");
        lblSuccessMessage.setText("");
    }

    /**
     * Handle register button click
     */
    @FXML
    public void handleRegister() {
        String fullName = tfFullName.getText().trim();
        String email = tfEmail.getText().trim();
        String password = tfPassword.getText();
        String confirmPassword = tfConfirmPassword.getText();

        // Validation
        if (fullName.isEmpty() || email.isEmpty() || password.isEmpty()) {
            setError("❌ All fields are required");
            return;
        }

        if (!ValidationUtil.isValidEmail(email)) {
            setError("❌ Invalid email format");
            return;
        }

        if (!ValidationUtil.isStrongPassword(password)) {
            setError("❌ Password must be 8+ chars with uppercase, lowercase, digit, and special char");
            return;
        }

        if (!password.equals(confirmPassword)) {
            setError("❌ Passwords do not match");
            return;
        }

        try {
            // Create user with hashed password
            User newUser = new User();
            newUser.setFullName(fullName);
            newUser.setEmail(email);
            newUser.setPasswordHash(password); // Will be hashed by AuthService
            newUser.setRole("USER");

            // Register through AuthService (auto-creates wallet and portfolio)
            User registeredUser = authService.register(newUser);

            if (registeredUser != null) {
                setSuccess("✅ Registration successful! Redirecting to login...");

                // Redirect after 2 seconds
                javafx.application.Platform.runLater(() -> {
                    try {
                        Thread.sleep(2000);
                    } catch (InterruptedException e) {
                        Thread.currentThread().interrupt();
                    }
                    // Navigate based on role
                    if (registeredUser.getRole().equalsIgnoreCase("ADMIN")) {
                        SceneNavigator.goTo("AdminView.fxml", "PiWeb - Admin Dashboard", 1000, 600);
                    } else {
                        SceneNavigator.goTo("UserMainLayout.fxml", "PiWeb - User Dashboard", 1300, 800);
                    }
                });
            } else {
                setError("❌ Email already registered");
            }
        } catch (Exception e) {
            setError("❌ Registration error: " + e.getMessage());
        }
    }

    /**
     * Handle cancel button click - go back to login
     */
    @FXML
    public void handleCancel() {
        SceneNavigator.goTo("LoginView.fxml", "PiWeb - Authentication", 560, 500);
    }

    /**
     * Clear error message when user starts typing (non-password fields)
     */
    @FXML
    public void onFieldChanged() {
        lblErrorMessage.setText("");
    }

    /**
     * Evaluate password strength and update the indicator bar in real-time.
     */
    @FXML
    public void onPasswordChanged() {
        lblErrorMessage.setText("");
        String pwd = tfPassword.getText();
        int score = 0;
        if (pwd.length() >= 8)
            score++;
        if (pwd.matches(".*[A-Z].*"))
            score++;
        if (pwd.matches(".*[a-z].*"))
            score++;
        if (pwd.matches(".*\\d.*"))
            score++;
        if (pwd.matches(".*[^a-zA-Z0-9].*"))
            score++;

        double progress = score / 5.0;
        pbPasswordStrength.setProgress(progress);

        String color;
        String label;
        if (score <= 1) {
            color = "#ef4444";
            label = "🔴 Weak";
        } else if (score <= 3) {
            color = "#f59e0b";
            label = "🟡 Fair";
        } else if (score == 4) {
            color = "#3b82f6";
            label = "🔵 Good";
        } else {
            color = "#10b981";
            label = "🟢 Strong";
        }

        pbPasswordStrength.setStyle("-fx-accent: " + color + "; -fx-background-radius: 4;");
        lblPasswordStrength.setText(label);
        lblPasswordStrength.setStyle("-fx-font-size: 11px; -fx-text-fill: " + color + ";");
    }

    private void setError(String message) {
        lblErrorMessage.setText(message);
        lblSuccessMessage.setText("");
    }

    private void setSuccess(String message) {
        lblSuccessMessage.setText(message);
        lblErrorMessage.setText("");
    }
}
