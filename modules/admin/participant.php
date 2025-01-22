<?php
session_start();
require_once '../../db/db.php';
include '../../includes/adminnav.php';


// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle Delete Operation
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM participants WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Participant successfully deleted";
    } else {
        $_SESSION['error'] = "Error deleting participant";
    }
    header('Location: participant.php');
    exit();
}

// Handle Add/Edit Operation
if (isset($_POST['save'])) {
    $user_id = $_POST['user_id'];
    $program_id = $_POST['program_id'];
    
    if (isset($_POST['id'])) {
        // Edit existing participant
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE participants SET user_id = ?, program_id = ? WHERE id = ?");
        $stmt->bind_param("iii", $user_id, $program_id, $id);
    } else {
        // Add new participant
        $stmt = $conn->prepare("INSERT INTO participants (user_id, program_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $program_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Participant successfully saved";
    } else {
        $_SESSION['error'] = "Error saving participant";
    }
    header('Location: participant.php');
    exit();
}

// Get all users for dropdown
$users_query = "SELECT id, username FROM users WHERE role != 'admin'";
$users_result = $conn->query($users_query);
$users = $users_result->fetch_all(MYSQLI_ASSOC);

// Get all programs for dropdown
$programs_query = "SELECT id, title FROM programs";
$programs_result = $conn->query($programs_query);
$programs = $programs_result->fetch_all(MYSQLI_ASSOC);

// Get participants with join
$query = "SELECT p.*, u.username, pr.title as program_title 
          FROM participants p 
          JOIN users u ON p.user_id = u.id 
          JOIN programs pr ON p.program_id = pr.id 
          ORDER BY p.joined_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Participants - AutiReach Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <div class="main-content">
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-semibold text-gray-800">Manage Participants</h1>
            <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i> Add Participant
            </button>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Participants Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['username']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($row['program_title']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500"><?php echo date('M d, Y H:i', strtotime($row['joined_at'])); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="editParticipant(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                    class="text-indigo-600 hover:text-indigo-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this participant?');">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Modal -->
        <div id="participantModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-md max-h-full">
                <div class="relative bg-white rounded-lg shadow">
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">
                            Add New Participant
                        </h3>
                        <button type="button" onclick="closeModal()" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form action="" method="POST">
                        <input type="hidden" name="id" id="editId">
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">User</label>
                                <select name="user_id" id="userId" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="">Select User</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">Program</label>
                                <select name="program_id" id="programId" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="">Select Program</option>
                                    <?php foreach ($programs as $program): ?>
                                        <option value="<?php echo $program['id']; ?>"><?php echo htmlspecialchars($program['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                            <button type="submit" name="save" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save</button>
                            <button type="button" onclick="closeModal()" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script>
        const modal = document.getElementById('participantModal');
        
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Add New Participant';
            document.getElementById('editId').value = '';
            document.getElementById('userId').value = '';
            document.getElementById('programId').value = '';
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        function editParticipant(participant) {
            document.getElementById('modalTitle').textContent = 'Edit Participant';
            document.getElementById('editId').value = participant.id;
            document.getElementById('userId').value = participant.user_id;
            document.getElementById('programId').value = participant.program_id;
            modal.classList.remove('hidden');
        }
    </script>
    </main>
</body>
</html>