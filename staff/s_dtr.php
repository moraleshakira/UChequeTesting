<?php
include('./includes/authentication.php');

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    $query = "DELETE FROM dtr_extracted_data WHERE id = ?";
    $stmt = $con->prepare($query);

    if ($stmt === false) {
        die("Error preparing query: " . $con->error);
    }   

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: dtr.php?deleted=true");
        exit();
    } else {
        echo "Error deleting record: " . $stmt->error;
    }

    $stmt->close();
}


include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <div class="tabular--wrapper">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success" id="successMessage" style="opacity: 1; transition: opacity 1s;">
                <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']);?>
            <script>
                setTimeout(function() {
                    var successMessage = document.getElementById('successMessage');
                    successMessage.style.opacity = 0;
                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 1000); 
                }, 3000);
            </script>
        <?php endif; ?>

        <div class="add">
        <div class="filter">
            <form method="GET" action="" class="d-flex align-items-center">
            <input type="text" name="search_user" placeholder="Search user..." 
                    value="<?php echo isset($_GET['search_user']) ? $_GET['search_user'] : ''; ?>" 
                    style="width: 200px; margin-right: 10px; height: 43px;">

            <select name="academic_year" onchange="this.form.submit()" style="height: 43px; margin-right: 10px; width: 220px;">
                <option value="" selected>Select Academic Year</option>
                <?php
                $academicYearQuery = "SELECT * FROM academic_years";
                $academicYearResult = $con->query($academicYearQuery);
                while ($academicYear = $academicYearResult->fetch_assoc()):
                ?>
                <option value="<?php echo $academicYear['academic_year_id']; ?>" 
                    <?php echo (isset($_GET['academic_year']) && $_GET['academic_year'] == $academicYear['academic_year_id']) ? 'selected' : ''; ?>>
                    <?php echo $academicYear['academic_year']; ?>
                </option>
                <?php endwhile; ?>
            </select>

            <select name="semester" onchange="this.form.submit()" style="height: 43px; margin-right: 10px; width: 180px;">
                <option value="" selected>Select Semester</option>
                <?php
                $semesterQuery = "SELECT * FROM semesters";
                $semesterResult = $con->query($semesterQuery);
                while ($semester = $semesterResult->fetch_assoc()):
                ?>
                <option value="<?php echo $semester['semester_id']; ?>" 
                    <?php echo (isset($_GET['semester']) && $_GET['semester'] == $semester['semester_id']) ? 'selected' : ''; ?>>
                    <?php echo $semester['semester_name']; ?>
                </option>
                <?php endwhile; ?>
            </select>
            </form>
        </div>

        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class='bx bxs-file-import'></i>
            <span class="text">Import DTR</span>
        </button>

        </div>
        <div class="table-container">
            <?php
            $search_user = isset($_GET['search_user']) ? $_GET['search_user'] : '';
            $academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
            $semester = isset($_GET['semester']) ? $_GET['semester'] : '';

            $maxHours = 40; 
            $creditThreshold = 12;
            
            $query = "SELECT d.id, d.userId, d.academic_year_id, d.semester_id, 
                d.week1, d.week2, d.week3, d.week4, d.week5, d.overall_total, 
                d.filePath, d.month_year, 
                e.firstName, e.middleName, e.lastName, e.employeeId,
                a.academic_year, s.semester_name, 
                COALESCE(itl.totalOverload, 0) AS totalOverload,
                itl.designated,
                d.week1_overload, d.week2_overload, d.week3_overload, d.week4_overload
            FROM dtr_extracted_data d
            JOIN employee e ON d.userId = e.userId
            JOIN academic_years a ON d.academic_year_id = a.academic_year_id
            JOIN semesters s ON d.semester_id = s.semester_id
            LEFT JOIN itl_extracted_data itl ON d.userId = itl.userId
            WHERE 1=1";

            if (!empty($search_user)) {
                $search_user = $con->real_escape_string($search_user);
                $query .= " AND (e.firstName LIKE '%$search_user%' 
                                OR e.middleName LIKE '%$search_user%' 
                                OR e.lastName LIKE '%$search_user%' 
                                OR e.employeeId LIKE '%$search_user%')";
            }

            if (!empty($academic_year)) {
                $query .= " AND d.academic_year_id = $academic_year";
            }

            if (!empty($semester)) {
                $query .= " AND d.semester_id = $semester";
            }

            $result = $con->query($query);

            if (!$result) {
                die("Error fetching data: " . $con->error);
            }
            ?>

            <table class="table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Semester/A.Y</th>
                        <th>Month/Year</th>
                        <th>Week 1</th>
                        <th>Week 2</th>
                        <th>Week 3</th>
                        <th>Week 4</th>
                        <th>Total Credits</th>
                        <th>Overload</th> 
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()):
                        $weeks = [
                            'week1' => $row['week1'],
                            'week2' => $row['week2'],
                            'week3' => $row['week3'],
                            'week4' => $row['week4'],
                            'week5' => $row['week5'],
                        ];

                        $totalOverload = $row['totalOverload'];
                        $excess = []; 
                        $overload = []; 

                        foreach ($weeks as $key => $weekHours) {
                            if ($weekHours > $maxHours) {
                                $overload[$key] = round($weekHours - $maxHours, 2);
                                $excess[$key] = round($weekHours - $maxHours - $totalOverload, 2);
                            } else {
                                $overload[$key] = 0;
                                $excess[$key] = 0;
                            }
                        }

                        $totalCredits = 0;
                        $weekOverloads = 0;

                        foreach (['week1_overload', 'week2_overload', 'week3_overload', 'week4_overload'] as $week) {
                            $weekOverloads += $row[$week];
                            if ($row[$week] > 12) {
                                $totalCredits += ($row[$week] - 12);
                            }
                        }

                        if ($totalCredits > 0) {
                            $weekOverloads -= $totalCredits;
                            if ($weekOverloads < 0) {
                                $weekOverloads = 0;
                            }
                        }
                    ?>

                    <tr>
                        <td><?php echo htmlspecialchars($row['employeeId']); ?></td>
                        <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']); ?></td>
                        <td><?php echo htmlspecialchars($row['designated']); ?></td>
                        <td><?php echo htmlspecialchars($row['semester_name'] . ' ' . $row['academic_year']); ?></td>
                        <td><?php echo htmlspecialchars($row['month_year']); ?></td>
                        <td>
                            <strong>Total Overload:</strong> <br>
                            <?php echo $row['week1_overload']; ?>
                        </td>
                        <td>
                            <strong>Total Overload:</strong> <br>
                            <?php echo $row['week2_overload']; ?>
                        </td>
                        <td>
                            <strong>Total Overload:</strong> <br>
                            <?php echo $row['week3_overload']; ?>
                        </td>
                        <td>
                            <strong>Total Overload:</strong> <br>
                            <?php echo $row['week4_overload']; ?>
                        </td>
                        <td>
                            <?php echo ($totalCredits > 0) ? $totalCredits : '0'; ?>
                        </td>
                        <td>
                            <?php echo ($weekOverloads > 0) ? $weekOverloads : '0'; ?>
                        </td>
                        <td>
                        <a href="<?php echo htmlspecialchars('/UCheque/uploads/' . $row['filePath']); ?>" download>Download</a>
                            <a href="#" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>


        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Daily Time Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="./controller/import-dtr.php" method="POST" enctype="multipart/form-data" id="importForm">
                            <div class="mb-3">
                                <label for="userId" class="form-label">Select User</label>
                                <select class="form-control" id="userId" name="userId" required>
                                    <option value="" disabled selected>---Select User---</option>
                                    <?php
                                    $query = "SELECT employee.userId, employee.employeeId, employee.firstName, employee.middleName, employee.lastName 
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

                            <?php
                            $sql = "SELECT academic_year_id, academic_year FROM academic_years";
                            $result = $con->query($sql);

                            if ($result->num_rows > 0) {
                                $academicYears = [];
                                while ($row = $result->fetch_assoc()) {
                                    $academicYears[] = $row;
                                }
                            } else {
                                echo "No academic years found.";
                            }
                            ?>

                            <div class="mb-3">
                                <label for="academic_year" class="form-label">Select Academic Year</label>
                                <select class="form-control" id="academic_year" name="academic_year_id" required>
                                    <option value="" selected>Select Academic Year</option>
                                    <?php
                                    foreach ($academicYears as $year) {
                                        echo '<option value="' . $year['academic_year_id'] . '">' . $year['academic_year'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <?php
                            $sql = "SELECT semester_id, semester_name FROM semesters";
                            $result = $con->query($sql);

                            if ($result->num_rows > 0) {
                                $semesters = [];
                                while ($row = $result->fetch_assoc()) {
                                    $semesters[] = $row;
                                }
                            } else {
                                echo "<option value=''>No semesters found</option>";
                            }
                            ?>
                            <div class="mb-3">
                                <label for="semester" class="form-label">Select Semester</label>
                                <select class="form-control" id="semester" name="semester_id" required>
                                    <option value="" selected>Select Semester</option>
                                    <?php
                                    foreach ($semesters as $semester) {
                                        echo '<option value="' . $semester['semester_id'] . '">' . $semester['semester_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="file" class="form-label">Upload File</label>
                                <input type="file" class="form-control" id="file" name="file" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Import</button>
                            </div>
                       
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
include('./includes/footer.php');
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('deleted') && urlParams.get('deleted') === 'true') {
        Swal.fire({
            title: 'Deleted!',
            text: 'The record has been deleted successfully.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
        });
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "dtr.php?id=" + id;
            }
        });
    }
</script>
