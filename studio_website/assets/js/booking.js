document.addEventListener('DOMContentLoaded', function() {
    // Date picker - disable past dates
    const dateInput = document.getElementById('date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }

    // Form validation
    const bookingForm = document.querySelector('.booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate name
            const name = document.getElementById('name');
            if (name.value.trim() === '') {
                isValid = false;
                name.style.borderColor = '#dc3545';
            } else {
                name.style.borderColor = '#ddd';
            }
            
            // Validate email
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                isValid = false;
                email.style.borderColor = '#dc3545';
            } else {
                email.style.borderColor = '#ddd';
            }
            
            // Validate phone
            const phone = document.getElementById('phone');
            const phoneRegex = /^[\d\s\-()+]{10,}$/;
            if (!phoneRegex.test(phone.value)) {
                isValid = false;
                phone.style.borderColor = '#dc3545';
            } else {
                phone.style.borderColor = '#ddd';
            }
            
            // Validate service
            const service = document.getElementById('service');
            if (service.value === '') {
                isValid = false;
                service.style.borderColor = '#dc3545';
            } else {
                service.style.borderColor = '#ddd';
            }
            
            // Validate date
            if (dateInput && dateInput.value === '') {
                isValid = false;
                dateInput.style.borderColor = '#dc3545';
            } else if (dateInput) {
                dateInput.style.borderColor = '#ddd';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill out all required fields correctly.');
            }
        });
    }

    // Gallery filter functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-item');

    if (filterButtons.length > 0 && galleryItems.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                const filterValue = this.getAttribute('data-filter');
                
                galleryItems.forEach(item => {
                    if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }
});