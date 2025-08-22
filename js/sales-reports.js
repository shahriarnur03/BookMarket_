/**
 * Admin Sales Reports JavaScript
 * Comprehensive sales analytics and reporting functionality
 */

// Sales Reports JavaScript
// Note: This file is initialized by the authentication check in the HTML

function initSalesReports() {
    console.log("initSalesReports called");
    setupEventListeners();
    setDefaultDates();
    loadInitialData();

    // Test modal functionality
    setTimeout(() => {
        const modal = document.getElementById("report-modal");
        console.log("Modal element found:", modal);
        if (modal) {
            console.log("Modal display style:", modal.style.display);
            console.log(
                "Modal computed style:",
                window.getComputedStyle(modal).display
            );
        }
    }, 1000);
}

function setupEventListeners() {
    console.log("setupEventListeners called");

    // Date range change handler
    const dateRangeSelect = document.getElementById("date-range");
    if (dateRangeSelect) {
        dateRangeSelect.addEventListener("change", handleDateRangeChange);
        console.log("Date range change listener added");
    }

    // Generate report button
    const generateBtn = document.getElementById("generate-report");
    if (generateBtn) {
        generateBtn.addEventListener("click", generateReport);
        console.log("Generate report listener added");
    }

    // Reset filters button
    const resetBtn = document.getElementById("reset-filters");
    if (resetBtn) {
        resetBtn.addEventListener("click", resetFilters);
        console.log("Reset filters listener added");
    }

    // Export report button
    const exportBtn = document.getElementById("export-report");
    console.log("Export button element:", exportBtn);
    if (exportBtn) {
        console.log("Adding click event listener to export button");
        exportBtn.addEventListener("click", function () {
            console.log("Export button clicked!");
            showExportModal();
        });
        console.log("Export button listener added");
    } else {
        console.error("Export button not found!");
    }

    // Modal close button
    const closeBtn = document.querySelector(".close");
    if (closeBtn) {
        closeBtn.addEventListener("click", hideExportModal);
        console.log("Close button listener added");
    }

    // Cancel button
    const cancelBtn = document.getElementById("cancel-report");
    if (cancelBtn) {
        cancelBtn.addEventListener("click", hideExportModal);
        console.log("Cancel button listener added");
    }

    // Report form submission
    const reportForm = document.getElementById("report-form");
    if (reportForm) {
        reportForm.addEventListener("submit", handleReportSubmission);
        console.log("Report form listener added");
    }

    // Close modal when clicking outside
    window.addEventListener("click", function (event) {
        const modal = document.getElementById("report-modal");
        if (event.target === modal) {
            hideExportModal();
        }
    });

    console.log("All event listeners setup completed");
}

function setDefaultDates() {
    const today = new Date();
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    const startDateInput = document.getElementById("start-date");
    const endDateInput = document.getElementById("end-date");

    if (startDateInput && endDateInput) {
        startDateInput.value = startOfMonth.toISOString().split("T")[0];
        endDateInput.value = endOfMonth.toISOString().split("T")[0];
    }
}

function handleDateRangeChange() {
    const dateRange = document.getElementById("date-range").value;
    const startDateInput = document.getElementById("start-date");
    const endDateInput = document.getElementById("end-date");

    if (!startDateInput || !endDateInput) return;

    const today = new Date();
    let startDate, endDate;

    switch (dateRange) {
        case "month":
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case "last-month":
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        case "quarter":
            const quarter = Math.floor(today.getMonth() / 3);
            startDate = new Date(today.getFullYear(), quarter * 3, 1);
            endDate = new Date(today.getFullYear(), (quarter + 1) * 3, 0);
            break;
        case "year":
            startDate = new Date(today.getFullYear(), 0, 1);
            endDate = new Date(today.getFullYear(), 11, 31);
            break;
        case "last-year":
            startDate = new Date(today.getFullYear() - 1, 0, 1);
            endDate = new Date(today.getFullYear() - 1, 11, 31);
            break;
        case "custom":
            // Keep current custom dates
            return;
    }

    startDateInput.value = startDate.toISOString().split("T")[0];
    endDateInput.value = endDate.toISOString().split("T")[0];

    // Auto-generate report when date range changes
    generateReport();
}

function loadInitialData() {
    generateReport();
}

function generateReport() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!startDate || !endDate) {
        showNotification("Please select both start and end dates", "error");
        return;
    }

    showLoading(true);

    // Load KPI data for the summary table
    loadKPIData(startDate, endDate)
        .then(() => {
            showLoading(false);
            showNotification("Report generated successfully", "success");
        })
        .catch((error) => {
            showLoading(false);
            showNotification(
                "Error generating report: " + error.message,
                "error"
            );
            console.error("Error generating report:", error);
        });
}

function loadKPIData(startDate, endDate) {
    return fetch(
        `../../backend/api/sales_reports.php?action=kpi&start_date=${startDate}&end_date=${endDate}`
    )
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                updateSummaryTable(data.data, startDate, endDate);
            } else {
                throw new Error(data.message || "Failed to load KPI data");
            }
        })
        .catch((error) => {
            console.error("Error loading KPI data:", error);
            // Show sample data if API fails
            showSampleData(startDate, endDate);
        });
}

function showSampleData(startDate, endDate) {
    // Show sample data for demonstration purposes
    const sampleData = {
        total_orders: 25,
        total_books_sold: 45,
        total_sales: 12500,
        avg_order_value: 500,
    };

    updateSummaryTable(sampleData, startDate, endDate);
    showNotification(
        "Showing sample data. Please log in as admin to see real data.",
        "info"
    );
}

function updateSummaryTable(data, startDate, endDate) {
    const tableBody = document.getElementById("summary-table-body");
    if (!tableBody) return;

    // Format dates for display
    const startDateFormatted = formatDate(startDate);
    const endDateFormatted = formatDate(endDate);
    const periodText = getPeriodText(startDate, endDate);

    // Calculate additional metrics
    const totalRevenue = data.total_sales || 0;
    const totalOrders = data.total_orders || 0;
    const totalBooks = data.total_books_sold || 0;
    const avgOrderValue = data.avg_order_value || 0;

    // Calculate profit margin (assuming 30% profit margin for example)
    const estimatedProfit = totalRevenue * 0.3;
    const profitMargin =
        totalRevenue > 0 ? (estimatedProfit / totalRevenue) * 100 : 0;

    // Calculate books per order
    const booksPerOrder =
        totalOrders > 0 ? (totalBooks / totalOrders).toFixed(1) : 0;

    // Calculate daily averages
    const daysDiff =
        Math.ceil(
            (new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24)
        ) + 1;
    const dailyRevenue =
        daysDiff > 0 ? (totalRevenue / daysDiff).toFixed(0) : 0;
    const dailyOrders = daysDiff > 0 ? (totalOrders / daysDiff).toFixed(1) : 0;

    tableBody.innerHTML = `
        <tr>
            <td class="metric-label">Total Revenue</td>
            <td class="metric-value">${formatCurrency(totalRevenue)}</td>
            <td>Sum of all order values</td>
            <td class="metric-description">Total sales amount for ${periodText}</td>
        </tr>
        <tr>
            <td class="metric-label">Total Orders</td>
            <td class="metric-value">${totalOrders}</td>
            <td>Count of completed orders</td>
            <td class="metric-description">Number of successful transactions</td>
        </tr>
        <tr>
            <td class="metric-label">Total Books Sold</td>
            <td class="metric-value">${totalBooks}</td>
            <td>Sum of all book quantities</td>
            <td class="metric-description">Total units sold across all orders</td>
        </tr>
        <tr>
            <td class="metric-label">Average Order Value</td>
            <td class="metric-value">${formatCurrency(avgOrderValue)}</td>
            <td>Total Revenue ÷ Total Orders</td>
            <td class="metric-description">Average amount spent per order</td>
        </tr>
        <tr>
            <td class="metric-label">Books Per Order</td>
            <td class="metric-value">${booksPerOrder}</td>
            <td>Total Books ÷ Total Orders</td>
            <td class="metric-description">Average number of books per transaction</td>
        </tr>
        <tr>
            <td class="metric-label">Daily Average Revenue</td>
            <td class="metric-value">${formatCurrency(dailyRevenue)}</td>
            <td>Total Revenue ÷ ${daysDiff} days</td>
            <td class="metric-description">Average daily sales performance</td>
        </tr>
        <tr>
            <td class="metric-label">Daily Average Orders</td>
            <td class="metric-value">${dailyOrders}</td>
            <td>Total Orders ÷ ${daysDiff} days</td>
            <td class="metric-description">Average daily order volume</td>
        </tr>
        <tr>
            <td class="metric-label">Estimated Profit</td>
            <td class="metric-value">${formatCurrency(estimatedProfit)}</td>
            <td>Total Revenue × 30%</td>
            <td class="metric-description">Estimated profit based on 30% margin</td>
        </tr>
        <tr>
            <td class="metric-label">Profit Margin</td>
            <td class="metric-value">${profitMargin.toFixed(1)}%</td>
            <td>(Estimated Profit ÷ Total Revenue) × 100</td>
            <td class="metric-description">Profit as percentage of revenue</td>
        </tr>
        <tr>
            <td class="metric-label">Report Period</td>
            <td class="metric-value">${periodText}</td>
            <td>${startDateFormatted} to ${endDateFormatted}</td>
            <td class="metric-description">Date range for this report</td>
        </tr>
    `;
}

function getPeriodText(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const today = new Date();

    // Check if it's current month
    if (
        start.getMonth() === today.getMonth() &&
        start.getFullYear() === today.getFullYear()
    ) {
        return "This Month";
    }

    // Check if it's last month
    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
    if (
        start.getMonth() === lastMonth.getMonth() &&
        start.getFullYear() === lastMonth.getFullYear()
    ) {
        return "Last Month";
    }

    // Check if it's current year
    if (start.getFullYear() === today.getFullYear()) {
        return "This Year";
    }

    // Check if it's last year
    if (start.getFullYear() === today.getFullYear() - 1) {
        return "Last Year";
    }

    // Check if it's current quarter
    const currentQuarter = Math.floor(today.getMonth() / 3);
    const reportQuarter = Math.floor(start.getMonth() / 3);
    if (
        start.getFullYear() === today.getFullYear() &&
        reportQuarter === currentQuarter
    ) {
        return "This Quarter";
    }

    return "Custom Period";
}

function resetFilters() {
    const dateRangeSelect = document.getElementById("date-range");
    if (dateRangeSelect) {
        dateRangeSelect.value = "month";
    }

    setDefaultDates();
    generateReport();
}

function showExportModal() {
    console.log("=== showExportModal called ===");
    const modal = document.getElementById("report-modal");
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    console.log("Modal element:", modal);
    console.log("Start date:", startDate);
    console.log("End date:", endDate);

    if (!startDate || !endDate) {
        console.log("Dates missing, showing error notification");
        showNotification(
            "Please select both start and end dates before exporting",
            "error"
        );
        return;
    }

    if (!modal) {
        console.error("Modal element not found!");
        alert("Modal element not found! Check console for details.");
        return;
    }

    // Populate form fields
    const periodField = document.getElementById("report-period");
    const generatedByField = document.getElementById("generated-by");
    const generatedDateField = document.getElementById("generated-date");

    console.log("Form fields found:", {
        periodField: !!periodField,
        generatedByField: !!generatedByField,
        generatedDateField: !!generatedDateField,
    });

    if (periodField) periodField.value = `${startDate} to ${endDate}`;
    if (generatedByField) generatedByField.value = getUsername() || "Admin";
    if (generatedDateField)
        generatedDateField.value = new Date().toLocaleDateString();

    console.log("Setting modal display to block");
    modal.style.display = "block";
    console.log("Modal display style:", modal.style.display);

    // Force a reflow to ensure the modal is visible
    modal.offsetHeight;

    // Check if modal is actually visible
    const computedStyle = window.getComputedStyle(modal);
    console.log("Modal computed display:", computedStyle.display);
    console.log("Modal computed visibility:", computedStyle.visibility);
    console.log("Modal computed opacity:", computedStyle.opacity);

    if (modal.style.display === "block") {
        console.log("Modal should be visible now");
    } else {
        console.error("Modal display not set to block!");
    }
}

function hideExportModal() {
    const modal = document.getElementById("report-modal");
    modal.style.display = "none";
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
        startDate: document.getElementById("start-date").value,
        endDate: document.getElementById("end-date").value,
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

    // Use main sales reports API
    const url = `../../backend/api/sales_reports.php?action=export&format=pdf&start_date=${reportData.startDate}&end_date=${reportData.endDate}`;

    console.log("HTML export URL:", url);

    // Create a temporary link to trigger download
    const link = document.createElement("a");
    link.href = url;
    link.download = `sales_report_${reportData.startDate}_to_${reportData.endDate}.html`;

    console.log("Triggering HTML download...");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showNotification(
        "HTML report generated successfully! Open in browser and print to PDF.",
        "success"
    );
}

function exportReport(format) {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!startDate || !endDate) {
        showNotification(
            "Please select both start and end dates before exporting",
            "error"
        );
        return;
    }

    const url = `../../backend/api/sales_reports.php?action=export&format=${format}&start_date=${startDate}&end_date=${endDate}`;

    // Create a temporary link to trigger download
    const link = document.createElement("a");
    link.href = url;
    link.download = `sales_report_${startDate}_to_${endDate}.${getFileExtension(
        format
    )}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showNotification(
        `Report exported as ${format.toUpperCase()} successfully`,
        "success"
    );
}

function getFileExtension(format) {
    switch (format) {
        case "pdf":
            return "pdf";
        case "excel":
            return "xlsx";
        case "csv":
            return "csv";
        default:
            return "pdf";
    }
}

function showLoading(show) {
    const generateBtn = document.getElementById("generate-report");
    if (generateBtn) {
        if (show) {
            generateBtn.innerHTML =
                '<i class="fas fa-spinner fa-spin"></i> Generating...';
            generateBtn.disabled = true;
        } else {
            generateBtn.innerHTML =
                '<i class="fas fa-search"></i> Generate Report';
            generateBtn.disabled = false;
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

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("en-BD", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}
