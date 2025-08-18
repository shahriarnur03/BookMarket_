// Main JavaScript File for BookMarket

// Wait for the DOM to be fully loaded
document.addEventListener("DOMContentLoaded", function () {
    // Mobile Navigation Toggle
    const navbarToggle = document.querySelector(".navbar-toggle");
    const navbarMenu = document.querySelector(".navbar-menu");
    const navbarButtons = document.querySelector(".navbar-buttons");

    if (navbarToggle) {
        navbarToggle.addEventListener("click", function () {
            navbarMenu.classList.toggle("active");
            navbarButtons.classList.toggle("active");
        });
    }

    // Add to Cart Button Functionality
    const addToCartButtons = document.querySelectorAll(".btn-add-cart");

    addToCartButtons.forEach((button) => {
        button.addEventListener("click", function () {
            // Update cart count
            const cartCount = document.querySelector(".cart-count");
            if (cartCount) {
                let currentCount = parseInt(cartCount.textContent);
                cartCount.textContent = currentCount + 1;
            }

            // Show a subtle notification instead of alert
            showNotification("Book added to cart successfully!");
        });
    });

    // View Details Button Functionality
    const viewDetailsButtons = document.querySelectorAll(".btn-view");

    viewDetailsButtons.forEach((button) => {
        button.addEventListener("click", function (e) {
            // If it's a link (a tag), let it navigate naturally
            if (button.tagName !== "A") {
                e.preventDefault();
                // Redirect to book details page
                window.location.href = "pages/book-details.html";
            }
        });
    });

    // Placeholder for Category Navigation
    const categoryLinks = document.querySelectorAll(".btn-explore");

    categoryLinks.forEach((link) => {
        link.addEventListener("click", function (e) {
            // This is just a placeholder until backend is connected
            // We'll keep the default link behavior for now
        });
    });

    // Dropdown functionality for profile menu if user is logged in
    function initDropdowns() {
        const dropdownBtns = document.querySelectorAll(".dropdown-btn");

        dropdownBtns.forEach((btn) => {
            btn.addEventListener("click", function () {
                const dropdownContent = this.nextElementSibling;
                dropdownContent.classList.toggle("show");
            });
        });

        // Close dropdown when clicking outside
        window.addEventListener("click", function (event) {
            if (!event.target.matches(".dropdown-btn")) {
                const dropdowns =
                    document.querySelectorAll(".dropdown-content");
                dropdowns.forEach((dropdown) => {
                    if (dropdown.classList.contains("show")) {
                        dropdown.classList.remove("show");
                    }
                });
            }
        });
    }

    // Initialize dropdowns
    initDropdowns();

    // Logout functionality handled in auth.js

    // Initialize notification system
    initNotifications();

    // Create notification container if it doesn't exist
    if (!document.getElementById("notification-container")) {
        const notificationContainer = document.createElement("div");
        notificationContainer.id = "notification-container";
        notificationContainer.className = "notification-container";
        document.body.appendChild(notificationContainer);
    }
});

/**
 * Book Slider Functionality
 * This will be implemented later when connected with backend
 * It will show most rated books in a carousel/slider
 */
function initializeBookSlider() {
    // Placeholder for now
    console.log("Book slider will be implemented when connected with backend");
    // This function will create a slider for most rated books
}

/**
 * Search Functionality
 * This will be implemented on the browse books page
 */
function initializeSearch() {
    // Will be implemented later
    console.log("Search functionality will be implemented on browse page");
}

/**
 * Filter Functionality
 * This will be implemented on the browse books page
 */
function initializeFilters() {
    // Will be implemented later
    console.log("Filter functionality will be implemented on browse page");
}

/**
 * Helper function to format currency (BDT)
 * @param {number} amount - The amount to format
 * @returns {string} - Formatted amount with BDT symbol
 */
function formatCurrency(amount) {
    return "৳" + amount.toFixed(2);
}

/**
 * Helper function to create star rating
 * @param {number} rating - Rating value (0-5)
 * @returns {string} - HTML string for star rating
 */
function createStarRating(rating) {
    let stars = "";
    const fullStar = '<i class="fas fa-star"></i>';
    const halfStar = '<i class="fas fa-star-half-alt"></i>';
    const emptyStar = '<i class="far fa-star"></i>';

    // Calculate full stars
    const fullStars = Math.floor(rating);
    // Calculate if there should be a half star
    const hasHalfStar = rating % 1 >= 0.5;
    // Calculate empty stars
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

    // Add full stars
    for (let i = 0; i < fullStars; i++) {
        stars += fullStar;
    }

    // Add half star if needed
    if (hasHalfStar) {
        stars += halfStar;
    }

    // Add empty stars
    for (let i = 0; i < emptyStars; i++) {
        stars += emptyStar;
    }

    return stars;
}

/**
 * Initialize notification system
 */
function initNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById("notification-container")) {
        const notificationContainer = document.createElement("div");
        notificationContainer.id = "notification-container";
        document.body.appendChild(notificationContainer);

        // Add styles for notifications
        const style = document.createElement("style");
        style.textContent = `
            #notification-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            }
            .notification {
                background-color: #4a6fa5;
                color: white;
                padding: 12px 20px;
                margin-bottom: 10px;
                border-radius: 4px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
                display: flex;
                justify-content: space-between;
                align-items: center;
                min-width: 250px;
                max-width: 350px;
                opacity: 0;
                transform: translateY(-20px);
                transition: all 0.3s ease;
            }
            .notification.success {
                background-color: #27ae60;
            }
            .notification.error {
                background-color: #e74c3c;
            }
            .notification.warning {
                background-color: #f39c12;
            }
            .notification.show {
                opacity: 1;
                transform: translateY(0);
            }
            .notification-close {
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                font-size: 16px;
                margin-left: 10px;
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Show a notification message
 * @param {string} message - The message to display
 * @param {string} type - The type of notification (success, error, warning)
 * @param {number} duration - How long to show the notification in ms
 */
function showNotification(message, type = "success", duration = 3000) {
    const container = document.getElementById("notification-container");

    // Create notification element
    const notification = document.createElement("div");
    notification.className = `notification ${type}`;

    // Create message element
    const messageElement = document.createElement("span");
    messageElement.textContent = message;
    notification.appendChild(messageElement);

    // Create close button
    const closeButton = document.createElement("button");
    closeButton.className = "notification-close";
    closeButton.innerHTML = "&times;";
    closeButton.addEventListener("click", () => {
        notification.classList.remove("show");
        setTimeout(() => {
            container.removeChild(notification);
        }, 300);
    });
    notification.appendChild(closeButton);

    // Add to container
    container.appendChild(notification);

    // Show notification with animation
    setTimeout(() => {
        notification.classList.add("show");
    }, 10);

    // Auto remove after duration
    setTimeout(() => {
        notification.classList.remove("show");
        setTimeout(() => {
            if (container.contains(notification)) {
                container.removeChild(notification);
            }
        }, 300);
    }, duration);
}

/**
 * Helper function to get relative path to root
 * @param {string} targetPath - The target path to navigate to
 * @returns {string} - The relative path
 */
function getRelativePath(targetPath) {
    // Get current path
    const currentPath = window.location.pathname;

    // Count directories from root
    const pathParts = currentPath.split("/").filter(Boolean);
    const depth = pathParts.length - 1; // -1 because we don't count the file itself

    // Create relative path
    let relativePath = "";
    for (let i = 0; i < depth; i++) {
        relativePath += "../";
    }

    return relativePath + targetPath;
}
