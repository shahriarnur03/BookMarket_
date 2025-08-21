// Customer Dashboard JavaScript
// Handles fetching and displaying real-time dashboard data from the database

class CustomerDashboardManager {
    constructor() {
        this.dashboardData = null;
        this.init();
    }

    async init() {
        await this.loadDashboardData();
        this.setupEventListeners();
        this.updateWelcomeMessage();
    }

    async loadDashboardData() {
        try {
            const form = new FormData();
            form.append("action", "get_dashboard_data");

            const response = await fetch(
                "../../backend/api/customer_dashboard.php",
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
                this.dashboardData = data.data;
                console.log("✅ Dashboard data loaded successfully");
                this.updateDashboard();
            } else {
                console.error(
                    "❌ Failed to load dashboard data:",
                    data.message
                );
                this.showNotification("Failed to load dashboard data", "error");
            }
        } catch (error) {
            console.error("❌ Error loading dashboard data:", error);
            this.showNotification(
                "Failed to load dashboard data. Please try again.",
                "error"
            );
        }
    }

    updateDashboard() {
        if (!this.dashboardData) return;

        this.updateStats();
        this.renderCharts();
    }

    updateStats() {
        const stats = this.dashboardData.stats;
        if (!stats) return;

        // Update purchases stat
        const purchasesValue = document.getElementById("purchases-stat");
        if (purchasesValue) {
            purchasesValue.textContent = stats.purchases.total;
        }

        // Update earnings stat
        const earningsValue = document.getElementById("earnings-stat");
        if (earningsValue) {
            earningsValue.textContent = `৳${stats.earnings.total_earnings.toFixed(
                2
            )}`;
        }

        // Update books stat
        const booksValue = document.getElementById("books-stat");
        if (booksValue) {
            booksValue.textContent = stats.books.total;
        }

        console.log("✅ Stats updated:", stats);
    }

    renderCharts() {
        this.renderSalesTrendChart();
        this.renderBookStatusChart();
        this.renderMonthlyPerformanceChart();
        this.renderCategoryPerformanceChart();
    }

    renderSalesTrendChart() {
        const salesData = this.dashboardData.sales_overview;
        const ctx = document.getElementById("salesTrendChart");

        if (!ctx) return;

        if (!salesData || salesData.length === 0) {
            // Show placeholder when no data
            ctx.style.display = "none";
            const container = ctx.parentElement;
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-chart-line"></i>
                    <h3>No sales data yet</h3>
                    <p>Sales data will appear here once you start selling books.</p>
                </div>
            `;
            return;
        }

        // Prepare data for chart
        const labels = salesData.map((item) => {
            const date = new Date(item.month + "-01");
            return date.toLocaleDateString("en-US", {
                month: "short",
                year: "2-digit",
            });
        });

        const revenueData = salesData.map((item) =>
            parseFloat(item.total_revenue)
        );
        const ordersData = salesData.map((item) => parseInt(item.total_orders));

        new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Revenue (৳)",
                        data: revenueData,
                        borderColor: "#4A90E2",
                        backgroundColor: "rgba(74, 144, 226, 0.1)",
                        tension: 0.4,
                        yAxisID: "y",
                    },
                    {
                        label: "Orders",
                        data: ordersData,
                        borderColor: "#27AE60",
                        backgroundColor: "rgba(39, 174, 96, 0.1)",
                        tension: 0.4,
                        yAxisID: "y1",
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                    },
                    title: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        type: "linear",
                        display: true,
                        position: "left",
                        title: {
                            display: true,
                            text: "Revenue (৳)",
                        },
                    },
                    y1: {
                        type: "linear",
                        display: true,
                        position: "right",
                        title: {
                            display: true,
                            text: "Orders",
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                },
            },
        });
    }

    renderBookStatusChart() {
        const stats = this.dashboardData.stats;
        const ctx = document.getElementById("bookStatusChart");

        if (!ctx || !stats) return;

        const bookData = stats.books;
        const data = [
            { label: "Approved", value: bookData.approved, color: "#27AE60" },
            { label: "Pending", value: bookData.pending, color: "#F39C12" },
            { label: "Rejected", value: bookData.rejected, color: "#E74C3C" },
            { label: "Sold", value: bookData.sold, color: "#3498DB" },
        ].filter((item) => item.value > 0);

        if (data.length === 0) {
            ctx.style.display = "none";
            const container = ctx.parentElement;
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-chart-pie"></i>
                    <h3>No books yet</h3>
                    <p>Book status distribution will appear here once you list books.</p>
                </div>
            `;
            return;
        }

        new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: data.map((item) => item.label),
                datasets: [
                    {
                        data: data.map((item) => item.value),
                        backgroundColor: data.map((item) => item.color),
                        borderWidth: 2,
                        borderColor: "#fff",
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const total = data.reduce(
                                    (sum, item) => sum + item.value,
                                    0
                                );
                                const percentage = (
                                    (context.parsed / total) *
                                    100
                                ).toFixed(1);
                                return `${context.label}: ${context.parsed} (${percentage}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    renderMonthlyPerformanceChart() {
        const stats = this.dashboardData.stats;
        const ctx = document.getElementById("monthlyPerformanceChart");

        if (!ctx || !stats) return;

        // Create sample monthly data (in real implementation, this would come from API)
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun"];
        const earningsData = months.map(
            () => Math.floor(Math.random() * 1000) + 100
        );
        const booksData = months.map(() => Math.floor(Math.random() * 10) + 1);

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: months,
                datasets: [
                    {
                        label: "Earnings (৳)",
                        data: earningsData,
                        backgroundColor: "rgba(74, 144, 226, 0.8)",
                        borderColor: "#4A90E2",
                        borderWidth: 1,
                    },
                    {
                        label: "Books Listed",
                        data: booksData,
                        backgroundColor: "rgba(39, 174, 96, 0.8)",
                        borderColor: "#27AE60",
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });
    }

    renderCategoryPerformanceChart() {
        const books = this.dashboardData.recent_books;
        const ctx = document.getElementById("categoryPerformanceChart");

        if (!ctx) return;

        if (!books || books.length === 0) {
            ctx.style.display = "none";
            const container = ctx.parentElement;
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-chart-bar"></i>
                    <h3>No category data</h3>
                    <p>Category performance will appear here once you list books.</p>
                </div>
            `;
            return;
        }

        // Group books by category
        const categoryData = {};
        books.forEach((book) => {
            const category = book.category_name || "Unknown";
            if (!categoryData[category]) {
                categoryData[category] = { count: 0, totalValue: 0 };
            }
            categoryData[category].count++;
            categoryData[category].totalValue += parseFloat(book.price);
        });

        // Sort categories by total value (descending)
        const sortedCategories = Object.entries(categoryData)
            .sort(([, a], [, b]) => b.totalValue - a.totalValue)
            .slice(0, 6); // Show top 6 categories

        const labels = sortedCategories.map(([category]) => category);
        const countData = sortedCategories.map(([, data]) => data.count);
        const valueData = sortedCategories.map(([, data]) => data.totalValue);

        // Create gradient colors for better visual appeal
        const gradients = this.createGradients(
            ctx.getContext("2d"),
            labels.length
        );

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Total Value (৳)",
                        data: valueData,
                        backgroundColor: gradients,
                        borderColor: "#2C3E50",
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                indexAxis: "y", // Horizontal bar chart
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false, // Hide legend for cleaner look
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const category = context.label;
                                const value = context.parsed.x;
                                const count = countData[context.dataIndex];
                                return [
                                    `Category: ${category}`,
                                    `Total Value: ৳${value.toFixed(2)}`,
                                    `Books: ${count}`,
                                ];
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(0, 0, 0, 0.1)",
                        },
                        ticks: {
                            callback: function (value) {
                                return "৳" + value.toFixed(0);
                            },
                        },
                    },
                    y: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                weight: "600",
                            },
                        },
                    },
                },
                elements: {
                    bar: {
                        borderWidth: 0,
                    },
                },
            },
        });
    }

    // Helper function to create beautiful gradients for bars
    createGradients(ctx, count) {
        const gradients = [];
        const colors = [
            ["#667eea", "#764ba2"], // Blue to Purple
            ["#f093fb", "#f5576c"], // Pink to Red
            ["#4facfe", "#00f2fe"], // Blue to Cyan
            ["#43e97b", "#38f9d7"], // Green to Cyan
            ["#fa709a", "#fee140"], // Pink to Yellow
            ["#a8edea", "#fed6e3"], // Light Blue to Light Pink
        ];

        for (let i = 0; i < count; i++) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            const colorPair = colors[i % colors.length];
            gradient.addColorStop(0, colorPair[0]);
            gradient.addColorStop(1, colorPair[1]);
            gradients.push(gradient);
        }

        return gradients;
    }

    updateWelcomeMessage() {
        const user = JSON.parse(
            localStorage.getItem("bookmarket_user") || "{}"
        );
        const customerName = document.getElementById("customer-name");

        if (customerName && user.username) {
            customerName.textContent = user.username;
        }
    }

    setupEventListeners() {
        // Add any other event listeners here
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

    // Get dashboard data (for external use)
    getDashboardData() {
        return this.dashboardData;
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
            console.log(
                "✅ Auth system ready, initializing Dashboard Manager..."
            );
            new CustomerDashboardManager();
        } else {
            console.log("⏳ Auth system not ready yet, waiting...");
            setTimeout(waitForAuthSystem, 100);
        }
    }

    // Start waiting for auth system
    setTimeout(waitForAuthSystem, 100);
});
