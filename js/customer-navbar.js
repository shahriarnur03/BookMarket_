// Common Customer Navbar for BookMarket
document.addEventListener("DOMContentLoaded", function () {
    // Function to generate customer navbar
    function generateCustomerNavbar() {
        const navbar = document.querySelector(".navbar");
        if (!navbar) return;

        // Get current page to determine active state
        const currentPage = window.location.pathname
            .split("/")
            .pop()
            .replace(".html", "");

        // Determine the correct path based on current page
        const path = window.location.pathname;
        let basePath = "";
        let homePath = "";
        let pagesPath = "";

        if (path.includes("/pages/customer/")) {
            basePath = "./";
            homePath = "../../index.html";
            pagesPath = "../";
        } else if (path.includes("/pages/")) {
            basePath = "../";
            homePath = "../index.html";
            pagesPath = "./";
        } else {
            basePath = "./";
            homePath = "./index.html";
            pagesPath = "./pages/";
        }

        navbar.innerHTML = `
            <div class="navbar-container">
                <div class="navbar-logo">
                    <img src="${homePath.replace(
                        "index.html",
                        ""
                    )}images/bookmarket-logo.png" alt="BookMarket" onerror="this.src='https://via.placeholder.com/30x30?text=BM'; this.onerror='';">
                    <a href="${homePath}">BookMarket</a>
                </div>
                <ul class="navbar-menu">
                    <li><a href="${homePath}">Home</a></li>
                    <li><a href="${pagesPath}browse.html" class="${
            currentPage === "browse" ? "active" : ""
        }">Browse Books</a></li>
                    <li><a href="${pagesPath}sell.html" class="${
            currentPage === "sell" ? "active" : ""
        }">Sell</a></li>
                    <li><a href="${pagesPath}about.html" class="${
            currentPage === "about" ? "active" : ""
        }">About</a></li>
                    <li><a href="${pagesPath}contact.html" class="${
            currentPage === "contact" ? "active" : ""
        }">Contact</a></li>
                </ul>
                <div class="navbar-buttons">
                    <!-- Auth buttons and profile dropdown will be populated by auth.js based on login state -->
                    <!-- Cart icon will also be handled by auth.js -->
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
            
            .navbar-menu .active:hover {
                background-color: rgba(52, 90, 128, 0.2);
            }
        `;
        document.head.appendChild(style);

        // Initialize mobile menu toggle
        initializeMobileMenu();
    }

    // Function to initialize mobile menu functionality
    function initializeMobileMenu() {
        const navbarToggle = document.querySelector(".navbar-toggle");
        const navbarMenu = document.querySelector(".navbar-menu");

        if (navbarToggle && navbarMenu) {
            navbarToggle.addEventListener("click", function () {
                navbarMenu.classList.toggle("active");
            });

            // Close mobile menu when clicking outside
            document.addEventListener("click", function (event) {
                if (!event.target.closest(".navbar")) {
                    navbarMenu.classList.remove("active");
                }
            });
        }
    }

    // Generate the navbar
    generateCustomerNavbar();

    // Wait a bit for auth system to initialize, then update UI
    setTimeout(() => {
        if (typeof window.bookmarketAuth !== "undefined") {
            window.bookmarketAuth.updateUIForAuthState();
        }
    }, 100);
});
