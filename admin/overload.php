<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>
<div class="tabular--wrapper">
    <div class="add">
        <!-- <div class="filter">
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
        </div> -->

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
            <select id="academic_year" onchange="updateTable()">
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
                    <!-- First Semester Months -->
                    <th class="first-sem">August</th>
                    <th class="first-sem">September</th>
                    <th class="first-sem">October</th>
                    <th class="first-sem">November</th>
                    <th class="first-sem">December</th>
                    <!-- Second Semester Months -->
                    <th class="second-sem">January</th>
                    <th class="second-sem">February</th>
                    <th class="second-sem">March</th>
                    <th class="second-sem">April</th>
                    <th class="second-sem">May</th>
                    <th class="second-sem">June</th>
                    <th class="second-sem">July</th>
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
                    <tr>
                        <td><?php echo htmlspecialchars($row['employeeId']); ?></td>
                        <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?></td>
                        <td><?php echo htmlspecialchars($row['designated'] ?? 'N/A'); ?></td>

                        <?php
                        $firstSemMonths = ['August', 'September', 'October', 'November', 'December'];
                        $secondSemMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July'];

                        $userEntries = $con->query("SELECT * FROM dtr_extracted_data 
                            WHERE userId = {$row['userId']}");

                        $monthData = array_fill_keys(array_merge($firstSemMonths, $secondSemMonths), 
                            ['credits' => 0, 'overload' => 0]);

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

                        foreach ($firstSemMonths as $month) {
                            echo "<td class='first-sem'>";
                            if ($monthData[$month]['credits'] > 0 || $monthData[$month]['overload'] > 0) {
                                echo "Total Credits: " . $monthData[$month]['credits'] . "<br>";
                                echo "Overload: " . $monthData[$month]['overload'];
                            }
                            echo "</td>";
                        }

                        foreach ($secondSemMonths as $month) {
                            echo "<td class='second-sem'>";
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
    let firstSemCells = document.getElementsByClassName('first-sem');
    let secondSemCells = document.getElementsByClassName('second-sem');

    for (let cell of firstSemCells) {
        cell.style.display = 'none';
    }
    for (let cell of secondSemCells) {
        cell.style.display = 'none';
    }

    if (semester == 1) {
        for (let cell of firstSemCells) {
            cell.style.display = 'table-cell';
        }
    } else if (semester == 2) {
        for (let cell of secondSemCells) {
            cell.style.display = 'table-cell';
        }
    }

    let academicYear = document.getElementById("academic_year").value;
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_data.php?semester=" + semester + "&academic_year=" + academicYear, true);
    xhr.onload = function() {
        if (xhr.status == 200) {
            let data = JSON.parse(xhr.responseText);
            let tableBody = document.getElementById("table-body");
            tableBody.innerHTML = '';

            data.forEach(function(row) {
                let tr = document.createElement("tr");
                let basicCells = `
                    <td>${row.employeeId}</td>
                    <td>${row.firstName} ${row.lastName}</td>
                    <td>${row.designated || 'N/A'}</td>
                `;

                let firstSemCells = `
                    <td class="first-sem">${formatMonthData(row.august)}</td>
                    <td class="first-sem">${formatMonthData(row.september)}</td>
                    <td class="first-sem">${formatMonthData(row.october)}</td>
                    <td class="first-sem">${formatMonthData(row.november)}</td>
                    <td class="first-sem">${formatMonthData(row.december)}</td>
                `;

                let secondSemCells = `
                    <td class="second-sem">${formatMonthData(row.january)}</td>
                    <td class="second-sem">${formatMonthData(row.february)}</td>
                    <td class="second-sem">${formatMonthData(row.march)}</td>
                    <td class="second-sem">${formatMonthData(row.april)}</td>
                    <td class="second-sem">${formatMonthData(row.may)}</td>
                    <td class="second-sem">${formatMonthData(row.june)}</td>
                    <td class="second-sem">${formatMonthData(row.july)}</td>
                `;

                tr.innerHTML = basicCells + firstSemCells + secondSemCells;
                tableBody.appendChild(tr);
            });

            updateTable();
        }
    };
    xhr.send();
}

function formatMonthData(data) {
    if (!data) return '';
    return `Total Credits: ${data.credits}<br>Overload: ${data.overload}`;
}
</script>
