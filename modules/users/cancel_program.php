<?php
session_start();
include '../../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_cancel']) && $_POST['confirm_cancel'] === 'true') {
        $participantId = intval($_POST['participant_id']);
        $userId = $_SESSION['user_id'];

        $query = "DELETE FROM participants WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $participantId, $userId);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Program successfully canceled.";
        } else {
            $_SESSION['error'] = "Failed to cancel the program.";
        }

        header('Location: profile.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Program</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }

        .modal-header {
            border-bottom: 2px solid rgba(220, 53, 69, 0.1);
            padding: 1.5rem;
            background: linear-gradient(to right, rgba(220, 53, 69, 0.05), transparent);
            border-radius: 20px 20px 0 0;
        }

        .modal-title {
            color: #dc3545;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-body {
            padding: 2rem 1.5rem;
        }

        .warning-icon {
            font-size: 3rem;
            color: #dc3545;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
            padding: 0.8rem 2rem;
            font-weight: 500;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
            background: linear-gradient(45deg, #c82333, #dc3545);
        }

        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #5a6268);
            border: none;
            padding: 0.8rem 2rem;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            background: linear-gradient(45deg, #5a6268, #6c757d);
        }

        .modal-footer {
            border-top: 2px solid rgba(108, 117, 125, 0.1);
            padding: 1.5rem;
            background: linear-gradient(to right, rgba(108, 117, 125, 0.05), transparent);
            border-radius: 0 0 20px 20px;
        }

        .confirmation-text {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <!-- Modal -->
    <div class="modal fade show" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" style="display: block;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cancel Program Confirmation
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-circle warning-icon"></i>
                    <h4 class="mb-3">Are you sure you want to cancel this program?</h4>
                    <p class="text-muted mb-0">This action cannot be undone. All program data will be permanently removed.</p>
                    
                    <form action="" method="POST" class="mt-4">
                        <input type="hidden" name="participant_id" value="<?php echo htmlspecialchars($_POST['participant_id'] ?? ''); ?>">
                        <input type="hidden" name="confirm_cancel" value="true">
                        
                        <div class="d-flex justify-content-center gap-3">
                            <a href="profile.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>No, Keep Program
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-2"></i>Yes, Cancel Program
                            </button>
                        </div>
                        
                        <div class="confirmation-text">
                            <i class="fas fa-info-circle me-1"></i>
                            By clicking "Yes, Cancel Program", you acknowledge that this action is irreversible.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal backdrop -->
    <div class="modal-backdrop fade show"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>