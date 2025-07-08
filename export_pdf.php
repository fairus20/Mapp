<?php

require_once('tcpdf/tcpdf.php'); 

include 'db_connect.php';

$app_id = $_GET['id'] ?? null;

if (!$app_id || !is_numeric($app_id)) {
    die("Invalid application ID.");
}

$app = null;
$comments = [];


$stmt_app = $conn->prepare("SELECT a.*, c.title AS category_title FROM Applications a LEFT JOIN Categories c ON a.category_id = c.id WHERE a.id = ?");
$stmt_app->bind_param("i", $app_id);
$stmt_app->execute();
$result_app = $stmt_app->get_result();
if ($result_app->num_rows > 0) {
    $app = $result_app->fetch_assoc();
}
$stmt_app->close();

$stmt_comments = $conn->prepare("SELECT * FROM Comments WHERE application_id = ? ORDER BY created ASC");
$stmt_comments->bind_param("i", $app_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
if ($result_comments->num_rows > 0) {
    while ($row = $result_comments->fetch_assoc()) {
        $comments[] = $row;
    }
}
$stmt_comments->close();
$conn->close();

if (!$app) {
    die("Application not found.");
}

function formatDateTime($datetime) {
    return date("F j, Y, g:i a", strtotime($datetime));
}


class MYPDF extends TCPDF {
    
    public function Header() {
        
        $this->SetFont('helvetica', 'B', 10);
        
        $this->Cell(0, 15, 'Mobile Application Review', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    
    public function Footer() {
    
        $this->SetY(-15);
        
        $this->SetFont('helvetica', 'I', 8);
    
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}


$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Mobile App Review System');
$pdf->SetTitle('Application Review - ' . $app['title']);
$pdf->SetSubject('Application Review Details');


$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING);


$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));


$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);


$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);


$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}


$pdf->SetFont('helvetica', '', 11);


$pdf->AddPage();


$html = '
    <h1 style="color:#333;">Application Review: ' . htmlspecialchars($app['title']) . '</h1>
    <p><strong>Author:</strong> ' . htmlspecialchars($app['author']) . '</p>
    <p><strong>Category:</strong> ' . htmlspecialchars($app['category_title']) . '</p>
    <p><strong>Status:</strong> ' . ucfirst(htmlspecialchars($app['status'])) . '</p>
';


if (!empty($app['image_dir']) && file_exists($app['image_dir'])) {
    $html .= '<p><br></p>'; 
  
    $image_path = realpath($app['image_dir']);
    if ($image_path) {
        $html .= '<img src="' . $image_path . '" width="200" height="150" border="0" />';
    }
    $html .= '<p><br></p>'; 
}

$html .= '
    <h2 style="color:#555; border-bottom:1px solid #ccc; padding-bottom:5px;">Review Details:</h2>
    <p>' . nl2br(htmlspecialchars($app['review'])) . '</p>
    <p><br></p>
    <div style="font-size:10px; color:#777;">
        <p>Posted: ' . formatDateTime($app['posted_date']) . '</p>
        <p>Last Modified: ' . formatDateTime($app['modified']) . '</p>
    </div>
';


$pdf->writeHTML($html, true, false, true, false, '');


if (!empty($comments)) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 11); 
    $html_comments = '<h2 style="color:#555; border-bottom:1px solid #ccc; padding-bottom:5px;">Comments:</h2>';
    foreach ($comments as $comment) {
        $html_comments .= '
            <div style="border:1px solid #ddd; padding:10px; margin-bottom:10px; border-radius:5px;">
                <p style="font-weight:bold; color:#333;">' . htmlspecialchars($comment['name']) . '</p>
                <p style="color:#555; margin-top:5px;">' . nl2br(htmlspecialchars($comment['comment'])) . '</p>
                <p style="font-size:9px; color:#888; margin-top:5px;">Rating: ' . htmlspecialchars($comment['rating']) . '/5</p>
                <p style="font-size:9px; color:#888; margin-top:5px;">Posted: ' . formatDateTime($comment['created']) . '</p>';
        if ($comment['modified'] != $comment['created']) {
            $html_comments .= '<p style="font-size:9px; color:#888;">Modified: ' . formatDateTime($comment['modified']) . '</p>';
        }
        $html_comments .= '</div>';
    }
    $pdf->writeHTML($html_comments, true, false, true, false, '');
} else {
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Write(0, 'No comments for this review.', '', 0, 'L', true, 0, false, false, 0);
}



$pdf->Output('application_review_' . $app_id . '.pdf', 'D'); 
exit;
?>