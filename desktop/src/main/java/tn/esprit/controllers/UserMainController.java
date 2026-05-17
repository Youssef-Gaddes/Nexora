package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.control.ButtonType;
import javafx.scene.control.Label;
import javafx.scene.layout.StackPane;
import javafx.scene.layout.VBox;
import javafx.scene.Node;
import tn.esprit.entities.User;
import tn.esprit.services.AuthService;
import tn.esprit.services.ExchangeRateService;
import tn.esprit.services.UserService;
import tn.esprit.services.WalletService;
import tn.esprit.utils.SceneNavigator;
import tn.esprit.utils.UserContext;

import java.io.IOException;
import java.util.Optional;

public class UserMainController {

    @FXML
    private Label lblWelcome;
    @FXML
    private Label lblWalletBalance;
    @FXML
    private Label lblCurrencySidebar;
    @FXML
    private VBox sidebarMenu;
    @FXML
    private StackPane contentArea;

    @FXML
    private Button btnDashboard, btnMarket, btnOrders, btnHistory, btnP2P;

    private AuthService authService;
    private UserService userService;
    private WalletService walletService;
    private ExchangeRateService fxService;
    private long userId;
    private static UserMainController instance;

    public static UserMainController getInstance() {
        return instance;
    }

    @FXML
    public void initialize() {
        instance = this;
        authService = new AuthService();
        userService = new UserService();
        walletService = new WalletService();
        fxService = new ExchangeRateService();
        userId = UserContext.getCurrentUserId();

        // Show user's real name
        User user = userService.findById(userId);
        String name = user != null ? user.getFullName() : "User";
        lblWelcome.setText("Welcome, " + name);

        // Show wallet balance in sidebar
        refreshWalletBalance();

        // Select the Dashboard as default screen
        loadView("UserDashboardView.fxml", btnDashboard);
    }

    public void refreshWalletBalance() {
        try {
            var wallet = walletService.findByUserId(UserContext.getCurrentUserId());
            if (wallet != null) {
                lblWalletBalance.setText(String.format("💰 %.2f TND", wallet.getBalance()));
                lblCurrencySidebar.setText(fxService.formatAll(wallet.getBalance()));
            } else {
                lblWalletBalance.setText("💰 0.00 TND");
                lblCurrencySidebar.setText("€ 0.00 | $ 0.00 | £ 0.00");
            }
        } catch (Exception e) {
            System.err.println("Could not refresh wallet balance: " + e.getMessage());
        }
    }

    @FXML
    public void showDashboard() {
        loadView("UserDashboardView.fxml", btnDashboard);
    }

    @FXML
    public void showMarket() {
        loadView("UserAssetsMarketView.fxml", btnMarket);
    }

    @FXML
    public void showOrders() {
        loadView("UserOrdersView.fxml", btnOrders);
    }

    @FXML
    public void showHistory() {
        loadView("UserTransactionsView.fxml", btnHistory);
    }

    @FXML
    public void showP2P() {
        loadView("P2PContractView.fxml", btnP2P);
    }

    public void loadPortfolio() {
        loadView("PortfolioDetailsView.fxml", null);
    }

    @FXML
    public void logout() {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Confirm Logout");
        confirm.setHeaderText("Are you sure you want to log out?");
        confirm.setContentText("You will be returned to the login screen.");
        Optional<ButtonType> result = confirm.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            authService.logout();
            SceneNavigator.goTo("LoginView.fxml", "PiWeb - Authentication", 560, 500);
        }
    }

    private void loadView(String fxmlFile, Button clickedButton) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/" + fxmlFile));
            Node view = loader.load();

            contentArea.getChildren().setAll(view);
            updateSidebarSelection(clickedButton);

        } catch (IOException e) {
            System.err.println("Error loading view: " + fxmlFile);
            e.printStackTrace();
        }
    }

    private void updateSidebarSelection(Button selectedButton) {
        // Remove active class from all
        btnDashboard.getStyleClass().remove("sidebar-btn-active");
        btnMarket.getStyleClass().remove("sidebar-btn-active");
        btnOrders.getStyleClass().remove("sidebar-btn-active");
        btnHistory.getStyleClass().remove("sidebar-btn-active");

        // Add to selected
        if (selectedButton != null) {
            selectedButton.getStyleClass().add("sidebar-btn-active");
        }
    }
}
