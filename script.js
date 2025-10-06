// Custom JavaScript for Blog Application
document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-hide alerts after 5 seconds
    const autoHideAlerts = () => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert.classList.contains('show')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        });
    };

    // Confirm delete actions
    const confirmDeleteActions = () => {
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    };

    // Form validation enhancement
    const enhanceForms = () => {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                    
                    // Re-enable button after 5 seconds (safety net)
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }, 5000);
                }
            });
        });
    };

    // Character counter for forms
    const addCharacterCounters = () => {
        const titleInput = document.getElementById('title');
        const contentTextarea = document.getElementById('content');
        
        if (titleInput) {
            const titleCounter = document.createElement('div');
            titleCounter.className = 'form-text text-end';
            titleCounter.id = 'title-counter';
            titleInput.parentNode.appendChild(titleCounter);
            
            titleInput.addEventListener('input', function() {
                const count = this.value.length;
                const max = this.getAttribute('maxlength') || 255;
                titleCounter.textContent = `${count}/${max} characters`;
                titleCounter.style.color = count > max * 0.8 ? '#dc3545' : '#6c757d';
            });
            
            // Trigger initial count
            titleInput.dispatchEvent(new Event('input'));
        }
        
        if (contentTextarea) {
            const contentCounter = document.createElement('div');
            contentCounter.className = 'form-text text-end';
            contentCounter.id = 'content-counter';
            contentTextarea.parentNode.appendChild(contentCounter);
            
            contentTextarea.addEventListener('input', function() {
                const count = this.value.length;
                const min = this.getAttribute('minlength') || 10;
                contentCounter.textContent = `${count} characters (minimum ${min})`;
                contentCounter.style.color = count < min ? '#dc3545' : '#28a745';
            });
            
            // Trigger initial count
            contentTextarea.dispatchEvent(new Event('input'));
        }
    };

    // Initialize all functions
    const init = () => {
        autoHideAlerts();
        confirmDeleteActions();
        enhanceForms();
        addCharacterCounters();
        
        console.log('Blog application JavaScript initialized successfully!');
    };

    // Initialize when DOM is ready
    init();
});