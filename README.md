# Nexora — Crypto Wallet & Trading Platform

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php&logoColor=white)
![Symfony](https://img.shields.io/badge/Symfony-6.4-black?style=flat-square&logo=symfony)
![Java](https://img.shields.io/badge/Java-11+-ED8B00?style=flat-square&logo=openjdk&logoColor=white)
![JavaFX](https://img.shields.io/badge/JavaFX-21-2196F3?style=flat-square)
![Doctrine](https://img.shields.io/badge/Doctrine_ORM-MySQL-F60?style=flat-square)
![PHPUnit](https://img.shields.io/badge/PHPUnit-800+_tests-9C4121?style=flat-square)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=flat-square&logo=docker&logoColor=white)

A crypto wallet and trading platform delivered on **two platforms simultaneously** — a full-stack Symfony web application and a native JavaFX desktop client — sharing the same domain model and business logic.

**Status:** Complete · 3rd-year Integrated Project · Team of 3

---

## 🏗️ Architecture Overview

```
Nexora/
├── webApp/      ← Symfony 6.4 web application
└── desktop/     ← JavaFX 21 desktop client
```

Both platforms implement the same core domain: wallets, portfolios, assets, P2P contracts, and user reputation. Building the same product twice across different stacks required designing **platform-agnostic business logic** that could be expressed consistently in both PHP and Java.

---

## webApp/ — Symfony 6.4

**Stack:** PHP 8.1+ · Symfony 6.4 · MySQL · Doctrine ORM · Twig · PHPUnit · Docker

### Features
- DB-backed authentication with role-based access control (Admin / User)
- Wallet creation, real-time balance tracking, fiat/crypto conversions
- Peer-to-peer (P2P) contract lifecycle management
- Dynamic user reputation scoring and rank calculation
- Asset & portfolio tracking with real-time price synchronization
- Full admin back-office

### Testing — 800+ automated tests

```bash
cd webApp
php bin/phpunit --testdox
```

| Test Type | What it covers |
|---|---|
| **Unit** | Wallet conversions, goal progressions, reputation calculations |
| **Static** | Code structure, namespace validity, validation constraints |
| **Doctrine** | DB mapping, column types, bidirectional relationships |

### Architecture decisions
- **Thin controllers** — business logic lives in dedicated Service classes, not controllers
- **Doctrine ORM** — strict entity mapping with cascading and orphan removal
- **Migrations** — full schema versioning via Doctrine Migrations
- **RBAC** — role-based routing enforced at the firewall level, not just the view

---

## desktop/ — JavaFX 21 Client

**Stack:** Java 11+ · JavaFX 21 · Maven

A fully functional native desktop client covering asset management, order handling, and portfolio tracking. Implements the same domain entities and business rules as the web app via the **DAO pattern** with a persistent MySQL backend.

---

## 🛠️ Tech Stack

| Domain | Technologies |
|---|---|
| **Web backend** | PHP 8.1+, Symfony 6.4, Doctrine ORM |
| **Desktop** | Java 11+, JavaFX 21, Maven |
| **Database** | MySQL, Doctrine Migrations |
| **Frontend** | Twig, HTML5, CSS3, Vanilla JS |
| **Testing** | PHPUnit (Unit, Static, Doctrine) |
| **Environment** | Docker, Symfony Local Web Server |

---

## 🚀 Running the Web App

```bash
cd webApp
composer install
cp .env .env.local   # add your DB credentials
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start
```

*For the desktop client, see `desktop/README.md`.*

---

## 👤 About the Developer

3rd-year integrated project (Projet Intégré) — team of 3.

In practice I owned the majority of the codebase: full Symfony architecture,  Doctrine ORM design, the entire 800+ test suite, the JavaFX desktop client, and code review and integration of my teammates' contributions (entity definitions, business logic, authentication and RBAC, and third-party API integrations).

📬 [youssefgaddes3@gmail.com](mailto:[youssefgaddes3@gmail.com) 
