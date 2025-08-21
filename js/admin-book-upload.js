/**
 * Admin Book Upload JavaScript
 * Handles book upload functionality for admin users
 *
 * @author BookMarket Team
 * @version 1.0
 */

class AdminBookUpload {
    constructor() {
        this.uploadedFiles = {
            cover: null,
            additional: [],
        };
        this.categories = [];
        this.init();
    }

    /**
     * Initialize the book upload functionality
     */
    init() {
        this.testConnection();
        this.loadCategories();
        this.setupEventListeners();
        this.setupDragAndDrop();
        this.updateSubmitButtonState(); // Set initial button state
    }

    /**
     * Test backend connection
     */
    async testConnection() {
        try {
            const formData = new FormData();
            formData.append("action", "test_connection");

            const response = await fetch(
                "../../backend/books/admin_book_upload_simple.php",
                {
                    method: "POST",
                    body: formData,
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            if (!responseText) {
                throw new Error("Empty response from server");
            }

            try {
                const result = JSON.parse(responseText);
                if (result.success) {
                    console.log("Backend connection successful");
                } else {
                    console.error("Backend connection failed:", result.message);
                }
                return result;
            } catch (parseError) {
                console.error("Response text:", responseText);
                throw new Error(`Invalid JSON response: ${parseError.message}`);
            }
        } catch (error) {
            console.error("Connection test failed:", error);
            return { success: false, message: error.message };
        }
    }

    /**
     * Load book categories from the backend
     */
    async loadCategories() {
        try {
            const response = await fetch(
                "../../backend/books/admin_book_upload_simple.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: "action=get_categories",
                }
            );

            const result = await response.json();

            if (result.success) {
                this.categories = result.data;
                this.populateCategories();
            } else {
                this.showAlert(
                    "Failed to load categories: " + result.message,
                    "error"
                );
            }
        } catch (error) {
            console.error("Error loading categories:", error);
            this.showAlert(
                "Failed to load categories. Please try again.",
                "error"
            );
        }
    }

    /**
     * Populate the categories dropdown
     */
    populateCategories() {
        const categorySelect = document.getElementById("category_id");
        if (!categorySelect) return;

        // Clear existing options except the first one
        categorySelect.innerHTML = '<option value="">Select Category</option>';

        this.categories.forEach((category) => {
            const option = document.createElement("option");
            option.value = category.id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });
    }

    /**
     * Setup event listeners for the form
     */
    setupEventListeners() {
        // Form submission
        const form = document.getElementById("book-upload-form");
        if (form) {
            form.addEventListener("submit", (e) => this.handleFormSubmit(e));

            // Real-time validation for cover image
            form.addEventListener("input", () => {
                this.updateSubmitButtonState();
            });
        }

        // Cancel button
        const cancelBtn = document.getElementById("cancel-upload");
        if (cancelBtn) {
            cancelBtn.addEventListener("click", () => this.handleCancel());
        }

        // Cover image upload
        const coverUploadSection = document.getElementById(
            "cover-upload-section"
        );
        if (coverUploadSection) {
            coverUploadSection.addEventListener("click", () => {
                document.getElementById("cover_image").click();
            });
        }

        // Additional images upload
        const additionalUploadSection = document.getElementById(
            "additional-upload-section"
        );
        if (additionalUploadSection) {
            additionalUploadSection.addEventListener("click", () => {
                document.getElementById("additional_images").click();
            });
        }

        // File input change events
        const coverInput = document.getElementById("cover_image");
        if (coverInput) {
            coverInput.addEventListener("change", (e) =>
                this.handleCoverImageChange(e)
            );
        }

        const additionalInput = document.getElementById("additional_images");
        if (additionalInput) {
            additionalInput.addEventListener("change", (e) =>
                this.handleAdditionalImagesChange(e)
            );
        }
    }

    /**
     * Setup drag and drop functionality
     */
    setupDragAndDrop() {
        const coverSection = document.getElementById("cover-upload-section");
        const additionalSection = document.getElementById(
            "additional-upload-section"
        );

        [coverSection, additionalSection].forEach((section) => {
            if (!section) return;

            section.addEventListener("dragover", (e) => {
                e.preventDefault();
                section.classList.add("dragover");
            });

            section.addEventListener("dragleave", (e) => {
                e.preventDefault();
                section.classList.remove("dragover");
            });

            section.addEventListener("drop", (e) => {
                e.preventDefault();
                section.classList.remove("dragover");

                const files = e.dataTransfer.files;
                if (section === coverSection) {
                    this.handleCoverImageDrop(files);
                } else {
                    this.handleAdditionalImagesDrop(files);
                }
            });
        });
    }

    /**
     * Handle cover image file selection
     */
    handleCoverImageChange(event) {
        const file = event.target.files[0];
        if (file) {
            if (this.validateImageFile(file)) {
                this.uploadedFiles.cover = file;
                this.displayImagePreview(file, "cover-preview", true);

                // Remove error styling and show success feedback
                const coverSection = document.getElementById(
                    "cover-upload-section"
                );
                if (coverSection) {
                    coverSection.classList.remove("required");
                    coverSection.style.borderColor = "#27ae60";
                    coverSection.style.backgroundColor =
                        "rgba(39, 174, 96, 0.05)";

                    // Show success message
                    this.showAlert(
                        "Cover image uploaded successfully!",
                        "success"
                    );

                    // Reset styling after 2 seconds
                    setTimeout(() => {
                        coverSection.style.borderColor = "";
                        coverSection.style.backgroundColor = "";
                    }, 2000);
                }

                // Update submit button state
                this.updateSubmitButtonState();
            }
        }
    }

    /**
     * Handle additional images file selection
     */
    handleAdditionalImagesChange(event) {
        const files = Array.from(event.target.files);
        files.forEach((file) => {
            this.uploadedFiles.additional.push(file);
            this.displayImagePreview(file, "additional-preview", false);
        });
    }

    /**
     * Handle cover image drop
     */
    handleCoverImageDrop(files) {
        if (files.length > 0) {
            const file = files[0];
            if (this.validateImageFile(file)) {
                this.uploadedFiles.cover = file;
                this.displayImagePreview(file, "cover-preview", true);

                // Update the file input
                const coverInput = document.getElementById("cover_image");
                if (coverInput) {
                    coverInput.files = files;
                }

                // Remove error styling and show success feedback
                const coverSection = document.getElementById(
                    "cover-upload-section"
                );
                if (coverSection) {
                    coverSection.classList.remove("required");
                    coverSection.style.borderColor = "#27ae60";
                    coverSection.style.backgroundColor =
                        "rgba(39, 174, 96, 0.05)";

                    // Show success message
                    this.showAlert(
                        "Cover image uploaded successfully!",
                        "success"
                    );

                    // Reset styling after 2 seconds
                    setTimeout(() => {
                        coverSection.style.borderColor = "";
                        coverSection.style.backgroundColor = "";
                    }, 2000);
                }

                // Update submit button state
                this.updateSubmitButtonState();
            }
        }
    }

    /**
     * Handle additional images drop
     */
    handleAdditionalImagesDrop(files) {
        const validFiles = Array.from(files).filter((file) =>
            this.validateImageFile(file)
        );
        validFiles.forEach((file) => {
            this.uploadedFiles.additional.push(file);
            this.displayImagePreview(file, "additional-preview", false);
        });

        // Update the file input
        const additionalInput = document.getElementById("additional_images");
        if (additionalInput) {
            // Create a new FileList-like object
            const dt = new DataTransfer();
            [...additionalInput.files, ...validFiles].forEach((file) =>
                dt.items.add(file)
            );
            additionalInput.files = dt.files;
        }
    }

    /**
     * Validate image file
     */
    validateImageFile(file) {
        const allowedTypes = [
            "image/jpeg",
            "image/jpg",
            "image/png",
            "image/gif",
            "image/webp",
        ];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(file.type)) {
            this.showAlert(
                "Invalid file type. Only images are allowed.",
                "error"
            );
            return false;
        }

        if (file.size > maxSize) {
            this.showAlert(
                "File size too large. Maximum size is 5MB.",
                "error"
            );
            return false;
        }

        return true;
    }

    /**
     * Display image preview
     */
    displayImagePreview(file, previewId, isCover) {
        const previewContainer = document.getElementById(previewId);
        if (!previewContainer) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const previewItem = document.createElement("div");
            previewItem.className = "image-preview-item";
            previewItem.dataset.filename = file.name;

            const img = document.createElement("img");
            img.src = e.target.result;
            img.alt = file.name;

            const removeBtn = document.createElement("button");
            removeBtn.className = "remove-image";
            removeBtn.innerHTML = "×";
            removeBtn.addEventListener("click", () =>
                this.removeImage(file, previewId, isCover)
            );

            previewItem.appendChild(img);
            previewItem.appendChild(removeBtn);
            previewContainer.appendChild(previewItem);
        };

        reader.readAsDataURL(file);
    }

    /**
     * Remove image from preview and uploaded files
     */
    removeImage(file, previewId, isCover) {
        const previewContainer = document.getElementById(previewId);
        if (!previewContainer) return;

        // Remove from preview
        const previewItem = previewContainer.querySelector(
            `[data-filename="${file.name}"]`
        );
        if (previewItem) {
            previewItem.remove();
        }

        // Remove from uploaded files
        if (isCover) {
            this.uploadedFiles.cover = null;
            const coverInput = document.getElementById("cover_image");
            if (coverInput) {
                coverInput.value = "";
            }

            // Update submit button state and show warning
            this.updateSubmitButtonState();
            this.showAlert(
                "Cover image removed. You must upload a new cover image.",
                "warning"
            );
        } else {
            const index = this.uploadedFiles.additional.findIndex(
                (f) => f.name === file.name
            );
            if (index > -1) {
                this.uploadedFiles.additional.splice(index, 1);
            }

            // Update the file input
            const additionalInput =
                document.getElementById("additional_images");
            if (additionalInput) {
                const dt = new DataTransfer();
                this.uploadedFiles.additional.forEach((f) => dt.items.add(f));
                additionalInput.files = dt.files;
            }
        }
    }

    /**
     * Handle form submission
     */
    async handleFormSubmit(event) {
        event.preventDefault();

        if (!this.validateForm()) {
            return;
        }

        const submitBtn = document.getElementById("submit-upload");
        submitBtn.disabled = true;
        submitBtn.innerHTML =
            '<i class="fas fa-spinner fa-spin"></i> Uploading...';

        try {
            // Test connection first
            console.log("Testing connection...");
            const connectionTest = await this.testConnection();
            if (!connectionTest.success) {
                throw new Error(
                    "Backend connection failed: " + connectionTest.message
                );
            }
            console.log("Connection test passed");

            // First upload images
            console.log("Uploading images...");
            console.log("Uploaded files:", this.uploadedFiles);
            const imageUploads = await this.uploadImages();
            console.log("Image upload result:", imageUploads);
            if (!imageUploads.success) {
                throw new Error(imageUploads.message);
            }

            // Check if cover image was actually uploaded successfully
            if (
                !imageUploads.coverImagePath ||
                imageUploads.coverImagePath.trim() === ""
            ) {
                throw new Error(
                    "Cover image upload failed. Please try uploading the image again."
                );
            }

            console.log("Images uploaded successfully");

            // Then create the book
            console.log("Creating book...");
            const bookData = this.getFormData();
            bookData.cover_image_path = imageUploads.coverImagePath;
            bookData.additional_images = JSON.stringify(
                imageUploads.additionalImagePaths
            );

            console.log("Final book data being sent:", bookData);
            console.log(
                "Cover image path being sent:",
                bookData.cover_image_path
            );

            const result = await this.createBook(bookData);
            console.log("Book creation result:", result);

            if (result.success) {
                this.showAlert("Book uploaded successfully!", "success");
                this.resetForm();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error("Upload error:", error);
            this.showAlert("Failed to upload book: " + error.message, "error");
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Book';
        }
    }

    /**
     * Update submit button state based on form validation
     */
    updateSubmitButtonState() {
        const submitBtn = document.getElementById("submit-upload");
        const coverImage = this.uploadedFiles.cover;
        const statusDiv = document.getElementById("cover-status");
        const statusText = document.getElementById("cover-status-text");

        if (submitBtn) {
            if (coverImage) {
                submitBtn.disabled = false;
                submitBtn.style.backgroundColor = "var(--success-color)";
                submitBtn.innerHTML =
                    '<i class="fas fa-upload"></i> Upload Book';

                // Update status indicator
                if (statusDiv && statusText) {
                    statusDiv.style.display = "block";
                    statusDiv.style.backgroundColor = "rgba(39, 174, 96, 0.1)";
                    statusDiv.style.border = "1px solid rgba(39, 174, 96, 0.3)";
                    statusText.style.color = "#27ae60";
                    statusText.innerHTML = `✅ Cover image uploaded: ${coverImage.name}`;
                }
            } else {
                submitBtn.disabled = true;
                submitBtn.style.backgroundColor = "#bdc3c7";
                submitBtn.innerHTML =
                    '<i class="fas fa-exclamation-triangle"></i> Upload Cover Image First';

                // Update status indicator
                if (statusDiv && statusText) {
                    statusDiv.style.display = "block";
                    statusDiv.style.backgroundColor = "rgba(231, 76, 60, 0.1)";
                    statusDiv.style.border = "1px solid rgba(231, 76, 60, 0.3)";
                    statusText.style.color = "#e74c3c";
                    statusText.innerHTML = "❌ No cover image uploaded";
                }
            }
        }
    }

    /**
     * Validate form data
     */
    validateForm() {
        const requiredFields = [
            "title",
            "author",
            "category_id",
            "price",
            "book_condition",
        ];

        for (const field of requiredFields) {
            const element = document.getElementById(field);
            if (!element || !element.value.trim()) {
                this.showAlert(
                    `Please fill in the ${field.replace("_", " ")} field.`,
                    "error"
                );
                element?.focus();
                return false;
            }
        }

        // Cover image is mandatory for admin uploads
        if (!this.uploadedFiles.cover) {
            this.showAlert("Please upload a cover image.", "error");
            // Highlight the cover image upload section
            const coverSection = document.getElementById(
                "cover-upload-section"
            );
            if (coverSection) {
                coverSection.classList.add("required");
                // Add shake animation
                coverSection.style.animation = "shake 0.5s ease-in-out";
                setTimeout(() => {
                    coverSection.style.animation = "";
                }, 500);
            }
            return false;
        } else {
            // Remove error styling if image is uploaded
            const coverSection = document.getElementById(
                "cover-upload-section"
            );
            if (coverSection) {
                coverSection.classList.remove("required");
            }
        }

        const price = parseFloat(document.getElementById("price").value);
        if (isNaN(price) || price <= 0) {
            this.showAlert("Please enter a valid price.", "error");
            return false;
        }

        return true;
    }

    /**
     * Upload images to the server
     */
    async uploadImages() {
        // Check if any files are selected
        if (
            !this.uploadedFiles.cover &&
            this.uploadedFiles.additional.length === 0
        ) {
            // No files to upload, return error
            return {
                success: false,
                message: "No files selected for upload",
            };
        }

        const formData = new FormData();
        formData.append("action", "upload_book_images");

        // Upload cover image
        if (this.uploadedFiles.cover) {
            formData.append("images[]", this.uploadedFiles.cover);
        }

        // Upload additional images
        this.uploadedFiles.additional.forEach((file) => {
            formData.append("images[]", file);
        });

        try {
            const response = await fetch(
                "../../backend/uploads/file_upload_simple.php",
                {
                    method: "POST",
                    body: formData,
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            if (!responseText) {
                throw new Error("Empty response from server");
            }

            const result = JSON.parse(responseText);

            if (result.success) {
                const files = result.files;
                console.log("Upload result files:", files);

                // Check if files array exists and has content
                if (!files || !Array.isArray(files) || files.length === 0) {
                    console.error("Upload succeeded but no files returned");
                    return {
                        success: false,
                        message:
                            "Upload succeeded but no files were returned. Please try again.",
                    };
                }

                const coverImagePath = files[0]?.file_path || "";
                const additionalImagePaths = files
                    .slice(1)
                    .map((f) => f.file_path);

                console.log("Cover image path:", coverImagePath);
                console.log("Additional image paths:", additionalImagePaths);

                // Validate that cover image path is not empty
                if (!coverImagePath || coverImagePath.trim() === "") {
                    console.error("Cover image path is empty");
                    return {
                        success: false,
                        message: "Cover image upload failed. Please try again.",
                    };
                }

                return {
                    success: true,
                    coverImagePath,
                    additionalImagePaths,
                };
            } else {
                console.error("Upload failed:", result);
                return result;
            }
        } catch (error) {
            console.error("Upload error:", error);
            return {
                success: false,
                message: "Upload failed: " + error.message,
            };
        }
    }

    /**
     * Create book in the database
     */
    async createBook(bookData) {
        const formData = new FormData();
        formData.append("action", "add_book");

        // Add all book data
        Object.keys(bookData).forEach((key) => {
            formData.append(key, bookData[key]);
        });

        try {
            const response = await fetch(
                "../../backend/books/admin_book_upload_simple.php",
                {
                    method: "POST",
                    body: formData,
                }
            );

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response has content
            const responseText = await response.text();
            if (!responseText) {
                throw new Error("Empty response from server");
            }

            // Try to parse JSON
            try {
                return JSON.parse(responseText);
            } catch (parseError) {
                console.error("Response text:", responseText);
                throw new Error(`Invalid JSON response: ${parseError.message}`);
            }
        } catch (error) {
            console.error("Fetch error:", error);
            throw new Error(`Network error: ${error.message}`);
        }
    }

    /**
     * Get form data
     */
    getFormData() {
        return {
            title: document.getElementById("title").value.trim(),
            author: document.getElementById("author").value.trim(),
            isbn: document.getElementById("isbn").value.trim(),
            description: document.getElementById("description").value.trim(),
            price: document.getElementById("price").value,
            book_condition: document.getElementById("book_condition").value,
            category_id: document.getElementById("category_id").value,
            stock_quantity: document.getElementById("stock_quantity").value,
        };
    }

    /**
     * Reset the form
     */
    resetForm() {
        document.getElementById("book-upload-form").reset();
        this.uploadedFiles = { cover: null, additional: [] };

        // Clear image previews
        document.getElementById("cover-preview").innerHTML = "";
        document.getElementById("additional-preview").innerHTML = "";

        // Clear file inputs
        document.getElementById("cover_image").value = "";
        document.getElementById("additional_images").value = "";
    }

    /**
     * Handle cancel button click
     */
    handleCancel() {
        if (
            confirm(
                "Are you sure you want to cancel? All entered data will be lost."
            )
        ) {
            this.resetForm();
        }
    }

    /**
     * Show alert message
     */
    showAlert(message, type) {
        const alertElement = document.getElementById(`alert-${type}`);
        if (alertElement) {
            alertElement.textContent = message;
            alertElement.style.display = "block";

            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertElement.style.display = "none";
            }, 5000);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    new AdminBookUpload();
});
