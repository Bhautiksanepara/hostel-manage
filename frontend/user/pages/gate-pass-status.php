<?php
include '../../../backend/user/gate-pass-status.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <title>Gate Pass Status</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-dashboard.css">
 
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>

    
    <script>
        $(document).ready(function(){
            $('#example').DataTable({
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
<?php include 'sidebar.php';?>

    <div class="content">
    <?php include 'topbar.php';?>

        <div class="main-content">
            <div class="page-header">
                <div>
                    <h1>Gate Pass & Leave Requests Status</h1>
                    <p>Review the approval status and timing of your submitted requests.</p>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div>
                        <h3>Request History</h3>
                    </div>
                </div>
                <table id="example" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Reason</th>
                            <th>Out Date</th>
                            <th>Return Date</th>
                            <th>Out Time</th>
                            <th>In Time</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data)) : ?>
                            <?php foreach ($data as $row) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date_from']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date_to']); ?></td>
                                    <td><?php echo htmlspecialchars($row['out_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['in_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
