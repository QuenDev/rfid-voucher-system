<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "config.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $stmt = null;

    try {
      $stmt = $conn->prepare("SELECT id, password, role FROM adminstafflogs WHERE username = ?");

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION["admin_id"] = $id;
                $_SESSION["role"] = $role;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid credentials. Please check your username and password.";
            }
        } else {
            $error = "Admin account not found with that username.";
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    } finally {
        if ($stmt) {
            $stmt->close();
        }
        $conn->close();
    }
}
?>

	<!DOCTYPE html>
	<html>
	<head>
		<title>Admin Login</title>
		<style>
			body {
				font-family: Arial, sans-serif;
				display: flex;
				justify-content: center;
				align-items: center;
				height: 100vh;
				margin: 0;
				background: url('background.jpg') no-repeat center center fixed;
				background-size: cover;
			}

			.login-container {
				background: rgba(255, 255, 255, 0.95);
				border: 1px solid rgba(255, 255, 255, 0.3);
				padding: 2rem;
				border-radius: 15px;
				box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
				width: 100%;
				max-width: 400px;
				margin: 20px;
				backdrop-filter: blur(15px);;
			}

			.logo {
				width: 120px;
				height: 120px;
				margin: 0 auto 25px;
				background: #007bff url(isu-logo.png) no-repeat center;
				background-size: cover;
				border-radius: 50%;
				border: 3px solid white;
				box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			}

			h2 {
				color: #2c3e50;
				margin-bottom: 30px;
				font-size: 1.8em;
				text-align: center;
			}

			.form-group {
				margin-bottom: 1.5rem;
			}

			label {
				display: block;
				margin-bottom: 0.5rem;
				color: #5f6368;
				font-weight: 500;
			}

			input {
				width: 100%;
				padding: 12px 20px;
				margin: 8px 0;
				border: 2px solid #e0e0e0;
				border-radius: 8px;
				font-size: 16px;
				box-sizing: border-box;
				transition: border-color 0.3s ease;
			}

			input:focus {
				border-color: #007bff;
				outline: none;
			}

			.button-group {
				display: flex;
				gap: 1rem;
				margin-top: 2rem;
			}

			button {
				flex: 1;
				padding: 12px;
				border: none;
				border-radius: 8px;
				font-weight: 600;
				cursor: pointer;
				transition: transform 0.2s ease, opacity 0.2s ease;
			}

			button:hover {
				transform: translateY(-2px);
			}

			.login-btn {
				background: #007bff;
				color: white;
			}

			.cancel-btn {
				background: #f0f0f0;
				color: #666;
			}

			.error-message {
				color: #dc3545;
				margin-bottom: 1rem;
				text-align: center;
				font-weight: bold;
			}

			@media (max-width: 480px) {
				.login-container {
					padding: 1.5rem;
					margin: 15px;
				}
			}
		</style>
	</head>
	<body>
		<div class="login-container">
			<div class="logo"></div>
			<h2>Voucher System Portal</h2>
			<?php if (!empty($error)) echo "<div class='error-message'>$error</div>"; ?>
			
			<form method="post" action="">
				<div class="form-group">
					<label for="username">User ID</label>
					<input type="text" id="username" name="username" placeholder="Enter Admin ID" required>
				</div>
				
				<div class="form-group">
					<label for="password">Password</label>
					<input type="password" id="password" name="password" placeholder="Enter Password" required>
				</div>
				
				<div class="button-group">
					<button type="submit" class="login-btn">Login</button>
					<button type="reset" class="cancel-btn">Cancel</button>
				</div>
			</form>
		</div>
	</body>
	</html>