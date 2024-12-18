<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Validation</title>
    	<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <style>
        .valid {
            color: green;
        }

        .invalid {
            color: red;
        }

        .password-criteria {
            list-style-type: none;
            padding-left: 0;
        }
    </style>
</head>
<body>
    <div id="security" class="tabcontent" style="display: block;">
        <form class="profile-form" method="POST" action="./controller/change-password.php">
            <div class="form-section">
                <label for="currentPassword">Current Password</label>
                <input type="password" id="currentPassword" name="currentPassword" class="form-control mb-3" placeholder="Enter current password" required>

                <label for="newPassword">New Password</label>
                <input type="password" id="newPassword" name="newPassword" class="form-control mb-3" placeholder="Enter new password" required>
                <ul id="passwordCriteria" class="password-criteria">
                    <li id="lengthCriteria" class="invalid">At least 8 characters</li>
                    <li id="uppercaseCriteria" class="invalid">At least one uppercase letter</li>
                    <li id="lowercaseCriteria" class="invalid">At least one lowercase letter</li>
                    <li id="numberCriteria" class="invalid">At least one number</li>
                    <li id="specialCriteria" class="invalid">At least one special character (e.g., @, #, $, etc.)</li>
                </ul>

                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control mb-3" placeholder="Re-enter new password" required>
                <span id="matchMessage" class="invalid"></span>
            </div>

            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const newPassword = document.getElementById("newPassword");
            const confirmPassword = document.getElementById("confirmPassword");
            const matchMessage = document.getElementById("matchMessage");

            const lengthCriteria = document.getElementById("lengthCriteria");
            const uppercaseCriteria = document.getElementById("uppercaseCriteria");
            const lowercaseCriteria = document.getElementById("lowercaseCriteria");
            const numberCriteria = document.getElementById("numberCriteria");
            const specialCriteria = document.getElementById("specialCriteria");

            const passwordCriteria = {
                length: 8,
                uppercase: /[A-Z]/,
                lowercase: /[a-z]/,
                number: /\d/,
                special: /[!@#$%^&*(),.?":{}|<>]/
            };

            function validatePasswordCriteria() {
                const value = newPassword.value;

                lengthCriteria.className = value.length >= passwordCriteria.length ? "valid" : "invalid";
                uppercaseCriteria.className = passwordCriteria.uppercase.test(value) ? "valid" : "invalid";
                lowercaseCriteria.className = passwordCriteria.lowercase.test(value) ? "valid" : "invalid";
                numberCriteria.className = passwordCriteria.number.test(value) ? "valid" : "invalid";
                specialCriteria.className = passwordCriteria.special.test(value) ? "valid" : "invalid";
            }

            function validatePasswordMatch() {
                if (confirmPassword.value === newPassword.value && newPassword.value !== "") {
                    matchMessage.textContent = "Passwords match";
                    matchMessage.className = "valid";
                } else {
                    matchMessage.textContent = "Passwords do not match";
                    matchMessage.className = "invalid";
                }
            }

            newPassword.addEventListener("input", function () {
                validatePasswordCriteria();
                validatePasswordMatch();
            });

            confirmPassword.addEventListener("input", validatePasswordMatch);
        });
    </script>
</body>
</html>
