// Sales Reports Page JavaScript
// Handles fetching and displaying sales data from the database

class SalesReportsManager {
    constructor() {
        this.salesData = null;
        this.currentFilters = {
            dateRange: 'all-time',
            bookFilter: 'all',
            sortBy: 'date-desc',
            customDateFrom: '',
            customDateTo: ''
        };
        this.init();
    }

    async init() {
        await this.loadSalesData();
        this.setupEventListeners();
        this.updateStats();
        this.renderSalesTable();
    }

    async loadSalesData() {
        try {
            const form = new FormData();
            form.append("action", "get_sales_data");

            const response = await fetch(
                "../../backend/api/sales_reports.php",
                {
                    method: "POST",
                    body: form,
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success && data.data) {
                this.salesData = data.data;
                console.log("✅ Sales data loaded successfully:", this.salesData);
            } else {
                console.error("❌ Failed to load sales data:", data.message);
                this.salesData = null;
            }
        } catch (error) {
            console.error("❌ Error loading sales data:", error);
            this.salesData = null;
            this.showNotification("Failed to load sales data. Please try again.", "error");
        }
    }

    updateStats() {
        if (!this.salesData) return;

        // Update Books Sold stat
        const booksSoldElement = document.querySelector('.stat-card:nth-child(1) .stat-value');
        if (booksSoldElement) {
            booksSoldElement.textContent = this.salesData.total_books_sold || 0;
        }

        // Update Total Earnings stat
        const earningsElement = document.querySelector('.stat-card:nth-child(2) .stat-value');
        if (earningsElement) {
            const earnings = this.salesData.total_earnings || 0;
            earningsElement.textContent = `৳${parseFloat(earnings).toFixed(2)}`;
        }

        // Update Commission Paid stat
        const commissionElement = document.querySelector('.stat-card:nth-child(3) .stat-value');
        if (commissionElement) {
            const commission = this.salesData.total_commission || 0;
            commissionElement.textContent = `৳${parseFloat(commission).toFixed(2)}`;
        }

        console.log("✅ Stats updated");
    }

    renderSalesTable() {
        const container = document.getElementById('sales-table-container');
        if (!container) return;

        if (!this.salesData || !this.salesData.sales || this.salesData.sales.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-chart-bar"></i>
                    <h3>No Sales Data Available</h3>
                    <p>You haven't sold any books yet. Once you make sales, your data will appear here.</p>
                </div>
            `;
            return;
        }

        // Apply filters
        const filteredSales = this.applyFilters(this.salesData.sales);
        
        // Sort data
        const sortedSales = this.sortSalesData(filteredSales);

        let tableHTML = `
            <table class="sales-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Book Title</th>
                        <th>Order ID</th>
                        <th>Buyer</th>
                        <th>Price</th>
                        <th>Commission</th>
                        <th>Earnings</th>
                    </tr>
                </thead>
                <tbody>
        `;

        let totalPrice = 0;
        let totalCommission = 0;
        let totalEarnings = 0;

        sortedSales.forEach(sale => {
            const saleDate = new Date(sale.order_date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            const price = parseFloat(sale.price_per_item || sale.total_amount);
            const commission = price * 0.05; // 5% commission
            const earnings = price - commission;

            totalPrice += price;
            totalCommission += commission;
            totalEarnings += earnings;

            tableHTML += `
                <tr>
                    <td>${saleDate}</td>
                    <td>${this.escapeHtml(sale.book_title || 'N/A')}</td>
                    <td>${this.escapeHtml(sale.order_number || sale.id)}</td>
                    <td>${this.escapeHtml(sale.buyer_name || 'Customer')}</td>
                    <td>৳${price.toFixed(2)}</td>
                    <td>৳${commission.toFixed(2)}</td>
                    <td>৳${earnings.toFixed(2)}</td>
                </tr>
            `;
        });

        tableHTML += `
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 600;">Total:</td>
                        <td style="font-weight: 600;">৳${totalPrice.toFixed(2)}</td>
                        <td style="font-weight: 600;">৳${totalCommission.toFixed(2)}</td>
                        <td style="font-weight: 600;">৳${totalEarnings.toFixed(2)}</td>
                    </tr>
                </tfoot>
            </table>
        `;

        container.innerHTML = tableHTML;
        console.log("✅ Sales table rendered:", sortedSales.length, "sales");
    }

    applyFilters(sales) {
        if (!sales) return [];

        let filtered = [...sales];

        // Apply date range filter
        if (this.currentFilters.dateRange !== 'all-time') {
            const now = new Date();
            let startDate = new Date();

            switch (this.currentFilters.dateRange) {
                case 'this-month':
                    startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    break;
                case 'last-month':
                    startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    break;
                case 'last-3-months':
                    startDate = new Date(now.getFullYear(), now.getMonth() - 3, 1);
                    break;
                case 'last-6-months':
                    startDate = new Date(now.getFullYear(), now.getMonth() - 6, 1);
                    break;
                case 'this-year':
                    startDate = new Date(now.getFullYear(), 0, 1);
                    break;
                case 'custom':
                    if (this.currentFilters.customDateFrom) {
                        startDate = new Date(this.currentFilters.customDateFrom);
                    }
                    break;
            }

            filtered = filtered.filter(sale => {
                const saleDate = new Date(sale.order_date);
                return saleDate >= startDate;
            });
        }

        // Apply book filter
        if (this.currentFilters.bookFilter !== 'all') {
            filtered = filtered.filter(sale => 
                sale.book_id == this.currentFilters.bookFilter
            );
        }

        return filtered;
    }

    sortSalesData(sales) {
        if (!sales) return [];

        const sorted = [...sales];

        switch (this.currentFilters.sortBy) {
            case 'date-desc':
                sorted.sort((a, b) => new Date(b.order_date) - new Date(a.order_date));
                break;
            case 'date-asc':
                sorted.sort((a, b) => new Date(a.order_date) - new Date(b.order_date));
                break;
            case 'price-high':
                sorted.sort((a, b) => parseFloat(b.price_per_item || b.total_amount) - parseFloat(a.price_per_item || a.total_amount));
                break;
            case 'price-low':
                sorted.sort((a, b) => parseFloat(a.price_per_item || a.total_amount) - parseFloat(b.price_per_item || b.total_amount));
                break;
        }

        return sorted;
    }

    setupEventListeners() {
        // Date range filter
        const dateRangeSelect = document.getElementById('date-range');
        if (dateRangeSelect) {
            dateRangeSelect.addEventListener('change', (e) => {
                this.currentFilters.dateRange = e.target.value;
                this.handleCustomDateRange();
                this.renderSalesTable();
            });
        }

        // Book filter
        const bookFilter = document.getElementById('book-filter');
        if (bookFilter) {
            bookFilter.addEventListener('change', (e) => {
                this.currentFilters.bookFilter = e.target.value;
                this.renderSalesTable();
            });
        }

        // Sort by
        const sortBy = document.getElementById('sort-by');
        if (sortBy) {
            sortBy.addEventListener('change', (e) => {
                this.currentFilters.sortBy = e.target.value;
                this.renderSalesTable();
            });
        }

        // Custom date inputs
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');
        if (dateFrom) {
            dateFrom.addEventListener('change', (e) => {
                this.currentFilters.customDateFrom = e.target.value;
                this.renderSalesTable();
            });
        }
        if (dateTo) {
            dateTo.addEventListener('change', (e) => {
                this.currentFilters.customDateTo = e.target.value;
                this.renderSalesTable();
            });
        }

        // Filter buttons
        const resetFiltersBtn = document.getElementById('reset-filters');
        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', () => {
                this.resetFilters();
            });
        }

        const applyFiltersBtn = document.getElementById('apply-filters');
        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', () => {
                this.renderSalesTable();
            });
        }

        // Export report button
        const exportReportBtn = document.getElementById('export-report');
        if (exportReportBtn) {
            exportReportBtn.addEventListener('click', () => {
                this.exportReport();
            });
        }
    }

    handleCustomDateRange() {
        const customDateRange = document.getElementById('custom-date-range');
        if (customDateRange) {
            if (this.currentFilters.dateRange === 'custom') {
                customDateRange.style.display = 'flex';
            } else {
                customDateRange.style.display = 'none';
            }
        }
    }

    resetFilters() {
        this.currentFilters = {
            dateRange: 'all-time',
            bookFilter: 'all',
            sortBy: 'date-desc',
            customDateFrom: '',
            customDateTo: ''
        };

        // Reset form elements
        const dateRangeSelect = document.getElementById('date-range');
        if (dateRangeSelect) dateRangeSelect.selectedIndex = 0;

        const bookFilter = document.getElementById('book-filter');
        if (bookFilter) bookFilter.selectedIndex = 0;

        const sortBy = document.getElementById('sort-by');
        if (sortBy) sortBy.selectedIndex = 0;

        const dateFrom = document.getElementById('date-from');
        if (dateFrom) dateFrom.value = '';

        const dateTo = document.getElementById('date-to');
        if (dateTo) dateTo.value = '';

        const customDateRange = document.getElementById('custom-date-range');
        if (customDateRange) customDateRange.style.display = 'none';

        this.renderSalesTable();
        this.showNotification("Filters reset successfully", "success");
    }

    async exportReport() {
        try {
            const form = new FormData();
            form.append("action", "export_sales_report");
            form.append("filters", JSON.stringify(this.currentFilters));

            const response = await fetch(
                "../../backend/api/sales_reports.php",
                {
                    method: "POST",
                    body: form,
                }
            );

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `sales_report_${new Date().toISOString().split('T')[0]}.csv`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                this.showNotification("Report exported successfully", "success");
            } else {
                throw new Error("Export failed");
            }
        } catch (error) {
            console.error("❌ Export error:", error);
            this.showNotification("Failed to export report. Please try again.", "error");
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
                notification.remove();
            }, 3000);
        }
    }

    escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
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
            console.log("✅ Auth system ready, initializing Sales Reports Manager...");
            new SalesReportsManager();
        } else {
            console.log("⏳ Auth system not ready yet, waiting...");
            setTimeout(waitForAuthSystem, 100);
        }
    }

    // Start waiting for auth system
    setTimeout(waitForAuthSystem, 100);
});
