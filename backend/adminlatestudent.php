<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/tFPDF/tfpdf.php');



$con = new mysqli('localhost', 'root', '', 'hostel_manage');

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$con->query("CREATE TABLE IF NOT EXISTS gatepass_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gatepass_id INT NOT NULL,
    otr_number VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    check_out_date DATE NOT NULL,
    check_out_time TIME NOT NULL,
    check_in_date DATE DEFAULT NULL,
    check_in_time TIME DEFAULT NULL,
    late_entry TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gatepass_open (gatepass_id, check_in_time),
    INDEX idx_otr_open (otr_number, check_in_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// Fetch Data
if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $query = "SELECT
                  gp.otr_number,
                  gp.name,
                  gl.check_out_date,
                  gl.check_out_time,
                  gl.check_in_date,
                  gl.check_in_time,
                  CASE
                      WHEN gl.late_entry = 1
                        OR TIMESTAMP(gl.check_in_date, gl.check_in_time) > TIMESTAMP(gp.date_to, gp.in_time)
                      THEN 1
                      ELSE 0
                  END AS late_entry
              FROM gatepass_logs gl
              INNER JOIN gatepass gp ON gp.id = gl.gatepass_id
              WHERE gl.check_in_date BETWEEN ? AND ?
                AND gl.check_in_time IS NOT NULL
                AND (
                    gl.late_entry = 1
                    OR TIMESTAMP(gl.check_in_date, gl.check_in_time) > TIMESTAMP(gp.date_to, gp.in_time)
                )
              UNION ALL
              SELECT
                  gp.otr_number,
                  gp.name,
                  gp.check_out_date,
                  gp.check_out_time,
                  gp.check_in_date,
                  gp.check_in_time,
                  gp.late_entry
              FROM gatepass gp
              WHERE gp.late_entry = 1
                AND gp.check_in_date BETWEEN ? AND ?
                AND NOT EXISTS (
                    SELECT 1
                    FROM gatepass_logs gl
                    WHERE gl.gatepass_id = gp.id
                )
              ORDER BY check_in_date DESC, check_in_time DESC";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $late_students = [];
    while ($row = $result->fetch_assoc()) {
        $late_students[] = $row;
    }

    // If request is to generate PDF
    if (isset($_POST['generate_pdf'])) {
        generatePDF($late_students, $start_date, $end_date);
    } else {
        foreach ($late_students as $student) {
            $otr_number = htmlspecialchars($student['otr_number']);
            $name = htmlspecialchars($student['name']);
            $check_out_date = htmlspecialchars($student['check_out_date']);
            $check_out_time = htmlspecialchars($student['check_out_time']);
            $check_in_date = htmlspecialchars($student['check_in_date']);
            $check_in_time = htmlspecialchars($student['check_in_time']);

            echo "<tr>
                    <td>{$otr_number}</td>
                    <td>{$name}</td>
                    <td>{$check_out_date}</td>
                    <td>{$check_out_time}</td>
                    <td>{$check_in_date}</td>
                    <td>{$check_in_time}</td>
                    <td>Yes</td>
                  </tr>";
        }
        exit;
    }
}

// Function to Generate PDF
function generatePDF($students, $start_date, $end_date) {
    $pdf = new tFPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    // $pdf->SetFont('ArialUnicode', '', 14);
    
    $pdf->Cell(0, 10, "Late Student Report", 0, 1, 'C');
    $pdf->Cell(0, 10, "Date Range: $start_date to $end_date", 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(30, 10, "OTR No", 1);
    $pdf->Cell(40, 10, "Name", 1);
    $pdf->Cell(30, 10, "Check-out Date", 1);
    $pdf->Cell(30, 10, "Check-in Date", 1);
    $pdf->Cell(20, 10, "Late Entry", 1);
    $pdf->Ln();

    foreach ($students as $student) {
        $pdf->Cell(30, 10, $student['otr_number'], 1);
        $pdf->Cell(40, 10, $student['name'], 1);
        $pdf->Cell(30, 10, $student['check_out_date'], 1);
        $pdf->Cell(30, 10, $student['check_in_date'], 1);
        $pdf->Cell(20, 10, "Yes", 1);
        $pdf->Ln();
    }

    $pdf->Output("D", "Late_Student_Report.pdf");
    exit;
}
?>
