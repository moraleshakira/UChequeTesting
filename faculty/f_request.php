<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

$loggedInUserId = $_SESSION['auth_user']['userId']; 
?>

<div class="tabular--wrapper">

    <h3 class="main--title">Request DTR</h3>
    <div class="add">
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#requestModal">
            <i class='bx bxs-file-import'></i>
            <span class="text">Request</span>
        </button>
    </div>

    <div class="table-container">
        <?php if (isset($_SESSION['status'])): ?>
            <div id="statusAlert" class="alert alert-<?php echo $_SESSION['status_code'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['status']; ?>
            </div>
            <?php unset($_SESSION['status']); ?>
        <?php endif; ?>

        <script>
            setTimeout(() => {
                const alert = document.getElementById('statusAlert');
                if (alert) {
                    alert.classList.remove('show'); 
                    alert.classList.add('fade');   
                    setTimeout(() => alert.remove(), 500); 
                }
            }, 3000);
        </script>

        <table class="table">
            <thead>
                <tr>
                    <th>Request Date</th>
                    <th>Start Month</th>
                    <th>End Month</th>
                    <th>Date Approved</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT requestDate, startMonth, endMonth, dateApproved, status 
                          FROM request 
                          WHERE userId = ? 
                          ORDER BY requestDate DESC";
                $stmt = $con->prepare($query);
                $stmt->bind_param('i', $loggedInUserId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . date("F j, Y", strtotime($row['requestDate'])) . "</td>
                                <td>" . htmlspecialchars($row['startMonth']) . "</td>
                                <td>" . htmlspecialchars($row['endMonth']) . "</td>
                                <td>" . htmlspecialchars($row['dateApproved']) . "</td>
                                <td>" . htmlspecialchars($row['status']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr>
                            <td colspan='4' class='text-center'>No requests found</td>
                          </tr>";
                }

                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestModalLabel">Request for Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
            <form id="requestForm" method="POST" action="./controller/handle_request.php">
                    <div class="mb-3">
                        <label for="startMonth" class="form-label">Starting Month</label>
                        <select id="startMonth" class="form-select" name="startMonth" required>
                            <option value="" disabled selected>Select Starting Month</option>
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="endMonth" class="form-label">Ending Month</label>
                        <select id="endMonth" class="form-select" name="endMonth" required>
                            <option value="" disabled selected>Select Ending Month</option>
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('./includes/footer.php');
?>

<script>
    document.getElementById('requestForm').addEventListener('submit', function (e) {
    const startMonth = document.getElementById('startMonth').value;
    const endMonth = document.getElementById('endMonth').value;

    const monthOrder = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    if (monthOrder.indexOf(startMonth) > monthOrder.indexOf(endMonth)) {
        e.preventDefault();
        alert("Error: Starting month must be before or the same as the ending month.");
    }
});

 </script>