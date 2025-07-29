# BookMarket - Premium Online Bookstore Frontend

A complete, responsive frontend for a premium online bookstore that allows users to buy and sell books of all types. Built with modern HTML5, CSS3, and vanilla JavaScript.

## 🚀 Features

### For Buyers

-   **Browse & Search**: Advanced filtering by category, condition, price, and more
-   **Book Details**: Comprehensive book information with seller details and reviews
-   **Secure Shopping**: Add to cart functionality with localStorage persistence
-   **User Reviews**: Read and write reviews for books and sellers

### For Sellers

-   **Easy Listing**: Intuitive form to list books with image upload
-   **Seller Dashboard**: Manage listings, track sales, and view earnings
-   **Pricing Tools**: Smart pricing suggestions based on condition and market value
-   **Performance Analytics**: Track views, sales, and seller ratings

### General Features

-   **Responsive Design**: Mobile-first approach that works on all devices
-   **Modern UI**: Clean, professional design with smooth animations
-   **User Authentication**: Login and signup with form validation
-   **Community Focus**: User profiles, ratings, and testimonials
-   **Contact & Support**: Multiple ways to get help and stay connected

## 📁 Project Structure

```
BookMarket/
├── index.html              # Homepage with hero, featured books, categories
├── login.html              # User login page
├── signup.html             # User registration page
├── browse.html             # Book browsing with filters and search
├── book-details.html       # Individual book details page
├── sell-book.html          # Seller landing page
├── add-book.html           # Book listing form for sellers
├── dashboard.html          # User/seller dashboard
├── contact.html            # Contact page with form and info
├── about.html              # About us with mission and team
├── css/
│   └── styles.css          # Main stylesheet with design system
├── js/
│   └── main.js             # All JavaScript functionality
├── assets/
│   ├── img/                # Image assets directory
│   └── fonts/              # Font assets directory
├── components/
│   ├── navbar.html         # Reusable navigation component
│   └── footer.html         # Reusable footer component
└── README.md               # This file
```

## 🎨 Design System

### Color Palette

-   **Primary Navy**: `#1e2a5a` - Main brand color
-   **Secondary Gold**: `#d4af37` - Accent and call-to-action color
-   **Accent Beige**: `#f5f1e8` - Background and subtle highlights
-   **Text Colors**: Dark (`#2c2c2c`) and Light (`#6b7280`)
-   **System Colors**: Success, Warning, and Error states

### Typography

-   **Primary Font**: Poppins (Sans-serif for UI elements)
-   **Secondary Font**: Lora (Serif for headings and emphasis)
-   **Responsive Text**: Uses clamp() for fluid typography

### Components

-   **Cards**: Consistent card design with hover effects
-   **Buttons**: Multiple variants (primary, secondary, outline)
-   **Forms**: Styled inputs with validation states
-   **Grid System**: Responsive CSS Grid layouts

## 🛠️ Technologies Used

-   **HTML5**: Semantic markup with modern best practices
-   **CSS3**: Custom properties, Grid, Flexbox, animations
-   **JavaScript (ES6+)**: Vanilla JS with modern features
-   **FontAwesome**: Icons for enhanced UI
-   **Google Fonts**: Poppins and Lora font families

## 📱 Responsive Features

-   **Mobile-First Design**: Optimized for mobile devices
-   **Breakpoints**:
    -   Mobile: < 480px
    -   Tablet: 480px - 768px
    -   Desktop: > 768px
-   **Touch-Friendly**: Large tap targets and intuitive gestures
-   **Performance**: Optimized images and efficient CSS

## ⚡ JavaScript Features

### Core Functionality

-   **Navigation**: Mobile hamburger menu, active states
-   **Shopping Cart**: Add/remove items, localStorage persistence
-   **Form Validation**: Real-time validation with helpful messages
-   **Image Handling**: Preview uploads, file size validation
-   **Search & Filters**: Dynamic filtering and sorting
-   **Dashboard**: Tabbed interface for user management

### Interactive Elements

-   **Password Strength**: Real-time password strength indicator
-   **Price Calculator**: Dynamic earnings calculation for sellers
-   **Image Gallery**: Multiple image uploads with previews
-   **Smooth Scrolling**: Enhanced navigation experience
-   **Loading States**: Visual feedback for user actions

## 🚀 Getting Started

### Prerequisites

-   A modern web browser (Chrome, Firefox, Safari, Edge)
-   A local web server (optional but recommended)

### Installation

1. **Clone or Download** the project files

```bash
git clone <your-repo-url>
cd bookmarket-frontend
```

2. **Serve the Files**

```bash
# Option 1: Using Python
python -m http.server 8000

# Option 2: Using Node.js (http-server)
npx http-server

# Option 3: Using PHP
php -S localhost:8000

# Option 4: Using VS Code Live Server extension
# Right-click on index.html and select "Open with Live Server"
```

3. **Open in Browser**
   Navigate to `http://localhost:8000` to view the website

### File Server Note

Due to the use of `fetch()` for loading components, the website needs to be served from a web server rather than opened directly as files in a browser.

## 📄 Page Overview

### Homepage (`index.html`)

-   Hero section with call-to-action
-   Featured books carousel
-   Category browsing cards
-   Why choose us section
-   Customer testimonials
-   Newsletter signup

### Browse (`browse.html`)

-   Advanced search and filtering
-   Grid/list view toggle
-   Sorting options
-   Pagination
-   Results counter

### Book Details (`book-details.html`)

-   Comprehensive book information
-   Image gallery
-   Seller information
-   Reviews and ratings
-   Related books
-   Tabbed content (description, reviews, shipping)

### Seller Pages

-   **Sell Book**: Landing page with benefits and how-it-works
-   **Add Book**: Complete listing form with image upload
-   **Dashboard**: Manage listings, view sales, edit profile

### Authentication

-   **Login**: Clean login form with password toggle
-   **Signup**: Registration with password strength indicator

### Support

-   **About**: Company story, mission, team, and values
-   **Contact**: Multiple contact methods, form, and map

## 🔧 Customization

### Colors

Modify CSS custom properties in `styles.css`:

```css
:root {
    --primary-navy: #1e2a5a;
    --secondary-gold: #d4af37;
    /* ... other colors */
}
```

### Fonts

Change font imports and variables in `styles.css`:

```css
@import url("your-google-fonts-url");

:root {
    --font-primary: "YourFont", sans-serif;
    --font-secondary: "YourFont", serif;
}
```

### Sample Data

Modify the `sampleBooks` array in `main.js` to change the demo books displayed throughout the site.

## 🌟 Key Features Implemented

### Advanced Form Handling

-   Real-time validation
-   Password strength indicators
-   Image upload with preview
-   Draft saving functionality
-   Multi-step forms

### Shopping Cart System

-   Add/remove items
-   Quantity management
-   localStorage persistence
-   Cart count updates
-   Checkout preparation

### Search & Filter System

-   Text search across titles and authors
-   Category filtering
-   Condition filtering
-   Price range filtering
-   Sort by multiple criteria
-   Real-time results

### User Dashboard

-   Tabbed interface
-   Listing management
-   Order history
-   Profile editing
-   Earnings tracking
-   Settings management

## 📱 Browser Support

-   **Chrome**: 70+
-   **Firefox**: 65+
-   **Safari**: 12+
-   **Edge**: 79+
-   **Mobile**: iOS Safari 12+, Chrome Mobile 70+

## 🔮 Future Enhancements

Ready for backend integration:

-   User authentication system
-   Database integration for books and users
-   Payment processing
-   Real-time messaging between buyers/sellers
-   Advanced search with Elasticsearch
-   Email notifications
-   Analytics and reporting

## 📞 Support

For questions about the frontend code:

1. Check the code comments in `main.js` and `styles.css`
2. Review the component structure in `/components/`
3. Test features using the browser developer tools

## 📄 License

This project is ready for commercial use. The frontend code is clean, well-commented, and production-ready.

---

**BookMarket Frontend** - Premium book marketplace experience built with modern web technologies.
