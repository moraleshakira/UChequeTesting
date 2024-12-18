<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>

<div class="tabular--wrapper">
    <div class="add">
        <div class="filter">
            <form method="GET" action="">
                <input type="text" name="search_user" placeholder="Search user..." 
                       value="<?php echo isset($_GET['search_user']) ? htmlspecialchars($_GET['search_user']) : ''; ?>" 
                       style="width: 200px; margin-right: 10px;" 
                       onkeydown="if(event.key === 'Enter') this.form.submit();">

                <select name="academic_year_id" onchange="this.form.submit()" style="width: 200px; margin-right: 10px;">
                    <option value="" selected>Select Academic Year</option>
                    <?php
                    $academicYearQuery = "SELECT academic_year_id, academic_year FROM academic_years";
                    $academicYearResult = $con->query($academicYearQuery);
                    if ($academicYearResult && $academicYearResult->num_rows > 0) {
                        while ($row = $academicYearResult->fetch_assoc()) {
                            $selected = (isset($_GET['academic_year_id']) && $_GET['academic_year_id'] == $row['academic_year_id']) ? 'selected' : '';
                            echo "<option value='{$row['academic_year_id']}' $selected>{$row['academic_year']}</option>";
                        }
                    }
                    ?>
                </select>

                <select name="semester_id" onchange="this.form.submit()" style="width: 200px; margin-right: 10px;">
                    <option value="" selected>Select Semester</option>
                    <?php
                    $semesterQuery = "SELECT semester_id, semester_name FROM semesters";
                    $semesterResult = $con->query($semesterQuery);
                    if ($semesterResult && $semesterResult->num_rows > 0) {
                        while ($row = $semesterResult->fetch_assoc()) {
                            $selected = (isset($_GET['semester_id']) && $_GET['semester_id'] == $row['semester_id']) ? 'selected' : '';
                            echo "<option value='{$row['semester_id']}' $selected>{$row['semester_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </form>
        </div>

        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class='bx bxs-file-import'></i>
            <span class="text">Import ITL</span>
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th>Total Overload</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $limit = 8;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $page = max($page, 1);
                $offset = ($page - 1) * $limit;

                $search_user = isset($_GET['search_user']) ? $con->real_escape_string($_GET['search_user']) : '';
                $academic_year_id = isset($_GET['academic_year_id']) ? $con->real_escape_string($_GET['academic_year_id']) : '';
                $semester_id = isset($_GET['semester_id']) ? $con->real_escape_string($_GET['semester_id']) : '';

                $whereClauses = ["employee_role.role_id = 2"];

                if (!empty($search_user)) {
                    $whereClauses[] = "(employee.firstName LIKE '%$search_user%' OR employee.lastName LIKE '%$search_user%')";
                }

                if (!empty($academic_year_id)) {
                    $whereClauses[] = "itl_extracted_data.academic_year_id = '$academic_year_id'";
                }

                if (!empty($semester_id)) {
                    $whereClauses[] = "itl_extracted_data.semester_id = '$semester_id'";
                }

                $whereClause = implode(' AND ', $whereClauses);

                $totalQuery = "
                    SELECT COUNT(*) as total
                    FROM employee
                    INNER JOIN itl_extracted_data ON employee.userId = itl_extracted_data.userId
                    INNER JOIN employee_role ON employee.userId = employee_role.userId
                    WHERE $whereClause";

                $totalResult = $con->query($totalQuery);
                $totalRows = $totalResult->fetch_assoc()['total'] ?? 0;
                $totalPages = ceil($totalRows / $limit);

                $sql = "
                    SELECT
                        employee.employeeId,
                        employee.firstName,
                        employee.middleName,
                        employee.lastName,
                        itl_extracted_data.totalOverload,
                        itl_extracted_data.designated,
                        academic_years.academic_year,
                        semesters.semester_name,
                        itl_extracted_data.filePath
                    FROM
                        employee
                    LEFT JOIN itl_extracted_data ON employee.userId = itl_extracted_data.userId
                    LEFT JOIN employee_role ON employee.userId = employee_role.userId
                    LEFT JOIN academic_years ON itl_extracted_data.academic_year_id = academic_years.academic_year_id
                    LEFT JOIN semesters ON itl_extracted_data.semester_id = semesters.semester_id
                    WHERE $whereClause
                    LIMIT $limit OFFSET $offset";

                $result = $con->query($sql);

                if ($result && $result->num_rows > 0) {
                    $counter = $offset;
                    while ($row = $result->fetch_assoc()) {
                        $counter++;
                        $fullName = trim($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']);
                        $totalOverload = ($row['totalOverload'] <= 0) ? "No overload" : htmlspecialchars($row['totalOverload']);
                        
                        $filePath = htmlspecialchars($row['filePath']);
                        $downloadLink = !empty($filePath) ? 'uploads/' . $filePath : '#';
                        $downloadDisabled = empty($filePath) ? 'style="pointer-events: none; color: gray;"' : '';

                        echo "<tr>
                                <td>$counter</td>
                                <td>$fullName</td>
                                <td>" . htmlspecialchars($row['designated']) . "</td>
                                <td>" . htmlspecialchars($row['academic_year']) . "</td>
                                <td>" . htmlspecialchars($row['semester_name']) . "</td>
                                <td>$totalOverload</td>
                                <td>
                                    <a href='$downloadLink' class='action' download $downloadDisabled>Download</a>
                                    <a href='controller/delete-itl.php' class='action'>Delete</a>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No users found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php
            if ($totalPages > 1) {
                for ($i = 1; $i <= $totalPages; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    $queryString = http_build_query(array_merge($_GET, ['page' => $i]));
                    echo "<a href='?$queryString' class='pagination-button $active'>$i</a>";
                }
            }
            ?>
        </div>
    </div>
</div>

<?php
include('./includes/footer.php');
?>


<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importModalLabel">Import Individual Teacher's Load</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="./controller/import-itl.php" method="POST" enctype="multipart/form-data">
          
          <div class="mb-3">
            <label for="userId" class="form-label">Select User</label>
            <select class="form-control" id="userId" name="userId" required>
              <option value="" disabled selected>---Select User---</option>
              <?php
                $query = "SELECT employee.userId, employee.employeeId, employee.firstName, employee.middleName, employee.lastName 
                          FROM employee 
                          INNER JOIN employee_role ON employee.userId = employee_role.userId
                          WHERE employee_role.role_id = 2"; // Only Faculty
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

          <div class="mb-3">
            <label for="academicYear" class="form-label">Select Academic Year</label>
            <select class="form-control" id="academicYear" name="academicYear" required>
              <option value="" selected>Select Academic Year</option>
              <option value="1">2024-2025</option>
              <option value="2">2025-2026</option>
              <option value="3">2026-2027</option>
              <option value="4">2027-2028</option>
              <option value="5">2028-2029</option>
              <option value="6">2029-2030</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="semester" class="form-label">Select Semester</label>
            <select class="form-control" id="semester" name="semester" required>
              <option value="" selected>Select Semester Year</option>
              <option value="1">1st Semester</option>
              <option value="2">2nd Semester</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="file" class="form-label">Upload Excel File</label>
            <input type="file" class="form-control" id="file" name="file" accept=".xlsx" required>
          </div>
          
          <div class="text-end">
            <button type="submit" class="btn btn-primary">Import file</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include('./includes/footer.php'); ?>
