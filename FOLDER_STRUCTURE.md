# BookMarket - Role-Based Folder Structure

## 📁 **NEW FOLDER ORGANIZATION**

```
bookMarket_f/
├── 📂 buyer/              # Buyer-only pages
│   ├── buyer-dashboard.html
│   ├── cart.html
│   ├── checkout.html
│   ├── order-history.html
│   ├── order-details.html
│   ├── order-success.html
│   └── wishlist.html
│
├── 📂 seller/             # Seller-only pages
│   ├── seller-dashboard.html
│   ├── add-book.html
│   └── sell-book.html
│
├── 📂 admin/              # Admin-only pages
│   └── admin-dashboard.html
│
├── 📂 common/             # Pages accessible by all
│   ├── index.html         # Main homepage
│   ├── login.html
│   ├── signup.html
│   ├── browse.html
│   ├── book-details.html
│   ├── about.html
│   ├── contact.html
│   ├── profile.html
│   ├── messages.html
│   └── dashboard.html
│
├── 📂 components/         # Shared components
│   ├── navbar.html
│   └── footer.html
│
├── 📂 css/               # Stylesheets
├── 📂 js/                # JavaScript files
├── 📂 assets/            # Images, fonts, etc.
├── index.html            # Root redirect page
└── README.md
```

## 🔐 **ROLE-BASED ACCESS CONTROL**

### **🛒 BUYER FEATURES**

-   ✅ Browse and search books
-   ✅ Add books to cart and wishlist
-   ✅ Place orders and track them
-   ✅ View order history
-   ✅ Message sellers
-   ❌ **Cannot sell books** (must create seller account)

### **💰 SELLER FEATURES**

-   ✅ List and manage books for sale
-   ✅ View sales analytics
-   ✅ Message buyers
-   ✅ Manage inventory
-   ❌ **Cannot purchase books** (must create buyer account)

### **👑 ADMIN FEATURES**

-   ✅ Manage all users
-   ✅ Approve seller accounts
-   ✅ Monitor transactions
-   ✅ System administration
-   ✅ Access all areas

### **🌍 COMMON AREAS (All Users)**

-   ✅ Homepage and browsing
-   ✅ Book details
-   ✅ User profiles
-   ✅ About/Contact pages
-   ✅ Authentication pages

## 🔄 **ROLE SWITCHING FLOW**

### **Buyer → Seller**

1. Buyer tries to sell a book
2. System shows: "You need a seller account to sell books"
3. Redirects to signup page to create seller account
4. (Future: Admin approval required)

### **Seller → Buyer**

1. Seller tries to buy a book
2. System shows: "You need a buyer account to purchase books"
3. Redirects to signup page to create buyer account

## 🚀 **TESTING THE NEW STRUCTURE**

### **Access URLs:**

-   **Root**: `http://localhost/bookMarket_f/` → Redirects to homepage
-   **Homepage**: `http://localhost/bookMarket_f/common/index.html`
-   **Login**: `http://localhost/bookMarket_f/common/login.html`
-   **Buyer Dashboard**: `http://localhost/bookMarket_f/buyer/buyer-dashboard.html`
-   **Seller Dashboard**: `http://localhost/bookMarket_f/seller/seller-dashboard.html`
-   **Admin Dashboard**: `http://localhost/bookMarket_f/admin/admin-dashboard.html`

### **Test Scenarios:**

1. **Login as Buyer** → Can access buyer/ pages, redirected from seller/ pages
2. **Login as Seller** → Can access seller/ pages, redirected from buyer/ pages
3. **Login as Admin** → Can access all pages
4. **No Login** → Only common/ pages accessible

## ⚡ **AUTOMATIC FEATURES**

-   ✅ **Smart Navigation**: Navbar adapts based on user role
-   ✅ **Access Control**: Users automatically redirected if accessing wrong role pages
-   ✅ **Role Switching**: Prompts to create appropriate account type
-   ✅ **Persistent Authentication**: Login state maintained across all pages
-   ✅ **Dynamic Paths**: All file paths automatically adjust based on folder location

## 🔧 **TECHNICAL IMPLEMENTATION**

-   **Role Check**: `checkPageAccess()` function validates page access
-   **Smart Redirects**: `checkRoleSwitchNeeded()` handles role switching
-   **Dynamic Paths**: `getCorrectPath()` adjusts file paths
-   **Navigation**: Smart navbar with role-based menu items
