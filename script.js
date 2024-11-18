document.addEventListener('DOMContentLoaded', function() {
    fetchCageStatus();
    fetchFeedingData();
    fetchFinancialSummary();
});

async function fetchCageStatus() {
    try {
        // Fetch data from the API endpoint
        const response = await fetch('dashboard.php?format=json');
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json(); // Parse the JSON response

        // Locate the container element in the DOM
        const cageStatusDiv = document.querySelector('.cage-status');
        if (!cageStatusDiv) {
            throw new Error('Cage status container not found in the DOM.');
        }

        // Generate the HTML dynamically based on the data received
        cageStatusDiv.innerHTML = data.map(cage => `
            <div class="cage-card">
                <h3>
                    ${cage.cage_name}
                    <span class="status-badge status-${cage.status.toLowerCase()}">
                        ${cage.status}
                    </span>
                </h3>
                <div class="cage-stats">
                    <p>Capacity: ${cage.capacity} fish</p>
                    <p>Current Fish: ${cage.current_fish_count} fish</p>
                    <p>Utilization: ${cage.capacity > 0 ? ((cage.current_fish_count / cage.capacity) * 100).toFixed(1) : 0}%</p>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error fetching cage status:', error);

        // Optional: Display an error message in the container
        const cageStatusDiv = document.querySelector('.cage-status');
        if (cageStatusDiv) {
            cageStatusDiv.innerHTML = '<p class="error">Failed to load cage data. Please try again later.</p>';
        }
    }
}


async function fetchFeedingData() {
    try {
        const response = await fetch('api/feeding_data.php');
        const data = await response.json();
        
        const ctx = document.getElementById('feedingChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.cage_name),
                datasets: [{
                    label: 'Feed Amount (kg)',
                    data: data.map(item => item.feed_amount),
                    backgroundColor: '#1a237e'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error fetching feeding data:', error);
    }
}

async function fetchFinancialSummary() {
    try {
        const response = await fetch('api/financial_summary.php');
        const data = await response.json();
        
        const summaryDiv = document.querySelector('.financial-summary');
        summaryDiv.innerHTML = `
            <div class="summary-item">
                <h3>Total Income</h3>
                <p>₱${data.total_income.toLocaleString()}</p>
            </div>
            <div class="summary-item">
                <h3>Total Expenses</h3>
                <p>₱${data.total_expenses.toLocaleString()}</p>
            </div>
            <div class="summary-item">
                <h3>Net Profit</h3>
                <p>₱${data.net_profit.toLocaleString()}</p>
            </div>
        `;
    } catch (error) {
        console.error('Error fetching financial summary:', error);
    }
}