// Toggle user dropdown
document.addEventListener('DOMContentLoaded', function() {
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenu = document.getElementById('user-menu');
    
    if (userMenuButton && userMenu) {
        userMenuButton.addEventListener('click', function() {
            userMenu.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!userMenu.contains(event.target) && !userMenuButton.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });
    }
    
    // File upload validation
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Check file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File terlalu besar. Maksimal 2MB');
                    this.value = '';
                }
                
                // Check file type
                const validTypes = ['application/pdf'];
                if (!validTypes.includes(file.type)) {
                    alert('Hanya file PDF yang diizinkan');
                    this.value = '';
                }
            }
        });
    });
    
    // Date validation for bimbingan (min 2 days from now)
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (input.id === 'tanggal-bimbingan') {
            input.min = new Date(new Date().getTime() + 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            const requiredFields = this.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('border-red-500');
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Harap isi semua field yang wajib diisi');
            }
        });
    });
});

// Toggle forms
function toggleForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.classList.toggle('hidden');
    }
}

// Confirm actions
function confirmAction(message) {
    return confirm(message || 'Apakah Anda yakin?');
}
