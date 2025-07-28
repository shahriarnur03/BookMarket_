// BookMarket - Main JavaScript Functionality

// Global variables
let cart = JSON.parse(localStorage.getItem('bookCart')) || [];
let books = [];
let currentUser = JSON.parse(localStorage.getItem('currentUser')) || null;

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Initialize application
function initializeApp() {
    loadBooks();
    updateCartCount();
    checkAuthStatus();
    initializeEventListeners();
}

// Sample book data
function loadBooks() {
    books = [
        {
            id: 1,
            title: "The Great Gatsby",
            author: "F. Scott Fitzgerald",
            genre: "Fiction",
            price: 15.99,
            condition: "Like New",
            image: "images/book1.jpg",
            description: "A classic American novel set in the Jazz Age.",
            seller: "John Doe"
        },
        {
            id: 2,
            title: "To Kill a Mockingbird",
            author: "Harper Lee",
            genre: "Fiction",
            price: 12.99,
            condition: "Good",
            image: "images/book2.jpg",
            description: "A gripping tale of racial injustice and childhood innocence.",
            seller: "Jane Smith"
        },
        {
            id: 3,
            title: "1984",
            author: "George Orwell",
            genre: "Dystopian",
            price: 14.99,
            condition: "New",
            image: "images/book3.jpg",
            description: "A dystopian novel about totalitarian control.",
            seller: "Mike Johnson"
        }
    ];
    localStorage.setItem('books', JSON.stringify(books));
}

// Event listeners
function initializeEventListeners() {
    const searchForms = document.querySelectorAll('.search-form');
    searchForms.forEach(form => {
        form.addEventListener('submit', handleSearch);
    });
    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart')) {
            const bookId = parseInt(e.target.getAttribute('data-book-id'));
            addToCart(bookId);
        }
    });
}

// Search functionality
function handleSearch(e) {
    e.preventDefault();
    const searchTerm = e.target.querySelector('.search-input').value.toLowerCase();
    const filteredBooks = books.filter(book => 
        book.title.toLowerCase().includes(searchTerm) ||
        book.author.toLowerCase().includes(searchTerm) ||
        book.genre.toLowerCase().includes(searchTerm)
    );
    
    if (window.location.pathname.includes('search.html')) {
        displaySearchResults(filteredBooks);
    } else {
        sessionStorage.setItem('searchResults', JSON.stringify(filteredBooks));
        window.location.href = 'search.html';
    }
}

// Cart functionality
function addToCart(bookId) {
    const book = books.find(b => b.id === bookId);
    if (!book) return;
    
    const existingItem = cart.find(item => item.id === bookId);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ ...book, quantity: 1 });
    }
    
    localStorage.setItem('bookCart', JSON.stringify(cart));
    updateCartCount();
    showNotification('Book added to cart!', 'success');
}

function updateCartCount() {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? 'inline' : 'none';
    }
}

// Authentication
function checkAuthStatus() {
    const authButtons = document.querySelector('.nav-actions');
    if (!authButtons) return;
    
    if (currentUser) {
        authButtons.innerHTML = `
            <span>Welcome, \${currentUser.name}</span>
            <a href="profile.html" class="btn btn-outline">Profile</a>
            <button class="btn btn-primary" onclick="logout()">Logout</button>
        `;
    } else {
        authButtons.innerHTML = `
            <a href="login.html" class="btn btn-outline">Login</a>
            <a href="signup.html" class="btn btn-primary">Sign Up</a>
        `;
    }
}

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-\${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            \${message}
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        background: \${type === 'error' ? '#e74c3c' : type === 'success' ? '#27ae60' : '#3498db'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
