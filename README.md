# Smart Queue Management System

A modern, web-based digital queue management system for organizations, built with PHP and MySQL. It streamlines customer service, reduces wait times, and provides real-time queue analytics for both customers and administrators.

---

## Features

- **Customer Portal**
	- Join a queue and generate a ticket
	- View real-time queue status and your position
	- Check ticket status and estimated wait time
	- Appointment date and time selection

- **Admin Panel**
	- Secure login for staff/admins
	- Dashboard with live queue statistics
	- Manage and call next tickets
	- Mark tickets as completed or skipped
	- View detailed queue and service reports

- **Display Screen**
	- Public-facing digital display for “Now Serving” and “Next in Queue”
	- Auto-refresh and real-time updates

- **Reports & Analytics**
	- Daily and per-service ticket statistics
	- Average wait times and peak hour analysis

- **Responsive UI**
	- Clean, mobile-friendly design using Bootstrap 5

---

## Folder Structure

- `/admin` – Admin dashboard, login, queue management, reports
- `/customer` – Customer-facing ticket generation, status, and display
- `/assets` – CSS and JavaScript assets
- `/includes` – Shared PHP includes (DB connection, auth, helpers)
- `/tools` – Utility scripts (e.g., strip comments)
- `config.php` – Main configuration and constants

---

## Requirements

- PHP 7.4+
- MySQL/MariaDB
- Web server (Apache recommended)
- Composer (optional, for dependency management)

---

## Setup Instructions

1. **Clone or Download the Repository**

2. **Database Setup**
	 - Create a MySQL database (default: `queue_system_db`)
	 - Import the required tables (see below for a sample schema)
	 - Update DB credentials in `config.php` if needed

3. **Configure Web Server**
	 - Place the project in your web root (e.g., `htdocs` for XAMPP)
	 - Ensure `mod_rewrite` is enabled for clean URLs (optional)

4. **Set File Permissions**
	 - Ensure PHP can write to necessary directories if you add file uploads or logs

5. **Access the System**
	 - Customer portal: `/customer/index.php`
	 - Admin login: `/admin/login.php`
	 - Display screen: `/customer/display.php`

---

## Sample Database Schema

You’ll need tables like `users`, `services`, and `queue_tickets`. Here’s a minimal example for `queue_tickets`:

```sql
CREATE TABLE `queue_tickets` (
	`id` INT AUTO_INCREMENT PRIMARY KEY,
	`ticket_number` VARCHAR(32) NOT NULL,
	`service_id` INT NOT NULL,
	`customer_name` VARCHAR(100),
	`customer_phone` VARCHAR(20),
	`appointment_date` DATE,
	`appointment_time` TIME,
	`status` ENUM('waiting','serving','completed','skipped','cancelled') DEFAULT 'waiting',
	`queue_position` INT,
	`estimated_wait_time` INT,
	`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
	`served_at` DATETIME,
	`completed_at` DATETIME
);
```

You’ll also need a `users` table for admin authentication and a `services` table for service definitions.

---

## Customization

- Edit `config.php` to change site name, ticket prefix, and other constants.
- Modify styles in `assets/css/style.css` for branding.
- Add or edit services in the database.

---

## Security Notes

- Change default admin credentials after setup.
- Use HTTPS in production.
- Sanitize all user inputs (already handled in most scripts).

---

## License

This project is provided as-is for educational and organizational use.
