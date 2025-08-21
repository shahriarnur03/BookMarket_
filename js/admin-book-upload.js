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
            this.uploadedFiles.cover = file;
            this.displayImagePreview(file, "cover-preview", true);
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
            console.log("Images uploaded successfully");

            // Then create the book
            console.log("Creating book...");
            const bookData = this.getFormData();
            bookData.cover_image_path = imageUploads.coverImagePath;
            bookData.additional_images = JSON.stringify(
                imageUploads.additionalImagePaths
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

        // Cover image is optional for admin uploads
        // if (!this.uploadedFiles.cover) {
        //     this.showAlert("Please upload a cover image.", "error");
        //     return false;
        // }

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
            // No files to upload, return success with empty paths
            return {
                success: true,
                coverImagePath: "",
                additionalImagePaths: [],
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
                const coverImagePath = files[0]?.file_path || "";
                const additionalImagePaths = files
                    .slice(1)
                    .map((f) => f.file_path);

                return {
                    success: true,
                    coverImagePath,
                    additionalImagePaths,
                };
            } else {
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
