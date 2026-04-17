<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Issue</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-dashboard.css">
    <script src="../javascript/script.js"></script>
    
</head>
<body>
    <?php 
    session_start(); 
    include 'sidebar.php'; 
    ?>
    <div class="content">
        <?php include 'topbar.php';?>
        <div class="container mt-4">
            <h2>Describe the Maintenance Issue</h2>
            <form action="../../../backend/user/submit_issue.php" method="post" enctype="multipart/form-data" class="mt-3">
                
                <!-- Dropdown for Maintenance Issue Type -->
                <div class="form-group">
                    <label for="issue_type">Select Issue Type:</label>
                    <select id="issue_type" name="issue_type" class="form-control" required>
                        <option value="">-- Select Issue --</option>
                        <option value="Plumbing">Plumbing</option>
                        <option value="Electrical">Electrical</option>
                        <option value="Furniture">Furniture</option>
                        <option value="Cleaning">Cleaning</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <!-- Textarea for Issue Description -->
                <div class="form-group">
                    <label for="issue">Describe the Issue:</label>
                    <textarea id="issue" name="issue" rows="5" class="form-control" required></textarea>
                </div>
                
                <!-- File Upload for Image (Optional) -->
                <div class="form-group">
                    <label for="issue_image">Upload Image (Optional):</label>
                    <input type="file" id="issue_image" name="issue_image" class="form-control-file">
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Submit Issue</button>
            </form>
        </div>
    </div>
</body>
</html>
