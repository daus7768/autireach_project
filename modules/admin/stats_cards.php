<?php
function getDonationAnalytics($conn) {
    $query = "
        SELECT 
            DATE(created_at) as date,
            SUM(amount) as daily_amount,
            COUNT(*) as count
        FROM donations
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

function renderStatsCards($stats) {
    ?>
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-hand-holding-heart"></i>
            </div>
            <div class="stat-info">
                <h3>Total Donations</h3>
                <p>MYR <?= number_format($stats['total_amount'] ?? 0, 2) ?></p>
                <span class="trend up">↑ <?= $stats['trend'] ?>% vs last month</span>
            </div>
        </div>
        <div class="stat-card secondary">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>Total Donors</h3>
                <p><?= $stats['total_donors'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    <?php
}

// Fetch data
$conn = new mysqli('localhost', 'root', '', 'autireach'); // Adjust credentials
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$donationData = getDonationAnalytics($conn);

// Calculate stats
$totalAmount = array_sum(array_column($donationData, 'daily_amount'));
$totalDonors = array_sum(array_column($donationData, 'count'));
$stats = [
    'total_amount' => $totalAmount,
    'total_donors' => $totalDonors,
    'trend' => 12.5 // Example trend percentage
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <title>Donation Stats</title>
</head>
<body>
    <style>



.stats-section, .chart-section {
    padding: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.stat-card {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-card.primary {
    border-left: 5px solid #4f46e5;
}

.stat-card.secondary {
    border-left: 5px solid #22c55e;
}

.stat-icon {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(79, 70, 229, 0.2) 100%);
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .secondary .stat-icon {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(34, 197, 94, 0.2) 100%);
            color: var(--secondary-color);
        }


.stat-info h3 {
    margin: 0;
    font-size: 1.2rem;
    color: #333;
}

.stat-info p {
    margin: 5px 0;
    font-size: 1.5rem;
    color: #111;
}

.trend {
    font-size: 0.9rem;
    color: #22c55e;
}

.chart-section {
    margin: 20px;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

 </style>

<main>

    <script>
        const donationData = <?= json_encode($donationData); ?>;
        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('donationChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: donationData.map(item => item.date),
                    datasets: [{
                        label: 'Daily Donations (MYR)',
                        data: donationData.map(item => item.daily_amount),
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => 'MYR ' + value.toLocaleString()
                            }
                        }
                    }
                }
            });
        });
    </script>

    </main>
</body>

</html>
