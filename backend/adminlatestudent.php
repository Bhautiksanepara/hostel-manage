<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/tFPDF/tfpdf.php');



$con = new mysqli('localhost', 'root', '', 'hostel_manage');

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Fetch Data
if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $query = "SELECT * FROM gatepass WHERE late_entry = 1 AND check_in_date BETWEEN ? AND ? ORDER BY check_in_date DESC";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
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
            echo "<tr>
                    <td>{$student['otr_number']}</td>
                    <td>{$student['name']}</td>
                    <td>{$student['check_out_date']}</td>
                    <td>{$student['check_out_time']}</td>
                    <td>{$student['check_in_date']}</td>
                    <td>{$student['check_in_time']}</td>
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
