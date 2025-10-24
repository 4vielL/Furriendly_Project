// Date validation
document.getElementById('event_date').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const errorElement = document.getElementById('date-error');
    
    if (selectedDate < today) {
        errorElement.textContent = 'Please select a future date.';
        this.style.borderColor = '#dc3545';
    } else {
        errorElement.textContent = '';
        this.style.borderColor = '#ddd';
    }
});

// Services management
let services = [];

function addService() {
    const serviceInput = document.querySelector('.service-input');
    const service = serviceInput.value.trim();
    
    if (service && !services.includes(service)) {
        services.push(service);
        updateServicesList();
        serviceInput.value = '';
    }
}

function removeService(index) {
    services.splice(index, 1);
    updateServicesList();
}

function updateServicesList() {
    const servicesList = document.getElementById('services-list');
    servicesList.innerHTML = '';
    
    services.forEach((service, index) => {
        const serviceTag = document.createElement('div');
        serviceTag.className = 'service-tag';
        serviceTag.innerHTML = `
            ${service}
            <button type="button" class="remove-service" onclick="removeService(${index})">×</button>
        `;
        servicesList.appendChild(serviceTag);
    });
    
    // Add hidden input for services
    let existingInput = document.querySelector('input[name="services"]');
    if (existingInput) {
        existingInput.remove();
    }
    
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'services';
    hiddenInput.value = JSON.stringify(services);
    document.querySelector('.services-container').appendChild(hiddenInput);
}

// Toggle ID upload based on position
function toggleIdUpload() {
    const position = document.getElementById('position').value;
    const idUploadSection = document.getElementById('id-upload-section');
    
    if (position === 'Government Official' || position === 'Veterinarian') {
        idUploadSection.style.display = 'block';
        idUploadSection.querySelector('input').required = true;
    } else {
        idUploadSection.style.display = 'none';
        idUploadSection.querySelector('input').required = false;
    }
}

// Form submission validation
document.querySelector('.event-form').addEventListener('submit', function(e) {
    const eventDate = document.getElementById('event_date').value;
    const selectedDate = new Date(eventDate);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        e.preventDefault();
        alert('Please select a future date for your event.');
        return false;
    }
    
    if (services.length === 0) {
        e.preventDefault();
        alert('Please add at least one service.');
        return false;
    }
});