<?php
// add_prescription.php - إضافة وصفة طبية
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'doctor') {
    header("Location: index.php");
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'clinicdesk_db';
$conn = new mysqli($host, $user, $pass, $db);

$appointment_id = $_GET['appointment_id'] ?? 0;

// جلب بيانات الموعد للتحقق من الملكية والحالة
$stmt = $conn->prepare("
    SELECT a.*, d.user_id as doctor_user_id, u.name as patient_name 
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u ON a.patient_id = u.id
    WHERE a.id = ?
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

// التحقق: الموعد موجود، يخص هذا الدكتور، حالته completed، ولا يوجد وصفة مسبقاً
if (!$appointment) {
    header("Location: appointments.php?error=Appointment not found");
    exit;
}

if ($appointment['doctor_user_id'] != $_SESSION['user_id']) {
    header("Location: appointments.php?error=You don't own this appointment");
    exit;
}

if ($appointment['status'] != 'completed') {
    header("Location: appointments.php?error=Can only add prescription to completed appointments");
    exit;
}

// التحقق من وجود وصفة مسبقاً
$check = $conn->prepare("SELECT id FROM prescriptions WHERE appointment_id = ?");
$check->bind_param("i", $appointment_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    header("Location: appointments.php?error=Prescription already exists for this appointment");
    exit;
}

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $diagnosis = $_POST['diagnosis'] ?? '';
    $medications = $_POST['medications'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $file_path = null;
    
    // معالجة رفع الملف
    if (isset($_FILES['prescription_file']) && $_FILES['prescription_file']['error'] == 0) {
        $file = $_FILES['prescription_file'];
        
        // التحقق من حجم الملف (حد أقصى 3MB)
        if ($file['size'] > 3 * 1024 * 1024) {
            $error = "File too large. Max 3MB.";
        } else {
            // التحقق من نوع الملف باستخدام finfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if ($mime_type == 'application/pdf') {
                // إنشاء اسم فريد للملف
                $filename = 'prescription_' . $appointment_id . '_' . time() . '.pdf';
                $upload_path = __DIR__ . '/uploads/prescriptions/' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $file_path = $filename;
                } else {
                    $error = "Failed to upload file.";
                }
            } else {
                $error = "Only PDF files are allowed.";
            }
        }
    }
    
    if (!isset($error)) {
        // إضافة الوصفة إلى قاعدة البيانات
        $insert = $conn->prepare("INSERT INTO prescriptions (appointment_id, diagnosis, medications, notes, file_path) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("issss", $appointment_id, $diagnosis, $medications, $notes, $file_path);
        
        if ($insert->execute()) {
            header("Location: appointments.php?success=Prescription added successfully");
            exit;
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Prescription - ClinicDesk</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
        }
        .header {
            background: #4e73df;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 5px;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h2 {
            color: #4e73df;
            margin-bottom: 20px;
        }
        .appointment-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .btn-submit {
            background: #1cc88a;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-cancel {
            background: #858796;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .file-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 Add Prescription</h1>
        <a href="appointments.php">← Back to Appointments</a>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>📝 Add Medical Prescription</h2>
            
            <div class="appointment-info">
                <strong>Patient:</strong> <?= htmlspecialchars($appointment['patient_name']) ?><br>
                <strong>Date:</strong> <?= date('d M Y', strtotime($appointment['appt_date'])) ?><br>
                <strong>Reason:</strong> <?= htmlspecialchars($appointment['reason'] ?? '-') ?>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-msg">❌ <?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Diagnosis *</label>
                    <textarea name="diagnosis" required placeholder="Enter diagnosis..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Medications *</label>
                    <textarea name="medications" required placeholder="Enter medications (name, dosage, frequency)..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Additional Notes</label>
                    <textarea name="notes" placeholder="Any additional notes..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Upload Prescription (PDF)</label>
                    <input type="file" name="prescription_file" accept=".pdf">
                    <div class="file-info">Optional. Max 3MB, PDF only.</div>
                </div>
                
                <button type="submit" class="btn-submit">💾 Save Prescription</button>
                <a href="appointments.php" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>