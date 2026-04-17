<?php
include '../../../backend/admingatepass.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Manage Requests</title>
    <link rel="stylesheet" href="../../../global.css">
    <link rel="stylesheet" href="../CSS/modern-admin.css">
    <!-- <link rel="stylesheet" href="../CSS/gate-pass.css"> -->

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script>
        $(document).ready(function(){
            $('#example').DataTable({
                columnDefs: [
                    { orderable: false, targets: 9 }  // Disable sorting on Action column
                ],
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        })
    </script>
    <script src="../javascript/script.js"></script>
</head>

<body>
<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>

    <div class="admin-content">
        <div class="admin-main">
            <h2 class="my-4">Manage Gate Pass & Leave Requests</h2>
            <table id="example" class="table table-bordered">
                <thead>
                    <tr>
                        <th>OTR </th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Out Date</th>
                        <th>Return Date</th>
                        <th>Out Time</th>
                        <th>In Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)) : ?>
                        <?php foreach ($data as $row) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['otr_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['type']); ?></td>
                                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                <td><?php echo htmlspecialchars($row['date_from']); ?></td>
                                <td><?php echo htmlspecialchars($row['date_to']); ?></td>
                                <td><?php echo htmlspecialchars($row['out_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['in_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td>
    <form method="POST" style="background: none; border: none; padding: 0; margin: 0;">
        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($row['id']); ?>">
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
        </div>
    </form>
</td>


                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="../javascript/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.action-btn');

        buttons.forEach(button => {
            button.addEventListener('click', function () {
                const requestId = this.dataset.id;
                const action = this.dataset.action;

                fetch('manage-requests.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `request_id=${requestId}&action=${action}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Reload the page or dynamically update the row
                        location.reload();
                    } else {
                        console.error('Action failed');
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    });
</script>

</body>

</html>
