// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Handle form submissions with confirmation
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
    
    // Dynamic subcategory loading based on category selection
    const categorySelect = document.getElementById('project_category_id');
    const subcategorySelect = document.getElementById('project_subcategory_id');
    
    if (categorySelect && subcategorySelect) {
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            
            // Clear existing options
            subcategorySelect.innerHTML = '<option value="">Loading...</option>';
            
            if (categoryId) {
                fetch(`/api/project-categories/${categoryId}/subcategories`)
                    .then(response => response.json())
                    .then(data => {
                        subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                        data.forEach(subcategory => {
                            const option = document.createElement('option');
                            option.value = subcategory.id;
                            option.textContent = subcategory.name;
                            subcategorySelect.appendChild(option);
                        });
                    });
            } else {
                subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            }
        });
    }
    
    // Dynamic client contact loading based on client selection
    const clientSelect = document.getElementById('client_id');
    const contactSelect = document.getElementById('client_contact_person_id');
    
    if (clientSelect && contactSelect) {
        clientSelect.addEventListener('change', function() {
            const clientId = this.value;
            
            // Clear existing options
            contactSelect.innerHTML = '<option value="">Loading...</option>';
            
            if (clientId) {
                fetch(`/api/clients/${clientId}/contacts`)
                    .then(response => response.json())
                    .then(data => {
                        contactSelect.innerHTML = '<option value="">Select Contact Person</option>';
                        data.forEach(contact => {
                            const option = document.createElement('option');
                            option.value = contact.id;
                            option.textContent = contact.name;
                            contactSelect.appendChild(option);
                        });
                    });
            } else {
                contactSelect.innerHTML = '<option value="">Select Contact Person</option>';
            }
        });
    }
});