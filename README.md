# University Assignment & Quiz Portal

A clean PHP/MySQL application for managing student assignments and quizzes.

## Features

- Admin-created student and teacher accounts
- Role-based dashboard
- Teachers can create assignments and quizzes
- Students can submit assignments and take quizzes
- Quiz grading and submission tracking

## Setup

1. Copy the `university-portal` folder into your web server root, e.g. `C:\xampp\htdocs\university-portal`
2. Import `db/schema.sql` into MySQL if you want to set up the database manually
3. Adjust `includes/config.php` if your database user, password or URL differ
4. Start Apache and MySQL
5. Open the site at:

    `http://localhost/university-portal/public/index.php`

## Notes

- Student and teacher accounts must be created by an admin from the Admin Panel.
- Public registration is disabled; users should contact the admin to get access.
- Admin can create users using `admin_create_user.php` or via the Admin Panel.
- If the database has no users yet, the app will create a default admin account on first load with:
  - Email: `admin@university-portal.test`
  - Password: `Admin@123`
- Uploaded assignment files are stored in `uploads/`
- The database schema includes sample user and content tables
- Use the teacher role to add assignments and quizzes
