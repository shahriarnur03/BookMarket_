// My Books Page JavaScript
// Handles fetching and displaying user's books from the database

class MyBooksManager {
    constructor() {
        this.books = [];
        this.currentFilter = "all";
        this.init();
    }

    async init() {
        await this.loadBooks();
        this.setupEventListeners();
        this.displayBooks();
    }

    async loadBooks() {
        try {
            const form = new FormData();
            form.append("action", "get_my_books");

            const response = await fetch(
                "../../backend/books/book_manager.php",
                {
                    method: "POST",
                    body: form,
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success && Array.isArray(data.data)) {
                this.books = data.data;
                console.log(
                    "✅ Books loaded successfully:",
                    this.books.length,
                    "books"
                );
            } else {
                console.error("❌ Failed to load books:", data.message);
                this.books = [];
            }
        } catch (error) {
            console.error("❌ Error loading books:", error);
            this.books = [];
            this.showNotification(
                "Failed to load books. Please try again.",
                "error"
            );
        }
    }

    displayBooks() {
        const booksGrid = document.getElementById("all-books");
        if (!booksGrid) return;

        // Clear existing content
        booksGrid.innerHTML = "";

        if (this.books.length === 0) {
            this.showEmptyState();
            return;
        }

        // Filter books based on current filter
        const filteredBooks = this.getFilteredBooks();

        if (filteredBooks.length === 0) {
            this.showEmptyState();
            return;
        }

        // Display books
        filteredBooks.forEach((book) => {
            const quantity = book.stock_quantity || book.quantity || 1;
            const quantityText = quantity > 0 ? quantity : "Out of Stock";
            console.log(
                `Creating card for book: ${book.title} (Status: ${book.status}, Quantity: ${quantityText})`
            );
            const bookCard = this.createBookCard(book);
            booksGrid.appendChild(bookCard);
        });

        // Update book count
        this.updateBookCount();
    }

    getFilteredBooks() {
        if (this.currentFilter === "all") {
            return this.books;
        }
        return this.books.filter((book) => book.status === this.currentFilter);
    }

    createBookCard(book) {
        const bookCard = document.createElement("div");
        bookCard.className = "book-card";
        bookCard.setAttribute("data-status", book.status);
        bookCard.setAttribute("data-book-id", book.id);

        // Get cover image path
        let coverImagePath = book.cover_image_path;
        if (!coverImagePath || coverImagePath === "") {
            coverImagePath = "../../images/b_cover1.jpg"; // Default image
        } else if (
            !coverImagePath.startsWith("http") &&
            !coverImagePath.startsWith("../../")
        ) {
            coverImagePath = "../../" + coverImagePath;
        }

        // Get status badge class
        const statusClass = this.getStatusClass(book.status);
        const statusText = this.getStatusText(book.status);

        // Debug: Log book data to see what's available
        console.log("Book data for card:", book);
        const quantity = book.stock_quantity || book.quantity || 1;
        const isOutOfStock = quantity <= 0;
        console.log(
            "Book quantity:",
            quantity,
            isOutOfStock ? "(Out of Stock)" : ""
        );
        console.log("Book status:", book.status);

        bookCard.innerHTML = `
            <div class="book-image">
                <img
                    src="${coverImagePath}"
                    alt="${book.title}"
                    class="book-cover"
                    onerror="this.src='https://via.placeholder.com/200x250/4A90E2/FFFFFF?text=Book+Cover'; this.onerror='';"
                />
            </div>
            <div class="book-info">
                <h4 class="book-title">${this.escapeHtml(book.title)}</h4>
                <div class="book-author">by ${this.escapeHtml(
                    book.author
                )}</div>
                <div class="book-category">${this.escapeHtml(
                    book.category_name
                )}</div>
                <div class="book-price">৳${parseFloat(book.price).toFixed(
                    2
                )}</div>
                <div class="book-quantity ${
                    (book.stock_quantity || book.quantity || 1) > 0
                        ? ""
                        : "out-of-stock"
                }">Quantity: ${
            (book.stock_quantity || book.quantity || 1) > 0
                ? book.stock_quantity || book.quantity || 1
                : "Out of Stock"
        }</div>
                <div class="book-condition">Condition: ${this.escapeHtml(
                    book.book_condition
                )}</div>
                <div class="book-status">
                    Status:
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
                <div class="book-actions">
                    <a href="../book-details.html?id=${
                        book.id
                    }" class="book-btn view-btn" target="_blank">View Details</a>
                    <button class="book-btn delete-btn" data-book-id="${
                        book.id
                    }">Delete</button>
                </div>
            </div>
        `;

        return bookCard;
    }

    getStatusClass(status) {
        switch (status) {
            case "pending":
                return "status-pending";
            case "approved":
                return "status-approved";
            case "rejected":
                return "status-rejected";
            case "sold":
                return "status-sold";
            default:
                return "status-pending";
        }
    }

    getStatusText(status) {
        switch (status) {
            case "pending":
                return "Pending Approval";
            case "approved":
                return "Approved";
            case "rejected":
                return "Rejected";
            case "sold":
                return "Sold";
            default:
                return "Pending";
        }
    }

    escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }

    showEmptyState() {
        const booksGrid = document.getElementById("all-books");
        if (!booksGrid) return;

        booksGrid.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <h3>No books found</h3>
                <p>${
                    this.currentFilter === "all"
                        ? "You haven't added any books yet."
                        : `No ${this.currentFilter} books found.`
                }</p>
                <a href="../sell.html" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Your First Book
                </a>
            </div>
        `;
    }

    updateBookCount() {
        const filteredBooks = this.getFilteredBooks();
        const countElement = document.querySelector(".book-count");
        if (countElement) {
            countElement.textContent = `${filteredBooks.length} book${
                filteredBooks.length !== 1 ? "s" : ""
            }`;
        }
    }

    setupEventListeners() {
        // Filter tabs
        const filterTabs = document.querySelectorAll(".filter-tab");
        filterTabs.forEach((tab) => {
            tab.addEventListener("click", (e) => {
                e.preventDefault();
                this.handleFilterChange(tab);
            });
        });

        // Refresh button
        const refreshBtn = document.getElementById("refresh-books");
        if (refreshBtn) {
            refreshBtn.addEventListener("click", async () => {
                refreshBtn.disabled = true;
                refreshBtn.innerHTML =
                    '<i class="fas fa-spinner fa-spin"></i> Refreshing...';

                await this.refreshBooks();

                refreshBtn.disabled = false;
                refreshBtn.innerHTML =
                    '<i class="fas fa-sync-alt"></i> Refresh';
            });
        }

        // Delete buttons (delegated event)
        document.addEventListener("click", (e) => {
            if (e.target.classList.contains("delete-btn")) {
                e.preventDefault();
                this.handleDeleteBook(e.target);
            }
        });
    }

    handleFilterChange(clickedTab) {
        // Remove active class from all tabs
        document
            .querySelectorAll(".filter-tab")
            .forEach((t) => t.classList.remove("active"));

        // Add active class to clicked tab
        clickedTab.classList.add("active");

        // Get filter value
        this.currentFilter = clickedTab.getAttribute("data-filter");

        // Display filtered books
        this.displayBooks();
    }

    async handleDeleteBook(deleteBtn) {
        const bookId = deleteBtn.getAttribute("data-book-id");
        const bookCard = deleteBtn.closest(".book-card");
        const bookTitle = bookCard.querySelector(".book-title").textContent;

        if (confirm(`Are you sure you want to delete "${bookTitle}"?`)) {
            try {
                // Show loading state
                bookCard.style.opacity = "0.5";
                deleteBtn.textContent = "Deleting...";
                deleteBtn.disabled = true;

                const form = new FormData();
                form.append("action", "delete_book");
                form.append("book_id", bookId);

                const response = await fetch(
                    "../../backend/books/book_manager.php",
                    {
                        method: "POST",
                        body: form,
                    }
                );

                const data = await response.json();

                if (data.success) {
                    // Remove book from local array
                    this.books = this.books.filter((book) => book.id != bookId);

                    // Remove book card with animation
                    bookCard.style.opacity = "0";
                    setTimeout(() => {
                        bookCard.remove();
                        this.displayBooks(); // Refresh display
                    }, 300);

                    this.showNotification(
                        "Book deleted successfully",
                        "success"
                    );
                } else {
                    throw new Error(data.message || "Failed to delete book");
                }
            } catch (error) {
                console.error("❌ Delete book error:", error);
                this.showNotification(
                    "Failed to delete book. Please try again.",
                    "error"
                );

                // Reset button state
                bookCard.style.opacity = "1";
                deleteBtn.textContent = "Delete";
                deleteBtn.disabled = false;
            }
        }
    }

    showNotification(message, type = "info") {
        // Check if notification function exists
        if (typeof showNotification === "function") {
            showNotification(message, type);
        } else {
            // Fallback notification
            const notification = document.createElement("div");
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 4px;
                color: white;
                font-weight: 600;
                z-index: 1000;
                background-color: ${
                    type === "success"
                        ? "#27ae60"
                        : type === "error"
                        ? "#e74c3c"
                        : "#3498db"
                };
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideIn 0.3s ease-out;
            `;

            // Add animation keyframes
            if (!document.querySelector("#notification-styles")) {
                const style = document.createElement("style");
                style.id = "notification-styles";
                style.textContent = `
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(style);
            }

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = "slideOut 0.3s ease-in";
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    }

    // Refresh books data
    async refreshBooks() {
        await this.loadBooks();
        this.displayBooks();
    }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    // Wait for auth system to be ready
    function waitForAuthSystem() {
        if (
            typeof window.bookmarketAuth !== "undefined" &&
            window.bookmarketAuth.isLoggedIn
        ) {
            console.log(
                "✅ Auth system ready, initializing My Books Manager..."
            );
            new MyBooksManager();
        } else {
            console.log("⏳ Auth system not ready yet, waiting...");
            setTimeout(waitForAuthSystem, 100);
        }
    }

    // Start waiting for auth system
    setTimeout(waitForAuthSystem, 100);
});
