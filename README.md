# 💼 Finance ERP System

> A full-stack, AI-powered Enterprise Resource Planning (ERP) system built for financial management — featuring real-time dashboards, invoice tracking, payables/receivables, vendor management, and an intelligent AI Co-Pilot.

---

## 🚀 Features

### 📊 Financial Dashboard
- Real-time KPI cards (Revenue, Expenses, Net Profit, Cash Balance)
- Interactive charts powered by Chart.js
- Multi-currency support (USD, EUR, GBP, NGN, etc.)
- Period filtering (daily, weekly, monthly, yearly)

### 🧾 Invoice Management
- Create, view, and manage invoices
- Status tracking (Draft, Sent, Paid, Overdue)
- PDF generation support
- Client & vendor search

### 💳 Accounts Payable & Receivable
- Track all outstanding payables and receivables
- Aging reports and due-date alerts
- Bulk payment processing

### 🏦 General Ledger & Journal Entries
- Double-entry bookkeeping
- Chart of accounts
- Trial balance and financial statements

### 👥 Vendor & Customer Management
- Full CRUD for vendors and clients
- Contact details, payment terms, and transaction history

### 🤖 AI Finance Co-Pilot
- Powered by Google Gemini (via Laravel backend)
- Natural language queries (e.g., "Show pending payables", "What is the cash balance?")
- Structured intent parsing and real-time financial data responses
- Hot pink glassmorphism UI with smooth animations

### 🔐 Authentication & Security
- Secure login/logout
- Role-based access control
- CSRF protection

---

## 🛠️ Tech Stack

| Layer       | Technology                              |
|-------------|------------------------------------------|
| **Frontend**  | Vue 3 (Composition API), Vite, Tailwind CSS |
| **Backend**   | Laravel 11 (PHP 8.4), Artisan CLI       |
| **Database**  | MySQL / SQLite                          |
| **AI**        | Google Gemini (via LLM Co-Pilot Service)|
| **Icons**     | Remix Icon (ri-*)                       |
| **Charts**    | Chart.js                                |
| **Auth**      | Laravel Sanctum / Session Auth          |

---

## 📁 Project Structure

```
ERP_SYSTEM_FOR_FINANCE/
├── backend/               # Laravel 11 API & Web Backend
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   └── ApiController.php     # Main API handler
│   │   └── Services/
│   │       └── LlmCoPilotService.php # Gemini AI integration
│   ├── routes/
│   │   └── web.php                   # All routes
│   ├── database/migrations/          # DB schema
│   └── .env.example                  # Environment template
│
└── vue-app/               # Vue 3 Frontend (Vite)
    ├── src/
    │   ├── pages/                    # Route-level pages
    │   │   ├── Dashboard.vue
    │   │   ├── Invoices.vue
    │   │   ├── Payables.vue
    │   │   ├── Receivables.vue
    │   │   └── ...
    │   ├── components/
    │   │   ├── layout/
    │   │   │   └── Sidebar.vue       # Navigation sidebar
    │   │   └── finance/
    │   │       └── AiAssistant.vue   # AI Co-Pilot chat widget
    │   └── router/                   # Vue Router config
    └── vite.config.js
```

---

## ⚙️ Getting Started

### Prerequisites
- PHP 8.4+
- Composer
- Node.js 18+
- MySQL or SQLite
- A Google Gemini API key

---

### 1. Clone the Repository

```bash
git clone https://github.com/Venusian-Beam/ERP_SYSTEM_FOR_FINANCE.git
cd ERP_SYSTEM_FOR_FINANCE
```

---

### 2. Backend Setup (Laravel)

```bash
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your .env:
# - DB_DATABASE, DB_USERNAME, DB_PASSWORD
# - GEMINI_API_KEY=your_gemini_key_here

# Run migrations
php artisan migrate --seed

# Start the backend server
php artisan serve --port=8020
```

---

### 3. Frontend Setup (Vue 3 + Vite)

```bash
cd vue-app

# Install Node dependencies
npm install

# Start the dev server
npm run dev
```

The frontend will be available at **http://localhost:5173**  
The backend API will be available at **http://localhost:8020**

---

## 🌍 Environment Variables

### Backend (`backend/.env`)

```env
APP_NAME="Finance ERP"
APP_ENV=local
APP_KEY=base64:...
APP_URL=http://localhost:8020

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_erp
DB_USERNAME=root
DB_PASSWORD=

GEMINI_API_KEY=your_google_gemini_api_key_here
```

---

## 🤖 AI Co-Pilot Usage

The **Finance Co-Pilot** widget (bottom-right of the dashboard) allows you to:

- Ask natural language questions about your financial data
- Get structured intent responses with parsed data
- Query invoices, balances, payables, vendors in real time

**Example queries:**
- *"Show invoice 1024"*
- *"What is the current cash balance?"*
- *"Show all pending payables"*
- *"List vendors with overdue accounts"*

---

## 📸 Screenshots

> Dashboard with KPI cards, charts, and the AI Co-Pilot widget.

---

## 🧪 Running Tests

```bash
# Backend tests
cd backend
php artisan test

# Frontend lint
cd vue-app
npm run lint
```

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

## 👤 Author

**Venusian-Beam**  
GitHub: [@Venusian-Beam](https://github.com/Venusian-Beam)

---

> Built with ❤️ using Laravel + Vue 3 + Google Gemini AI
