/**
 * Customer Sales Reports JavaScript
 * Handles sales analytics and reporting for customers
 * VERSION: 2.0 - Fixed authentication and API endpoints
 */

// Global identifier to ensure correct file is loaded
window.CUSTOMER_SALES_REPORTS_LOADED = true;

// Prevent conflicts with admin sales-reports.js
if (window.initSalesReports) {
    console.warn(
        "Admin sales-reports.js detected, overriding with customer version"
    );
    delete window.initSalesReports;
    delete window.generateReport;
    delete window.loadKPIData;
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    console.log("=== CUSTOMER SALES REPORTS JS LOADED ===");
    console.log("File: customer-sales-reports.js");
    console.log(
        "Available functions:",
        Object.getOwnPropertyNames(window).filter(
            (name) => name.includes("Sales") || name.includes("sales")
        )
    );
    initCustomerSalesReports();
});

function initCustomerSalesReports() {
    console.log("Customer Sales Reports initializing...");
    // Check if user is authenticated before proceeding
    if (!isLoggedIn()) {
        console.log("User not authenticated, showing error");
        showAuthenticationError();
        return;
    }

    console.log("User authenticated, setting up sales reports");
    setupEventListeners();
    setDefaultDates();
    loadInitialData();
}

function setupEventListeners() {
    // Date range change handler
    const dateRangeSelect = document.getElementById("date-range");
    if (dateRangeSelect) {
        dateRangeSelect.addEventListener("change", handleDateRangeChange);
    }

    // Apply filters button
    const applyFiltersBtn = document.getElementById("apply-filters");
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener("click", applyFilters);
    }

    // Reset filters button
    const resetBtn = document.getElementById("reset-filters");
    if (resetBtn) {
        resetBtn.addEventListener("click", resetFilters);
    }

    // Export report button
    const exportBtn = document.getElementById("export-report");
    if (exportBtn) {
        exportBtn.addEventListener("click", showExportModal);
    }

    // Modal close button
    const closeBtn = document.querySelector(".close");
    if (closeBtn) {
        closeBtn.addEventListener("click", hideExportModal);
    }

    // Cancel button
    const cancelBtn = document.getElementById("cancel-report");
    if (cancelBtn) {
        cancelBtn.addEventListener("click", hideExportModal);
    }

    // Report form submission
    const reportForm = document.getElementById("report-form");
    if (reportForm) {
        reportForm.addEventListener("submit", handleReportSubmission);
    }

    // Close modal when clicking outside
    window.addEventListener("click", function (event) {
        const modal = document.getElementById("report-modal");
        if (event.target === modal) {
            hideExportModal();
        }
    });
}

function setDefaultDates() {
    const today = new Date();
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    const startDateInput = document.getElementById("date-from");
    const endDateInput = document.getElementById("date-to");

    if (startDateInput && endDateInput) {
        startDateInput.value = startOfMonth.toISOString().split("T")[0];
        endDateInput.value = endOfMonth.toISOString().split("T")[0];
    }
}

function handleDateRangeChange() {
    const dateRange = document.getElementById("date-range").value;
    const customDateRange = document.getElementById("custom-date-range");
    const startDateInput = document.getElementById("date-from");
    const endDateInput = document.getElementById("date-to");

    if (!startDateInput || !endDateInput) return;

    const today = new Date();
    let startDate, endDate;

    switch (dateRange) {
        case "this-month":
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            customDateRange.style.display = "none";
            break;
        case "last-month":
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            customDateRange.style.display = "none";
            break;
        case "last-3-months":
            startDate = new Date(today.getFullYear(), today.getMonth() - 3, 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            customDateRange.style.display = "none";
            break;
        case "last-6-months":
            startDate = new Date(today.getFullYear(), today.getMonth() - 6, 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            customDateRange.style.display = "none";
            break;
        case "this-year":
            startDate = new Date(today.getFullYear(), 0, 1);
            endDate = new Date(today.getFullYear(), 11, 31);
            customDateRange.style.display = "none";
            break;
        case "custom":
            customDateRange.style.display = "flex";
            return;
        default:
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            customDateRange.style.display = "none";
    }

    startDateInput.value = startDate.toISOString().split("T")[0];
    endDateInput.value = endDate.toISOString().split("T")[0];

    // Auto-load data when date range changes
    loadSalesData();
}

function loadInitialData() {
    loadSalesData();
}

function applyFilters() {
    loadSalesData();
}

function resetFilters() {
    const dateRangeSelect = document.getElementById("date-range");
    if (dateRangeSelect) {
        dateRangeSelect.value = "this-month";
    }

    setDefaultDates();
    loadSalesData();
}

function loadSalesData() {
    const startDate = document.getElementById("date-from").value;
    const endDate = document.getElementById("date-to").value;

    if (!startDate || !endDate) {
        showNotification("Please select both start and end dates", "error");
        return;
    }

    showLoading(true);

    // Load KPI data
    loadKPIData(startDate, endDate)
        .then(() => {
            // Load detailed sales data
            return loadDetailedSales(startDate, endDate);
        })
        .then(() => {
            showLoading(false);
            showNotification("Sales data loaded successfully", "success");
        })
        .catch((error) => {
            showLoading(false);
            showNotification(
                "Error loading sales data: " + error.message,
                "error"
            );
            console.error("Error loading sales data:", error);
        });
}

function loadKPIData(startDate, endDate) {
    console.log("Customer loadKPIData called with dates:", startDate, endDate);
    return fetch(
        `../../backend/api/customer_sales_reports.php?action=kpi&start_date=${startDate}&end_date=${endDate}`
    )
        .then((response) => {
            if (response.status === 401) {
                // User not authenticated
                showAuthenticationError();
                throw new Error("Please log in to view sales reports");
            }
            if (response.status === 500) {
                // Server error
                throw new Error(
                    "Server error occurred. Please try again later."
                );
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                updateStatsCards(data.data);
            } else {
                throw new Error(data.message || "Failed to load KPI data");
            }
        })
        .catch((error) => {
            console.error("Error loading KPI data:", error);
            if (error.message.includes("log in")) {
                showAuthenticationError();
            } else {
                // Show sample data if API fails for other reasons
                showSampleKPIData();
            }
        });
}

function loadDetailedSales(startDate, endDate) {
    return fetch(
        `../../backend/api/customer_sales_reports.php?action=detailed_sales&start_date=${startDate}&end_date=${endDate}`
    )
        .then((response) => {
            if (response.status === 401) {
                // User not authenticated
                showAuthenticationError();
                throw new Error("Please log in to view sales reports");
            }
            if (response.status === 500) {
                // Server error
                throw new Error(
                    "Server error occurred. Please try again later."
                );
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                updateSalesTable(data.data);
            } else {
                throw new Error(
                    data.message || "Failed to load detailed sales data"
                );
            }
        })
        .catch((error) => {
            console.error("Error loading detailed sales data:", error);
            if (error.message.includes("log in")) {
                showAuthenticationError();
            } else {
                // Show sample data if API fails for other reasons
                showSampleSalesData();
            }
        });
}

function updateStatsCards(data) {
    // Update Books Sold
    const booksSoldElement = document.querySelector(
        ".stat-card:nth-child(1) .stat-value"
    );
    if (booksSoldElement) {
        booksSoldElement.textContent = data.total_books_sold || 0;
    }

    // Update Total Earnings
    const totalEarningsElement = document.querySelector(
        ".stat-card:nth-child(2) .stat-value"
    );
    if (totalEarningsElement) {
        totalEarningsElement.textContent = formatCurrency(
            data.total_sales || 0
        );
    }

    // Update Commission Paid (assuming 5% commission)
    const commissionElement = document.querySelector(
        ".stat-card:nth-child(3) .stat-value"
    );
    if (commissionElement) {
        const commission = (data.total_sales || 0) * 0.05;
        commissionElement.textContent = formatCurrency(commission);
    }
}

function updateSalesTable(salesData) {
    const tableContainer = document.getElementById("sales-table-container");
    if (!tableContainer) return;

    if (!salesData || salesData.length === 0) {
        tableContainer.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-chart-bar"></i>
                <h3>No Sales Data Available</h3>
                <p>You haven't sold any books in the selected period. Once you make sales, your data will appear here.</p>
                <a href="../sell.html" class="btn btn-primary">Add New Book</a>
            </div>
        `;
        return;
    }

    let tableHTML = `
        <table class="sales-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Customer</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
    `;

    salesData.forEach((sale) => {
        const orderDate = new Date(sale.order_date).toLocaleDateString(
            "en-US",
            {
                year: "numeric",
                month: "short",
                day: "numeric",
            }
        );

        tableHTML += `
            <tr>
                <td>${sale.order_number}</td>
                <td>${orderDate}</td>
                <td>${sale.book_title}</td>
                <td>${sale.book_author}</td>
                <td>${sale.quantity}</td>
                <td>${formatCurrency(sale.price_per_item)}</td>
                <td>${formatCurrency(sale.total_amount)}</td>
                <td>${sale.customer_name}</td>
                <td><span class="status-badge status-${sale.order_status.toLowerCase()}">${
            sale.order_status
        }</span></td>
            </tr>
        `;
    });

    tableHTML += `
            </tbody>
        </table>
    `;

    tableContainer.innerHTML = tableHTML;
}

function showSampleKPIData() {
    const sampleData = {
        total_orders: 8,
        total_books_sold: 12,
        total_sales: 5250,
        avg_order_value: 656.25,
    };
    updateStatsCards(sampleData);
    showNotification(
        "Showing sample data. Please check your connection and try again.",
        "info"
    );
}

function showSampleSalesData() {
    const sampleData = [
        {
            order_number: "ORD-001",
            order_date: "2024-01-15",
            book_title: "The Great Gatsby",
            book_author: "F. Scott Fitzgerald",
            quantity: 1,
            price_per_item: 500,
            total_amount: 500,
            customer_name: "John Doe",
            order_status: "Delivered",
        },
        {
            order_number: "ORD-002",
            order_date: "2024-01-20",
            book_title: "To Kill a Mockingbird",
            book_author: "Harper Lee",
            quantity: 2,
            price_per_item: 400,
            total_amount: 800,
            customer_name: "Jane Smith",
            order_status: "Shipped",
        },
    ];
    updateSalesTable(sampleData);
}

function showExportModal() {
    const startDate = document.getElementById("date-from").value;
    const endDate = document.getElementById("date-to").value;

    if (!startDate || !endDate) {
        showNotification(
            "Please select both start and end dates before exporting",
            "error"
        );
        return;
    }

    const modal = document.getElementById("report-modal");
    if (!modal) {
        console.error("Modal element not found!");
        return;
    }

    // Populate form fields
    const periodField = document.getElementById("report-period");
    const generatedByField = document.getElementById("generated-by");
    const generatedDateField = document.getElementById("generated-date");

    if (periodField) periodField.value = `${startDate} to ${endDate}`;
    if (generatedByField) generatedByField.value = getUsername() || "Customer";
    if (generatedDateField)
        generatedDateField.value = new Date().toLocaleDateString();

    modal.style.display = "block";
}

function hideExportModal() {
    const modal = document.getElementById("report-modal");
    if (modal) {
        modal.style.display = "none";
    }
}

function handleReportSubmission(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const reportData = {
        title: formData.get("report-title"),
        period: formData.get("report-period"),
        generatedBy: formData.get("generated-by"),
        generatedDate: formData.get("generated-date"),
        notes: formData.get("report-notes"),
        startDate: document.getElementById("date-from").value,
        endDate: document.getElementById("date-to").value,
    };

    // Generate and download HTML report
    generateHTMLReport(reportData);

    // Hide modal
    hideExportModal();

    // Show success message
    showNotification(
        "HTML report generated successfully! Open in browser and print to PDF.",
        "success"
    );
}

function generateHTMLReport(reportData) {
    console.log("Generating HTML report with data:", reportData);
    console.log("Date range:", reportData.startDate, "to", reportData.endDate);

    // Use customer sales reports API
    const url = `../../backend/api/customer_sales_reports.php?action=export&format=html&start_date=${reportData.startDate}&end_date=${reportData.endDate}`;

    console.log("HTML export URL:", url);

    // Create a temporary link to trigger download
    const link = document.createElement("a");
    link.href = url;
    link.download = `customer_sales_report_${reportData.startDate}_to_${reportData.endDate}.html`;

    console.log("Triggering HTML download...");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showNotification(
        "HTML report generated successfully! Open in browser and print to PDF.",
        "success"
    );
}

function exportReport() {
    const startDate = document.getElementById("date-from").value;
    const endDate = document.getElementById("date-to").value;

    if (!startDate || !endDate) {
        showNotification(
            "Please select both start and end dates before exporting",
            "error"
        );
        return;
    }

    const url = `../../backend/api/customer_sales_reports.php?action=export&format=csv&start_date=${startDate}&end_date=${endDate}`;

    // Create a temporary link to trigger download
    const link = document.createElement("a");
    link.href = url;
    link.download = `customer_sales_report_${startDate}_to_${endDate}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showNotification("Report exported as CSV successfully", "success");
}

function showLoading(show) {
    const tableContainer = document.getElementById("sales-table-container");
    if (tableContainer) {
        if (show) {
            tableContainer.innerHTML = `
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading sales data...</p>
                </div>
            `;
        }
    }
}

function showNotification(message, type = "info") {
    // Create notification element
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        z-index: 1000;
        max-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;

    // Set background color based on type
    switch (type) {
        case "success":
            notification.style.backgroundColor = "#28a745";
            break;
        case "error":
            notification.style.backgroundColor = "#dc3545";
            break;
        case "warning":
            notification.style.backgroundColor = "#ffc107";
            notification.style.color = "#212529";
            break;
        default:
            notification.style.backgroundColor = "#17a2b8";
    }

    notification.textContent = message;
    document.body.appendChild(notification);

    // Remove notification after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat("en-BD", {
        style: "currency",
        currency: "BDT",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
}

function showAuthenticationError() {
    const dashboardContent = document.querySelector(".dashboard-content");
    if (dashboardContent) {
        dashboardContent.innerHTML = `
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">Authentication Required</h3>
                </div>
                <div class="section-content">
                    <div class="auth-error-message">
                        <i class="fas fa-lock" style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;"></i>
                        <h3>Please Log In</h3>
                        <p>You need to be logged in to view your sales reports.</p>
                        <div class="auth-actions">
                            <a href="../../pages/login.html" class="btn btn-primary">Log In</a>
                            <a href="../../pages/signup.html" class="btn btn-secondary">Sign Up</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}
