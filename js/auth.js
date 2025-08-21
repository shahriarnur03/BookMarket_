// Authentication utilities for BookMarket

// Check if user is logged in
function isLoggedIn() {
    return localStorage.getItem("bookmarket_user") !== null;
}

// Get logged in user type (admin or customer)
function getUserType() {
    const user = localStorage.getItem("bookmarket_user");
    return user ? JSON.parse(user).userType : null;
}

// Get logged in username
function getUsername() {
    const user = localStorage.getItem("bookmarket_user");
    return user ? JSON.parse(user).username : "Guest";
}

// Get logged in user ID
function getUserId() {
    const user = localStorage.getItem("bookmarket_user");
    console.log("Retrieved user data from localStorage:", user);
    if (user) {
        try {
            const userData = JSON.parse(user);
            // Check for both userId and id fields
            if (userData.userId) {
                return userData.userId;
            } else if (userData.id) {
                return userData.id;
            }
        } catch (e) {
            console.error("Error parsing user data:", e);
        }
    }
    return null;
}

// Login user
function loginUser(username, userType, userId) {
    const user = {
        username: username,
        userType: userType,
        userId: userId,
        loginTime: new Date().toISOString(),
    };
    console.log("Storing user data in localStorage:", user);
    localStorage.setItem("bookmarket_user", JSON.stringify(user));
}

// Get base path based on current page depth
function getBasePath() {
    const path = window.location.pathname;
    if (path.includes("/pages/customer/") || path.includes("/pages/admin/")) {
        return "../../";
    } else if (path.includes("/pages/")) {
        return "../";
    } else {
        return "./";
    }
}

// Logout user
function logoutUser() {
    localStorage.removeItem("bookmarket_user");
    window.location.href = getBasePath() + "index.html";
}

document.addEventListener("DOMContentLoaded", function () {
    // Update UI based on login state
    function updateUIForAuthState() {
        const isUserLoggedIn = isLoggedIn();
        const userType = getUserType();
        const username = getUsername();

        // Get all navbar buttons containers
        const navbarButtonsContainers =
            document.querySelectorAll(".navbar-buttons");

        if (navbarButtonsContainers.length === 0) return;

        navbarButtonsContainers.forEach((container) => {
            // Get existing elements
            const existingCartIcon = container.querySelector(".cart-icon");
            const existingProfileDropdown =
                container.querySelector(".profile-dropdown");

            if (isUserLoggedIn) {
                // User is logged in - show profile dropdown and cart

                // If login/signup buttons exist, remove them
                const existingAuthButtons =
                    container.querySelector(".auth-buttons");
                if (existingAuthButtons) existingAuthButtons.remove();

                // If profile dropdown doesn't exist, create it
                if (!existingProfileDropdown) {
                    const profileDropdown = document.createElement("div");
                    profileDropdown.className = "profile-dropdown";

                    const basePath = getBasePath();
                    const dashboardPath =
                        userType === "admin"
                            ? basePath + "pages/admin/dashboard.html"
                            : basePath + "pages/customer/dashboard.html";

                    // Create different dropdown content based on user type
                    if (userType === "admin") {
                        profileDropdown.innerHTML = `
                            <button class="dropdown-btn">
                                <img src="${basePath}images/admin_avater.png" alt="Admin Avatar" class="user-avatar" onerror="this.src='https://via.placeholder.com/32x32/4A90E2/FFFFFF?text=A'; this.onerror='';">
                                <span>Admin: ${username}</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-content">
                                <a href="${basePath}pages/admin/profile.html">Profile</a>
                                <a href="${dashboardPath}">Dashboard</a>
                                <a href="#" id="logout-btn">Logout</a>
                            </div>
                        `;
                    } else {
                        profileDropdown.innerHTML = `
                            <button class="dropdown-btn">
                                <img src="${basePath}images/avater_customer.png" alt="Customer Avatar" class="user-avatar" onerror="this.src='https://via.placeholder.com/32x32/27AE60/FFFFFF?text=C'; this.onerror='';">
                                <span>${username}</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-content">
                                <a href="${basePath}pages/customer/profile.html">Profile</a>
                                <a href="${dashboardPath}">Dashboard</a>
                                <a href="${basePath}pages/cart.html">My Cart</a>
                                <a href="${basePath}pages/customer/my-orders.html">My Orders</a>
                                <a href="#" id="logout-btn">Logout</a>
                            </div>
                        `;
                    }

                    // Make sure cart icon exists for customers
                    if (userType === "customer" && !existingCartIcon) {
                        const cartIcon = document.createElement("a");
                        cartIcon.href = basePath + "pages/cart.html";
                        cartIcon.className = "cart-icon";
                        cartIcon.innerHTML =
                            '<i class="fas fa-shopping-cart"></i> <span class="cart-count">0</span>';
                        container.appendChild(cartIcon);

                        // Fetch real cart count from database
                        updateCartCountFromDatabase();
                    }

                    container.appendChild(profileDropdown);

                    // Add event listener to logout button
                    const logoutBtn =
                        profileDropdown.querySelector("#logout-btn");
                    if (logoutBtn) {
                        logoutBtn.addEventListener("click", function (e) {
                            e.preventDefault();
                            showNotification(
                                "Logged out successfully!",
                                "success"
                            );
                            setTimeout(() => {
                                logoutUser();
                            }, 1500);
                        });
                    }
                }
            } else {
                // User is not logged in - show login/signup buttons

                // If profile dropdown exists, remove it
                if (existingProfileDropdown) existingProfileDropdown.remove();

                // If login/signup buttons don't exist, create them
                const existingAuthButtons =
                    container.querySelector(".auth-buttons");
                if (!existingAuthButtons) {
                    const basePath = getBasePath();

                    // Make sure cart icon exists
                    if (!existingCartIcon) {
                        const cartIcon = document.createElement("a");
                        cartIcon.href = basePath + "pages/cart.html";
                        cartIcon.className = "cart-icon";
                        cartIcon.innerHTML =
                            '<i class="fas fa-shopping-cart"></i> <span class="cart-count">0</span>';
                        container.appendChild(cartIcon);
                    }

                    const authButtonsContainer = document.createElement("div");
                    authButtonsContainer.className = "auth-buttons";

                    const loginBtn = document.createElement("a");
                    loginBtn.href = basePath + "pages/login.html";
                    loginBtn.className = "btn-login";
                    loginBtn.textContent = "Login";

                    const signupBtn = document.createElement("a");
                    signupBtn.href = basePath + "pages/signup.html";
                    signupBtn.className = "btn-signup";
                    signupBtn.textContent = "Sign Up";

                    authButtonsContainer.appendChild(loginBtn);
                    authButtonsContainer.appendChild(signupBtn);
                    container.appendChild(authButtonsContainer);
                }
            }
        });
    }

    // Update cart count from database
    function updateCartCountFromDatabase() {
        const user = JSON.parse(
            localStorage.getItem("bookmarket_user") || "{}"
        );
        if (!user.userId) return;

        const form = new FormData();
        form.append("action", "get_cart_count");
        form.append("user_id", user.userId);

        fetch("../backend/cart/cart_manager.php", {
            method: "POST",
            body: form,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success !== false) {
                    const cartCountElements =
                        document.querySelectorAll(".cart-count");
                    cartCountElements.forEach((element) => {
                        element.textContent = data.count || 0;
                    });
                }
            })
            .catch((error) => {
                console.error("Failed to fetch cart count:", error);
            });
    }

    // Initialize authentication state
    updateUIForAuthState();

    // Expose functions globally
    window.bookmarketAuth = {
        isLoggedIn,
        getUserType,
        getUsername,
        getUserId,
        loginUser,
        logoutUser,
        updateUIForAuthState,
    };

    // Handle logout button clicks
    const logoutBtns = document.querySelectorAll(
        "#logout-btn, #sidebar-logout"
    );
    logoutBtns.forEach((btn) => {
        if (btn) {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                showNotification("Logged out successfully!", "success");
                setTimeout(() => {
                    logoutUser();
                }, 1500);
            });
        }
    });
});
