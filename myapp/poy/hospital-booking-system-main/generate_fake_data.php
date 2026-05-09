<?php
// ============================================
// MEDCORE HOSPITAL FAKE DATA GENERATOR
// ============================================

require_once 'config.php';
require_once 'functions.php';

// Define realistic hospital data
$doctors_data = [
    ['Dr. James', 'Anderson', 'Cardiologist', 12, 150],
    ['Dr. Sarah', 'Johnson', 'Pediatrician', 8, 100],
    ['Dr. Michael', 'Chen', 'Neurologist', 15, 200],
    ['Dr. Emily', 'Williams', 'Dermatologist', 7, 120],
    ['Dr. David', 'Martinez', 'Orthopedic', 10, 180],
    ['Dr. Jennifer', 'Taylor', 'Dentist', 6, 80],
    ['Dr. Robert', 'Lee', 'Psychiatrist', 14, 140],
    ['Dr. Lisa', 'Brown', 'ENT Specialist', 9, 110],
    ['Dr. Christopher', 'Garcia', 'General Practice', 11, 90],
    ['Dr. Patricia', 'Rodriguez', 'Ophthalmologist', 8, 130],
    ['Dr. Daniel', 'Thompson', 'Gastroenterologist', 13, 170],
    ['Dr. Linda', 'Harris', 'Radiologist', 10, 150],
    ['Dr. Mark', 'White', 'Urologist', 12, 160],
    ['Dr. Karen', 'Clark', 'Gynecologist', 11, 140],
    ['Dr. Steven', 'Lewis', 'Oncologist', 16, 220],
    ['Dr. Betty', 'Walker', 'Anesthesiologist', 9, 180],
    ['Dr. Paul', 'Hall', 'Pulmonologist', 14, 190],
    ['Dr. Nancy', 'Young', 'Rheumatologist', 7, 110],
    ['Dr. Andrew', 'King', 'Nephrologist', 10, 140],
    ['Dr. Sandra', 'Scott', 'Endocrinologist', 8, 120],
];

$patient_first_names = ['John', 'Mary', 'Robert', 'Patricia', 'Michael', 'Jennifer', 'William', 'Linda', 'David', 'Barbara', 
                         'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Karen', 'Charles', 'Nancy', 'Christopher', 'Lisa',
                         'Daniel', 'Betty', 'Matthew', 'Margaret', 'Anthony', 'Sandra', 'Mark', 'Ashley', 'Donald', 'Kimberly'];

$patient_last_names = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
                        'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
                        'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson'];

$specializations = [
    'Cardiologist', 'Pediatrician', 'Neurologist', 'Dermatologist', 'Orthopedic',
    'Dentist', 'Psychiatrist', 'ENT Specialist', 'General Practice', 'Ophthalmologist',
    'Gastroenterologist', 'Radiologist', 'Urologist', 'Gynecologist', 'Oncologist',
    'Anesthesiologist', 'Pulmonologist', 'Rheumatologist', 'Nephrologist', 'Endocrinologist'
];

$reasons_for_visit = [
    'General checkup', 'Follow-up consultation', 'Symptoms evaluation', 'Vaccination',
    'Lab work review', 'Prescription refill', 'Injury assessment', 'Chronic disease management'
];

/**
 * Generate fake data and populate database
 */
function generateFakeData() {
    global $link, $doctors_data, $patient_first_names, $patient_last_names, $specializations, $reasons_for_visit;
    
    echo "Starting fake data generation...\n";
    
    // Check if data already exists
    $checkQuery = "SELECT COUNT(*) as count FROM doctors";
    $result = mysqli_query($link, $checkQuery);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] > 0) {
        echo "Data already exists. Skipping generation.\n";
        return;
    }
    
    // Generate Doctors
    echo "Generating doctors...\n";
    foreach ($doctors_data as $index => $doctor) {
        $firstName = $doctor[0];
        $lastName = $doctor[1];
        $specialization = $doctor[2];
        $experience = $doctor[3];
        $fee = $doctor[4];
        
        $email = strtolower($firstName . '.' . $lastName . '@medcore.hospital');
        $password = hashPassword('doctor123');
        $phone = '555' . str_pad($index + 1, 7, '0', STR_PAD_LEFT);
        
        // Insert user
        $userQuery = "INSERT INTO users (first_name, last_name, email, phone, password_hash, role, status)
                      VALUES ('$firstName', '$lastName', '$email', '$phone', '$password', 'doctor', 'active')";
        
        if (!mysqli_query($link, $userQuery)) {
            echo "Error inserting doctor user: " . mysqli_error($link) . "\n";
            continue;
        }
        
        $userId = mysqli_insert_id($link);
        $licenseNumber = 'LIC' . str_pad($userId, 5, '0', STR_PAD_LEFT);
        $roomNumber = '2' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
        
        // Insert doctor
        $doctorQuery = "INSERT INTO doctors (user_id, license_number, specialization, experience_years, consultation_fee, room_number, verified, rating)
                        VALUES ($userId, '$licenseNumber', '$specialization', $experience, $fee, '$roomNumber', TRUE, " . round(3 + mt_rand(0, 20) / 10, 2) . ")";
        
        if (!mysqli_query($link, $doctorQuery)) {
            echo "Error inserting doctor: " . mysqli_error($link) . "\n";
            continue;
        }
        
        $doctorId = mysqli_insert_id($link);
        
        // Insert doctor schedules
        $schedule_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        foreach ($schedule_days as $day) {
            $scheduleQuery = "INSERT INTO schedules (doctor_id, day_of_week, morning_start, morning_end, afternoon_start, afternoon_end, is_available)
                              VALUES ($doctorId, '$day', '08:00:00', '12:00:00', '13:00:00', '17:00:00', TRUE)";
            mysqli_query($link, $scheduleQuery);
        }
        
        echo "✓ Generated doctor: $firstName $lastName\n";
    }
    
    // Generate Patients
    echo "Generating patients...\n";
    for ($i = 0; $i < 50; $i++) {
        $firstName = $patient_first_names[array_rand($patient_first_names)];
        $lastName = $patient_last_names[array_rand($patient_last_names)];
        $email = strtolower(str_replace(' ', '', $firstName) . '.' . str_replace(' ', '', $lastName) . $i . '@email.com');
        $password = hashPassword('patient123');
        $phone = '555' . str_pad($i + 1, 7, '0', STR_PAD_LEFT);
        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        $genders = ['male', 'female', 'other'];
        
        $userQuery = "INSERT INTO users (first_name, last_name, email, phone, password_hash, role, status)
                      VALUES ('$firstName', '$lastName', '$email', '$phone', '$password', 'patient', 'active')";
        
        if (!mysqli_query($link, $userQuery)) {
            echo "Error inserting patient user: " . mysqli_error($link) . "\n";
            continue;
        }
        
        $userId = mysqli_insert_id($link);
        $bloodGroup = $bloodGroups[array_rand($bloodGroups)];
        $age = rand(18, 75);
        $gender = $genders[array_rand($genders)];
        
        $patientQuery = "INSERT INTO patients (user_id, blood_group, age, gender)
                         VALUES ($userId, '$bloodGroup', $age, '$gender')";
        
        if (!mysqli_query($link, $patientQuery)) {
            echo "Error inserting patient: " . mysqli_error($link) . "\n";
        }
    }
    echo "✓ Generated 50 patients\n";
    
    // Generate Appointments
    echo "Generating appointments...\n";
    $patientQuery = "SELECT id FROM patients LIMIT 50";
    $patientResult = mysqli_query($link, $patientQuery);
    $patients = [];
    while ($row = mysqli_fetch_assoc($patientResult)) {
        $patients[] = $row['id'];
    }
    
    $doctorQuery = "SELECT id FROM doctors LIMIT 20";
    $doctorResult = mysqli_query($link, $doctorQuery);
    $doctors = [];
    while ($row = mysqli_fetch_assoc($doctorResult)) {
        $doctors[] = $row['id'];
    }
    
    $statuses = ['pending', 'approved', 'completed', 'cancelled'];
    $times = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'];
    
    for ($i = 0; $i < 100; $i++) {
        $doctor_id = $doctors[array_rand($doctors)];
        $patient_id = $patients[array_rand($patients)];
        $date = date('Y-m-d', strtotime('+' . rand(1, 30) . ' days'));
        $time = $times[array_rand($times)];
        $status = $statuses[array_rand($statuses)];
        $reason = $reasons_for_visit[array_rand($reasons_for_visit)];
        
        $appointmentQuery = "INSERT INTO appointments (doctor_id, patient_id, appointment_date, appointment_time, status, reason_for_visit)
                             VALUES ($doctor_id, $patient_id, '$date', '$time', '$status', '$reason')";
        
        if (!mysqli_query($link, $appointmentQuery)) {
            // Silently skip duplicates
            continue;
        }
    }
    echo "✓ Generated 100 appointments\n";
    
    // Generate Reviews
    echo "Generating reviews...\n";
    $appointmentQuery = "SELECT a.id, a.doctor_id, a.patient_id FROM appointments a WHERE a.status = 'completed' LIMIT 50";
    $appointmentResult = mysqli_query($link, $appointmentQuery);
    
    while ($appointment = mysqli_fetch_assoc($appointmentResult)) {
        $rating = rand(3, 5);
        $comments = [
            'Excellent doctor, very professional',
            'Great consultation, highly recommended',
            'Doctor was very attentive and caring',
            'Quick and efficient service',
            'Very knowledgeable and helpful',
            'Would definitely come back again',
            'Great communication skills',
            'Thorough examination and explanation'
        ];
        $comment = $comments[array_rand($comments)];
        $comment = mysqli_real_escape_string($link, $comment);
        
        $reviewQuery = "INSERT INTO reviews (doctor_id, patient_id, appointment_id, rating, comment, status, would_recommend)
                        VALUES ({$appointment['doctor_id']}, {$appointment['patient_id']}, {$appointment['id']}, $rating, '$comment', 'approved', TRUE)";
        
        mysqli_query($link, $reviewQuery);
    }
    echo "✓ Generated reviews\n";
    
    // Update doctor ratings
    echo "Updating doctor ratings...\n";
    $doctorQuery = "SELECT id FROM doctors";
    $doctorResult = mysqli_query($link, $doctorQuery);
    while ($doctor = mysqli_fetch_assoc($doctorResult)) {
        updateDoctorRating($doctor['id'], $link);
    }
    echo "✓ Updated doctor ratings\n";
    
    echo "\n✓ Fake data generation completed successfully!\n";
    echo "Default Credentials:\n";
    echo "Admin: admin@medcore.com / admin123\n";
    echo "Secretary: secretary@medcore.com / admin123\n";
    echo "Doctor: dr.james.anderson@medcore.hospital / doctor123\n";
    echo "Patient: Any from generated list / patient123\n";
}

// Run if executed directly
if (php_sapi_name() === 'cli' || !empty($_GET['generate_fake_data'])) {
    generateFakeData();
}

?>
