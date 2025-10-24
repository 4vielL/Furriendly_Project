// Filter events by status
function filterEvents(status) {
    const events = document.querySelectorAll('.event-card');
    const tabBtns = document.querySelectorAll('.tab-btn');
    
    // Update active tab
    tabBtns.forEach(btn => {
        if (btn.textContent.toLowerCase().includes(status)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Show/hide events based on status
    events.forEach(event => {
        if (status === 'all') {
            event.style.display = 'block';
            setTimeout(() => {
                event.style.opacity = '1';
                event.style.transform = 'translateY(0)';
            }, 50);
        } else if (event.dataset.status === status) {
            event.style.display = 'block';
            setTimeout(() => {
                event.style.opacity = '1';
                event.style.transform = 'translateY(0)';
            }, 50);
        } else {
            event.style.opacity = '0';
            event.style.transform = 'translateY(20px)';
            setTimeout(() => {
                event.style.display = 'none';
            }, 300);
        }
    });
}

// Mark event as completed
function markCompleted(eventId) {
    if (confirm('Are you sure you want to mark this event as completed? This action cannot be undone.')) {
        const btn = event.target;
        btn.innerHTML = 'Marking...';
        btn.disabled = true;
        
        fetch('complete_event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `event_id=${eventId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification('Event marked as completed successfully!', 'success');
                // Reload after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Error: ' + data.message, 'error');
                btn.innerHTML = 'Mark as Completed';
                btn.disabled = false;
            }
        })
        .catch(error => {
            showNotification('Error marking event as completed', 'error');
            btn.innerHTML = 'Mark as Completed';
            btn.disabled = false;
        });
    }
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    if (type === 'success') {
        notification.style.background = 'linear-gradient(135deg, #56ab2f, #a8e6cf)';
    } else {
        notification.style.background = 'linear-gradient(135deg, #ff6b6b, #ff8e8e)';
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Add CSS for notification animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);

// Initialize page - show all events by default
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth transitions to event cards
    const events = document.querySelectorAll('.event-card');
    events.forEach((event, index) => {
        event.style.transition = 'all 0.3s ease';
        event.style.opacity = '0';
        event.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            event.style.opacity = '1';
            event.style.transform = 'translateY(0)';
        }, index * 100);
    });
});