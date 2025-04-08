document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Handle Start Service button clicks
    document.querySelectorAll('.btn-success').forEach(button => {
        button.addEventListener('click', function(e) {
            const row = e.target.closest('tr');
            const customer = row.querySelector('td:nth-child(2)').textContent;
            const service = row.querySelector('td:nth-child(3)').textContent;
            
            if(confirm(`Start service for ${customer} - ${service}?`)) {
                // Update status badge
                const statusBadge = row.querySelector('.badge');
                statusBadge.className = 'badge badge-success';
                statusBadge.textContent = 'In Progress';
                
                // Disable start button
                e.target.disabled = true;
                e.target.textContent = 'Started';
                
                // Here you would typically make an AJAX call to update the server
                // updateServiceStatus(serviceId, 'in_progress');
            }
        });
    });

    // Handle Details button clicks
    document.querySelectorAll('.btn-primary').forEach(button => {
        button.addEventListener('click', function(e) {
            const row = e.target.closest('tr');
            const customer = row.querySelector('td:nth-child(2)').textContent;
            const service = row.querySelector('td:nth-child(3)').textContent;
            
            // Here you would typically show a modal with details
            // For now, we'll just alert the info
            alert(`Service Details:\nCustomer: ${customer}\nService: ${service}`);
        });
    });

    // Refresh dashboard data periodically (every 5 minutes)
    function refreshDashboardData() {
        // Here you would typically make an AJAX call to get updated stats
        // For now, we'll just log to console
        console.log('Refreshing dashboard data...');
    }

    // Set up periodic refresh
    setInterval(refreshDashboardData, 300000); // 5 minutes

    // Add animation to service items
    document.querySelectorAll('.service-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });

        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});

// Function to update service status (to be implemented with actual API)
function updateServiceStatus(serviceId, status) {
    // Make API call to update service status
    console.log(`Updating service ${serviceId} to status: ${status}`);
}

// Function to show error messages
function showError(message) {
    // Create and show error toast/alert
    alert(message); // Replace with better UI feedback
} 