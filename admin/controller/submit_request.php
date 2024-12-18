<?php
session_start();
require '../../vendor/autoload.php';
require '../../config/config.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_ids = $_POST['employee_id'];  
    $starting_month = $_POST['starting_month'];
    $end_month = $_POST['end_month'];
    $semester_id = $_POST['semester_id'];
    $academic_year_id = $_POST['academic_year_id'];
    $request_type = $_POST['request_type']; 

    $semester_query = "SELECT semester_name FROM semesters WHERE semester_id = '$semester_id'";
    $semester_result = $con->query($semester_query);
    $semester_name = '';
    if ($semester_result->num_rows > 0) {
        $semester_row = $semester_result->fetch_assoc();
        $semester_name = $semester_row['semester_name'];
    }

    $academic_year_query = "SELECT academic_year FROM academic_years WHERE academic_year_id = '$academic_year_id'";
    $academic_year_result = $con->query($academic_year_query);
    $academic_year = '';
    if ($academic_year_result->num_rows > 0) {
        $academic_year_row = $academic_year_result->fetch_assoc();
        $academic_year = $academic_year_row['academic_year'];
    }

    $phpWord = new PhpWord();
    $sectionStyle = [
        'marginTop' => 720,
        'marginRight' => 720,
        'marginBottom' => 720,
        'marginLeft' => 720
    ];
    $section = $phpWord->addSection($sectionStyle);

    $header = $section->addHeader();
    $header->addImage('../template/header.png', ['width' => 350, 'height' => 120, 'align' => 'center']);
    $header->addText('Office of the Dean - College of Information Technology and Computing', ['bold' => true, 'size' => 10], ['align' => 'center']);

    $section->addText(date('F j, Y'));
    $section->addText('Dear Atty. Albina:', ['bold' => true]);

    if ($request_type == 'Request for CTO') {
        $employee_id = $employee_ids[0];  
        $query = "SELECT employee.firstName, employee.middleName, employee.lastName 
                  FROM employee 
                  INNER JOIN employee_role ON employee.userId = employee_role.userId
                  WHERE employee_role.role_id = 2 AND employee.userId = '$employee_id'";
        $result = $con->query($query);

        $employeeName = '';
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $employeeName = $row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName'];
        }

        $section->addText(
            'With reference to the approved Summary of Honoraria for the ' . $semester_name . ' S.Y. ' . 
            $academic_year . ' from ' . $starting_month . ' to ' . $end_month . ', this is to respectfully request the ' . 
            'Compensatory Time-Off (CTO)/Service Credits of the undersigned as shown in the table below:',
            ['size' => 10]
        );

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $table->addRow();
        $table->addCell(2000)->addText('Name', ['bold' => true]);
        $table->addCell(2000)->addText('Subject Handled', ['bold' => true]);
        $table->addCell(2000)->addText('Equivalent Teaching Load', ['bold' => true]);
        $table->addCell(2000)->addText('Actual Overload', ['bold' => true]);
        $table->addCell(2000)->addText('Paid Overload', ['bold' => true]);
        $table->addCell(2000)->addText('Number of Hours to Claim for CTO (4.25x18/wks)', ['bold' => true]);

        $itlQuery = "SELECT facultyCredit, allowableUnit, totalOverload 
                     FROM itl_extracted_data 
                     WHERE userId = '$employee_id'";
        $itlResult = $con->query($itlQuery);

        $facultyCredit = $allowableUnit = $totalOverload = '';
        if ($itlResult->num_rows > 0) {
            $itlRow = $itlResult->fetch_assoc();
            $facultyCredit = $itlRow['facultyCredit'];
            $allowableUnit = $itlRow['allowableUnit'];
            $totalOverload = $itlRow['totalOverload'];
        }

        $table->addRow();
        $table->addCell(2000)->addText($employeeName, ['bold' => true]);
        $table->addCell(2000)->addText('');
        $table->addCell(2000)->addText('');
        $table->addCell(2000)->addText($totalOverload);
        $table->addCell(2000)->addText('');
        $table->addCell(2000)->addText('');
    } elseif ($request_type == 'Request Letter Overload') {
        $section->addText(
            'This letter is to request your good office to allow the following faculty members under the ' . 
            '______________ Department of the College of Information Technology and Computing (CITC) to handle ' . 
            'subjects beyond the allowable units for overload for the following reasons: ',
            ['size' => 10]
        );

        $section->addText(
            'Shown below is the faculty load of the faculty members who will exceed the allowable units for overload: ',
            ['size' => 10]
        );

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $table->addRow();
        $table->addCell(2000)->addText('Name', ['bold' => true]);
        $table->addCell(2000)->addText('Subject Code', ['bold' => true]);
        $table->addCell(2000)->addText('Descriptive Title', ['bold' => true]);
        $table->addCell(2000)->addText('Total Teaching Load', ['bold' => true]);
        $table->addCell(2000)->addText('Allowable Teaching Load', ['bold' => true]);
        $table->addCell(2000)->addText('Actual Overload/ Underload', ['bold' => true]);

        foreach ($employee_ids as $employee_id) {
            $query = "SELECT employee.firstName, employee.middleName, employee.lastName 
                      FROM employee 
                      INNER JOIN employee_role ON employee.userId = employee_role.userId
                      WHERE employee_role.role_id = 2 AND employee.userId = '$employee_id'";
            $result = $con->query($query);

            $employeeName = '';
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $employeeName = $row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName'];
            }

            $itlQuery = "SELECT facultyCredit, allowableUnit, totalOverload 
                         FROM itl_extracted_data 
                         WHERE userId = '$employee_id'";
            $itlResult = $con->query($itlQuery);

            $facultyCredit = $allowableUnit = $totalOverload = '';
            if ($itlResult->num_rows > 0) {
                $itlRow = $itlResult->fetch_assoc();
                $facultyCredit = $itlRow['facultyCredit'];
                $allowableUnit = $itlRow['allowableUnit'];
                $totalOverload = $itlRow['totalOverload'];
            }

            $table->addRow();
            $table->addCell(2000)->addText($employeeName, ['bold' => true]);
            $table->addCell(2000)->addText(''); 
            $table->addCell(2000)->addText(''); 
            $table->addCell(2000)->addText($facultyCredit); 
            $table->addCell(2000)->addText($allowableUnit); 
            $table->addCell(2000)->addText($totalOverload); 
        }
    }

    $footer = $section->addFooter();
    $footer->addImage('../template/footer.png', ['width' => 450, 'height' => 80, 'align' => 'right']);

    $section->addText('Respectfully yours,');
    $textRun = $section->addTextRun();

    $textRun->addImage('../template/signature.png', [
        'width' => 50, 
        'height' => 30,
        'align' => 'baseline',
    ]);

    $textRun->addText('JUNAR A. LANDICHO, PhD', ['bold' => true]);

    $section->addText('Dean, CITC', ['bold' => true]);
    $section->addText('Noted by:');
    $section->addText('JUDY ANN T. UGAY, RPm', ['bold' => true]);
    $section->addText('Director, HRMO', ['bold' => true]);
    $section->addText('');

    header('Content-Type: application/msword');
    header('Content-Disposition: attachment; filename="request_letter.docx"');
    header('Cache-Control: max-age=0');
    $phpWord->save('php://output', 'Word2007');
}
?>
