<?php
session_start();
require_once '../include/config.php';
// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
	header('Location: ../index.php');
	exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
	$firstname = trim($_POST['firstname']);
	$lastname = trim($_POST['lastname']);
	$email = trim($_POST['email']);
	$phone = trim($_POST['phone']);
	$department = trim($_POST['department']);

	$sql = "UPDATE user_table SET firstname = :firstname, lastname = :lastname, email = :email, phone = :phone, department = :department WHERE id = :id";
	$stmt = $conn->prepare($sql);
	if ($stmt->execute([
		':firstname' => $firstname,
		':lastname' => $lastname,
		':email' => $email,
		':phone' => $phone,
		':department' => $department,
		':id' => $user_id
	])) {
		$message = 'Profile updated successfully!';
	} else {
		$message = 'Error updating profile.';
	}
}

// Fetch user info
$stmt = $conn->prepare('SELECT firstname, lastname, username, email, phone, department, role FROM user_table WHERE id = :id');
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>User Profile</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body {
			min-height: 100vh;
			background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.profile-container {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			min-height: 80vh;
		}
		.profile-card {
			background: #fff;
			border-radius: 1.5rem;
			box-shadow: 0 8px 32px rgba(0,0,0,0.18);
			padding: 2.5rem 2rem;
			max-width: 480px;
			width: 100%;
			margin: 0 auto;
			animation: fadeInCard 1s;
		}
		@keyframes fadeInCard {
			from { opacity: 0; transform: translateY(40px) scale(0.95); }
			to { opacity: 1; transform: translateY(0) scale(1); }
		}
		h2 {
			font-family: 'Montserrat', Arial, sans-serif;
			font-weight: 700;
			color: #1e90ff;
			margin-bottom: 1.5rem;
			text-align: center;
		}
		.btn-primary {
			background: linear-gradient(90deg, #1e90ff 0%, #00c6ff 100%);
			border: none;
			transition: box-shadow 0.3s, transform 0.2s;
		}
		.btn-primary:hover, .btn-primary:focus {
			box-shadow: 0 4px 16px rgba(30,144,255,0.25);
			transform: translateY(-2px) scale(1.03);
		}
		.form-label {
			font-weight: 600;
			color: #005fa3;
		}
		.form-control:focus {
			border-color: #1e90ff;
			box-shadow: 0 0 0 0.2rem rgba(30,144,255,0.15);
		}
		.alert-info {
			text-align: center;
			font-size: 1rem;
			border-radius: 1rem;
		}
	</style>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
	<div class="profile-container">
		<div class="profile-card">
			<h2>User Profile</h2>
			<?php if ($message): ?>
				<div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
			<?php endif; ?>
			<form method="POST">
				<div class="mb-3">
					<label for="firstname" class="form-label">First Name</label>
					<input type="text" class="form-control" id="firstname" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required>
				</div>
				<div class="mb-3">
					<label for="lastname" class="form-label">Last Name</label>
					<input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>
				</div>
				<div class="mb-3">
					<label for="username" class="form-label">Username</label>
					<input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>
				</div>
				<div class="mb-3">
					<label for="email" class="form-label">Email</label>
					<input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
				</div>
				<div class="mb-3">
					<label for="phone" class="form-label">Phone</label>
					<input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
				</div>
				<div class="mb-3">
					<label for="department" class="form-label">Department</label>
					<input type="text" class="form-control" id="department" name="department" value="<?= htmlspecialchars($user['department']) ?>">
				</div>
				<div class="mb-3">
					<label for="role" class="form-label">Role</label>
					<input type="text" class="form-control" id="role" name="role" value="<?= htmlspecialchars($user['role']) ?>" readonly>
				</div>
				<button type="submit" name="update" class="btn btn-success w-100">Update Profile</button></br></br>

                <a href="prodashboard.php"><button type="button" class="btn btn-primary w-100">Back</button></a>
			</form>
		</div>
	</div>
</body>
</html>
