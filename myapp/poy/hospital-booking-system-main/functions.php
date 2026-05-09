<?php
// ============================================
// MEDCORE HOSPITAL MANAGEMENT SYSTEM
// Core Helper Functions
// ============================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// ============================================
// AUTHENTICATION FUNCTIONS
// ============================================

/**
 * Hash password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Authenticate user login
 */
function authenticateUser($email, $password, $link) {
    $email = mysqli_real_escape_string($link, trim($email));
    
    $query = "SELECT u.*, 
              CASE 
                  WHEN u.role = 'doctor' THEN d.id
                  WHEN u.role = 'patient' THEN p.id
                  WHEN u.role = 'secretary' THEN s.id
              END as role_id
              FROM users u
              LEFT JOIN doctors d ON u.id = d.user_id
              LEFT JOIN patients p ON u.id = p.user_id
              LEFT JOIN secretaries s ON u.id = s.user_id
              WHERE u.email = '$email' AND u.status = 'active'
              LIMIT 1";
    
    $result = mysqli_query($link, $query);
    if (!$result || mysqli_num_rows($result) === 0) {
        return false;
    }
    
    $user = mysqli_fetch_assoc($result);
    
    if (!verifyPassword($password, $user['password_hash'])) {
        return false;
    }
    
    return $user;
}

/**
 * Create user session
 */
function createUserSession($user, $link) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['profile_photo'] = $user['profile_photo'];
    $_SESSION['logged_in'] = true;
    
    // Update last login
    $userId = $user['id'];
    $loginTime = date('Y-m-d H:i:s');
    mysqli_query($link, "UPDATE users SET last_login = '$loginTime' WHERE id = $userId");
    
    // Log activity
    logActivity($userId, 'login', 'User logged in', 'login', null, null, $link);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

/**
 * Check if user has any of the given roles
 */
function hasAnyRole($roles) {
    if (!isLoggedIn()) return false;
    return in_array($_SESSION['user_role'], (array)$roles);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return isLoggedIn() ? $_SESSION['user_role'] : null;
}

/**
 * Get current role ID
 */
function getCurrentRoleId() {
    return isLoggedIn() ? $_SESSION['role_id'] : null;
}

/**
 * Logout user
 */
function logoutUser($link) {
    if (isLoggedIn()) {
        logActivity($_SESSION['user_id'], 'logout', 'User logged out', 'logout', null, null, $link);
    }
    session_destroy();
}

// ============================================
// USER FUNCTIONS
// ============================================

/**
 * Get user by ID
 */
function getUserById($userId, $link) {
    $userId = intval($userId);
    $query = "SELECT * FROM users WHERE id = $userId";
    $result = mysqli_query($link, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Register new user
 */
function registerUser($firstName, $lastName, $email, $phone, $password, $role, $link) {
    $firstName = mysqli_real_escape_string($link, trim($firstName));
    $lastName = mysqli_real_escape_string($link, trim($lastName));
    $email = mysqli_real_escape_string($link, trim($email));
    $phone = mysqli_real_escape_string($link, trim($phone));
    $role = mysqli_real_escape_string($link, trim($role));
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address'];
    }
    
    // Check if email exists
    $checkQuery = "SELECT id FROM users WHERE email = '$email'";
    $checkResult = mysqli_query($link, $checkQuery);
    if (mysqli_num_rows($checkResult) > 0) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password
    $passwordHash = hashPassword($password);
    
    // Insert user
    $query = "INSERT INTO users (first_name, last_name, email, phone, password_hash, role, status)
              VALUES ('$firstName', '$lastName', '$email', '$phone', '$passwordHash', '$role', 'active')";
    
    if (!mysqli_query($link, $query)) {
        return ['success' => false, 'message' => 'Registration failed: ' . mysqli_error($link)];
    }
    
    $userId = mysqli_insert_id($link);
    
    // Create role-specific records
    if ($role === 'patient') {
        $patientQuery = "INSERT INTO patients (user_id) VALUES ($userId)";
        mysqli_query($link, $patientQuery);
    } elseif ($role === 'doctor') {
        $doctorQuery = "INSERT INTO doctors (user_id, license_number, specialization, experience_years, consultation_fee, rating)
                        VALUES ($userId, 'LN-$userId', 'General Practice', 0, 0, 0)";
        mysqli_query($link, $doctorQuery);
    }
    
    return ['success' => true, 'message' => 'Registration successful', 'user_id' => $userId];
}

// ============================================
// DOCTOR FUNCTIONS
// ============================================

/**
 * Get all doctors with optional filters
 */
function getDoctors($link, $specialization = null, $limit = null, $offset = 0) {
    $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo
              FROM doctors d
              JOIN users u ON d.user_id = u.id
              WHERE u.status = 'active'";
    
    if ($specialization) {
        $specialization = mysqli_real_escape_string($link, $specialization);
        $query .= " AND d.specialization = '$specialization'";
    }
    
    $query .= " ORDER BY d.rating DESC";
    
    if ($limit) {
        $limit = intval($limit);
        $offset = intval($offset);
        $query .= " LIMIT $offset, $limit";
    }
    
    $result = mysqli_query($link, $query);
    $doctors = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $doctors[] = $row;
    }
    return $doctors;
}

/**
 * Get doctor by ID
 */
function getDoctorById($doctorId, $link) {
    $doctorId = intval($doctorId);
    $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo
              FROM doctors d
              JOIN users u ON d.user_id = u.id
              WHERE d.id = $doctorId";
    
    $result = mysqli_query($link, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Get doctor by user ID
 */
function getDoctorByUserId($userId, $link) {
    $userId = intval($userId);
    $query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo
              FROM doctors d
              JOIN users u ON d.user_id = u.id
              WHERE d.user_id = $userId";
    
    $result = mysqli_query($link, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Get doctor specializations
 */
function getDoctorSpecializations($link) {
    $query = "SELECT DISTINCT specialization FROM doctors ORDER BY specialization ASC";
    $result = mysqli_query($link, $query);
    $specializations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $specializations[] = $row['specialization'];
    }
    return $specializations;
}

// ============================================
// PATIENT FUNCTIONS
// ============================================

/**
 * Get patient by ID
 */
function getPatientById($patientId, $link) {
    $patientId = intval($patientId);
    $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo
              FROM patients p
              JOIN users u ON p.user_id = u.id
              WHERE p.id = $patientId";
    
    $result = mysqli_query($link, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Get patient by user ID
 */
function getPatientByUserId($userId, $link) {
    $userId = intval($userId);
    $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo
              FROM patients p
              JOIN users u ON p.user_id = u.id
              WHERE p.user_id = $userId";
    
    $result = mysqli_query($link, $query);
    return mysqli_fetch_assoc($result);
}

// ============================================
// APPOINTMENT FUNCTIONS
// ============================================

/**
 * Get appointments with filters
 */
function getAppointments($link, $filters = []) {
    $query = "SELECT a.*, 
              d.specialization, u1.first_name as doctor_first_name, u1.last_name as doctor_last_name,
              p.id as patient_id, u2.first_name as patient_first_name, u2.last_name as patient_last_name
              FROM appointments a
              JOIN doctors d ON a.doctor_id = d.id
              JOIN users u1 ON d.user_id = u1.id
              JOIN patients p ON a.patient_id = p.id
              JOIN users u2 ON p.user_id = u2.id
              WHERE 1=1";
    
    if (isset($filters['doctor_id'])) {
        $query .= " AND a.doctor_id = " . intval($filters['doctor_id']);
    }
    if (isset($filters['patient_id'])) {
        $query .= " AND a.patient_id = " . intval($filters['patient_id']);
    }
    if (isset($filters['status'])) {
        $status = mysqli_real_escape_string($link, $filters['status']);
        $query .= " AND a.status = '$status'";
    }
    if (isset($filters['date'])) {
        $date = mysqli_real_escape_string($link, $filters['date']);
        $query .= " AND a.appointment_date = '$date'";
    }
    if (isset($filters['date_from']) && isset($filters['date_to'])) {
        $from = mysqli_real_escape_string($link, $filters['date_from']);
        $to = mysqli_real_escape_string($link, $filters['date_to']);
        $query .= " AND a.appointment_date BETWEEN '$from' AND '$to'";
    }
    
    $query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    
    $result = mysqli_query($link, $query);
    $appointments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }
    return $appointments;
}

/**
 * Get appointment by ID
 */
function getAppointmentById($appointmentId, $link) {
    $appointmentId = intval($appointmentId);
    $query = "SELECT a.*, 
              d.specialization, u1.first_name as doctor_first_name, u1.last_name as doctor_last_name,
              p.id as patient_id, u2.first_name as patient_first_name, u2.last_name as patient_last_name
              FROM appointments a
              JOIN doctors d ON a.doctor_id = d.id
              JOIN users u1 ON d.user_id = u1.id
              JOIN patients p ON a.patient_id = p.id
              JOIN users u2 ON p.user_id = u2.id
              WHERE a.id = $appointmentId";
    
    $result = mysqli_query($link, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Book appointment
 */
function bookAppointment($doctorId, $patientId, $date, $time, $reason, $link) {
    $doctorId = intval($doctorId);
    $patientId = intval($patientId);
    $date = mysqli_real_escape_string($link, $date);
    $time = mysqli_real_escape_string($link, $time);
    $reason = mysqli_real_escape_string($link, $reason);
    
    // Check if slot is available
    $checkQuery = "SELECT COUNT(*) as count FROM appointments 
                   WHERE doctor_id = $doctorId AND appointment_date = '$date' 
                   AND appointment_time = '$time' AND status IN ('approved', 'pending')";
    $checkResult = mysqli_query($link, $checkQuery);
    $checkRow = mysqli_fetch_assoc($checkResult);
    
    if ($checkRow['count'] > 0) {
        return ['success' => false, 'message' => 'This time slot is already booked'];
    }
    
    // Insert appointment
    $query = "INSERT INTO appointments (doctor_id, patient_id, appointment_date, appointment_time, reason_for_visit, status)
              VALUES ($doctorId, $patientId, '$date', '$time', '$reason', 'pending')";
    
    if (!mysqli_query($link, $query)) {
        return ['success' => false, 'message' => 'Booking failed: ' . mysqli_error($link)];
    }
    
    $appointmentId = mysqli_insert_id($link);
    
    // Create notification for secretary/doctor
    createNotification('Appointment Pending Approval', 'A new appointment has been requested', 
                       'appointment', $appointmentId, null, $link);
    
    // Log activity
    logActivity($patientId, 'Booked appointment', 'New appointment booked', 'booking', 
                $appointmentId, 'appointments', $link);
    
    return ['success' => true, 'message' => 'Appointment booked successfully', 'appointment_id' => $appointmentId];
}

/**
 * Cancel appointment
 */
function cancelAppointment($appointmentId, $userId, $reason, $link) {
    $appointmentId = intval($appointmentId);
    $userId = intval($userId);
    $reason = mysqli_real_escape_string($link, $reason);
    
    $appointment = getAppointmentById($appointmentId, $link);
    if (!$appointment) {
        return ['success' => false, 'message' => 'Appointment not found'];
    }
    
    // Get user role
    $userQuery = "SELECT role FROM users WHERE id = $userId";
    $userResult = mysqli_query($link, $userQuery);
    $userRow = mysqli_fetch_assoc($userResult);
    $userRole = $userRow['role'];
    
    // Update appointment status
    $query = "UPDATE appointments SET status = 'cancelled', cancelled_by = '$userRole', 
              cancellation_reason = '$reason' WHERE id = $appointmentId";
    
    if (!mysqli_query($link, $query)) {
        return ['success' => false, 'message' => 'Cancellation failed'];
    }
    
    // Log cancellation
    $cancelQuery = "INSERT INTO cancellations (appointment_id, cancelled_by_user_id, reason, cancelled_by_role)
                    VALUES ($appointmentId, $userId, '$reason', '$userRole')";
    mysqli_query($link, $cancelQuery);
    
    // Create notification
    createNotification('Appointment Cancelled', 'An appointment has been cancelled', 
                       'cancellation', $appointmentId, null, $link);
    
    // Log activity
    logActivity($userId, 'Cancelled appointment', 'Appointment cancelled', 'cancellation', 
                $appointmentId, 'appointments', $link);
    
    return ['success' => true, 'message' => 'Appointment cancelled successfully'];
}

/**
 * Update appointment status
 */
function updateAppointmentStatus($appointmentId, $status, $link) {
    $appointmentId = intval($appointmentId);
    $status = mysqli_real_escape_string($link, $status);
    
    $validStatuses = ['pending', 'approved', 'completed', 'cancelled', 'no_show'];
    if (!in_array($status, $validStatuses)) {
        return ['success' => false, 'message' => 'Invalid status'];
    }
    
    $query = "UPDATE appointments SET status = '$status' WHERE id = $appointmentId";
    
    if (!mysqli_query($link, $query)) {
        return ['success' => false, 'message' => 'Update failed'];
    }
    
    return ['success' => true, 'message' => 'Status updated successfully'];
}

// ============================================
// REVIEW FUNCTIONS
// ============================================

/**
 * Get reviews for doctor
 */
function getDoctorReviews($doctorId, $link, $limit = 10) {
    $doctorId = intval($doctorId);
    $limit = intval($limit);
    
    $query = "SELECT r.*, u.first_name, u.last_name, u.profile_photo
              FROM reviews r
              JOIN patients p ON r.patient_id = p.id
              JOIN users u ON p.user_id = u.id
              WHERE r.doctor_id = $doctorId AND r.status = 'approved'
              ORDER BY r.created_at DESC
              LIMIT $limit";
    
    $result = mysqli_query($link, $query);
    $reviews = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }
    return $reviews;
}

/**
 * Add review for doctor
 */
function addReview($doctorId, $patientId, $appointmentId, $rating, $comment, $link) {
    $doctorId = intval($doctorId);
    $patientId = intval($patientId);
    $appointmentId = intval($appointmentId);
    $rating = intval($rating);
    $comment = mysqli_real_escape_string($link, $comment);
    
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'message' => 'Invalid rating'];
    }
    
    $query = "INSERT INTO reviews (doctor_id, patient_id, appointment_id, rating, comment, status)
              VALUES ($doctorId, $patientId, $appointmentId, $rating, '$comment', 'pending')";
    
    if (!mysqli_query($link, $query)) {
        return ['success' => false, 'message' => 'Review failed'];
    }
    
    // Update doctor rating
    updateDoctorRating($doctorId, $link);
    
    return ['success' => true, 'message' => 'Review submitted for approval'];
}

/**
 * Update doctor average rating
 */
function updateDoctorRating($doctorId, $link) {
    $doctorId = intval($doctorId);
    
    $query = "SELECT AVG(rating) as avg_rating FROM reviews 
              WHERE doctor_id = $doctorId AND status = 'approved'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);
    $avgRating = $row['avg_rating'] ? round($row['avg_rating'], 2) : 0;
    
    $updateQuery = "UPDATE doctors SET rating = $avgRating WHERE id = $doctorId";
    mysqli_query($link, $updateQuery);
}

// ============================================
// NOTIFICATION FUNCTIONS
// ============================================

/**
 * Create notification
 */
function createNotification($title, $message, $type, $relatedId, $actionUrl, $link) {
    $title = mysqli_real_escape_string($link, $title);
    $message = mysqli_real_escape_string($link, $message);
    $type = mysqli_real_escape_string($link, $type);
    $relatedId = intval($relatedId);
    $actionUrl = $actionUrl ? mysqli_real_escape_string($link, $actionUrl) : 'NULL';
    
    // Get relevant users to notify
    $notifyUsers = [];
    
    if ($type === 'appointment') {
        // Notify all admin/secretaries
        $query = "SELECT u.id FROM users u WHERE u.role IN ('admin', 'secretary')";
        $result = mysqli_query($link, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $notifyUsers[] = $row['id'];
        }
    }
    
    foreach ($notifyUsers as $userId) {
        $query = "INSERT INTO notifications (user_id, type, title, message, related_id, action_url)
                  VALUES ($userId, '$type', '$title', '$message', $relatedId, $actionUrl)";
        mysqli_query($link, $query);
    }
}

/**
 * Get user notifications
 */
function getUserNotifications($userId, $limit = 10, $link) {
    $userId = intval($userId);
    $limit = intval($limit);
    
    $query = "SELECT * FROM notifications WHERE user_id = $userId 
              ORDER BY created_at DESC LIMIT $limit";
    
    $result = mysqli_query($link, $query);
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    return $notifications;
}

/**
 * Get unread notification count
 */
function getUnreadNotificationCount($userId, $link) {
    $userId = intval($userId);
    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = $userId AND is_read = FALSE";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($notificationId, $link) {
    $notificationId = intval($notificationId);
    $query = "UPDATE notifications SET is_read = TRUE, read_at = NOW() WHERE id = $notificationId";
    return mysqli_query($link, $query);
}

// ============================================
// ACTIVITY LOG FUNCTIONS
// ============================================

/**
 * Log user activity
 */
function logActivity($userId, $action, $description, $actionType, $relatedId, $relatedTable, $link) {
    $userId = intval($userId);
    $action = mysqli_real_escape_string($link, $action);
    $description = mysqli_real_escape_string($link, $description);
    $actionType = mysqli_real_escape_string($link, $actionType);
    $relatedId = $relatedId ? intval($relatedId) : 'NULL';
    $relatedTable = $relatedTable ? mysqli_real_escape_string($link, $relatedTable) : 'NULL';
    $ipAddress = mysqli_real_escape_string($link, $_SERVER['REMOTE_ADDR'] ?? '');
    $userAgent = mysqli_real_escape_string($link, $_SERVER['HTTP_USER_AGENT'] ?? '');
    
    $query = "INSERT INTO activity_logs (user_id, action, description, action_type, related_id, related_table, ip_address, user_agent)
              VALUES ($userId, '$action', '$description', '$actionType', $relatedId, $relatedTable, '$ipAddress', '$userAgent')";
    
    return mysqli_query($link, $query);
}

/**
 * Get user activity logs
 */
function getUserActivityLogs($userId, $limit = 50, $link) {
    $userId = intval($userId);
    $limit = intval($limit);
    
    $query = "SELECT * FROM activity_logs WHERE user_id = $userId 
              ORDER BY created_at DESC LIMIT $limit";
    
    $result = mysqli_query($link, $query);
    $logs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = $row;
    }
    return $logs;
}

// ============================================
// SCHEDULE FUNCTIONS
// ============================================

/**
 * Get doctor schedule
 */
function getDoctorSchedule($doctorId, $link) {
    $doctorId = intval($doctorId);
    $query = "SELECT * FROM schedules WHERE doctor_id = $doctorId ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
    
    $result = mysqli_query($link, $query);
    $schedule = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $schedule[] = $row;
    }
    return $schedule;
}

/**
 * Get available time slots for doctor on date
 */
function getAvailableTimeSlots($doctorId, $date, $link) {
    $doctorId = intval($doctorId);
    $date = mysqli_real_escape_string($link, $date);
    
    $dayOfWeek = date('l', strtotime($date)); // Monday, Tuesday, etc.
    
    // Get doctor's schedule for this day
    $scheduleQuery = "SELECT * FROM schedules WHERE doctor_id = $doctorId AND day_of_week = '$dayOfWeek'";
    $scheduleResult = mysqli_query($link, $scheduleQuery);
    $schedule = mysqli_fetch_assoc($scheduleResult);
    
    if (!$schedule) {
        return [];
    }
    
    $slots = [];
    $slotDuration = 30; // minutes
    
    // Morning slots
    if ($schedule['morning_start'] && $schedule['morning_end']) {
        $start = strtotime($schedule['morning_start']);
        $end = strtotime($schedule['morning_end']);
        
        while ($start < $end) {
            $slotTime = date('H:i', $start);
            
            // Check if slot is booked
            $bookingCheck = "SELECT COUNT(*) as count FROM appointments 
                            WHERE doctor_id = $doctorId AND appointment_date = '$date' 
                            AND appointment_time = '$slotTime' AND status IN ('approved', 'pending')";
            $bookingResult = mysqli_query($link, $bookingCheck);
            $bookingRow = mysqli_fetch_assoc($bookingResult);
            
            if ($bookingRow['count'] === 0) {
                $slots[] = [
                    'time' => $slotTime,
                    'available' => true,
                    'period' => 'Morning'
                ];
            }
            
            $start += $slotDuration * 60;
        }
    }
    
    // Afternoon slots
    if ($schedule['afternoon_start'] && $schedule['afternoon_end']) {
        $start = strtotime($schedule['afternoon_start']);
        $end = strtotime($schedule['afternoon_end']);
        
        while ($start < $end) {
            $slotTime = date('H:i', $start);
            
            // Check if slot is booked
            $bookingCheck = "SELECT COUNT(*) as count FROM appointments 
                            WHERE doctor_id = $doctorId AND appointment_date = '$date' 
                            AND appointment_time = '$slotTime' AND status IN ('approved', 'pending')";
            $bookingResult = mysqli_query($link, $bookingCheck);
            $bookingRow = mysqli_fetch_assoc($bookingResult);
            
            if ($bookingRow['count'] === 0) {
                $slots[] = [
                    'time' => $slotTime,
                    'available' => true,
                    'period' => 'Afternoon'
                ];
            }
            
            $start += $slotDuration * 60;
        }
    }
    
    return $slots;
}

// ============================================
// STATISTICS FUNCTIONS
// ============================================

/**
 * Get dashboard statistics
 */
function getDashboardStats($link) {
    $stats = [];
    
    // Total doctors
    $query = "SELECT COUNT(*) as total FROM doctors WHERE verified = TRUE";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['total_doctors'] = $row['total'];
    
    // Total patients
    $query = "SELECT COUNT(*) as total FROM patients";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['total_patients'] = $row['total'];
    
    // Total appointments
    $query = "SELECT COUNT(*) as total FROM appointments";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['total_appointments'] = $row['total'];
    
    // Pending appointments
    $query = "SELECT COUNT(*) as total FROM appointments WHERE status = 'pending'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['pending_appointments'] = $row['total'];
    
    // Completed appointments
    $query = "SELECT COUNT(*) as total FROM appointments WHERE status = 'completed'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['completed_appointments'] = $row['total'];
    
    // Cancelled appointments
    $query = "SELECT COUNT(*) as total FROM appointments WHERE status = 'cancelled'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['cancelled_appointments'] = $row['total'];
    
    // Total reviews
    $query = "SELECT COUNT(*) as total FROM reviews";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['total_reviews'] = $row['total'];
    
    return $stats;
}

/**
 * Get today's appointments
 */
function getTodayAppointments($link) {
    $today = date('Y-m-d');
    return getAppointments($link, ['date' => $today]);
}

/**
 * Get top rated doctors
 */
function getTopRatedDoctors($link, $limit = 10) {
    $limit = intval($limit);
    $query = "SELECT d.*, u.first_name, u.last_name, u.profile_photo
              FROM doctors d
              JOIN users u ON d.user_id = u.id
              WHERE u.status = 'active'
              ORDER BY d.rating DESC
              LIMIT $limit";
    
    $result = mysqli_query($link, $query);
    $doctors = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $doctors[] = $row;
    }
    return $doctors;
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Format date for display
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Format time for display
 */
function formatTime($time, $format = 'h:i A') {
    return date($format, strtotime($time));
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: ' . $url);
    exit;
}

/**
 * Display message
 */
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'success';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($message) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    }
    return '';
}

?>
