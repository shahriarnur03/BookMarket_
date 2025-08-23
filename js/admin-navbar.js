// Common Admin Navbar for BookMarket
document.addEventListener("DOMContentLoaded", function () {
    // Function to generate admin navbar
    function generateAdminNavbar() {
        const navbar = document.querySelector(".navbar");
        if (!navbar) return;

        // Get current page to determine active state
        const currentPage = window.location.pathname
            .split("/")
            .pop()
            .replace(".html", "");

        // Identify pages that fall under Management umbrella
        const managementPages = new Set([
            "management",
            "order-management",
            "book-management",
            "user-management",
        ]);

        // Determine the correct path based on current page
        const path = window.location.pathname;
        let basePath = "";
        let imagesBasePath = "";

        if (path.includes("/pages/admin/")) {
            basePath = "./";
            imagesBasePath = "../../images/"; // from pages/admin/* to /images
        } else if (path.includes("/pages/")) {
            basePath = "../";
            imagesBasePath = "../images/"; // from pages/* to /images
        } else {
            basePath = "./";
            imagesBasePath = "./images/"; // from root
        }

        navbar.innerHTML = `
			<div class="navbar-container">
				<div class="navbar-logo">
					<img src="${imagesBasePath}bookmarket-logo.png" alt="BookMarket" onerror="this.src='https://via.placeholder.com/30x30?text=BM'; this.onerror='';">
					<a href="${basePath}dashboard.html">BookMarket</a>
				</div>
                <ul class="navbar-menu">
                    <li><a href="${basePath}order-management.html" class="${
            managementPages.has(currentPage) ? "active" : ""
        }">Management</a></li>
                    <li><a href="${basePath}book-upload.html" class="${
            currentPage === "book-upload" ? "active" : ""
        }">Book Upload</a></li>
                    <li><a href="${basePath}book-approval.html" class="${
            currentPage === "book-approval" ? "active" : ""
        }">Book Approval</a></li>
                    <li><a href="${basePath}sales-reports.html" class="${
            currentPage === "sales-reports" ? "active" : ""
        }">Sales Reports</a></li>
                </ul>
                <div class="navbar-buttons">
                    <div class="profile-dropdown">
                        <button class="dropdown-btn">
                            <img src="${imagesBasePath}admin_avater.png" alt="Admin Avatar" class="user-avatar" onerror="this.src='https://via.placeholder.com/32x32/4A90E2/FFFFFF?text=A'; this.onerror='';">
                            <span>Admin</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="${basePath}profile.html">Profile</a>
                            <a href="${basePath}dashboard.html">Dashboard</a>
                            <a href="${basePath}order-management.html">Order Management</a>
                            <a href="${basePath}book-management.html">Book Management</a>
                            <a href="${basePath}user-management.html">User Management</a>
                            <a href="${basePath}sales-reports.html">Sales Reports</a>
                            <a href="#" id="logout-btn">Logout</a>
                        </div>
                    </div>
                </div>
                <div class="navbar-toggle">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        `;

        // Add active class styling for navbar menu items
        const style = document.createElement("style");
        style.textContent = `
            .navbar-menu .active {
                color: var(--primary-color) !important;
                font-weight: 600;
                background-color: rgba(52, 90, 128, 0.1);
                border-radius: 4px;
                padding: 0.5rem 1rem;
            }
            
            .navbar-menu .active:hover { background-color: rgba(58, 90, 128, 0.15); }

            .dropdown-content.show {
                display: block;
            }
        `;
        document.head.appendChild(style);

        // Initialize dropdown functionality
        initializeDropdowns();
    }

    // Function to initialize dropdown functionality
    function initializeDropdowns() {
        const dropdownBtn = document.querySelector(".dropdown-btn");
        const dropdownContent = document.querySelector(".dropdown-content");

        if (dropdownBtn && dropdownContent) {
            dropdownBtn.addEventListener("click", function () {
                dropdownContent.classList.toggle("show");
            });

            // Close dropdown when clicking outside
            document.addEventListener("click", function (event) {
                if (!event.target.closest(".profile-dropdown")) {
                    dropdownContent.classList.remove("show");
                }
            });

            // Handle logout
            const logoutBtn = document.querySelector("#logout-btn");
            if (logoutBtn) {
                logoutBtn.addEventListener("click", function (e) {
                    e.preventDefault();
                    if (typeof window.bookmarketAuth !== "undefined") {
                        window.bookmarketAuth.logoutUser();
                    } else {
                        // Fallback logout
                        localStorage.removeItem("bookmarket_user");
                        window.location.href = "../../index.html";
                    }
                });
            }
        }
    }

    // Generate the navbar
    generateAdminNavbar();
});
