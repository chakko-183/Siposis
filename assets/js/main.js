/**
 * Main JavaScript file for OSIS Registration System
 */

document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const menuToggle = document.querySelector(".menu-toggle")
  const navMenu = document.querySelector(".nav-menu")

  if (menuToggle && navMenu) {
    menuToggle.addEventListener("click", function () {
      navMenu.classList.toggle("active")
      this.classList.toggle("active")
    })
  }

  // File upload handling
  const fileUploads = document.querySelectorAll(".file-upload")

  fileUploads.forEach((upload) => {
    const input = upload.querySelector('input[type="file"]')

    if (input) {
      // Handle click on upload area
      upload.addEventListener("click", (e) => {
        if (e.target !== input) {
          input.click()
        }
      })

      // Handle drag and drop
      upload.addEventListener("dragover", function (e) {
        e.preventDefault()
        this.classList.add("dragover")
      })

      upload.addEventListener("dragleave", function () {
        this.classList.remove("dragover")
      })

      upload.addEventListener("drop", function (e) {
        e.preventDefault()
        this.classList.remove("dragover")

        if (e.dataTransfer.files.length) {
          input.files = e.dataTransfer.files

          // Trigger change event
          const event = new Event("change", { bubbles: true })
          input.dispatchEvent(event)
        }
      })

      // Handle file selection
      input.addEventListener("change", function () {
        const parent = this.closest(".file-upload")
        const filePreview = parent.nextElementSibling

        if (this.files.length > 0) {
          const file = this.files[0]
          const fileSize = (file.size / 1024).toFixed(2) + " KB"

          // Create file preview if it doesn't exist
          if (!filePreview || !filePreview.classList.contains("file-preview")) {
            const preview = document.createElement("div")
            preview.className = "file-preview"

            let fileIcon = "fa-file"
            if (file.type.includes("image")) {
              fileIcon = "fa-file-image"
            } else if (file.type.includes("pdf")) {
              fileIcon = "fa-file-pdf"
            }

            preview.innerHTML = `
                            <i class="fas ${fileIcon}"></i>
                            <div class="file-info">
                                <div class="file-name">${file.name}</div>
                                <div class="file-size">${fileSize}</div>
                            </div>
                            <div class="file-actions">
                                <button type="button" class="btn btn-sm btn-outline remove-file">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `

            parent.parentNode.insertBefore(preview, parent.nextSibling)

            // Add event listener to remove button
            const removeBtn = preview.querySelector(".remove-file")
            removeBtn.addEventListener("click", (e) => {
              e.preventDefault()
              input.value = ""
              preview.remove()
              parent.querySelector("p").textContent = "Klik untuk mengunggah atau seret file ke sini"
            })
          } else {
            // Update existing preview
            const fileNameEl = filePreview.querySelector(".file-name")
            const fileSizeEl = filePreview.querySelector(".file-size")

            if (fileNameEl) fileNameEl.textContent = file.name
            if (fileSizeEl) fileSizeEl.textContent = fileSize
          }

          // Update upload area text
          parent.querySelector("p").textContent = "File selected"
        }
      })
    }
  })

  // Form validation
  const forms = document.querySelectorAll("form")

  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const requiredFields = form.querySelectorAll("[required]")
      let isValid = true

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false
          field.classList.add("is-invalid")

          // Create error message if it doesn't exist
          let errorMessage = field.nextElementSibling
          if (!errorMessage || !errorMessage.classList.contains("invalid-feedback")) {
            errorMessage = document.createElement("div")
            errorMessage.className = "invalid-feedback"
            errorMessage.textContent = "Field ini wajib diisi"
            field.parentNode.insertBefore(errorMessage, field.nextSibling)
          }
        } else {
          field.classList.remove("is-invalid")

          // Remove error message if it exists
          const errorMessage = field.nextElementSibling
          if (errorMessage && errorMessage.classList.contains("invalid-feedback")) {
            errorMessage.remove()
          }
        }
      })

      if (!isValid) {
        e.preventDefault()

        // Scroll to first invalid field
        const firstInvalid = form.querySelector(".is-invalid")
        if (firstInvalid) {
          firstInvalid.scrollIntoView({ behavior: "smooth", block: "center" })
          firstInvalid.focus()
        }
      }
    })
  })

  // Notification handling
  const notificationBtns = document.querySelectorAll(".btn-notification")

  notificationBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const notificationId = this.dataset.id

      // Mark notification as read via AJAX
      if (notificationId) {
        fetch("mark_notification_read.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `notification_id=${notificationId}`,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // Update UI
              this.closest(".notification-item").classList.remove("unread")
            }
          })
          .catch((error) => console.error("Error:", error))
      }
    })
  })

  // Dropdown handling
  const dropdownToggles = document.querySelectorAll(".dropdown-toggle")

  dropdownToggles.forEach((toggle) => {
    toggle.addEventListener("click", function (e) {
      e.preventDefault()
      const dropdown = this.nextElementSibling

      if (dropdown && dropdown.classList.contains("dropdown-menu")) {
        dropdown.classList.toggle("show")

        // Close other open dropdowns
        document.querySelectorAll(".dropdown-menu.show").forEach((menu) => {
          if (menu !== dropdown) {
            menu.classList.remove("show")
          }
        })

        // Close dropdown when clicking outside
        document.addEventListener("click", function closeDropdown(e) {
          if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove("show")
            document.removeEventListener("click", closeDropdown)
          }
        })
      }
    })
  })
})
