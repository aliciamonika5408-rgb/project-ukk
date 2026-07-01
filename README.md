# 🐾 Stock Gudang — Kawaii Pastel Purple Theme 💜

An adorable, clean, and highly responsive **Inventory Management System (Manajemen Stock Gudang)** built with a lovely pastel purple theme and cute cat illustration accents. This project is specifically optimized, structured, and refactored for the **UKK (Ujian Kompetensi Keahlian) Practical Exam**.

---

## 🎨 Theme & Aesthetics
- **Color Palette:** Soft Pastel Lavender, Peach-Pink, and warm whites.
- **Accents:** Cute cat mascots (`cat-laptop`, `cat-hi`, `cat-peek`) that cheer you on as you manage the warehouse!
- **Transitions:** Smooth hover transitions, floating animations for stat cards, and modern glassmorphism details.
- **Responsive:** 100% optimized for mobile screens with horizontal scrolling tables and stacked form toolbars.

---

## ✨ Features
1. **📊 Interactive Dashboard:**
   - Real-time inventory status counters (Total Items, Suppliers, Goods In/Out).
   - Dynamic Yearly Transaction Chart (Chart.js) with calendar selector (**Juli 2024, 2025, 2026**).
   - Lists of recent Goods Inbound and Outbound.
2. **📦 Inventory Management (CRUD Data Barang):**
   - Manage items with item code, name, category, unit, buying/selling price, minimum stock, and profile photo upload.
   - Smart alert triggers when items hit their minimum stock limit.
3. **🏷️ Category & Supplier CRUD:**
   - Manage categories with dependency safety (prevents deleting categories containing active items).
   - Manage supplier contact directories.
4. **📥 Transaction Logging (Barang Masuk & Keluar):**
   - Log goods movement and dynamically update database inventory counts.
   - Custom transactional number auto-generator (e.g. `BM-2026-0001`).
   - Integrated date filter and quick reset tool.
5. **📝 Stock Opname:**
   - Reconcile database inventory counts with actual physical warehouse counts.
6. **📈 Report Center (Laporan):**
   - Period-based transaction report generator.
   - Clean tabular reports with **Cetak/Print** view and fully working **Export to Excel** download stream.
7. **🔒 User Management & Multi-role Access:**
   - Register, login, and profile avatars.
   - Role-based permissions: `Admin` (full access), `Gudang` (transactions & stock opname), and `Viewer` (read-only charts & tables).

---

## 🛠️ Technology Stack
- **Backend:** PHP 8.x (Native Object-Oriented MySQLi)
- **Frontend:** HTML5, CSS3 (Vanilla Responsive Grid/Flexbox), JavaScript (Vanilla ES6)
- **Database:** MySQL / MariaDB
- **Libraries:** Chart.js, FontAwesome v6.5, Poppins & Playfair Google Fonts

---

## 🚀 Installation & Setup
1. **Clone or Download the Project:**
   Move the folder to your local server directory (e.g., `C:/xampp/htdocs/ukk-cia/`).
2. **Database Setup:**
   - Open phpMyAdmin.
   - Create a new database named `stock_gudang`.
   - Import the database schema from the file: `database/stock_gudang.sql`.
3. **Configuration:**
   - Open `config/database.php`.
   - Update your MySQL credentials (host, user, password, database name) if necessary.
4. **Run the App:**
   - Start Apache and MySQL in XAMPP.
   - Open your browser and navigate to `http://localhost/ukk-cia/`.

---

## 🔑 Login Accounts (Default Credentials)
| Role | Username | Password |
|---|---|---|
| 👑 Admin | `admin` | `password` |
| 📦 Staff Gudang | `gudang` | `password` |
| 👁️ Viewer | `viewer` | `password` |

---

## 👩‍💻 UKK Exam Notes (Catatan Penguji)
- **Clean Architecture:** Navigations are handled cleanly on the server-side via PHP in `includes/sidebar.php` for robust rendering.
- **Standards Compliant:** Trailing PHP closing tags (`?>`) have been removed from all AJAX scripts under the `ajax/` folder to prevent output buffer pollution and prevent file download corruption.
- **Page-scoped CSS:** The `<body>` element contains a dynamic class prefix `page-<?= $page_param ?>` to allow custom style adjustments in `assets/css/style.css` without breaking other views.
