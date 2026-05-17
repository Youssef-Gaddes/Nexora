# Nexora — Crypto Wallet & Trading Platform

![Symfony](https://img.shields.io/badge/Symfony-6.4-black?style=flat-square&logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php)
![Doctrine](https://img.shields.io/badge/Doctrine-ORM-blue?style=flat-square)
![Testing](https://img.shields.io/badge/PHPUnit-10.5-blue?style=flat-square&logo=phpunit)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat-square&logo=mysql)

**Nexora** is a comprehensive, full-stack web application built with **Symfony 6.4**. It simulates a modern crypto-trading and wallet management platform. This repository showcases a strong understanding of MVC architecture, relational database design (Doctrine ORM), server-side validation, secure authentication, and robust automated testing.

---

## 🎯 Project Overview & Recruiter Highlights

This project was built to demonstrate proficiency in modern PHP frameworks and software engineering best practices. 

**Key engineering highlights:**
- **Robust Architecture:** Implemented following the MVC design pattern using Symfony 6.4.
- **Data Integrity:** Complex relationships managed via Doctrine ORM with strict mapping, cascading, and orphan removal.
- **Security & Authentication:** DB-backed user authentication, role-based access control (Admin/User), and secure password hashing.
- **Strict Server-Side Validation:** Form and entity validations are handled securely in PHP rather than relying on browser-side HTML constraints.
- **Comprehensive Test Suite:** Over **800 tests** encompassing Unit Tests (business logic), Static Tests (code structure), and Doctrine Tests (ORM mapping integrity).
- **Responsive UI:** Integrated with Twig templates for a dynamic and responsive user interface, featuring a custom back-office for admin management.

---

## 🧩 Core Modules & Features

The platform is divided into several interconnected modules:

- **🔐 User & Authentication:**
  - Secure Login/Logout & Self-registration.
  - Automatic `Wallet` creation upon user registration.
  - Role-based routing (Admin vs. Standard User).
  - Admin Back-office: Full CRUD for user management, role assignment, and wallet linking.
- **💼 Wallet & Goals:**
  - Real-time balance tracking and fiat/crypto conversions.
  - Goal setting module with dynamic progress calculation based on target amounts and deadlines.
- **📈 Assets & Portfolio:**
  - Track owned assets and portfolio total value.
  - Sync real-time or mocked asset prices.
- **🛒 Trading & Orders (P2P):**
  - Place BUY/SELL orders.
  - Peer-to-peer (P2P) contract lifecycle management (OPEN → COMPLETED / CANCELLED).
- **⭐ User Reputation:**
  - Dynamic reputation scoring based on completed/canceled contracts.
  - Ranks (e.g., "Expert Trader") calculated via dedicated services.

---

## 🛠️ Tech Stack

- **Backend:** PHP 8.1+, Symfony 6.4
- **Database:** MySQL, Doctrine ORM, Doctrine Migrations
- **Frontend:** Twig, Vanilla CSS / JS (Custom UI components)
- **Testing:** PHPUnit (Unit, Static Analysis, Doctrine Mapping)
- **Environment & Deployment:** Docker (`compose.yaml` available), Symfony Local Web Server

---

## 🧪 Testing & Code Quality

A major focus of this project is reliability and code quality. The test suite includes **~800 tests** and **~1546 assertions**:

1. **Unit Tests:** Verify the isolated business logic of Entities and Services (e.g., Wallet conversions, Goal progressions, Reputation calculations, Sentiment AI services).
2. **Static Tests:** Validate the structural integrity of the codebase, ensuring classes, namespaces, method signatures, and validation constraints (`#[NotBlank]`, `#[Positive]`) are correctly defined.
3. **Doctrine ORM Tests:** Ensure database mapping is flawless by validating `#[ORM\Entity]`, column types, precision/scale for financials, bidirectional relationships, and cascading behaviors.

Run the tests using:
```bash
# Run all tests
php bin/phpunit --testdox

# Run specific test suites
php bin/phpunit tests/Entity tests/Service --testdox
php bin/phpunit tests/Static --testdox
php bin/phpunit tests/Doctrine --testdox
```

---

## 🚀 Getting Started

### Prerequisites
- PHP >= 8.1
- Composer
- MySQL Database or Docker

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd symfony-nexora-fullintegrationv1
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Environment Setup**
   Copy the `.env` file to `.env.local` and configure your `DATABASE_URL`.
   ```bash
   DATABASE_URL="mysql://root:@127.0.0.1:3306/nexora_db?serverVersion=8.0&charset=utf8mb4"
   ```

4. **Run Database Migrations**
   This will set up the tables and seed initial Admin/User accounts.
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Start the Application**
   Using Symfony CLI:
   ```bash
   symfony server:start
   ```
   *Or using built-in PHP server:*
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

### Default Credentials
- **Admin:** `admin@nexora.tn` / `admin123`
- **User:** `ayoub1@nexora.tn` / `user123`

---

## 📂 Project Architecture highlights

- **`src/Entity/`**: Contains the rich domain model mapped to the database. Uses PHP 8 attributes.
- **`src/Controller/`**: Keeps controllers thin, delegating heavy logic to Services.
- **`src/Service/`**: Encapsulates core business logic (e.g., `ReputationService`, `AssetPriceService`).
- **`tests/`**: Extensively structured into `Unit`, `Static`, and `Doctrine` namespaces.
- **`templates/`**: Modular Twig views separated by feature domain.

---
*Developed as a comprehensive showcase of modern Symfony development, software testing, and secure backend engineering.*
