<?php
session_start();
require('../fpdf/fpdf.php'); // Make sure FPDF is installed in this location

// Check if the request includes the necessary parameters
if (!isset($_GET['inquiry_id']) || !isset($_GET['mechanic'])) {
    die("Missing required parameters");
}

// Database connection
include '../authentication/db.php';

// Get inquiry ID and mechanic name from the request
$inquiry_id = $_GET['inquiry_id'];
$mechanic_name = $_GET['mechanic'];
$head_mechanic_name = $_GET['head_mechanic'] ?? 'Head Mechanic';

// Fetch inquiry details from the database
$query = "SELECT * FROM service_inquiries WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $inquiry_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Create PDF
    class PDF extends FPDF {
        function Header() {
            // Logo (optional - you can add your company logo here)
            // $this->Image('logo.png', 10, 6, 30);
            
            // Arial bold 15
            $this->SetFont('Arial', 'B', 15);
            
            // Title
            $this->Cell(0, 10, 'Service Assignment Form', 0, 1, 'C');
            
            // Line break
            $this->Ln(5);
        }
        
        function Footer() {
            // Position at 1.5 cm from bottom
            $this->SetY(-15);
            
            // Arial italic 8
            $this->SetFont('Arial', 'I', 8);
            
            // Page number
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }
    
    // Initialize PDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    
    // Add current date and reference number
    $pdf->Cell(0, 10, 'Date: ' . date('Y-m-d'), 0, 1);
    $pdf->Cell(0, 10, 'Reference Number: ' . $row['reference_number'], 0, 1);
    $pdf->Ln(5);
    
    // Vehicle details section
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Vehicle Details', 0, 1);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, 'Brand:', 0, 0);
    $pdf->Cell(0, 10, $row['brand'], 0, 1);
    $pdf->Cell(50, 10, 'Model:', 0, 0);
    $pdf->Cell(0, 10, $row['model'], 0, 1);
    $pdf->Cell(50, 10, 'Year Model:', 0, 0);
    $pdf->Cell(0, 10, $row['year_model'], 0, 1);
    $pdf->Ln(5);
    
    // Service details section
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Service Details', 0, 1);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, 'Service Type:', 0, 0);
    $pdf->Cell(0, 10, $row['service_type'], 0, 1);
    $pdf->Cell(50, 10, 'Preferred Date:', 0, 0);
    $pdf->Cell(0, 10, $row['preferred_date'], 0, 1);
    $pdf->Cell(50, 10, 'Contact Number:', 0, 0);
    $pdf->Cell(0, 10, $row['contact_number'], 0, 1);
    $pdf->Ln(5);
    
    // Description section
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Service Description', 0, 1);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 10, $row['description']);
    $pdf->Ln(5);
    
    // Assignment section
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Assignment Details', 0, 1);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, 'Assigned Mechanic:', 0, 0);
    $pdf->Cell(0, 10, $mechanic_name, 0, 1);
    $pdf->Cell(50, 10, 'Assignment Date:', 0, 0);
    $pdf->Cell(0, 10, date('Y-m-d'), 0, 1);
    $pdf->Ln(10);
    
    // Signature section
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Signatures', 0, 1);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(20); // Space for signatures
    
    // Signature lines
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(90, 10, '_______________________', 0, 0, 'C');
    $pdf->Cell(90, 10, '_______________________', 0, 1, 'C');
    $pdf->Cell(90, 10, 'Mechanic Signature', 0, 0, 'C');
    $pdf->Cell(90, 10, 'Head Mechanic Signature', 0, 1, 'C');
    $pdf->Cell(90, 10, $mechanic_name, 0, 0, 'C');
    $pdf->Cell(90, 10, $head_mechanic_name, 0, 1, 'C');
    
    // Output PDF as download
    $pdf->Output('D', 'Service_Assignment_' . $row['reference_number'] . '.pdf');
} else {
    die("Inquiry not found");
}
?>
