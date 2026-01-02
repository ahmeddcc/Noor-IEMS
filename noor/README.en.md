# 🐟 Noor Financial Management System

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-3-003B57?style=for-the-badge&logo=sqlite&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**A Complete Income & Expense Management System for Fish Trading**

[🚀 Installation](#-installation) • [📖 Usage](#-usage) • [🏗️ Structure](#️-structure) • [📷 Screenshots](#-screenshots)

</div>

---

## 📋 Overview

Noor is a comprehensive financial management system specifically designed for fish trading businesses. The system provides a modern Glass Morphism user interface with full Arabic language support.

## ✨ Features

### 💰 Financial Transaction Management
- Record income (revenue)
- Record expenses
- Record advances and settlements
- Categorize transactions
- Link transactions to clients

### 👥 Client Management
- Add, edit, and delete clients
- Track client balances
- View transaction history per client

### 📊 Dashboard
- Real-time statistics
- Interactive charts
- Current net balance
- Daily/monthly transaction summaries

### 📈 Reports
- Income and expense reports
- Client balance reports
- Period-based reports
- Export functionality

### 👤 User Management
- Multi-level permission system
- Login activity logs
- Protection against brute force attacks

### ⚙️ Settings
- Category management
- Company settings
- Backup and restore
- Audit log

### 🔔 Notifications
- Telegram error notifications
- System alerts

## 🛠️ Requirements

- **PHP** 8.0 or higher
- **XAMPP** or any PHP-compatible web server
- **SQLite3** (bundled with PHP)
- Modern browser with CSS3 support

## 🚀 Installation

### 1. Clone the Project
```bash
# Navigate to htdocs folder
cd C:\xampp\htdocs

# Clone the project
git clone https://github.com/your-username/noor.git

# Or copy the folder directly
```

### 2. Database Setup
The SQLite database will be created automatically on first run at:
```
noor/db/database.sqlite
```

### 3. Configure Settings
Open `config.php` and update the URL:
```php
define('APP_URL', 'http://localhost/noor');
```

### 4. Run the Project
Open your browser and navigate to:
```
http://localhost/noor
```

### 5. Default Login Credentials
```
Username: admin
Password: 123456
```

> ⚠️ **Important:** Change the password immediately after first login!

## 🏗️ Structure

```
noor/
├── 📁 app/
│   ├── 📁 Config/          # Route definitions
│   ├── 📁 Controllers/     # Controllers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── ClientController.php
│   │   ├── TransactionController.php
│   │   ├── ReportController.php
│   │   ├── SettingsController.php
│   │   ├── UserController.php
│   │   └── AuditController.php
│   ├── 📁 Core/            # Core classes
│   │   ├── Router.php
│   │   ├── Session.php
│   │   ├── Database.php
│   │   ├── ErrorAnalyzer.php
│   │   └── TelegramNotifier.php
│   ├── 📁 Models/          # Models
│   │   ├── User.php
│   │   ├── Client.php
│   │   ├── Transaction.php
│   │   ├── Category.php
│   │   ├── Setting.php
│   │   ├── Permission.php
│   │   └── AuditLog.php
│   ├── 📁 Views/           # Views
│   │   ├── 📁 auth/        # Authentication pages
│   │   ├── 📁 layouts/     # Layout templates
│   │   ├── 📁 dashboard/   # Dashboard
│   │   ├── 📁 clients/     # Client management
│   │   ├── 📁 transactions/# Transaction management
│   │   ├── 📁 reports/     # Reports
│   │   ├── 📁 settings/    # Settings
│   │   └── 📁 users/       # User management
│   └── init.php            # Application initialization
├── 📁 assets/
│   ├── 📁 css/             # Stylesheets
│   ├── 📁 js/              # JavaScript files
│   ├── 📁 fonts/           # Fonts
│   └── 📁 images/          # Images
├── 📁 db/                  # Database
├── 📁 backups/             # Backup files
├── 📁 logs/                # Error logs
├── config.php              # Main configuration
├── index.php               # Entry point
├── .htaccess               # Apache configuration
├── README.md               # Arabic documentation
└── README.en.md            # This file
```

## 📖 Usage

### Adding a New Transaction
1. From the dashboard, click **"Add Transaction"**
2. Select transaction type (Income/Expense/Advance)
3. Enter amount and details
4. Select client and category (optional)
5. Click **"Save"**

### Managing Clients
1. Navigate to **Clients** from the sidebar
2. Click **"Add Client"**
3. Enter client information
4. Click **"Save"**

### Viewing Reports
1. Navigate to **Reports**
2. Select report type
3. Define the time period
4. Click **"View Report"**

## 🎨 Design

The system uses a modern **Glass Morphism** design featuring:
- 🌊 Animated wave backgrounds
- ✨ Cyan neon glow effects
- 🎭 Transparent glass effects
- 📱 Fully responsive design for all devices

## 🔐 Security

- CSRF protection for all forms
- Password encryption
- Brute force attack protection
- XSS input sanitization
- Audit logging for all operations

## 📱 Browser Compatibility

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Edge (latest)
- ✅ Safari (latest)
- ✅ Mobile phones and tablets

## 🤝 Contributing

We welcome contributions! Please:
1. Fork the project
2. Create a new branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For questions and support:
- 📧 Email: support@example.com
- 💬 Telegram: @example

---

<div align="center">

**Made with ❤️ in Egypt**

© 2026 Noor - All Rights Reserved

</div>
