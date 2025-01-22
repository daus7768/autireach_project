<?php
function renderHeader($user) {
    ?>
    <div class="dashboard-header">
        <div class="header-content">
            <div class="welcome-section">
              
            <div class="quick-actions">
                <button class="action-btn" onclick="downloadReport('donations')">
                    <i class="fas fa-download"></i> Download Donation Report
                </button>
                <button class="action-btn" onclick="downloadReport('members')">
                    <i class="fas fa-file-export"></i> Export Member List
                </button>
            </div>
        </div>
    </div>
    <?php
}
?>