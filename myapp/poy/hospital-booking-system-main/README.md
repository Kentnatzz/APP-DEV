# MedCore Hospital Management System

A professional enterprise-level hospital management and doctor appointment platform built with PHP, MySQL, Bootstrap 5, and JavaScript.

## Features

- **Multi-Role Authentication**: Admin, Secretary, Doctor, Patient roles
- **Doctor Management**: Doctor profiles, specializations, scheduling, ratings
- **Appointment System**: Book, approve, cancel, and manage appointments
- **Patient Management**: Patient profiles, medical history, activity tracking
- **Reviews & Ratings**: 5-star rating system with detailed reviews
- **Notifications**: Real-time appointment and system notifications
- **Activity Logging**: Complete audit trail of user actions
- **Analytics Dashboard**: Charts and statistics for admin/secretary
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Professional UI**: Modern glassmorphism design with smooth animations

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP (recommended for local development)

## Installation

### 1. Setup XAMPP

1. Download and install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services
3. Open http://localhost/phpmyadmin/

### 2. Copy Files

Copy the `hospital-booking-system-main` folder to:
```
C:\xampp\htdocs\myapp\
```

### 3. Initialize Database

The database will be created automatically on first access. The system will:
- Create the database `medcore_hospital`
- Initialize all required tables
- Insert default users (admin, secretary)

### 4. Generate Fake Data

After the database is initialized, generate realistic hospital data by running:

**Method 1: Via Browser**
```
http://localhost/myapp/hospital-booking-system-main/generate_fake_data.php?generate_fake_data=1
```

**Method 2: Via Command Line**
```bash
cd C:\xampp\htdocs\myapp\hospital-booking-system-main
php generate_fake_data.php
```

This will generate:
- 20 doctors with various specializations
- 50 patients
- 100 appointments
- 50 reviews
- Default admin and secretary accounts

### 5. Access the System

Open your browser and navigate to:
```
http://localhost/myapp/hospital-booking-system-main/
```

## Default Login Credentials

### Admin
- **Email**: admin@medcore.com
- **Password**: admin123
- **Access**: Full system control, user management, analytics

### Secretary
- **Email**: secretary@medcore.com
- **Password**: admin123
- **Access**: Appointment management, patient records, approvals

### Doctor
- **Email**: james.anderson@medcore.hospital
- **Password**: doctor123
- **Access**: View appointments, patient info, manage schedule

### Patient
- **Register New Account** at `/register.php`
- **Password**: Your chosen password
- **Access**: Find doctors, book appointments, write reviews

## File Structure

```
hospital-booking-system-main/
├── config.php                 # Database configuration
├── functions.php              # Core helper functions
├── generate_fake_data.php     # Fake data generator
├── database.sql               # Database schema
├── login.php                  # Login page
├── register.php               # Registration page
├── index.php                  # Landing page (redirect)
│
├── patient_dashboard.php      # Patient dashboard
├── doctor_dashboard.php       # Doctor dashboard
├── secretary_dashboard.php    # Secretary dashboard
├── admin_dashboard.php        # Admin dashboard
│
├── doctors.php                # Doctor listing & search
├── appointments.php           # Appointment management
├── book_appointment.php       # Book new appointment
├── reviews.php                # Write/view reviews
├── profile.php                # User profile
├── history.php                # Activity history
│
├── api/
│   ├── logout.php             # Logout endpoint
│   ├── update_appointment.php # Update appointment status
│
├── uploads/                   # User profile photos
└── header.php                 # Shared navigation component
```

## Key Features Overview

### Admin Dashboard
- System-wide statistics and analytics
- User management
- Doctor and specialty management
- Appointment approval workflow
- Revenue and performance metrics

### Secretary Dashboard
- Appointment management and approval
- Patient queue viewing
- Schedule assignments
- Notification center

### Doctor Dashboard
- Today's appointments
- Patient information
- Schedule management
- Review and rating tracking
- Performance metrics

### Patient Dashboard
- Upcoming appointments
- Doctor discovery and booking
- Appointment history
- Review management
- Activity tracking

## Database Tables

### Core Tables
- `users` - All system users (admin, secretary, doctor, patient)
- `doctors` - Doctor profiles and specializations
- `patients` - Patient medical information
- `secretaries` - Secretary assignments

### Appointment Management
- `appointments` - Booking records
- `schedules` - Doctor working schedules
- `cancellations` - Cancellation history

### Reviews & Ratings
- `reviews` - Patient ratings and comments

### System
- `notifications` - User notifications
- `activity_logs` - User activity audit trail

## Fake Data Included

The generator creates realistic data for testing:
- **20 Doctors** with realistic names, specializations, and ratings
- **50 Patients** with medical profiles
- **100 Appointments** with various statuses
- **50 Reviews** with ratings and comments
- **Specializations**: Cardiologist, Pediatrician, Neurologist, Dermatologist, Orthopedic, Dentist, Psychiatrist, ENT, and more

## Security Features

- Password hashing with bcrypt
- Prepared statements for SQL injection prevention
- Session management with role-based access control
- Activity logging and audit trails
- Secure password requirements (minimum 8 characters)
- Input validation and sanitization

## API Endpoints

### Authentication
- `POST /login.php` - User login
- `POST /register.php` - New user registration
- `GET /api/logout.php` - User logout

### Appointments
- `POST /api/update_appointment.php` - Update appointment status
- `GET /appointments.php` - View appointments

### Doctors
- `GET /doctors.php` - List doctors with filters
- `GET /book_appointment.php` - Booking interface

## Customization

### Change Hospital Name
Edit `config.php`:
```php
define('SITE_NAME', 'Your Hospital Name');
```

### Modify Color Scheme
Edit CSS in dashboard files:
```css
background: linear-gradient(135deg, #YOUR_COLOR_1 0%, #YOUR_COLOR_2 100%);
```

### Add More Specializations
Update `database.sql` and `generate_fake_data.php`:
```php
$specializations = ['Your Specialization', ...];
```

## Troubleshooting

### Database Connection Error
1. Ensure MySQL is running in XAMPP
2. Check `config.php` has correct credentials
3. Verify database user permissions

### Login Not Working
1. Check if fake data was generated
2. Verify password using phpMyAdmin
3. Clear browser cookies and try again

### Missing Styles/Images
1. Verify file permissions (755 for directories)
2. Clear browser cache (Ctrl+Shift+Delete)
3. Check Bootstrap CDN is accessible

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, Bootstrap 5, CSS3
- **Charts**: Chart.js
- **Icons**: Font Awesome 6
- **Authentication**: PHP Sessions, Bcrypt hashing

## Performance Tips

- Enable MySQL query caching
- Implement pagination for large datasets
- Use database indexes (already set up)
- Minify CSS and JavaScript for production
- Implement caching for dashboard statistics

## Development

### Adding a New Page
1. Create PHP file with authentication check
2. Include `config.php` and `functions.php`
3. Check user role with `hasRole()`
4. Use existing helper functions for data

Example:
```php
<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn() || !hasRole('patient')) {
    header('Location: login.php');
    exit;
}

// Your page code here
?>
```

## Future Enhancements

- SMS/Email notifications
- Video consultation support
- Prescription management
- Insurance integration
- Mobile app (React Native/Flutter)
- Advanced reporting
- Multi-language support
- Two-factor authentication

## Support

For issues or feature requests, please contact the development team.

## License

This project is provided as-is for educational and commercial use.

---

**Version**: 1.0.0  
**Last Updated**: May 2026  
**Status**: Production Ready
