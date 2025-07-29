// BookMarket - Main JavaScript File

// Initialize demo data for testing
function initializeDemoData() {
    // Add some demo books to localStorage if not already present
    if (!localStorage.getItem("sampleDemoBooks")) {
        const demoBooks = [
            {
                id: 1,
                title: "Clean Code",
                author: "Robert C. Martin",
                price: 45.99,
                category: "Programming",
                condition: "New",
                image: "https://via.placeholder.com/200x300/1e2a5a/ffffff?text=Clean+Code",
                availability: "In Stock",
            },
            {
                id: 2,
                title: "The Great Gatsby",
                author: "F. Scott Fitzgerald",
                price: 15.99,
                category: "Fiction",
                condition: "Used",
                image: "https://via.placeholder.com/200x300/d4af37/ffffff?text=Gatsby",
                availability: "In Stock",
            },
            {
                id: 3,
                title: "Introduction to Algorithms",
                author: "Thomas H. Cormen",
                price: 89.99,
                category: "Academic",
                condition: "New",
                image: "https://via.placeholder.com/200x300/1e2a5a/ffffff?text=Algorithms",
                availability: "In Stock",
            },
        ];
        localStorage.setItem("sampleDemoBooks", JSON.stringify(demoBooks));
    }

    // Add some demo cart items for testing (only if user is logged in)
    const userInfo = JSON.parse(localStorage.getItem("userInfo") || "null");
    if (userInfo && !localStorage.getItem("bookmarketCart")) {
        const demoCart = [
            {
                id: 1,
                title: "Clean Code",
                author: "Robert C. Martin",
                price: 45.99,
                image: "https://via.placeholder.com/200x300/1e2a5a/ffffff?text=Clean+Code",
                quantity: 1,
            },
        ];
        localStorage.setItem("bookmarketCart", JSON.stringify(demoCart));
    }
}

// DOM Content Loaded
document.addEventListener("DOMContentLoaded", function () {
    initializeDemoData();
    initializeNavigation();
    initializeCart();
    initializeForms();
    initializeFilters();
    initializeDashboard();
    initializeImagePreview();
    checkAuthState();
    loadCartPage();
});

// Function to load navbar and initialize auth state
function loadNavbarAndInitialize() {
    const currentPath = window.location.pathname;
    const currentFolder = currentPath.split("/").slice(-2, -1)[0]; // Get folder name

    let navbarPath, footerPath;
    // Determine correct navbar and footer based on folder depth
    if (
        currentFolder === "bookMarket_f" ||
        currentPath.endsWith("index.html") ||
        currentPath.endsWith("/")
    ) {
        // Root level
        navbarPath = "common/components/navbar-root.html";
        footerPath = "common/components/footer-root.html";
    } else if (["buyer", "seller", "admin", "common"].includes(currentFolder)) {
        // One level deep
        navbarPath = "../common/components/navbar-subfolder.html";
        footerPath = "../common/components/footer.html";
    } else {
        // Default to subfolder
        navbarPath = "../common/components/navbar-subfolder.html";
        footerPath = "../common/components/footer.html";
    }

    // Load navbar
    fetch(navbarPath)
        .then((response) => response.text())
        .then((data) => {
            document.getElementById("navbar-container").innerHTML = data;
            // Re-initialize navigation after loading
            initializeNavigation();
            // Check authentication state to update navbar - with delay to ensure DOM is ready
            setTimeout(() => {
                checkAuthState();
                // Initialize cart counter
                updateCartCount();
            }, 50);
            // Check page access permissions
            checkPageAccess();
        })
        .catch((error) => {
            console.error("Error loading navbar:", error);
            console.error("Failed to load navbar from:", navbarPath);
        });

    // Load footer if container exists
    const footerContainer = document.getElementById("footer-container");
    if (footerContainer) {
        fetch(footerPath)
            .then((response) => response.text())
            .then((data) => {
                footerContainer.innerHTML = data;
            })
            .catch((error) => {
                console.error("Error loading footer:", error);
                console.error("Failed to load footer from:", footerPath);
            });
    }
}

// Role-based access control
function checkPageAccess() {
    const userInfo = JSON.parse(localStorage.getItem("userInfo") || "null");
    const currentPath = window.location.pathname;
    const currentPage = currentPath.split("/").pop();
    const currentFolder = currentPath.split("/").slice(-2, -1)[0];

    // Define page access rules
    const pageAccess = {
        buyer: [
            "buyer-dashboard.html",
            "cart.html",
            "checkout.html",
            "order-history.html",
            "order-details.html",
            "order-success.html",
            "wishlist.html",
        ],
        seller: ["seller-dashboard.html", "add-book.html", "sell-book.html"],
        admin: ["admin-dashboard.html"],
    };

    // If user is not logged in, allow only common pages
    if (!userInfo) {
        if (["buyer", "seller", "admin"].includes(currentFolder)) {
            showAlert("Please login to access this page", "warning");
            setTimeout(() => {
                window.location.href = getCorrectPath("common/login.html");
            }, 2000);
            return false;
        }
        return true;
    }

    // Check if user is trying to access wrong role pages
    if (currentFolder === "buyer" && userInfo.type !== "buyer") {
        showAlert("Access denied. This page is for buyers only.", "error");
        setTimeout(() => {
            window.location.href = getDashboardUrl(userInfo.type);
        }, 2000);
        return false;
    }

    if (currentFolder === "seller" && userInfo.type !== "seller") {
        showAlert("Access denied. This page is for sellers only.", "error");
        setTimeout(() => {
            window.location.href = getDashboardUrl(userInfo.type);
        }, 2000);
        return false;
    }

    if (currentFolder === "admin" && userInfo.type !== "admin") {
        showAlert(
            "Access denied. This page is for administrators only.",
            "error"
        );
        setTimeout(() => {
            window.location.href = getDashboardUrl(userInfo.type);
        }, 2000);
        return false;
    }

    return true;
}

// Check if user is logged in and has required role
function checkRoleSwitchNeeded(action, requiredRole) {
    const userInfo = JSON.parse(localStorage.getItem("userInfo") || "null");

    if (!userInfo) {
        showAlert("Please login first", "warning");
        window.location.href = getCorrectPath("common/login.html");
        return false;
    }

    if (userInfo.type !== requiredRole) {
        // Just show simple alert instead of complex role switching
        showAlert(`This feature is for ${requiredRole}s only`, "info");
        return false;
    }

    return true;
}

// Check authentication state and update navbar
function checkAuthState() {
    const userInfo = JSON.parse(localStorage.getItem("userInfo") || "null");
    const navActions = document.querySelector(".nav-actions");

    // Show/hide role-specific options
    const sellOptions = document.querySelectorAll(".sell-only");
    sellOptions.forEach((option) => {
        if (
            userInfo &&
            (userInfo.type === "seller" || userInfo.type === "admin")
        ) {
            option.style.display = "block";
        } else {
            option.style.display = "none";
        }
    });

    // Show/hide buyer-only options (cart)
    const buyerOptions = document.querySelectorAll(".buyer-only");
    buyerOptions.forEach((option) => {
        if (userInfo && userInfo.type === "buyer") {
            option.style.display = "block";
        } else {
            option.style.display = "none";
        }
    });

    // Show/hide admin-only options
    const adminOptions = document.querySelectorAll(".admin-only");
    adminOptions.forEach((option) => {
        if (userInfo && userInfo.type === "admin") {
            option.style.display = "block";
        } else {
            option.style.display = "none";
        }
    });

    if (userInfo && navActions) {
        // User is logged in, show profile icon
        // Create profile menu dropdown options based on user type
        let profileDropdownOptions = `
            <a href="${getDashboardUrl(userInfo.type)}" class="dropdown-item">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="#" onclick="showProfile()" class="dropdown-item">
                <i class="fas fa-user"></i>
                My Profile
            </a>`;

        // Add buyer-specific options
        if (userInfo.type === "buyer") {
            profileDropdownOptions += `
            <a href="#" onclick="showOrders()" class="dropdown-item">
                <i class="fas fa-shopping-bag"></i>
                My Orders
            </a>
            <a href="#" onclick="showWishlist()" class="dropdown-item">
                <i class="fas fa-heart"></i>
                Wishlist
            </a>`;
        }

        profileDropdownOptions += `
            <hr>
            <a href="#" onclick="logout()" class="dropdown-item">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>`;

        // Create cart button only for buyers
        let cartButton = "";
        if (userInfo.type === "buyer") {
            cartButton = `
            <a href="${getCorrectPath(
                "buyer/cart.html"
            )}" class="nav-link cart-link" title="Shopping Cart">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count" style="display: none;">0</span>
            </a>`;
        }

        navActions.innerHTML = `
            <div class="profile-menu">
                <button class="profile-btn" onclick="toggleProfileMenu()">
                    <i class="fas fa-user-circle"></i>
                    <span class="profile-name">${
                        userInfo.email.split("@")[0]
                    }</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-header">
                        <i class="fas fa-user-circle"></i>
                        <div>
                            <div class="profile-name">${
                                userInfo.email.split("@")[0]
                            }</div>
                            <div class="profile-type">${
                                userInfo.type.charAt(0).toUpperCase() +
                                userInfo.type.slice(1)
                            }</div>
                        </div>
                    </div>
                    <hr>
                    ${profileDropdownOptions}
                </div>
            </div>
            ${cartButton}
        `;

        // Force re-initialization of profile menu after creating it
        setTimeout(() => {
            initializeProfileMenu();
        }, 100);
    }
}

function getDashboardUrl(userType) {
    switch (userType) {
        case "admin":
            return getCorrectPath("admin/admin-dashboard.html");
        case "seller":
            return getCorrectPath("seller/seller-dashboard.html");
        case "buyer":
            return getCorrectPath("buyer/buyer-dashboard.html");
        default:
            return getCorrectPath("common/dashboard.html");
    }
}

// Initialize profile menu functionality
function initializeProfileMenu() {
    const profileMenu = document.querySelector(".nav-actions .profile-menu");
    const dropdown = document.getElementById("profileDropdown");

    if (profileMenu && dropdown) {
        // Ensure dropdown is hidden initially
        dropdown.style.display = "none";
        dropdown.classList.remove("show");
        profileMenu.classList.remove("active");

        // Remove any existing event listeners
        const newProfileMenu = profileMenu.cloneNode(true);
        profileMenu.parentNode.replaceChild(newProfileMenu, profileMenu);

        // Add click event listener to close dropdown when clicking outside
        document.addEventListener("click", function (event) {
            const currentProfileMenu = document.querySelector(".nav-actions .profile-menu");
            const currentDropdown = document.getElementById("profileDropdown");
            
            if (
                currentProfileMenu &&
                currentDropdown &&
                !currentProfileMenu.contains(event.target)
            ) {
                currentDropdown.style.display = "none";
                currentDropdown.classList.remove("show");
                currentProfileMenu.classList.remove("active");
            }
        });

        // Ensure the toggle function works properly
        window.toggleProfileMenu = function() {
            const currentDropdown = document.getElementById("profileDropdown");
            const currentProfileMenu = document.querySelector(".nav-actions .profile-menu");

            if (currentDropdown && currentProfileMenu) {
                if (
                    currentDropdown.style.display === "none" ||
                    currentDropdown.style.display === ""
                ) {
                    currentDropdown.style.display = "block";
                    currentDropdown.classList.add("show");
                    currentProfileMenu.classList.add("active");
                } else {
                    currentDropdown.style.display = "none";
                    currentDropdown.classList.remove("show");
                    currentProfileMenu.classList.remove("active");
                }
            }
        };
    }
}

// Get correct path based on current folder location
function getCorrectPath(targetPath) {
    const currentPath = window.location.pathname;
    const currentFolder = currentPath.split("/").slice(-2, -1)[0]; // Get folder name

    // If we're in root folder
    if (
        currentFolder === "bookMarket_f" ||
        currentPath.endsWith("index.html") ||
        currentPath.endsWith("/")
    ) {
        return targetPath;
    }
    // If we're in subfolder (buyer, seller, admin, common)
    else if (["buyer", "seller", "admin", "common"].includes(currentFolder)) {
        return "../" + targetPath;
    }
    // Default case - assume subfolder
    return "../" + targetPath;
}

function toggleProfileMenu() {
    const dropdown = document.getElementById("profileDropdown");
    const profileMenu = document.querySelector(".nav-actions .profile-menu");

    if (dropdown && profileMenu) {
        if (
            dropdown.style.display === "none" ||
            dropdown.style.display === ""
        ) {
            dropdown.style.display = "block";
            dropdown.classList.add("show");
            profileMenu.classList.add("active");
        } else {
            dropdown.style.display = "none";
            dropdown.classList.remove("show");
            profileMenu.classList.remove("active");
        }
    }
}

// Show profile page
function showProfile() {
    window.location.href = getCorrectPath("common/profile.html");
}

// Show orders page
function showOrders() {
    const userInfo = JSON.parse(localStorage.getItem("userInfo") || "null");
    if (userInfo && userInfo.type === "buyer") {
        window.location.href = getCorrectPath("buyer/order-history.html");
    } else {
        showAlert("Order history is available for buyers only", "info");
    }
}

// Show wishlist page
function showWishlist() {
    const userInfo = JSON.parse(localStorage.getItem("userInfo") || "null");
    if (userInfo && userInfo.type === "buyer") {
        window.location.href = getCorrectPath("buyer/wishlist.html");
    } else {
        showAlert("Wishlist is available for buyers only", "info");
    }
}

// Show messages page - REMOVED (too complex)

// Show cart page
function showCart() {
    const userInfo = JSON.parse(localStorage.getItem("userInfo") || "null");
    if (userInfo && userInfo.type === "buyer") {
        window.location.href = getCorrectPath("buyer/cart.html");
    } else {
        if (!checkRoleSwitchNeeded("shop for books", "buyer")) return;
    }
}

// Add to wishlist function (for browse page and book details)
function addToWishlist(bookId, title, author, price, image) {
    // Check if user is a buyer before adding to wishlist
    if (!checkRoleSwitchNeeded("add items to wishlist", "buyer")) return;

    let wishlist = JSON.parse(localStorage.getItem("bookmarketWishlist")) || [];

    const existingItem = wishlist.find((item) => item.id === bookId);

    if (existingItem) {
        showAlert(`"${title}" is already in your wishlist`, "info");
        return;
    }

    wishlist.push({
        id: bookId,
        title: title,
        author: author,
        price: price,
        image: image,
        dateAdded: new Date().toISOString().split("T")[0],
        priceAlert: false,
        priceDropped: false,
    });

    localStorage.setItem("bookmarketWishlist", JSON.stringify(wishlist));
    showAlert(`"${title}" added to wishlist!`, "success");
}

// Remove from wishlist function
function removeFromWishlist(bookId) {
    let wishlist = JSON.parse(localStorage.getItem("bookmarketWishlist")) || [];
    wishlist = wishlist.filter((item) => item.id !== bookId);
    localStorage.setItem("bookmarketWishlist", JSON.stringify(wishlist));
    showAlert("Item removed from wishlist", "success");
}

// Check if item is in wishlist
function isInWishlist(bookId) {
    const wishlist =
        JSON.parse(localStorage.getItem("bookmarketWishlist")) || [];
    return wishlist.some((item) => item.id === bookId);
}

// Update wishlist button appearance
function updateWishlistButton(bookId, buttonElement) {
    if (isInWishlist(bookId)) {
        buttonElement.innerHTML = '<i class="fas fa-heart"></i> In Wishlist';
        buttonElement.classList.add("btn-secondary");
        buttonElement.classList.remove("btn-outline");
    } else {
        buttonElement.innerHTML =
            '<i class="far fa-heart"></i> Add to Wishlist';
        buttonElement.classList.add("btn-outline");
        buttonElement.classList.remove("btn-secondary");
    }
}

// Contact seller function (for book details page)
function contactSeller(bookId, sellerName) {
    // This would typically redirect to messages page with a pre-filled conversation
    const userInfo = JSON.parse(localStorage.getItem("userInfo") || "{}");

    if (!userInfo.email) {
        showAlert("Please login to contact sellers", "warning");
        window.location.href = getCorrectPath("common/login.html");
        return;
    }

    showAlert(`Opening conversation with ${sellerName}...`, "info");
    // In a real implementation, this would create a new conversation or navigate to existing one
    setTimeout(() => {
        showAlert("Contact feature coming soon!", "info");
    }, 1000);
}

// Start selling function (for homepage CTA)
function startSelling() {
    if (!checkRoleSwitchNeeded("sell books", "seller")) return;
    window.location.href = getCorrectPath("seller/add-book.html");
}

// Browse by category function
function browseByCategory(category) {
    window.location.href = `${getCorrectPath(
        "common/browse.html"
    )}?category=${encodeURIComponent(category)}`;
}

// Quick search function
function quickSearch(query) {
    if (query.trim()) {
        window.location.href = `${getCorrectPath(
            "common/browse.html"
        )}?search=${encodeURIComponent(query)}`;
    }
}

// Apply promo code function (for cart and checkout)
function applyPromoCode() {
    const promoInput = document.getElementById("promo-code");
    if (!promoInput) return;

    const promoCode = promoInput.value.trim().toUpperCase();

    const validCodes = {
        SAVE10: 10,
        WELCOME20: 20,
        STUDENT15: 15,
        NEWUSER25: 25,
        BOOK5: 5,
    };

    if (validCodes[promoCode]) {
        const discount = validCodes[promoCode];
        showAlert(`Promo code applied! ${discount}% discount`, "success");
        promoInput.value = "";

        // Store applied promo code
        localStorage.setItem(
            "appliedPromoCode",
            JSON.stringify({
                code: promoCode,
                discount: discount,
            })
        );

        // Reload cart page to apply discount
        if (typeof loadCartPage === "function") {
            loadCartPage();
        }
    } else {
        showAlert("Invalid promo code", "error");
    }
}

// Get applied promo code
function getAppliedPromoCode() {
    return JSON.parse(localStorage.getItem("appliedPromoCode") || "null");
}

// Remove promo code
function removePromoCode() {
    localStorage.removeItem("appliedPromoCode");
    showAlert("Promo code removed", "info");

    if (typeof loadCartPage === "function") {
        loadCartPage();
    }
}

// Enhanced notification system
function showNotification(message, type = "info", duration = 5000) {
    // Create notification element
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    // Add styles if not already added
    if (!document.querySelector("#notification-styles")) {
        const styles = document.createElement("style");
        styles.id = "notification-styles";
        styles.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                min-width: 300px;
                max-width: 500px;
                padding: 1rem;
                border-radius: var(--border-radius);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                animation: slideInRight 0.3s ease-out;
            }
            
            .notification-info { background: #e3f2fd; border-left: 4px solid #2196f3; color: #1976d2; }
            .notification-success { background: #e8f5e8; border-left: 4px solid #4caf50; color: #388e3c; }
            .notification-warning { background: #fff3e0; border-left: 4px solid #ff9800; color: #f57c00; }
            .notification-error { background: #ffebee; border-left: 4px solid #f44336; color: #d32f2f; }
            
            .notification-content {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            
            .notification-content i:first-child {
                font-size: 1.2rem;
            }
            
            .notification-content span {
                flex: 1;
                font-weight: 500;
            }
            
            .notification-close {
                background: none;
                border: none;
                cursor: pointer;
                opacity: 0.7;
                padding: 0.25rem;
                color: inherit;
            }
            
            .notification-close:hover {
                opacity: 1;
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(styles);
    }

    // Add to page
    document.body.appendChild(notification);

    // Auto remove after duration
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = "slideInRight 0.3s ease-out reverse";
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }, duration);
}

function getNotificationIcon(type) {
    const icons = {
        info: "info-circle",
        success: "check-circle",
        warning: "exclamation-triangle",
        error: "times-circle",
    };
    return icons[type] || "info-circle";
}

// Enhanced search functionality
function initializeAdvancedSearch() {
    // This would be called on browse.html to handle advanced search features
    const searchInput = document.querySelector(".search-bar input");
    const categoryFilter = document.querySelector("#category-filter");
    const priceFilter = document.querySelector("#price-filter");
    const conditionFilter = document.querySelector("#condition-filter");

    if (searchInput) {
        // Add search suggestions
        searchInput.addEventListener("input", function () {
            showSearchSuggestions(this.value);
        });
    }
}

function showSearchSuggestions(query) {
    if (query.length < 2) return;

    const suggestions = [
        "Clean Code",
        "Design Patterns",
        "JavaScript",
        "Python",
        "Algorithms",
        "Data Structures",
        "React",
        "Node.js",
        "Psychology",
        "Business",
        "Fiction",
        "Science",
    ].filter((item) => item.toLowerCase().includes(query.toLowerCase()));

    // Implementation for showing suggestions dropdown would go here
}

// User activity tracking (for analytics)
function trackUserActivity(action, data = {}) {
    const activity = {
        action: action,
        data: data,
        timestamp: new Date().toISOString(),
        page: window.location.pathname,
    };

    // Store in localStorage for demo (in production, this would be sent to analytics service)
    let activities = JSON.parse(localStorage.getItem("userActivities") || "[]");
    activities.push(activity);

    // Keep only last 100 activities
    if (activities.length > 100) {
        activities = activities.slice(-100);
    }

    localStorage.setItem("userActivities", JSON.stringify(activities));
}

// Enhanced error handling
function handleApiError(
    error,
    userMessage = "Something went wrong. Please try again."
) {
    console.error("API Error:", error);
    showAlert(userMessage, "error");

    // Track error for debugging
    trackUserActivity("error", {
        message: error.message || "Unknown error",
        stack: error.stack || "No stack trace",
    });
}

// Local storage management
function clearUserData() {
    const keysToRemove = [
        "userInfo",
        "bookmarketCart",
        "bookmarketWishlist",
        "appliedPromoCode",
        "userActivities",
    ];

    keysToRemove.forEach((key) => {
        localStorage.removeItem(key);
    });
}

// Enhanced form validation
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validatePhone(phone) {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ""));
}

function validatePassword(password) {
    return {
        isValid: password.length >= 8,
        hasLowercase: /[a-z]/.test(password),
        hasUppercase: /[A-Z]/.test(password),
        hasNumbers: /\d/.test(password),
        hasSpecialChar: /[!@#$%^&*(),.?":{}|<>]/.test(password),
        length: password.length,
    };
}

// Price formatting utilities
function formatPrice(price) {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(price);
}

function formatPriceCompact(price) {
    return `$${price.toFixed(2)}`;
}

// Date formatting utilities
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}

function formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 1) return "yesterday";
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.ceil(diffDays / 7)} weeks ago`;
    if (diffDays < 365) return `${Math.ceil(diffDays / 30)} months ago`;
    return `${Math.ceil(diffDays / 365)} years ago`;
}

// Analytics and insights
function getUserInsights() {
    const activities = JSON.parse(
        localStorage.getItem("userActivities") || "[]"
    );
    const cart = JSON.parse(localStorage.getItem("bookmarketCart") || "[]");
    const wishlist = JSON.parse(
        localStorage.getItem("bookmarketWishlist") || "[]"
    );

    return {
        totalActivities: activities.length,
        cartValue: cart.reduce(
            (sum, item) => sum + item.price * item.quantity,
            0
        ),
        wishlistValue: wishlist.reduce((sum, item) => sum + item.price, 0),
        favoriteCategories: getFavoriteCategories(activities),
        browsingSessions: getBrowsingSessions(activities),
    };
}

function getFavoriteCategories(activities) {
    const categoryActions = activities.filter((a) =>
        a.action.includes("category")
    );
    const categoryCounts = {};

    categoryActions.forEach((action) => {
        const category = action.data.category;
        if (category) {
            categoryCounts[category] = (categoryCounts[category] || 0) + 1;
        }
    });

    return Object.entries(categoryCounts)
        .sort(([, a], [, b]) => b - a)
        .slice(0, 5)
        .map(([category, count]) => ({ category, count }));
}

function getBrowsingSessions(activities) {
    const sessions = [];
    let currentSession = null;

    activities.forEach((activity) => {
        const activityDate = new Date(activity.timestamp);

        if (
            !currentSession ||
            activityDate - new Date(currentSession.endTime) > 30 * 60 * 1000
        ) {
            // 30 minutes gap
            currentSession = {
                startTime: activity.timestamp,
                endTime: activity.timestamp,
                pageViews: 1,
                actions: [activity.action],
            };
            sessions.push(currentSession);
        } else {
            currentSession.endTime = activity.timestamp;
            currentSession.pageViews++;
            currentSession.actions.push(activity.action);
        }
    });

    return sessions;
}

// Logout function
function logout() {
    clearUserData();
    showAlert("Logging out...", "info");
    setTimeout(() => {
        window.location.href = getCorrectPath("index.html");
    }, 1500);
}

// Close dropdown when clicking outside
document.addEventListener("click", function (event) {
    const profileMenu = document.querySelector(".profile-menu");
    const dropdown = document.getElementById("profileDropdown");

    if (profileMenu && dropdown && !profileMenu.contains(event.target)) {
        dropdown.style.display = "none";
    }
});

// Navigation functionality
function initializeNavigation() {
    const hamburger = document.querySelector(".hamburger");
    const navMenu = document.querySelector(".nav-menu");
    const navLinks = document.querySelectorAll(".nav-link");

    // Mobile menu toggle
    if (hamburger) {
        hamburger.addEventListener("click", () => {
            navMenu.classList.toggle("active");
            hamburger.classList.toggle("active");
        });
    }

    // Close mobile menu when clicking on a link
    navLinks.forEach((link) => {
        link.addEventListener("click", () => {
            navMenu.classList.remove("active");
            hamburger.classList.remove("active");
        });
    });

    // Set active navigation link based on current page
    const currentPage =
        window.location.pathname.split("/").pop() || "index.html";
    navLinks.forEach((link) => {
        if (link.getAttribute("href") === currentPage) {
            link.classList.add("active");
        }
    });

    // Navbar scroll effect
    let lastScrollTop = 0;
    const header = document.querySelector(".header");

    window.addEventListener("scroll", () => {
        const scrollTop =
            window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            header.style.transform = "translateY(-100%)";
        } else {
            // Scrolling up
            header.style.transform = "translateY(0)";
        }

        lastScrollTop = scrollTop;
    });
}

// Enhanced Shopping Cart functionality
let cart = JSON.parse(localStorage.getItem("bookmarketCart")) || [];

function initializeCart() {
    // Update cart count display
    updateCartCount();
}

function addToCart(bookId, title, author, price, image) {
    // Check if user is a buyer before adding to cart
    if (!checkRoleSwitchNeeded("add items to cart", "buyer")) return;

    const existingItem = cart.find((item) => item.id === bookId);

    if (existingItem) {
        existingItem.quantity += 1;
        showAlert(`"${title}" quantity updated in cart`, "success");
    } else {
        cart.push({
            id: bookId,
            title: title,
            author: author,
            price: parseFloat(price),
            image: image,
            quantity: 1,
        });
        showAlert(`"${title}" added to cart!`, "success");
    }

    localStorage.setItem("bookmarketCart", JSON.stringify(cart));
    updateCartCount();
    loadCartPage();
}

function removeFromCart(bookId) {
    cart = cart.filter((item) => item.id !== bookId);
    localStorage.setItem("bookmarketCart", JSON.stringify(cart));
    updateCartCount();
    loadCartPage();
    showAlert("Item removed from cart", "success");
}

function updateQuantity(bookId, change) {
    const item = cart.find((item) => item.id === bookId);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(bookId);
            return;
        }
        localStorage.setItem("bookmarketCart", JSON.stringify(cart));
        updateCartCount();
        loadCartPage();
    }
}

function updateCartCount() {
    const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
    const cartCountElements = document.querySelectorAll(".cart-count");
    cartCountElements.forEach((element) => {
        element.textContent = cartCount;
        element.style.display = cartCount > 0 ? "block" : "none";
    });
}

function clearCart() {
    cart = [];
    localStorage.setItem("bookmarketCart", JSON.stringify(cart));
    updateCartCount();
    loadCartPage();
    showAlert("Cart cleared", "success");
}

function getCartTotal() {
    return cart.reduce((total, item) => total + item.price * item.quantity, 0);
}

// Load cart page dynamically
function loadCartPage() {
    const cartItemsList = document.getElementById("cart-items-list");
    const itemCount = document.getElementById("item-count");
    const cartSummary = document.querySelector(".cart-summary");

    if (!cartItemsList) return;

    // Reload cart from localStorage to ensure fresh data
    cart = JSON.parse(localStorage.getItem("bookmarketCart") || "[]");

    if (cart.length === 0) {
        cartItemsList.innerHTML = `
            <div class="empty-cart" style="text-align: center; padding: 3rem; color: var(--text-light);">
                <i class="fas fa-shopping-cart" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                <h3>Your cart is empty</h3>
                <p>Start shopping to add items to your cart</p>
                <a href="browse.html" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-search"></i>
                    Browse Books
                </a>
            </div>
        `;
        if (itemCount) itemCount.textContent = "0";
        if (cartSummary) cartSummary.innerHTML = "";
        return;
    }

    if (itemCount) itemCount.textContent = cart.length;

    cartItemsList.innerHTML = cart
        .map(
            (item) => `
        <div class="cart-item" style="padding: 1.5rem; border: 1px solid var(--border-gray); border-radius: var(--border-radius); margin-bottom: 1rem; background: white;">
            <div style="display: grid; grid-template-columns: auto 1fr auto auto; gap: 1rem; align-items: center;">
                <img src="${item.image}" alt="${
                item.title
            }" style="width: 60px; height: 75px; object-fit: cover; border-radius: 4px;" />
                
                <div>
                    <h4 style="margin: 0 0 0.5rem; color: var(--primary-navy);">${
                        item.title
                    }</h4>
                    <p style="margin: 0 0 0.5rem; color: var(--text-light); font-size: 0.9rem;">by ${
                        item.author
                    }</p>
                    <p style="margin: 0; color: var(--success); font-size: 0.8rem;">
                        <i class="fas fa-check-circle"></i>
                        In Stock
                    </p>
                </div>

                <div class="quantity-controls" style="display: flex; align-items: center; gap: 0.5rem;">
                    <button class="quantity-btn btn-decrease" data-id="${
                        item.id
                    }" style="width: 30px; height: 30px; border: 1px solid var(--border-gray); background: white; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-minus" style="font-size: 0.8rem;"></i>
                    </button>
                    <span class="quantity" style="min-width: 30px; text-align: center; font-weight: bold;">${
                        item.quantity
                    }</span>
                    <button class="quantity-btn btn-increase" data-id="${
                        item.id
                    }" style="width: 30px; height: 30px; border: 1px solid var(--border-gray); background: white; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-plus" style="font-size: 0.8rem;"></i>
                    </button>
                </div>

                <div class="item-actions" style="text-align: right;">
                    <div class="item-price" style="font-size: 1.2rem; font-weight: bold; color: var(--secondary-gold); margin-bottom: 0.5rem;">
                        $${(item.price * item.quantity).toFixed(2)}
                    </div>
                    <button class="btn btn-outline btn-sm btn-remove" data-id="${
                        item.id
                    }" style="color: var(--error); border-color: var(--error);">
                        <i class="fas fa-trash"></i>
                        Remove
                    </button>
                </div>
            </div>
        </div>
    `
        )
        .join("");

    // Add event listeners for quantity controls
    attachCartEventListeners();

    // Update cart summary if it exists
    if (cartSummary) {
        const subtotal = getCartTotal();
        const tax = subtotal * 0.08; // 8% tax
        const shipping = subtotal > 50 ? 0 : 5.99;
        const total = subtotal + tax + shipping;

        cartSummary.innerHTML = `
            <div class="card sticky-top" style="top: 120px;">
                <div class="card-content">
                    <h2 style="margin: 0 0 1.5rem; color: var(--primary-navy);">Order Summary</h2>
                    
                    <div class="summary-line" style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Subtotal (${cart.reduce(
                            (total, item) => total + item.quantity,
                            0
                        )} items)</span>
                        <span>$${subtotal.toFixed(2)}</span>
                    </div>
                    
                    <div class="summary-line" style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Tax</span>
                        <span>$${tax.toFixed(2)}</span>
                    </div>
                    
                    <div class="summary-line" style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span>Shipping</span>
                        <span>${
                            shipping === 0 ? "FREE" : "$" + shipping.toFixed(2)
                        }</span>
                    </div>
                    
                    <hr style="margin: 1rem 0; border: none; height: 1px; background: var(--border-gray);">
                    
                    <div class="summary-total" style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; color: var(--primary-navy); margin-bottom: 1.5rem;">
                        <span>Total</span>
                        <span>$${total.toFixed(2)}</span>
                    </div>

                    <!-- Promo Code -->
                    <div class="promo-section mb-3">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Promo Code</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="promo-code" placeholder="Enter code" style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-gray); border-radius: 4px;" />
                            <button class="btn btn-outline" onclick="applyPromoCode()">Apply</button>
                        </div>
                    </div>
                    
                    <a href="checkout.html" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem; text-decoration: none; text-align: center; display: block;">
                        <i class="fas fa-credit-card"></i>
                        Proceed to Checkout
                    </a>
                    
                    <a href="browse.html" class="btn btn-outline" style="width: 100%; text-decoration: none; text-align: center; display: block;">
                        <i class="fas fa-arrow-left"></i>
                        Continue Shopping
                    </a>

                    <p style="margin: 1rem 0 0; font-size: 0.8rem; color: var(--text-light); text-align: center;">
                        <i class="fas fa-shield-alt"></i>
                        Secure checkout with SSL encryption
                    </p>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="card mt-3">
                <div class="card-content">
                    <h4 style="margin-bottom: 1rem; color: var(--primary-navy);">We Accept</h4>
                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        <div style="padding: 0.5rem; border: 1px solid var(--border-gray); border-radius: 4px; background: white;">
                            <i class="fab fa-cc-visa" style="color: #1a1f71; font-size: 1.5rem;"></i>
                        </div>
                        <div style="padding: 0.5rem; border: 1px solid var(--border-gray); border-radius: 4px; background: white;">
                            <i class="fab fa-cc-mastercard" style="color: #eb001b; font-size: 1.5rem;"></i>
                        </div>
                        <div style="padding: 0.5rem; border: 1px solid var(--border-gray); border-radius: 4px; background: white;">
                            <i class="fab fa-cc-amex" style="color: #006fcf; font-size: 1.5rem;"></i>
                        </div>
                        <div style="padding: 0.5rem; border: 1px solid var(--border-gray); border-radius: 4px; background: white;">
                            <i class="fas fa-money-bill-wave" style="color: #28a745; font-size: 1.5rem;"></i>
                            <span style="font-size: 0.8rem; margin-left: 0.25rem;">COD</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

// Attach event listeners to cart controls
function attachCartEventListeners() {
    // Quantity increase buttons
    document.querySelectorAll(".btn-increase").forEach((button) => {
        button.addEventListener("click", function () {
            const bookId = parseInt(this.getAttribute("data-id"));
            updateQuantity(bookId, 1);
        });
    });

    // Quantity decrease buttons
    document.querySelectorAll(".btn-decrease").forEach((button) => {
        button.addEventListener("click", function () {
            const bookId = parseInt(this.getAttribute("data-id"));
            updateQuantity(bookId, -1);
        });
    });

    // Remove buttons
    document.querySelectorAll(".btn-remove").forEach((button) => {
        button.addEventListener("click", function () {
            const bookId = parseInt(this.getAttribute("data-id"));
            const cartItem = cart.find((item) => item.id === bookId);
            if (cartItem) {
                removeFromCart(bookId);
            }
        });
    });
}

// Form validation and handling
function initializeForms() {
    // Login form - simplified without validation blocking
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;
            const userType = document.getElementById("userType").value;

            // Simple check for empty fields only
            if (!email || !password || !userType) {
                showAlert("Please fill in all fields", "error");
                return;
            }

            // Simulate login success
            showAlert("Login successful! Redirecting...", "success");

            // Store user info in localStorage
            localStorage.setItem(
                "userInfo",
                JSON.stringify({
                    email: email,
                    type: userType,
                    loginTime: new Date().toISOString(),
                    name: email.split("@")[0], // Use part of email as name
                })
            );

            setTimeout(() => {
                // Role-based redirection
                window.location.href = getDashboardUrl(userType);
            }, 1500);
        });
    }

    // Signup form - simplified without validation blocking
    const signupForm = document.getElementById("signupForm");
    if (signupForm) {
        signupForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const name = document.getElementById("name").value;
            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;
            const confirmPassword =
                document.getElementById("confirmPassword").value;
            const userType = document.getElementById("userType").value;

            // Simple check for empty fields only
            if (!name || !email || !password || !confirmPassword || !userType) {
                showAlert("Please fill in all required fields", "error");
                return;
            }

            // Simulate signup success
            showAlert(
                "Account created successfully! Redirecting to login...",
                "success"
            );
            setTimeout(() => {
                window.location.href = getCorrectPath("common/login.html");
            }, 1500);
        });
    }

    // Contact form - simplified
    const contactForm = document.getElementById("contactForm");
    if (contactForm) {
        contactForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const name = document.getElementById("name").value;
            const email = document.getElementById("email").value;
            const message = document.getElementById("message").value;

            // Simple check for empty fields only
            if (!name || !email || !message) {
                showAlert("Please fill in all required fields", "error");
                return;
            }

            // Simulate message sent
            showAlert(
                "Message sent successfully! We'll get back to you soon.",
                "success"
            );
            contactForm.reset();
        });
    }

    // Add book form - simplified
    const addBookForm = document.getElementById("addBookForm");
    if (addBookForm) {
        addBookForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const title = document.getElementById("title").value;
            const author = document.getElementById("author").value;
            const category = document.getElementById("category").value;
            const condition = document.getElementById("condition").value;
            const price = document.getElementById("price").value;
            const description = document.getElementById("description").value;

            // Simple check for empty fields only
            if (
                !title ||
                !author ||
                !category ||
                !condition ||
                !price ||
                !description
            ) {
                showAlert("Please fill in all required fields", "error");
                return;
            }

            // Simulate book added
            showAlert("Book listing created successfully!", "success");
            setTimeout(() => {
                window.location.href = getCorrectPath(
                    "seller/seller-dashboard.html"
                );
            }, 1500);
        });
    }

    // Newsletter form - simplified
    const newsletterForms = document.querySelectorAll(".newsletter-form");
    newsletterForms.forEach((form) => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;

            if (email) {
                showAlert("Successfully subscribed to newsletter!", "success");
                this.reset();
            } else {
                showAlert("Please enter an email address", "error");
            }
        });
    });
}

// Filter functionality for browse page
function initializeFilters() {
    const categoryFilter = document.getElementById("categoryFilter");
    const conditionFilter = document.getElementById("conditionFilter");
    const priceFilter = document.getElementById("priceFilter");
    const searchInput = document.getElementById("searchInput");
    const sortFilter = document.getElementById("sortFilter");

    function applyFilters() {
        const books = document.querySelectorAll(".book-card");
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : "";
        const category = categoryFilter ? categoryFilter.value : "";
        const condition = conditionFilter ? conditionFilter.value : "";
        const priceRange = priceFilter ? priceFilter.value : "";
        const sortBy = sortFilter ? sortFilter.value : "";

        let visibleBooks = [];

        books.forEach((book) => {
            const title =
                book.querySelector(".book-title")?.textContent.toLowerCase() ||
                "";
            const author =
                book.querySelector(".book-author")?.textContent.toLowerCase() ||
                "";
            const bookCategory = book.dataset.category || "";
            const bookCondition = book.dataset.condition || "";
            const bookPrice = parseFloat(book.dataset.price) || 0;

            let shouldShow = true;

            // Search filter
            if (
                searchTerm &&
                !title.includes(searchTerm) &&
                !author.includes(searchTerm)
            ) {
                shouldShow = false;
            }

            // Category filter
            if (category && bookCategory !== category) {
                shouldShow = false;
            }

            // Condition filter
            if (condition && bookCondition !== condition) {
                shouldShow = false;
            }

            // Price filter
            if (priceRange) {
                const [min, max] = priceRange.split("-").map(Number);
                if (max && (bookPrice < min || bookPrice > max)) {
                    shouldShow = false;
                } else if (!max && bookPrice < min) {
                    shouldShow = false;
                }
            }

            if (shouldShow) {
                book.style.display = "block";
                visibleBooks.push({
                    element: book,
                    price: bookPrice,
                    title: title,
                });
            } else {
                book.style.display = "none";
            }
        });

        // Sorting
        if (sortBy && visibleBooks.length > 0) {
            const container = visibleBooks[0].element.parentNode;

            visibleBooks.sort((a, b) => {
                switch (sortBy) {
                    case "price-low":
                        return a.price - b.price;
                    case "price-high":
                        return b.price - a.price;
                    case "title":
                        return a.title.localeCompare(b.title);
                    default:
                        return 0;
                }
            });

            visibleBooks.forEach((book) => {
                container.appendChild(book.element);
            });
        }
    }

    // Add event listeners
    if (categoryFilter) categoryFilter.addEventListener("change", applyFilters);
    if (conditionFilter)
        conditionFilter.addEventListener("change", applyFilters);
    if (priceFilter) priceFilter.addEventListener("change", applyFilters);
    if (searchInput)
        searchInput.addEventListener("input", debounce(applyFilters, 300));
    if (sortFilter) sortFilter.addEventListener("change", applyFilters);
}

// Dashboard functionality
function initializeDashboard() {
    const dashboardTabs = document.querySelectorAll(".dashboard-tab");
    const dashboardContents = document.querySelectorAll(
        ".dashboard-content > div"
    );

    dashboardTabs.forEach((tab) => {
        tab.addEventListener("click", () => {
            const targetTab = tab.dataset.tab;

            // Remove active class from all tabs
            dashboardTabs.forEach((t) => t.classList.remove("active"));
            dashboardContents.forEach(
                (content) => (content.style.display = "none")
            );

            // Add active class to clicked tab
            tab.classList.add("active");
            const targetContent = document.getElementById(targetTab);
            if (targetContent) {
                targetContent.style.display = "block";
            }
        });
    });
}

// Image preview functionality
function initializeImagePreview() {
    const imageInputs = document.querySelectorAll('input[type="file"]');

    imageInputs.forEach((input) => {
        input.addEventListener("change", function (e) {
            const file = e.target.files[0];
            const previewContainer =
                this.parentNode.querySelector(".image-preview") ||
                this.parentNode.parentNode.querySelector(".image-preview");

            if (file && previewContainer) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewContainer.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" 
                             style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 8px;">
                        <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--text-light);">
                            ${file.name}
                        </p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    });
}

// Utility functions
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showAlert(message, type = "info") {
    // Remove existing alerts
    const existingAlert = document.querySelector(".alert");
    if (existingAlert) {
        existingAlert.remove();
    }

    const alert = document.createElement("div");
    alert.className = `alert alert-${type}`;
    alert.textContent = message;

    // Add to top of main content or body
    const main = document.querySelector("main") || document.body;
    main.insertBefore(alert, main.firstChild);

    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Featured books carousel (for homepage)
function initializeCarousel() {
    const carousel = document.querySelector(".featured-carousel");
    if (!carousel) return;

    const books = carousel.querySelectorAll(".book-card");
    const prevBtn = document.querySelector(".carousel-prev");
    const nextBtn = document.querySelector(".carousel-next");

    let currentIndex = 0;
    const booksPerView = window.innerWidth >= 768 ? 3 : 1;

    function updateCarousel() {
        const translateX = -(currentIndex * (100 / booksPerView));
        carousel.style.transform = `translateX(${translateX}%)`;
    }

    if (prevBtn) {
        prevBtn.addEventListener("click", () => {
            currentIndex =
                currentIndex > 0
                    ? currentIndex - 1
                    : books.length - booksPerView;
            updateCarousel();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", () => {
            currentIndex =
                currentIndex < books.length - booksPerView
                    ? currentIndex + 1
                    : 0;
            updateCarousel();
        });
    }

    // Auto-play carousel
    setInterval(() => {
        if (nextBtn) nextBtn.click();
    }, 5000);
}

// Sample book data for demonstration
const sampleBooks = [
    {
        id: 1,
        title: "The Great Gatsby",
        author: "F. Scott Fitzgerald",
        price: 15.99,
        category: "fiction",
        condition: "new",
        rating: 4.5,
        image: "https://via.placeholder.com/200x250/1e2a5a/ffffff?text=The+Great+Gatsby",
    },
    {
        id: 2,
        title: "To Kill a Mockingbird",
        author: "Harper Lee",
        price: 12.99,
        category: "fiction",
        condition: "used",
        rating: 4.8,
        image: "https://via.placeholder.com/200x250/d4af37/ffffff?text=To+Kill+a+Mockingbird",
    },
    {
        id: 3,
        title: "Introduction to Algorithms",
        author: "Thomas H. Cormen",
        price: 89.99,
        category: "academic",
        condition: "new",
        rating: 4.6,
        image: "https://via.placeholder.com/200x250/1e2a5a/ffffff?text=Algorithms",
    },
    {
        id: 4,
        title: "The Lean Startup",
        author: "Eric Ries",
        price: 18.99,
        category: "business",
        condition: "new",
        rating: 4.3,
        image: "https://via.placeholder.com/200x250/d4af37/ffffff?text=Lean+Startup",
    },
    {
        id: 5,
        title: "Harry Potter and the Sorcerer's Stone",
        author: "J.K. Rowling",
        price: 14.99,
        category: "fiction",
        condition: "used",
        rating: 4.9,
        image: "https://via.placeholder.com/200x250/1e2a5a/ffffff?text=Harry+Potter",
    },
    {
        id: 6,
        title: "Clean Code",
        author: "Robert C. Martin",
        price: 45.99,
        category: "academic",
        condition: "new",
        rating: 4.7,
        image: "https://via.placeholder.com/200x250/d4af37/ffffff?text=Clean+Code",
    },
];

// Function to generate star rating HTML
function generateStarRating(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

    let starsHTML = "";

    for (let i = 0; i < fullStars; i++) {
        starsHTML += '<i class="fas fa-star"></i>';
    }

    if (hasHalfStar) {
        starsHTML += '<i class="fas fa-star-half-alt"></i>';
    }

    for (let i = 0; i < emptyStars; i++) {
        starsHTML += '<i class="far fa-star"></i>';
    }

    return starsHTML;
}

// Function to generate book card HTML
function generateBookCard(book) {
    return `
        <div class="book-card" data-category="${
            book.category
        }" data-condition="${book.condition}" data-price="${book.price}">
            <img src="${book.image}" alt="${book.title}" class="book-card-img">
            <div class="book-card-content">
                <h3 class="book-title">${book.title}</h3>
                <p class="book-author">by ${book.author}</p>
                <div class="book-rating">
                    <span class="stars">${generateStarRating(
                        book.rating
                    )}</span>
                    <span>(${book.rating})</span>
                </div>
                <p class="book-price">$${book.price}</p>
                <div class="book-actions" style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-primary btn-sm" 
                            onclick="addToCart(${book.id}, '${book.title}', '${
        book.author
    }', ${book.price}, '${book.image}')">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                    <a href="book-details.html?id=${
                        book.id
                    }" class="btn btn-outline btn-sm">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    `;
}

// Role-based login functionality - simplified
function handleLogin(event) {
    event.preventDefault();

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const userType = document.getElementById("userType").value;

    if (!email || !password || !userType) {
        showAlert("Please fill in all required fields", "error");
        return;
    }

    // Mock authentication - in real app, this would be server-side
    showAlert("Logging in...", "info");

    setTimeout(() => {
        // Store user info in localStorage for demo
        localStorage.setItem(
            "userInfo",
            JSON.stringify({
                email: email,
                type: userType,
                loginTime: new Date().toISOString(),
                name: email.split("@")[0],
            })
        );

        // Role-based redirection using new paths
        window.location.href = getDashboardUrl(userType);
    }, 1500);
}

// Enhanced signup functionality - simplified
function handleSignup(event) {
    event.preventDefault();

    const fullName = document.getElementById("fullName")
        ? document.getElementById("fullName").value
        : document.getElementById("name")
        ? document.getElementById("name").value
        : "";
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const userType = document.getElementById("userType").value;

    // Validation - simplified
    if (!fullName || !email || !password || !confirmPassword || !userType) {
        showAlert("Please fill in all required fields", "error");
        return;
    }

    showAlert("Creating account...", "info");

    setTimeout(() => {
        showAlert("Account created successfully!", "success");
        setTimeout(() => {
            window.location.href = getCorrectPath("common/login.html");
        }, 1500);
    }, 2000);
}

// Form validation for contact page - simplified
function handleContactForm(event) {
    event.preventDefault();

    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const subject = document.getElementById("subject").value;
    const message = document.getElementById("message").value;

    if (!name || !email || !subject || !message) {
        showAlert("Please fill in all required fields", "error");
        return;
    }

    showAlert("Sending message...", "info");

    setTimeout(() => {
        showAlert(
            "Message sent successfully! We'll get back to you soon.",
            "success"
        );
        document.getElementById("contactForm").reset();
    }, 2000);
}

// Initialize cart count on page load
document.addEventListener("DOMContentLoaded", function () {
    updateCartCount();

    // Attach form handlers
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", handleLogin);
    }

    const signupForm = document.getElementById("signupForm");
    if (signupForm) {
        signupForm.addEventListener("submit", handleSignup);
    }

    const contactForm = document.getElementById("contactForm");
    if (contactForm) {
        contactForm.addEventListener("submit", handleContactForm);
    }
});

// Buy Now functionality
function buyNow(bookId, title, author, price, image) {
    // Check if user is a buyer
    if (!checkRoleSwitchNeeded("purchase books", "buyer")) return;

    // Add to cart first
    addToCart(bookId, title, author, price, image);

    // Redirect to checkout after a brief delay
    showAlert(`"${title}" added to cart. Redirecting to checkout...`, "info");
    setTimeout(() => {
        window.location.href = getCorrectPath("buyer/checkout.html");
    }, 1500);
}

// Share book functionality
function shareBook() {
    if (navigator.share) {
        navigator
            .share({
                title: document.title,
                text: "Check out this book on BookMarket!",
                url: window.location.href,
            })
            .catch((err) => console.log("Error sharing:", err));
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard
            .writeText(window.location.href)
            .then(() => {
                showAlert("Link copied to clipboard!", "success");
            })
            .catch(() => {
                showAlert(
                    "Unable to share. Try copying the URL manually.",
                    "info"
                );
            });
    }
}

// Navigation functions for navbar
function navigateHome() {
    window.location.href = getCorrectPath("index.html");
}

function navigateBrowse() {
    window.location.href = getCorrectPath("common/browse.html");
}

function navigateSell() {
    if (!checkRoleSwitchNeeded("sell books", "seller")) return;
    window.location.href = getCorrectPath("seller/sell-book.html");
}

function navigateAbout() {
    window.location.href = getCorrectPath("common/about.html");
}

function navigateContact() {
    window.location.href = getCorrectPath("common/contact.html");
}

function navigateLogin() {
    window.location.href = getCorrectPath("common/login.html");
}

function navigateSignup() {
    window.location.href = getCorrectPath("common/signup.html");
}

function navigateCart() {
    const userInfo = JSON.parse(localStorage.getItem("userInfo") || "null");
    if (userInfo && userInfo.type === "buyer") {
        window.location.href = getCorrectPath("buyer/cart.html");
    } else {
        if (!checkRoleSwitchNeeded("access shopping cart", "buyer")) return;
    }
}

// Order tracking system
const ORDER_STATUSES = {
    CONFIRMED: "Order Confirmed",
    PROCESSING: "Processing",
    SHIPPED: "Shipped",
    DELIVERED: "Delivered",
};

// Initialize sample orders for testing
function initializeSampleOrders() {
    if (!localStorage.getItem("sampleOrders")) {
        const sampleOrders = [
            {
                id: "ORD001",
                userId: "buyer@example.com",
                customerName: "John Doe",
                items: [
                    {
                        title: "Clean Code",
                        author: "Robert C. Martin",
                        price: 45.99,
                        quantity: 1,
                    },
                ],
                total: 45.99,
                status: "CONFIRMED",
                orderDate: new Date().toISOString(),
                trackingUpdates: [
                    {
                        status: "CONFIRMED",
                        date: new Date().toISOString(),
                        note: "Order received and confirmed",
                    },
                ],
            },
            {
                id: "ORD002",
                userId: "buyer@example.com",
                customerName: "John Doe",
                items: [
                    {
                        title: "The Great Gatsby",
                        author: "F. Scott Fitzgerald",
                        price: 15.99,
                        quantity: 2,
                    },
                ],
                total: 31.98,
                status: "PROCESSING",
                orderDate: new Date(Date.now() - 86400000).toISOString(), // 1 day ago
                trackingUpdates: [
                    {
                        status: "CONFIRMED",
                        date: new Date(Date.now() - 86400000).toISOString(),
                        note: "Order received and confirmed",
                    },
                    {
                        status: "PROCESSING",
                        date: new Date(Date.now() - 43200000).toISOString(),
                        note: "Order is being prepared",
                    },
                ],
            },
        ];
        localStorage.setItem("sampleOrders", JSON.stringify(sampleOrders));
    }
}

// Admin functions for order management
function getAllOrders() {
    return JSON.parse(localStorage.getItem("sampleOrders") || "[]");
}

function updateOrderStatus(orderId, newStatus, note = "") {
    const orders = getAllOrders();
    const order = orders.find((o) => o.id === orderId);

    if (order) {
        order.status = newStatus;
        order.trackingUpdates.push({
            status: newStatus,
            date: new Date().toISOString(),
            note:
                note || `Order status updated to ${ORDER_STATUSES[newStatus]}`,
        });

        localStorage.setItem("sampleOrders", JSON.stringify(orders));
        showAlert(
            `Order ${orderId} updated to ${ORDER_STATUSES[newStatus]}`,
            "success"
        );
        return true;
    }
    return false;
}

function getUserOrders(userEmail) {
    const orders = getAllOrders();
    return orders.filter((order) => order.userId === userEmail);
}

// Buyer order tracking function
function trackOrder(orderId) {
    const orders = getAllOrders();
    const order = orders.find((o) => o.id === orderId);

    if (order) {
        // Create tracking modal or redirect to tracking page
        showOrderTrackingModal(order);
    } else {
        showAlert("Order not found", "error");
    }
}

function showOrderTrackingModal(order) {
    const modal = document.createElement("div");
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;

    const statusOrder = ["CONFIRMED", "PROCESSING", "SHIPPED", "DELIVERED"];
    const currentIndex = statusOrder.indexOf(order.status);

    modal.innerHTML = `
        <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0; color: var(--primary-navy);">Track Order ${
                    order.id
                }</h2>
                <button onclick="this.closest('.tracking-modal').remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h4>Order Details</h4>
                <p><strong>Customer:</strong> ${order.customerName}</p>
                <p><strong>Order Date:</strong> ${new Date(
                    order.orderDate
                ).toLocaleDateString()}</p>
                <p><strong>Total:</strong> $${order.total.toFixed(2)}</p>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h4>Items</h4>
                ${order.items
                    .map(
                        (item) => `
                    <div style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                        ${item.title} by ${item.author} - Qty: ${
                            item.quantity
                        } - $${item.price.toFixed(2)}
                    </div>
                `
                    )
                    .join("")}
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h4>Tracking Progress</h4>
                <div style="display: flex; justify-content: space-between; margin: 1rem 0;">
                    ${statusOrder
                        .map(
                            (status, index) => `
                        <div style="text-align: center; flex: 1;">
                            <div style="
                                width: 30px; 
                                height: 30px; 
                                border-radius: 50%; 
                                margin: 0 auto 0.5rem; 
                                display: flex; 
                                align-items: center; 
                                justify-content: center;
                                background: ${
                                    index <= currentIndex
                                        ? "var(--success)"
                                        : "var(--border-gray)"
                                };
                                color: white;
                                font-weight: bold;
                            ">
                                ${index + 1}
                            </div>
                            <small style="color: ${
                                index <= currentIndex
                                    ? "var(--success)"
                                    : "var(--text-light)"
                            };">
                                ${ORDER_STATUSES[status]}
                            </small>
                        </div>
                    `
                        )
                        .join("")}
                </div>
            </div>
            
            <div>
                <h4>Tracking History</h4>
                <div style="max-height: 200px; overflow-y: auto;">
                    ${order.trackingUpdates
                        .slice()
                        .reverse()
                        .map(
                            (update) => `
                        <div style="padding: 0.75rem; border-left: 3px solid var(--success); margin-bottom: 0.5rem; background: var(--light-gray);">
                            <div style="font-weight: bold;">${
                                ORDER_STATUSES[update.status]
                            }</div>
                            <div style="font-size: 0.9rem; color: var(--text-light);">
                                ${new Date(update.date).toLocaleString()}
                            </div>
                            <div style="margin-top: 0.25rem;">${
                                update.note
                            }</div>
                        </div>
                    `
                        )
                        .join("")}
                </div>
            </div>
        </div>
    `;

    modal.className = "tracking-modal";
    document.body.appendChild(modal);

    // Close on backdrop click
    modal.addEventListener("click", function (e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

// Initialize orders on page load
document.addEventListener("DOMContentLoaded", function () {
    initializeSampleOrders();
});

// Export sample data for use in other pages
window.sampleBooks = sampleBooks;
window.generateBookCard = generateBookCard;
window.generateStarRating = generateStarRating;
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateCartQuantity = updateQuantity;
window.clearCart = clearCart;
window.getCartTotal = getCartTotal;
window.buyNow = buyNow;
window.shareBook = shareBook;
window.navigateHome = navigateHome;
window.navigateBrowse = navigateBrowse;
window.navigateSell = navigateSell;
window.navigateAbout = navigateAbout;
window.navigateContact = navigateContact;
window.navigateLogin = navigateLogin;
window.navigateSignup = navigateSignup;
window.navigateCart = navigateCart;
window.trackOrder = trackOrder;
window.getAllOrders = getAllOrders;
window.updateOrderStatus = updateOrderStatus;
window.getUserOrders = getUserOrders;
