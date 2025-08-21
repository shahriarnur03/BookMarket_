/**
 * Customer Sales Reports JavaScript
 * Handles sales analytics and reporting for customers
 */

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    initCustomerSalesReports();
});

function initCustomerSalesReports() {
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
        exportBtn.addEventListener("click", exportReport);
    }
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
    return fetch(
        `../../backend/api/customer_sales_reports.php?action=kpi&start_date=${startDate}&end_date=${endDate}`
    )
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                updateStatsCards(data.data);
            } else {
                throw new Error(data.message || "Failed to load KPI data");
            }
        })
        .catch((error) => {
            console.error("Error loading KPI data:", error);
            // Show sample data if API fails
            showSampleKPIData();
        });
}

function loadDetailedSales(startDate, endDate) {
    return fetch(
        `../../backend/api/customer_sales_reports.php?action=detailed_sales&start_date=${startDate}&end_date=${endDate}`
    )
        .then((response) => response.json())
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
            // Show sample data if API fails
            showSampleSalesData();
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
