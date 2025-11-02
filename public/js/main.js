// Main JavaScript file for Zrozum School Management System

// Utility function for making API calls
async function apiCall(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API call failed:', error);
        return { error: error.message };
    }
}

// Show notification message
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.animation = 'slideIn 0.3s ease-out';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Confirm action
function confirmAction(message) {
    return confirm(message);
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
}

// Format datetime
function formatDateTime(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}.${month}.${year} ${hours}:${minutes}`;
}

// Toggle task status
async function toggleTaskStatus(taskId) {
    // This would call an API to update task status
    showNotification('Funkcja w przygotowaniu', 'info');
}

// Add event
function addEvent(schoolId) {
    showNotification('Funkcja w przygotowaniu', 'info');
}

// Edit event
function editEvent(eventId) {
    showNotification('Funkcja w przygotowaniu', 'info');
}

// Delete event
async function deleteEvent(eventId) {
    if (!confirmAction('Czy na pewno chcesz usunąć to wydarzenie?')) {
        return;
    }
    showNotification('Funkcja w przygotowaniu', 'info');
}

// Add contract
function addContract(schoolId) {
    showNotification('Funkcja w przygotowaniu', 'info');
}

// Edit contract
function editContract(contractId) {
    showNotification('Funkcja w przygotowaniu', 'info');
}

// Add task
function addTask(schoolId) {
    showNotification('Funkcja w przygotowaniu', 'info');
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Auto-save functionality
let autoSaveTimeout = null;
function autoSave(callback, delay = 1000) {
    if (autoSaveTimeout) {
        clearTimeout(autoSaveTimeout);
    }
    autoSaveTimeout = setTimeout(callback, delay);
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Add any initialization code here
    console.log('Zrozum School Management System initialized');
});
