package tn.esprit.utils;

import javafx.animation.FadeTransition;
import javafx.animation.PauseTransition;
import javafx.animation.SequentialTransition;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.layout.StackPane;
import javafx.util.Duration;

/**
 * Utility to display a floating, auto-dismissing toast notification
 * on top of any StackPane root (e.g. the app's contentArea).
 */
public class ToastService {

    public enum ToastType {
        SUCCESS, ERROR, INFO
    }

    /**
     * Shows a toast message on top of the given StackPane.
     *
     * @param root    The root StackPane to overlay the toast on.
     * @param message The message to display.
     * @param type    SUCCESS, ERROR, or INFO.
     */
    public static void show(StackPane root, String message, ToastType type) {
        Label toast = new Label(message);
        toast.setPadding(new Insets(12, 24, 12, 24));
        toast.setWrapText(true);
        toast.setMaxWidth(450);

        String color = switch (type) {
            case SUCCESS -> "#10b981";
            case ERROR -> "#ef4444";
            case INFO -> "#3b82f6";
        };

        toast.setStyle(
                "-fx-background-color: " + color + ";" +
                        "-fx-text-fill: white;" +
                        "-fx-font-size: 14px;" +
                        "-fx-font-weight: 700;" +
                        "-fx-background-radius: 10;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.3), 10, 0, 0, 4);");

        StackPane.setAlignment(toast, Pos.BOTTOM_CENTER);
        StackPane.setMargin(toast, new Insets(0, 0, 30, 0));
        toast.setOpacity(0);
        root.getChildren().add(toast);

        // Animate: fade in → pause → fade out → remove
        FadeTransition fadeIn = new FadeTransition(Duration.millis(300), toast);
        fadeIn.setFromValue(0);
        fadeIn.setToValue(1);

        PauseTransition hold = new PauseTransition(Duration.seconds(2.5));

        FadeTransition fadeOut = new FadeTransition(Duration.millis(400), toast);
        fadeOut.setFromValue(1);
        fadeOut.setToValue(0);

        SequentialTransition seq = new SequentialTransition(fadeIn, hold, fadeOut);
        seq.setOnFinished(e -> root.getChildren().remove(toast));
        seq.play();
    }
}
