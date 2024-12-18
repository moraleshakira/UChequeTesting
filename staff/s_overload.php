<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>
<div class="tabular--wrapper">
    <div class="add">
        <div class="filter">
            <select id="role" onchange="updateTable()">
                <option value="" disabled selected>For the Month of</option>
                <option value="option1">January</option>
                <option value="option2">February</option>
                <option value="option3">March</option>
                <option value="option4">April</option>
                <option value="option5">May</option>
                <option value="option6">June</option>
                <option value="option7">July</option>
                <option value="option8">August</option>
                <option value="option9">September</option>
                <option value="option10">October</option>
                <option value="option11">November</option>
                <option value="option12">December</option>
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
        <div class="filter">
            <select id="academic_year">
                <option value="" disabled selected>Select Academic Year</option>
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

        <div class="filter">
            <select id="semester" onchange="updateTable()">
                <option value="" disabled>Select Academic Semester</option>
                <?php
                foreach ($semesters as $semester) {
                    if ($semester['semester_id'] == 1) {
                        echo '<option value="' . $semester['semester_id'] . '" selected>' . $semester['semester_name'] . '</option>';
                    } else {
                        echo '<option value="' . $semester['semester_id'] . '">' . $semester['semester_name'] . '</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>
    <?php
        $search_user = isset($_GET['search_user']) ? $_GET['search_user'] : '';
        $academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
        $semester = isset($_GET['semester']) ? $_GET['semester'] : '';

        $maxHours = 40;
        $creditThreshold = 12;

        $query = "SELECT d.id, d.userId, d.academic_year_id, d.semester_id, 
            d.week1, d.week2, d.week3, d.week4, d.week5, d.overall_total, 
            d.fileName, d.month_year, 
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

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Faculty</th>
                    <th>Designation</th>
                    <th id="month1">August</th>
                    <th id="month2">September</th>
                    <th id="month3">October</th>
                    <th id="month4">November</th>
                    <th id="month5">December</th>
                    <th id="month6">January</th>
                    <th id="month7">February</th>
                    <th id="month8">March</th>
                    <th id="month9">April</th>
                    <th id="month10">May</th>
                    <th id="month11">June</th>
                    <th id="month12">July</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <?php 
                $processedUsers = [];

                while ($row = $result->fetch_assoc()): 
                    if (in_array($row['userId'], $processedUsers)) {
                        continue;
                    }
                    $processedUsers[] = $row['userId'];
                ?>
                    <?php
                    $weeks = [
                        'week1' => $row['week1'],
                        'week2' => $row['week2'],
                        'week3' => $row['week3'],
                        'week4' => $row['week4'],
                        'week5' => $row['week5'],
                    ];

                    $totalOverload = $row['totalOverload'];
                    ?>

                    <tr>
                        <td><?php echo htmlspecialchars($row['employeeId']); ?></td>
                        <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?></td>
                        <td><?php echo htmlspecialchars($row['designated'] ?? 'N/A'); ?></td>

                        <?php
                        $monthColumns = [
                            'August', 'September', 'October', 'November', 'December', 
                            'January', 'February', 'March', 'April', 'May', 'June', 'July'
                        ];

                        $userEntries = $con->query("SELECT * FROM dtr_extracted_data 
                            WHERE userId = {$row['userId']}");

                        $monthData = array_fill_keys($monthColumns, ['credits' => 0, 'overload' => 0]);

                        while ($entry = $userEntries->fetch_assoc()) {
                            $monthYear = date('F', strtotime($entry['month_year']));
                            
                            $totalCredits = 0;
                            $weekOverloads = 0;

                            foreach (['week1_overload', 'week2_overload', 'week3_overload', 'week4_overload'] as $week) {
                                $weekOverloads += $entry[$week];
                                if ($entry[$week] > $creditThreshold) {
                                    $totalCredits += ($entry[$week] - $creditThreshold);
                                }
                            }

                            if ($totalCredits > 0) {
                                $weekOverloads -= $totalCredits;
                                if ($weekOverloads < 0) {
                                    $weekOverloads = 0;
                                }
                            }

                            $monthData[$monthYear] = [
                                'credits' => $totalCredits,
                                'overload' => $weekOverloads
                            ];
                        }

                        foreach ($monthColumns as $month) {
                            echo "<td>";
                            if ($monthData[$month]['credits'] > 0 || $monthData[$month]['overload'] > 0) {
                                echo "Total Credits: " . $monthData[$month]['credits'] . "<br>";
                                echo "Overload: " . $monthData[$month]['overload'];
                            }
                            echo "</td>";
                        }
                        ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="pagination" id="pagination"></div>
    </div>

</div>
<?php
include('./includes/footer.php');
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let semester = document.getElementById("semester").value;
        if (!semester) {
            document.getElementById("semester").value = 1;
            semester = 1; 
        }
        updateTable(); 
    });

    function updateTable() {
    let semester = document.getElementById("semester").value;
    let monthHeaders = document.querySelectorAll("[id^='month']");
    let tableBody = document.getElementById("table-body");
    let totalCreditsElement = document.getElementById("total-credits");
    let overloadElement = document.getElementById("overload");

    monthHeaders.forEach(function(header) {
        header.style.display = "none";
    });

    if (semester == 1) {
        for (let i = 0; i < 5; i++) {
            monthHeaders[i].style.display = "table-cell";
        }
    } else if (semester == 2) {
        for (let i = 5; i < 12; i++) {
            monthHeaders[i].style.display = "table-cell";
        }
    }

    let academicYear = document.getElementById("academic_year").value;

    let xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_data.php?semester=" + semester + "&academic_year=" + academicYear, true);
    xhr.onload = function() {
        if (xhr.status == 200) {
            let data = JSON.parse(xhr.responseText);
            tableBody.innerHTML = '';
            let totalCredits = 0;
            let overload = 0;

            data.forEach(function(row) {
                let tr = document.createElement("tr");

                tr.innerHTML = `
                    <td>${row.id}</td>
                    <td>${row.faculty}</td>
                    <td>${row.designation}</td>
                    ${semester == 1 ? `
                        <td>${row.august}</td>
                        <td>${row.september}</td>
                        <td>${row.october}</td>
                        <td>${row.november}</td>
                        <td>${row.december}</td>
                    ` : `
                        <td>${row.january}</td>
                        <td>${row.february}</td>
                        <td>${row.march}</td>
                        <td>${row.april}</td>
                        <td>${row.may}</td>
                        <td>${row.june}</td>
                        <td>${row.july}</td>
                    `}
                `;
                tableBody.appendChild(tr);

                if (semester == 1) {
                    totalCredits += row.total_credits_first_sem;
                    overload += row.overload_first_sem;
                } else if (semester == 2) {
                    totalCredits += row.total_credits_second_sem;
                    overload += row.overload_second_sem;
                }
            });

            totalCreditsElement.textContent = totalCredits;
            overloadElement.textContent = overload;
        }
    };
    xhr.send();
}

</script>
