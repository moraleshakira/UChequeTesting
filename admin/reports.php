<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="tabular--wrapper row">
    <!-- Left Card -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Enter Reports</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="./controller/submit_request.php">
                <div class="form-group">
                    <label for="request_type" class="form-label">Type of Request</label>
                    <select class="form-control" id="request_type" name="request_type" required>
                        <option value="" disabled selected>Select Type of Request</option>
                        <option value="Request for CTO">Request for CTO/Service Credits</option>
                        <option value="Request Letter Overload">Request for Overload</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="employee" class="form-label">Select Employee</label>
                    <select class="form-control" id="employee" name="employee_id[]" required>
                     <option value="" selected>Select Faculty</option>
                        <?php
                        $query = "SELECT employee.userId, employee.firstName, employee.middleName, employee.lastName 
                                  FROM employee 
                                  INNER JOIN employee_role ON employee.userId = employee_role.userId
                                  WHERE employee_role.role_id = 2";
                        $result = $con->query($query);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $fullName = $row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName'];
                                echo "<option value='" . $row['userId'] . "'>" . htmlspecialchars($fullName) . "</option>";
                            }
                        } else {
                            echo "<option value=''>No users found</option>";
                        }
                        ?>
                    </select>
                </div>
        
                    <div class="form-group">
                        <label for="semester" class="form-label">Select Semester</label>
                        <select class="form-control" id="semester" name="semester_id" required>
                            <option value="" selected>Select Semester</option>
                            <?php
                            $sql = "SELECT semester_id, semester_name FROM semesters";
                            $result = $con->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row['semester_id'] . '">' . $row['semester_name'] . '</option>';
                                }
                            } else {
                                echo "<option value=''>No semesters found</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="academic_year" class="form-label">Select Academic Year</label>
                        <select class="form-control" id="academic_year" name="academic_year_id" required>
                            <option value="" selected>Select Academic Year</option>
                            <?php
                            $sql = "SELECT academic_year_id, academic_year FROM academic_years";
                            $result = $con->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row['academic_year_id'] . '">' . $row['academic_year'] . '</option>';
                                }
                            } else {
                                echo "No academic years found.";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                      <label for="starting_month">Starting Month</label>
                      <select id="starting_month" class="form-control" name="starting_month" required>
                          <?php 
                          $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                          foreach ($months as $month) {
                              echo "<option value='$month'>$month</option>";
                          }
                          ?>
                      </select>
                  </div>

                  <div class="form-group">
                      <label for="end_month">End Month</label>
                      <select id="end_month" class="form-control" name="end_month" required>
                          <?php 
                          foreach ($months as $month) {
                              echo "<option value='$month'>$month</option>";
                          }
                          ?>
                      </select>
                  </div>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
<?php
include('./includes/footer.php');
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script>
    const requestTypeSelect = document.getElementById('request_type');
    const startingMonthSelect = document.getElementById('starting_month');
    const endMonthSelect = document.getElementById('end_month');

    // Handle the request type change
    requestTypeSelect.addEventListener('change', function() {
        if (requestTypeSelect.value === 'Request for CTO') {
            startingMonthSelect.disabled = true;
            endMonthSelect.disabled = true;
        } else {
            startingMonthSelect.disabled = false;
            endMonthSelect.disabled = false;
        }
    });

    // Handle the starting month change
    startingMonthSelect.addEventListener('change', function() {
        const startMonthIndex = months.indexOf(startingMonthSelect.value);

        // If a starting month is selected, enable end month and set validation
        if (startMonthIndex >= 0) {
            endMonthSelect.disabled = false;
            validateEndMonth(startMonthIndex);
        }
    });

    // Ensure the end month is after the starting month
    function validateEndMonth(startMonthIndex) {
        endMonthSelect.addEventListener('change', function() {
            const endMonthIndex = months.indexOf(endMonthSelect.value);

            if (endMonthIndex < startMonthIndex) {
                alert('End month must be after the start month.');
                endMonthSelect.value = '';  // Clear the end month
            }
        });
    }

    // Array of months for easier validation
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
</script>
